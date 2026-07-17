<?php

namespace DiviTorqueLite;

use DiviTorqueLite\ModulesManager;
use DiviTorqueLite\AdminHelper;

class Dashboard
{

    private static $instance;
    private $menu_slug = 'divitorque';
    private $capability = 'manage_options';

    /**
     * Dashboard UI mode. 'v2' (default) is the @plugpress/ui app under
     * admin/; 'legacy' is the v1 SPA built by Mix into assets/admin. Legacy
     * ships unchanged until its removal release.
     */
    const UI_OPTION = 'divitorque_lite_dashboard_ui';

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
        add_action('rest_api_init', [$this, 'register_switch_route']);
        add_action('admin_init', [$this, 'handle_switch_link']);
        add_filter('admin_body_class', [$this, 'body_class']);

        if ($this->is_v2()) {
            add_action('admin_notices', [$this, 'begin_notice_capture'], -9999);
            add_action('admin_notices', [$this, 'end_notice_capture'], 9999);
        }
    }

    public function is_v2()
    {
        return get_option(self::UI_OPTION, 'v2') !== 'legacy';
    }

    private function is_our_screen()
    {
        return isset($_GET['page']) && $_GET['page'] === $this->menu_slug && !AdminHelper::is_pro_installed(); // phpcs:ignore WordPress.Security.NonceVerification
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

    }

    public function render_app()
    {
        if ($this->is_v2()) {
            $this->enqueue_v2();
            echo '<div id="divitorque-root"></div>';
            return;
        }

        $this->render_try_v2_banner();
        $this->enqueue_scripts();
        echo '<div id="divitorque-root"></div>';
    }

    /* ─── Dashboard v2 ─────────────────────────────────────────────── */

    private function enqueue_v2()
    {
        $base = DIVI_TORQUE_LITE_URL . 'admin/build/';
        $dir  = DIVI_TORQUE_LITE_DIR . 'admin/build/';

        // Version by file mtime so a rebuilt bundle always busts the browser/page
        // cache, even within the same plugin version. A static ?ver lets a stale
        // cached bundle load against a fresh runtime.
        $css_ver = is_readable($dir . 'index.css') ? (string) filemtime($dir . 'index.css') : DIVI_TORQUE_LITE_VERSION;
        $js_ver  = is_readable($dir . 'index.js') ? (string) filemtime($dir . 'index.js') : DIVI_TORQUE_LITE_VERSION;

        wp_enqueue_style('divi-torque-lite-admin-v2', $base . 'index.css', [], $css_ver);

        wp_enqueue_script(
            'divi-torque-lite-admin-v2',
            $base . 'index.js',
            ['wp-api-fetch', 'wp-i18n'],
            $js_ver,
            true
        );

        wp_add_inline_script(
            'divi-torque-lite-admin-v2',
            'window.divitorqueData = ' . wp_json_encode($this->get_v2_data()) . ';',
            'before'
        );
    }

    private function get_v2_data()
    {
        return [
            'root'        => esc_url_raw(get_rest_url()),
            'nonce'       => wp_create_nonce('wp_rest'),
            'ns'          => 'divitorque-lite/v1',
            'version'     => DIVI_TORQUE_LITE_VERSION,
            'adminUrl'    => esc_url_raw(admin_url()),
            'docsUrl'     => 'https://divitorque.com/docs/',
            'upgradeUrl'  => 'https://divitorque.com/pricing/?utm_source=divi-torque-lite&utm_medium=wp-admin&utm_campaign=upgrade-to-pro',
            'rollbackUrl' => esc_url_raw(admin_url('admin.php?page=divitorque-rollback')),
            'moduleInfo'  => ModulesManager::get_all_modules(),
        ];
    }

    public function body_class($classes)
    {
        if ($this->is_v2() && $this->is_our_screen()) {
            $classes .= ' pp-scope';
        }
        return $classes;
    }

    public function begin_notice_capture()
    {
        if (!$this->is_our_screen()) {
            return;
        }
        ob_start();
    }

    public function end_notice_capture()
    {
        if (!$this->is_our_screen()) {
            return;
        }
        $html = ob_get_clean();
        echo '<div id="dtl-foreign-notices" style="display:none">' . $html . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput
    }

    /* ─── v1 ⇄ v2 switching ────────────────────────────────────────── */

    public function register_switch_route()
    {
        register_rest_route('divitorque-lite/v1', '/switch_dashboard_ui', [
            'methods'             => 'POST',
            'callback'            => function ($request) {
                $ui = $request->get_param('ui') === 'legacy' ? 'legacy' : 'v2';
                update_option(self::UI_OPTION, $ui);
                return rest_ensure_response(['ui' => $ui]);
            },
            'permission_callback' => function () {
                return current_user_can($this->capability);
            },
        ]);
    }

    /** Nonce link on the legacy banner: admin.php?page=divitorque&dtl_ui=v2 */
    public function handle_switch_link()
    {
        if (!isset($_GET['dtl_ui']) || !$this->is_our_screen()) {
            return;
        }
        if (!current_user_can($this->capability)) {
            return;
        }
        check_admin_referer('dtl_switch_ui');

        update_option(self::UI_OPTION, $_GET['dtl_ui'] === 'legacy' ? 'legacy' : 'v2');
        wp_safe_redirect(admin_url('admin.php?page=' . $this->menu_slug));
        exit;
    }

    private function render_try_v2_banner()
    {
        $url = wp_nonce_url(admin_url('admin.php?page=' . $this->menu_slug . '&dtl_ui=v2'), 'dtl_switch_ui');
        ?>
        <div class="dtl-try-v2" style="margin:16px 20px 0 0;padding:12px 16px;display:flex;align-items:center;gap:12px;background:#fff;border:1px solid #dcdcde;border-left:4px solid #3979ff;border-radius:4px;">
            <span style="flex:1;">
                <strong><?php esc_html_e('The new Divi Torque dashboard is here.', 'divitorque'); ?></strong>
                <?php esc_html_e('Cleaner, faster, and where new features land first. This legacy dashboard will be retired in an upcoming release.', 'divitorque'); ?>
            </span>
            <a class="button button-primary" href="<?php echo esc_url($url); ?>">
                <?php esc_html_e('Try the new dashboard', 'divitorque'); ?>
            </a>
        </div>
        <?php
    }

    /* ─── Legacy v1 enqueue — unchanged ────────────────────────────── */

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
}
