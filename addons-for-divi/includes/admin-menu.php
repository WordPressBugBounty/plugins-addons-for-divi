<?php

namespace DiviTorqueLite;

class AdminMenu
{
    private $main_menu_slug = 'diviepic-plugins';
    private $capability = 'manage_options';
    private $api_url = 'https://diviepic.com/wp-json/';
    private $api_namespace = 'diviepic/v1';

    private static $instance;

    public static function get_instance()
    {
        if (!isset(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function __construct()
    {
        add_action('admin_menu', array($this, 'ensure_main_menu'));
        add_action('admin_enqueue_scripts', array($this, 'dashboard_enqueue_scripts'));
    }

    public function ensure_main_menu()
    {
        if (!$this->is_menu_exists()) {
            add_menu_page(
                __('DiviEpic', 'divitorque'),
                __('DiviEpic', 'divitorque'),
                $this->capability,
                $this->main_menu_slug,
                array($this, 'main_menu_page'),
                $this->icon_url(),
                130
            );

            add_submenu_page(
                $this->main_menu_slug,
                __('DiviEpic', 'divitorque'),
                __('Dashboard', 'divitorque'),
                $this->capability,
                $this->main_menu_slug,
                array($this, 'main_menu_page')
            );
        }
    }

    public function add_submenu($page_title, $menu_title, $menu_slug, $callback, $position)
    {
        add_submenu_page(
            $this->main_menu_slug,
            $page_title,
            $menu_title,
            $this->capability,
            $menu_slug,
            $callback,
            $position
        );
    }

    private function is_menu_exists()
    {
        global $menu;
        foreach ($menu as $item) {
            if ($item[2] === $this->main_menu_slug) {
                return true;
            }
        }
        return false;
    }

    public function main_menu_page()
    {
        $this->enqueue_scripts();
        echo '<div id="diviepic-root"></div>';
    }

    public function contact_menu_page()
    {
        $this->contact_enqueue_scripts();
        echo '<div id="diviepic-contact"></div>';
    }

    public function plugin_list()
    {
        if ($transient = get_transient('diviepic_plugins')) {
            return $transient;
        }

        $response = wp_remote_get($this->api_url . $this->api_namespace . '/plugins');
        if (is_wp_error($response) || !($body = wp_remote_retrieve_body($response)) || !($json = json_decode($body, true))) {
            return [];
        }

        set_transient('diviepic_plugins', $json, DAY_IN_SECONDS);
        return $json;
    }

    public function enqueue_scripts()
    {

        if (!file_exists(DIVI_TORQUE_LITE_DIR . 'assets/mix-manifest.json')) {
            return;
        }

        $manifest_json = file_get_contents(DIVI_TORQUE_LITE_DIR . 'assets/mix-manifest.json');
        $manifest = json_decode($manifest_json, true);

        if (!$manifest) {
            return;
        }

        wp_enqueue_script(
            'diviepic-admin',
            DIVI_TORQUE_LITE_URL . 'assets' . $manifest['/admin/js/admin.js'],
            array(
                'react', 'wp-api', 'wp-i18n', 'lodash', 'wp-components', 'wp-element', 'wp-api-fetch',
                'wp-core-data', 'wp-data', 'wp-dom-ready',
            ),
            null,
            true
        );

        wp_enqueue_style(
            'diviepic-admin',
            DIVI_TORQUE_LITE_URL . 'assets' . $manifest['/admin/css/admin.css'],
            array(),
            null
        );

        wp_enqueue_style(
            'diviepic-fs-override',
            DIVI_TORQUE_LITE_URL . 'assets' . $manifest['/admin/css/fs-override.css'],
            array(),
            null
        );

        $localize = [
            'root' => esc_url_raw(get_rest_url()),
            'ajax_url' => admin_url('admin-ajax.php'),
            '_ajax_nonce' => wp_create_nonce('updates'),
            'plugins' => $this->plugin_list(),

        ];

        wp_localize_script('diviepic-admin', 'diviEpic', $localize);
    }

    public function dashboard_enqueue_scripts()
    {
        if (!file_exists(DIVI_TORQUE_LITE_DIR . 'assets/mix-manifest.json')) {
            return;
        }

        $manifest_json = file_get_contents(DIVI_TORQUE_LITE_DIR . 'assets/mix-manifest.json');
        $manifest = json_decode($manifest_json, true);

        if (!$manifest) {
            return;
        }

        wp_enqueue_script(
            'diviepic-dashboard',
            DIVI_TORQUE_LITE_URL . 'assets' . $manifest['/admin/js/dashboard.js'],
            array(),
            null,
            true
        );

        wp_enqueue_style(
            'diviepic-dashboard',
            DIVI_TORQUE_LITE_URL . 'assets' . $manifest['/admin/css/dashboard.css'],
            array(),
            null
        );
    }

    public function contact_enqueue_scripts()
    {
        if (!file_exists(DIVI_TORQUE_LITE_DIR . 'assets/mix-manifest.json')) {
            return;
        }

        $manifest_json = file_get_contents(DIVI_TORQUE_LITE_DIR . 'assets/mix-manifest.json');
        $manifest = json_decode($manifest_json, true);

        if (!$manifest) {
            return;
        }

        wp_enqueue_script(
            'contact-us',
            DIVI_TORQUE_LITE_URL . 'assets' . $manifest['/admin/js/contact-us.js'],
            array(),
            null,
            true
        );

        wp_enqueue_style(
            'diviepic-contact-us',
            DIVI_TORQUE_LITE_URL . 'assets' . $manifest['/admin/css/contact-us.css'],
            array(),
            null
        );
    }

    private function icon_url()
    {
        return 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjQiIGhlaWdodD0iMjQiIHZpZXdCb3g9IjAgMCAyNCAyNCIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPHBhdGggZD0iTTExLjYyMDUgMjMuMTYyOEMzLjcyMzEzIDIzLjE2MjggLTIuMzA4MzcgMTUuMTE4NSAxLjIwNjM5IDYuNzY5ODFDMi4yNDc4IDQuMjQ3ODEgNC4yNDM4NCAyLjI0NzYgNi43NjA1OCAxLjIwNDAyQzE1LjA5MTkgLTIuMzE4MDkgMjMuMTYyOCAzLjc2OTUgMjMuMTYyOCAxMS42ODM0QzIzLjE2MjggMTIuOTg3OCAyMi45ODkyIDE0LjExODQgMjIuNTExOSAxNS40NjY0QzIyLjUxMTkgMTUuNDY2NCAyMi4yMDgyIDE2LjM3OTUgMjEuNjAwNyAxNy40MjMxSDExLjY2MzlWMTEuNjgzNEgxNy4zOTE3QzE3LjM5MTcgNy41MDkwMiAxMi45NjU3IDQuMzc4MjYgOC41ODMwNSA2LjcyNjMzQzcuODAyIDcuMTYxMTUgNy4xMDc3MiA3LjgxMzQgNi42NzM4IDguNjM5NTdDNC4zMzA2MiAxMy4wMzEzIDcuNDU0ODYgMTcuNDY2NiAxMS42MjA1IDE3LjQ2NjZWMjMuMTYyOFoiIGZpbGw9IiMxRjFGMUYiLz4KPC9zdmc+Cg==';
    }
}

AdminMenu::get_instance();
