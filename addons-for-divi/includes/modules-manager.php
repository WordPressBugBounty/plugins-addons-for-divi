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
            // Check if module is enabled in database
            if (!isset($saved_modules[$module_name])) {
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
     * Check if module is active
     */
    public static function is_module_active($module_name)
    {
        $saved_modules = AdminHelper::get_modules();
        return isset($saved_modules[$module_name]);
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
