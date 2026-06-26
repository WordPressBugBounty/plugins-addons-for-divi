<?php

namespace DiviTorqueLite;

use DiviTorqueLite\ModulesManager;
use DiviTorqueLite\AdminHelper;

class Dashboard
{

    private static $instance;
    private $menu_slug = 'divitorque';
    private $capability = 'manage_options';

    public static function get_instance()
    {
        if (!isset(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function __construct()
    {
        add_action('admin_menu', [$this, 'add_menu']);
        add_action('admin_enqueue_scripts', [$this, 'admin_enqueue_scripts'], 100);
    }

    public function add_menu()
    {
        if (!current_user_can($this->capability) || AdminHelper::is_pro_installed()) {
            return;
        }

        // SVG icon (base64 encoded)
        $icon_svg = 'data:image/svg+xml;base64,' . base64_encode('<svg width="20" height="20" viewBox="0 0 500 500" fill="none" xmlns="http://www.w3.org/2000/svg">
<path d="M250 0C388.071 0 500 111.929 500 250C500 388.071 388.071 500 250 500C111.929 500 0 388.071 0 250C0 111.929 111.929 0 250 0ZM248.109 110C219.952 110 194.004 118.155 171.92 131.747C147.076 147.514 127.2 170.349 116.158 197.533C114.502 202.426 111.741 210.582 110.085 216.562C109.533 219.281 111.741 221.999 114.502 222.543C117.815 223.087 120.575 221.456 121.127 218.737C121.679 216.563 123.335 212.215 123.336 211.67C124.44 208.951 127.2 207.32 129.961 208.407C132.169 209.495 133.825 211.67 133.825 213.845C133.273 214.388 133.273 214.388 133.273 214.932C131.065 223.087 128.857 232.873 128.305 242.116C127.753 244.834 129.961 247.553 133.273 247.553C136.034 248.096 138.794 245.378 138.794 242.66C138.794 242.116 139.347 237.222 139.347 236.679C141.003 226.893 143.764 217.107 147.628 207.864C148.18 206.233 149.284 205.146 149.836 203.515C150.94 201.34 154.253 200.252 157.014 201.34H157.565C160.326 202.427 161.43 205.146 160.326 207.864C159.774 208.409 156.461 214.389 154.253 223.631C153.149 226.349 155.358 229.612 158.67 230.155C161.43 230.699 163.639 229.068 164.743 226.35C166.951 219.282 170.263 212.214 170.815 211.126C178.545 195.903 190.691 183.398 205.598 175.242C217.744 168.174 232.099 164.369 247.558 164.369C295.59 164.369 334.237 202.427 334.237 249.728C334.237 293.222 301.663 328.562 259.704 334.543V241.028C259.704 225.805 247.557 213.845 232.099 213.845C216.64 213.845 204.494 225.805 204.494 241.028V362.815C204.494 378.038 216.64 390 232.099 390H247.558C325.955 390 390 327.475 390 250.271C390 173.068 326.507 110 248.109 110Z" fill="#a7aaad"/>
</svg>
');

        add_menu_page(
            __('Divi Torque', 'divitorque'),
            __('Divi Torque', 'divitorque'),
            $this->capability,
            $this->menu_slug,
            [$this, 'render_app'],
            $icon_svg,
            130
        );

        add_submenu_page(
            $this->menu_slug,
            __('Dashboard', 'divitorque'),
            __('Dashboard', 'divitorque'),
            $this->capability,
            $this->menu_slug,
            array($this, 'render_app')
        );

        add_submenu_page(
            $this->menu_slug,
            __('Modules', 'divitorque'),
            __('Modules', 'divitorque'),
            $this->capability,
            "{$this->menu_slug}&path=module-manager",
            [$this, 'render_app']
        );

        // add_submenu_page(
        //     $this->menu_slug,
        //     __('Free vs Pro', 'divitorque'),
        //     __('Free vs Pro', 'divitorque'),
        //     $this->capability,
        //     "{$this->menu_slug}&path=free-vs-pro",
        //     [$this, 'render_app']
        // );
    }

    public function render_app()
    {
        $this->enqueue_scripts();
        echo '<div id="divitorque-root"></div>';
    }

    public function enqueue_scripts()
    {
        $manifest_path = DIVI_TORQUE_LITE_DIR . 'assets/mix-manifest.json';
        if (!file_exists($manifest_path)) {
            return;
        }

        $manifest = json_decode(file_get_contents($manifest_path), true);
        if (!$manifest) {
            return;
        }

        $assets_url = DIVI_TORQUE_LITE_URL . 'assets';
        $dashboard_js = $assets_url . $manifest['/admin/js/dashboard.js'];
        $dashboard_css = $assets_url . $manifest['/admin/css/dashboard.css'];

        wp_enqueue_script(
            'divi-torque-lite-dashboard',
            $dashboard_js,
            $this->wp_deps(),
            DIVI_TORQUE_LITE_VERSION,
            true
        );

        wp_enqueue_style(
            'divi-torque-lite-dashboard',
            $dashboard_css,
            ['wp-components'],
            DIVI_TORQUE_LITE_VERSION
        );

        $localize = [
            'root'              => esc_url_raw(get_rest_url()),
            'admin_slug'        => $this->menu_slug,
            'nonce'             => wp_create_nonce('wp_rest'),
            'assetsPath'        => esc_url_raw($assets_url),
            'version'           => DIVI_TORQUE_LITE_VERSION,
            'module_info'       => ModulesManager::get_all_modules(),
            'pro_module_info'   => ModulesManager::get_all_pro_modules(),
            'module_icon_path'  => DIVI_TORQUE_LITE_URL . 'assets/imgs/icons',
            'isProInstalled'    => AdminHelper::is_pro_installed(),
            'upgradeLink'       => 'https://divitorque.com/pricing-lifetime/?utm_source=divi-torque-lite&utm_medium=wp-admin&utm_campaign=upgrade-to-pro&utm_content=menu-button',
            'currentVersion'   => DIVI_TORQUE_LITE_VERSION,
        ];

        wp_localize_script('divi-torque-lite-dashboard', 'diviTorqueLite', $localize);
    }

    public function admin_enqueue_scripts()
    {
        wp_enqueue_style(
            'divi-torque-lite-admin',
            DIVI_TORQUE_LITE_URL . 'assets/admin/css/admin.css',
            [],
            DIVI_TORQUE_LITE_VERSION
        );
    }

    public function wp_deps()
    {
        return [
            'react',
            'wp-api',
            'wp-i18n',
            'lodash',
            'wp-components',
            'wp-element',
            'wp-api-fetch',
            'wp-core-data',
            'wp-data',
            'wp-dom-ready',
        ];
    }

    private function icon_url()
    {
        return 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAiIGhlaWdodD0iMjAiIHZpZXdCb3g9IjAgMCA1MDAgNTAwIiBmaWxsPSJub25lIiB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciPjxwYXRoIGQ9Ik0yNTAgMEMzODguMDcxIDAgNTAwIDExMS45MjkgNTAwIDI1MEM1MDAgMzg4LjA3MSAzODguMDcxIDUwMCAyNTAgNTAwQzExMS45MjkgNTAwIDAgMzg4LjA3MSAwIDI1MEMwIDExMS45MjkgMTExLjkyOSAwIDI1MCAwWk0yNDguMTA5IDExMEMyMTkuOTUyIDExMCAxOTQuMDA0IDExOC4xNTUgMTcxLjkyIDEzMS43NDdDMTQ3LjA3NiAxNDcuNTE0IDEyNy4yIDE3MC4zNDkgMTE2LjE1OCAxOTcuNTMzQzExNC41MDIgMjAyLjQyNiAxMTEuNzQxIDIxMC41ODIgMTEwLjA4NSAyMTYuNTYyQzEwOS41MzMgMjE5LjI4MSAxMTEuNzQxIDIyMS45OTkgMTE0LjUwMiAyMjIuNTQzQzExNy44MTUgMjIzLjA4NyAxMjAuNTc1IDIyMS40NTYgMTIxLjEyNyAyMTguNzM3QzEyMS42NzkgMjE2LjU2MyAxMjMuMzM1IDIxMi4yMTUgMTIzLjMzNiAyMTEuNjdDMTI0LjQ0IDIwOC45NTEgMTI3LjIgMjA3LjMyIDEyOS45NjEgMjA4LjQwN0MxMzIuMTY5IDIwOS40OTUgMTMzLjgyNSAyMTEuNjcgMTMzLjgyNSAyMTMuODQ1QzEzMy4yNzMgMjE0LjM4OCAxMzMuMjczIDIxNC4zODggMTMzLjI3MyAyMTQuOTMyQzEzMS4wNjUgMjIzLjA4NyAxMjguODU3IDIzMi44NzMgMTI4LjMwNSAyNDIuMTE2QzEyNy43NTMgMjQ0LjgzNCAxMjkuOTYxIDI0Ny41NTMgMTMzLjI3MyAyNDcuNTUzQzEzNi4wMzQgMjQ4LjA5NiAxMzguNzk0IDI0NS4zNzggMTM4Ljc5NCAyNDIuNjZDMTM4Ljc5NCAyNDIuMTE2IDEzOS4zNDcgMjM3LjIyMiAxMzkuMzQ3IDIzNi42NzlDMTQxLjAwMyAyMjYuODkzIDE0My43NjQgMjE3LjEwNyAxNDcuNjI4IDIwNy44NjRDMTQ4LjE4IDIwNi4yMzMgMTQ5LjI4NCAyMDUuMTQ2IDE0OS44MzYgMjAzLjUxNUMxNTAuOTQgMjAxLjM0IDE1NC4yNTMgMjAwLjI1MiAxNTcuMDE0IDIwMS4zNEgxNTcuNTY1QzE2MC4zMjYgMjAyLjQyNyAxNjEuNDMgMjA1LjE0NiAxNjAuMzI2IDIwNy44NjRDMTU5Ljc3NCAyMDguNDA5IDE1Ni40NjEgMjE0LjM4OSAxNTQuMjUzIDIyMy42MzFDMTUzLjE0OSAyMjYuMzQ5IDE1NS4zNTggMjI5LjYxMiAxNTguNjcgMjMwLjE1NUMxNjEuNDMgMjMwLjY5OSAxNjMuNjM5IDIyOS4wNjggMTY0Ljc0MyAyMjYuMzVDMTY2Ljk1MSAyMTkuMjgyIDE3MC4yNjMgMjEyLjIxNCAxNzAuODE1IDIxMS4xMjZDMTc4LjU0NSAxOTUuOTAzIDE5MC42OTEgMTgzLjM5OCAyMDUuNTk4IDE3NS4yNDJDMjE3Ljc0NCAxNjguMTc0IDIzMi4wOTkgMTY0LjM2OSAyNDcuNTU4IDE2NC4zNjlDMjk1LjU5IDE2NC4zNjkgMzM0LjIzNyAyMDIuNDI3IDMzNC4yMzcgMjQ5LjcyOEMzMzQuMjM3IDI5My4yMjIgMzAxLjY2MyAzMjguNTYyIDI1OS43MDQgMzM0LjU0M1YyNDEuMDI4QzI1OS43MDQgMjI1LjgwNSAyNDcuNTU3IDIxMy44NDUgMjMyLjA5OSAyMTMuODQ1QzIxNi42NCAyMTMuODQ1IDIwNC40OTQgMjI1LjgwNSAyMDQuNDk0IDI0MS4wMjhWMzYyLjgxNUMyMDQuNDk0IDM3OC4wMzggMjE2LjY0IDM5MCAyMzIuMDk5IDM5MEgyNDcuNTU4QzMyNS45NTUgMzkwIDM5MCAzMjcuNDc1IDM5MCAyNTAuMjcxQzM5MCAxNzMuMDY4IDMyNi41MDcgMTEwIDI0OC4xMDkgMTEwWiIgZmlsbD0iI2E3YWFhZCIvPjwvc3ZnPg==';
    }
}
