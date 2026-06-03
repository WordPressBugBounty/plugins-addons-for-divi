<?php

namespace DiviTorqueLite;

use DiviTorqueLite\AdminHelper;
use DiviTorqueLite\AssetsManager;
use DiviTorqueLite\RestApi;
use DiviTorqueLite\Dashboard;
use DiviTorqueLite\ModulesManager;
use DiviTorqueLite\Deprecated;
use DiviTorqueLite\Divi_Library_Shortcode;

class PluginLoader
{
    /**
     * Holds the single instance of this class
     * @var PluginLoader
     */
    private static $instance;

    /**
     * Returns the singleton instance of this class
     * @return PluginLoader
     */
    public static function get_instance()
    {
        if (!isset(self::$instance) && !(self::$instance instanceof self)) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Constructor - Sets up the initial hooks
     */
    public function __construct()
    {
        register_activation_hook(DIVI_TORQUE_LITE_FILE, array($this, 'activation'));
        add_action('init', array($this, 'load_textdomain'));
        add_action('plugins_loaded', array($this, 'hooks_init'));
    }

    /**
     * Initializes plugin hooks and components
     */
    public function hooks_init()
    {
        add_action('divi_extensions_init', array($this, 'init_extension'));
        add_filter('plugin_action_links_' . plugin_basename(DIVI_TORQUE_LITE_FILE), array($this, 'add_pro_link'));
        add_action('admin_menu', array($this, 'register_rollback_page'), 999);

        AssetsManager::get_instance();
        RestApi::get_instance();
        Dashboard::get_instance();

        if (!get_option('divitorque_version')) {
            Divi_Library_Shortcode::get_instance();
        }

        if (get_option('divitorque_version') && version_compare(get_option('divitorque_version'), '3.5.7', '<=')) {
            require_once DIVI_TORQUE_LITE_DIR . 'includes/deprecated.php';
        }
    }

    /**
     * Handles plugin activation tasks
     */
    public function activation()
    {
        // Deprecated related
        if (get_option('divitorque_version') && version_compare(get_option('divitorque_version'), '3.5.7', '<=')) {
            require_once DIVI_TORQUE_LITE_DIR . 'includes/deprecated.php';
            $deprecated = new Deprecated();
            $deprecated->run();
        }

        // Activation Timestamp
        if (!get_option('divitorque_lite_activation_time')) {
            update_option('divitorque_lite_activation_time', time());
        }

        // Install Date
        if (!get_option('divitorque_lite_install_date')) {
            update_option('divitorque_lite_install_date', time());
        }

        // Set the version
        update_option('divitorque_lite_version', DIVI_TORQUE_LITE_VERSION);

        self::init();
    }

    /**
     * Initializes default module settings
     */
    public static function init()
    {
        $module_status = get_option('_divitorque_lite_modules', array());
        $modules = AdminHelper::get_modules();

        if (empty($module_status)) {
            foreach ($modules as $module) {
                $module_status[$module] = $module;
            }

            update_option('_divitorque_lite_modules', $module_status);
        }
    }

    /**
     * Loads plugin text domain for translations
     * Moved to init hook for WordPress 6.7.0 compatibility
     */
    public function load_textdomain()
    {
        load_plugin_textdomain('addons-for-divi', false, dirname(plugin_basename(DIVI_TORQUE_LITE_FILE)) . '/languages');
    }

    /**
     * Initializes Divi extension
     */
    public function init_extension()
    {
        ModulesManager::get_instance();
    }

    /**
     * Adds pro version link to plugin actions
     * @param array $links Existing plugin action links
     * @return array Modified plugin action links
     */
    public function add_pro_link($links)
    {
        if (defined('DIVI_TORQUE_PRO_VERSION')) {
            return $links;
        }

        $links[] = sprintf(
            '<a href="%s" target="_blank">%s</a>',
            esc_url_raw(self::get_url()),
            __('Dashboard', 'divitorque')
        );

        if (current_user_can('update_plugins')) {
            $links[] = sprintf(
                '<a href="%s">%s</a>',
                esc_url(admin_url('admin.php?page=divitorque-rollback')),
                __('Rollback', 'divitorque')
            );
        }

        return $links;
    }

    public function register_rollback_page()
    {
        add_submenu_page(
            'divitorque',
            __('Rollback Divi Torque Lite', 'divitorque'),
            __('Rollback', 'divitorque'),
            'update_plugins',
            'divitorque-rollback',
            array($this, 'render_rollback_page')
        );
        remove_submenu_page('divitorque', 'divitorque-rollback');
    }

    public function render_rollback_page()
    {
        if (!current_user_can('update_plugins')) {
            wp_die(__('You do not have permission to access this page.', 'divitorque'));
        }

        $rest_root  = esc_url_raw(rest_url('divitorque-lite/v1'));
        $rest_nonce = wp_create_nonce('wp_rest');
        $current    = DIVI_TORQUE_LITE_VERSION;
        ?>
        <div class="wrap" style="max-width:560px;">
            <h1><?php esc_html_e('Rollback Divi Torque Lite', 'divitorque'); ?></h1>
            <p><?php printf(esc_html__('Currently installed: %s', 'divitorque'), '<code>' . esc_html($current) . '</code>'); ?></p>

            <p class="description" style="margin:12px 0;">
                <?php esc_html_e('Choose a previous version published on WordPress.org. Your active state will be preserved.', 'divitorque'); ?>
            </p>

            <p>
                <label for="dtl-rollback-version"><strong><?php esc_html_e('Version', 'divitorque'); ?></strong></label><br>
                <select id="dtl-rollback-version" style="min-width:240px;margin-top:6px;">
                    <option value=""><?php esc_html_e('Loading versions…', 'divitorque'); ?></option>
                </select>
            </p>

            <p>
                <button type="button" class="button button-primary" id="dtl-rollback-btn" disabled>
                    <?php esc_html_e('Rollback', 'divitorque'); ?>
                </button>
                <span id="dtl-rollback-status" style="margin-left:12px;"></span>
            </p>

            <script>
            (function(){
                var REST  = <?php echo wp_json_encode($rest_root); ?>;
                var NONCE = <?php echo wp_json_encode($rest_nonce); ?>;
                var CURRENT = <?php echo wp_json_encode($current); ?>;
                var $sel = document.getElementById('dtl-rollback-version');
                var $btn = document.getElementById('dtl-rollback-btn');
                var $status = document.getElementById('dtl-rollback-status');

                function setStatus(msg, isError) {
                    $status.textContent = msg || '';
                    $status.style.color = isError ? '#b32d2e' : '#1d2327';
                }

                fetch(REST + '/get_plugin_versions', {
                    headers: { 'X-WP-Nonce': NONCE }
                }).then(function(r){ return r.json(); }).then(function(data){
                    if (!data || !data.versions) { setStatus('Could not load versions.', true); return; }
                    $sel.innerHTML = '';
                    data.versions.forEach(function(v){
                        if (v === CURRENT) return;
                        var opt = document.createElement('option');
                        opt.value = v;
                        opt.textContent = v;
                        $sel.appendChild(opt);
                    });
                    if ($sel.options.length === 0) {
                        $sel.innerHTML = '<option value="">' + 'No other versions available' + '</option>';
                    } else {
                        $btn.disabled = false;
                    }
                }).catch(function(){
                    setStatus('Could not load versions.', true);
                });

                $btn.addEventListener('click', function(){
                    var version = $sel.value;
                    if (!version) return;
                    if (!confirm('Rollback Divi Torque Lite to version ' + version + '?')) return;

                    $btn.disabled = true;
                    setStatus('Rolling back to ' + version + '…');

                    fetch(REST + '/rollback_plugin', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-WP-Nonce': NONCE
                        },
                        body: JSON.stringify({ version: version })
                    }).then(function(r){ return r.json().then(function(j){ return { ok: r.ok, body: j }; }); })
                    .then(function(res){
                        if (res.ok && res.body && res.body.success) {
                            setStatus('Done. Reloading…');
                            setTimeout(function(){ window.location.reload(); }, 1200);
                        } else {
                            var msg = (res.body && (res.body.message || res.body.code)) || 'Rollback failed.';
                            setStatus(msg, true);
                            $btn.disabled = false;
                        }
                    }).catch(function(){
                        setStatus('Rollback failed.', true);
                        $btn.disabled = false;
                    });
                });
            })();
            </script>
        </div>
        <?php
    }

    /**
     * Returns the plugin dashboard URL
     * @return string Dashboard URL
     */
    public static function get_url()
    {
        return admin_url('admin.php?page=divitorque');
    }
}

PluginLoader::get_instance();
