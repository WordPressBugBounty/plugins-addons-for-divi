<?php

namespace DiviTorqueLite;

use DiviTorqueLite\AdminHelper;
use DiviTorqueLite\AssetsManager;
use DiviTorqueLite\RestApi;
use DiviTorqueLite\Dashboard;
use DiviTorqueLite\ModulesManager;
use DiviTorqueLite\Review;

use DiviTorqueLite\Deprecated;

use DiviTorqueLite\Divi_Library_Shortcode;

class PluginLoader
{
    private static $instance;

    public static function get_instance()
    {
        if (!isset(self::$instance) && !(self::$instance instanceof self)) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function __construct()
    {
        register_activation_hook(DIVI_TORQUE_LITE_FILE, array($this, 'activation'));
        add_action('plugins_loaded', array($this, 'hooks_init'));
        add_action('init', array($this, 'load_textdomain'));
    }

    public function hooks_init()
    {
        add_action('divi_extensions_init', array($this, 'init_extension'));
        add_filter('plugin_action_links_' . plugin_basename(DIVI_TORQUE_LITE_FILE), array($this, 'add_pro_link'));

        AssetsManager::get_instance();
        RestApi::get_instance();
        Dashboard::get_instance();

        if (!get_option('divitorque_version')) {
            Divi_Library_Shortcode::get_instance();
        }

        Review::get_instance(array(
            'plugin_name'   => 'DiviTorque Lite',
            'review_url'    => 'https://wordpress.org/support/plugin/addons-for-divi/reviews/#new-post',
            'image_url'     => plugins_url('assets/imgs/icon.png', __FILE__),
            'cookie_name'   => 'dtl_review_notice_shown',
            'option_name'   => 'dtl_review_notice_shown',
            'nonce_action'  => 'dtl-dismiss-review',
            'screen_bases'  => array('dashboard', 'plugins', 'toplevel_page_divitorque')
        ));

        if (get_option('divitorque_version') && version_compare(get_option('divitorque_version'), '3.5.7', '<=')) {
            require_once DIVI_TORQUE_LITE_DIR . 'includes/deprecated.php';
        }
    }

    public function activation()
    {

        // Deprecated related
        if (get_option('divitorque_version') && version_compare(get_option('divitorque_version'), '3.5.7', '<=')) {
            require_once DIVI_TORQUE_LITE_DIR . 'includes/deprecated.php';
            $deprecated = new Deprecated();
            $deprecated->run();
        }

        // Activation Timestamp
        if (!get_option('divitorque_activation_time')) {
            update_option('divitorque_activation_time', time());
        }

        // Set the version
        update_option('divitorque_lite_version', DIVI_TORQUE_LITE_VERSION);

        self::init();
    }

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

    public function load_textdomain()
    {
        load_plugin_textdomain('addons-for-divi', false, dirname(plugin_basename(__FILE__)) . '/languages');
    }

    public function init_extension()
    {
        ModulesManager::get_instance();
    }

    public function add_pro_link($links)
    {
        if (defined('DIVI_TORQUE_PRO_VERSION')) {
            return $links;
        }

        $links[] = sprintf(
            '<a href="%s" target="_blank">%s</a>',
            esc_url_raw(self::get_url()),
            __('Dashboard', 'addons-for-divi')
        );

        return $links;
    }

    public static function get_url()
    {
        return admin_url('admin.php?page=divitorque');
    }
}

PluginLoader::get_instance();
