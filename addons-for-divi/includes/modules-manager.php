<?php

namespace DiviTorqueLite;

use DiviTorqueLite\AdminHelper;

/**
 * Modules Manager
 * 
 * Auto-discovers modules from directory and manages activation
 */
class ModulesManager
{
    private static $instance;
    private $modules_directory = '';
    private $discovered_modules = [];

    public static function get_instance()
    {
        if (!isset(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function __construct()
    {
        $this->modules_directory = __DIR__ . '/modules/divi-4';
        $this->discover_modules();

        add_action('et_builder_ready', [$this, 'load_modules'], 9);
    }

    /**
     * Auto-discover modules from directory
     */
    private function discover_modules()
    {
        if (!is_dir($this->modules_directory)) {
            return;
        }

        $items = scandir($this->modules_directory);

        foreach ($items as $item) {
            if ($item === '.' || $item === '..' || $item === 'index.js') {
                continue;
            }

            $path = $this->modules_directory . '/' . $item;

            if (!is_dir($path)) {
                continue;
            }

            // Skip child modules (end with 'Child')
            if (substr($item, -5) === 'Child') {
                continue;
            }

            // Check if module PHP file exists
            $module_file = $path . '/' . $item . '.php';
            if (!file_exists($module_file)) {
                continue;
            }

            // Convert directory name to module name (PascalCase to kebab-case)
            $module_name = $this->dir_to_name($item);

            // Check if child exists
            $child_dir = $item . 'Child';
            $has_child = is_dir($this->modules_directory . '/' . $child_dir);

            $this->discovered_modules[$module_name] = [
                'name' => $module_name,
                'title' => $this->dir_to_title($item),
                'directory' => $item,
                'has_child' => $has_child,
                'child_directory' => $has_child ? $child_dir : '',
            ];
        }

        // Native Divi 5-only free modules — no divi-4 directory, so the scan
        // above can't find them. Same toggle option gates them; the D5 loader
        // (includes/divi5/Modules.php) reads it too. Titles match the website
        // catalog (assets/module-catalog.json).
        $d5_only = [
            'accordion'         => 'Accordion Pro',
            'tabs'              => 'Tabs Pro',
            'modal-popup'       => 'Modal Popup',
            'post-carousel'     => 'Post Carousel',
            'table-of-contents' => 'Table of Contents',
            'fancy-text'        => 'Fancy Text',
            'faq'               => 'FAQ',
            'breadcrumbs'       => 'Breadcrumb Trail',
        ];
        foreach ($d5_only as $name => $title) {
            if (!isset($this->discovered_modules[$name])) {
                $this->discovered_modules[$name] = [
                    'name'            => $name,
                    'title'           => $title,
                    'directory'       => '',
                    'has_child'       => false,
                    'child_directory' => '',
                    'd5_only'         => true,
                ];
            }
        }

        // Website-catalog slug where it differs from the module name.
        if (isset($this->discovered_modules['contact-form7'])) {
            $this->discovered_modules['contact-form7']['catalog_slug'] = 'contact-form-7';
        }
    }

    /**
     * Convert directory name to module name (kebab-case)
     * IconBox -> icon-box
     */
    private function dir_to_name($dir)
    {
        return strtolower(preg_replace('/(?<!^)[A-Z]/', '-$0', $dir));
    }

    /**
     * Convert directory name to title
     * IconBox -> Icon Box
     */
    private function dir_to_title($dir)
    {
        // Canonical display names — mirror each module's registered $this->name so
        // the admin list matches the builder (and avoids core-Divi name clashes).
        $overrides = [
            'BusinessHour'        => 'Business Hours',
            'CompareImage'        => 'Before & After Slider',
            'ContactForm7'        => 'Contact Form 7',
            'Divider'             => 'Separator',
            'DualButton'          => 'Dual Buttons',
            'FlipBox'             => 'Flip Card',
            'InfoCard'            => 'Image Card',
            'InlineNotice'        => 'Alert Box',
            'NumberCounter'       => 'Animated Counter',
            'Review'              => 'Review Box',
            'TeamBox'             => 'Team Member',
            'Testimonial'         => 'Testimonial Card',
            'TwitterFeed'         => 'X (Twitter) Feed',
            'TwitterFeedCarousel' => 'X (Twitter) Carousel',
        ];
        if (isset($overrides[$dir])) {
            return $overrides[$dir];
        }
        return preg_replace('/(?<!^)([A-Z])/', ' $1', $dir);
    }

    /**
     * Get all discovered modules
     */
    public static function get_all_modules()
    {
        return array_values(self::get_instance()->discovered_modules);
    }

    /**
     * Get all pro modules (for dashboard)
     */
    public static function get_all_pro_modules()
    {
        return [];
    }

    /**
     * Load active modules
     */
    public function load_modules()
    {
        if (!class_exists(\ET_Builder_Element::class)) {
            return;
        }

        $saved_modules = AdminHelper::get_modules();

        foreach ($this->discovered_modules as $module_name => $module) {
            // Skip disabled modules. (The old `isset` check was a no-op:
            // AdminHelper::get_modules() merges defaults so every name is
            // always set — disabled modules kept loading in D4.)
            if (($saved_modules[$module_name] ?? $module_name) === 'disabled') {
                continue;
            }

            // Load parent module
            $this->load_module_file($module['directory']);

            // Load child module if exists
            if ($module['has_child']) {
                $this->load_module_file($module['child_directory']);
            }
        }
    }

    /**
     * Load single module file
     */
    private function load_module_file($directory)
    {
        $module_path = sprintf(
            '%s/%s/%s.php',
            $this->modules_directory,
            $directory,
            $directory
        );

        if (file_exists($module_path)) {
            require_once $module_path;
        }
    }

    /**
     * Check if module is active. (get_modules() merges defaults, so every
     * name is always set — the old isset() check was always true.)
     */
    public static function is_module_active($module_name)
    {
        $saved_modules = AdminHelper::get_modules();
        return ($saved_modules[$module_name] ?? $module_name) !== 'disabled';
    }

    /**
     * Get module by name
     */
    public static function get_module($module_name)
    {
        $modules = self::get_instance()->discovered_modules;
        return isset($modules[$module_name]) ? $modules[$module_name] : null;
    }
}
