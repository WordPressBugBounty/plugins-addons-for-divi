<?php

namespace DiviTorqueLite;

use DiviTorqueLite\AdminHelper;
use WP_REST_Request;
use WP_REST_Response;
use WP_Error;
use Automatic_Upgrader_Skin;
use Plugin_Upgrader;

/**
 * Class RestApi
 * Handles all REST API endpoints for the DiviTorque Lite plugin
 * 
 * @package DiviTorqueLite
 * @since 1.0.0
 */
class RestApi
{
    /** @var RestApi Single instance of this class */
    private static $instance;

    /** @var string REST API namespace */
    private $namespace = 'divitorque-lite/v1';

    /**
     * Get singleton instance of RestApi class
     * 
     * @return RestApi Instance of this class
     */
    public static function get_instance()
    {
        if (!isset(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor
     * Initializes output buffering and registers REST routes
     */
    private function __construct()
    {
        ob_start();
        add_action('rest_api_init', [$this, 'register_routes']);
    }

    /**
     * Register all REST API routes
     * Defines endpoints, methods, callbacks and permissions
     */
    public function register_routes()
    {
        $routes = [
            '/get_common_settings' => [
                'methods' => \WP_REST_Server::READABLE,
                'callback' => [$this, 'get_common_settings'],
                'permission_callback' => [$this, 'get_permissions_check'],
            ],
            '/save_common_settings' => [
                'methods' => \WP_REST_Server::EDITABLE,
                'callback' => [$this, 'save_common_settings'],
                'permission_callback' => [$this, 'get_permissions_check'],
            ],
            '/check_plugin_installed_and_active' => [
                'methods' => \WP_REST_Server::EDITABLE,
                'callback' => [$this, 'check_plugin_installed_and_active'],
                'permission_callback' => [$this, 'get_permissions_check'],
            ],
            '/activate_plugin' => [
                'methods' => \WP_REST_Server::EDITABLE,
                'callback' => [$this, 'activate_plugin'],
                'permission_callback' => function () {
                    return current_user_can('activate_plugins');
                },
                'args' => $this->get_plugin_args(),
            ],
            '/install_plugin' => [
                'methods' => \WP_REST_Server::EDITABLE,
                'callback' => [$this, 'install_plugin'],
                'permission_callback' => function () {
                    return current_user_can('install_plugins');
                },
                'args' => [
                    'slug' => [
                        'required' => true,
                        'validate_callback' => [$this, 'validate_string_param'],
                    ],
                ],
            ],
            '/get_plugin_versions' => [
                'methods' => \WP_REST_Server::READABLE,
                'callback' => [$this, 'get_plugin_versions'],
                'permission_callback' => function () {
                    return current_user_can('update_plugins');
                },
            ],
            '/rollback_plugin' => [
                'methods' => \WP_REST_Server::EDITABLE,
                'callback' => [$this, 'rollback_plugin'],
                'permission_callback' => function () {
                    return current_user_can('update_plugins');
                },
                'args' => [
                    'version' => [
                        'required' => true,
                        'validate_callback' => [$this, 'validate_string_param'],
                    ],
                ],
            ],
        ];

        foreach ($routes as $route => $args) {
            register_rest_route($this->namespace, $route, $args);
        }
    }

    /**
     * Get common arguments for plugin-related endpoints
     * 
     * @return array Array of argument definitions
     */
    private function get_plugin_args()
    {
        return [
            'slug' => [
                'required' => true,
                'validate_callback' => [$this, 'validate_string_param'],
            ],
            'plugin_file' => [
                'required' => true,
                'validate_callback' => [$this, 'validate_string_param'],
            ],
        ];
    }

    public function validate_string_param($param)
    {
        return is_string($param);
    }


    public function get_permissions_check()
    {
        if (!current_user_can('manage_options')) {
            return new \WP_Error(
                'rest_forbidden',
                esc_html__('You cannot view the templates resource.'),
                ['status' => $this->authorization_status_code()]
            );
        }
        return true;
    }

    private function authorization_status_code()
    {
        return is_user_logged_in() ? 403 : 401;
    }

    public function get_common_settings()
    {
        return AdminHelper::get_options();
    }

    public function save_common_settings(WP_REST_Request $request)
    {
        $modules = $request->get_param('modules_settings');
        update_option('_divitorque_lite_modules', $modules);
        return ['success' => true];
    }

    public function check_plugin_installed_and_active(WP_REST_Request $request)
    {
        $slug = $request->get_param('slug');
        $plugin_file = $request->get_param('plugin_file');
        $plugin_path = $slug . '/' . $plugin_file;

        return new WP_REST_Response([
            'installed' => file_exists(WP_PLUGIN_DIR . '/' . $plugin_path),
            'active' => is_plugin_active($plugin_path)
        ], 200);
    }

    public function activate_plugin(WP_REST_Request $request)
    {
        if (!current_user_can('activate_plugins')) {
            return new WP_Error(
                'insufficient_permissions',
                'You do not have permission to activate plugins.',
                ['status' => 403]
            );
        }

        $plugin_slug = $request->get_param('slug');
        $plugin_file = $request->get_param('plugin_file');
        $activate = activate_plugin("{$plugin_slug}/{$plugin_file}");

        if (is_wp_error($activate)) {
            return $activate;
        }

        return [
            'success' => true,
            'message' => "Plugin activated successfully"
        ];
    }

    public function install_plugin(WP_REST_Request $request)
    {
        $slug = sanitize_key(wp_unslash($request->get_param('slug')));

        if (empty($slug)) {
            return new WP_Error(
                'no_plugin_specified',
                __('No plugin specified.'),
                ['status' => 400]
            );
        }

        if (!current_user_can('install_plugins')) {
            return new WP_Error(
                'insufficient_permissions',
                __('Sorry, you are not allowed to install plugins on this site.'),
                ['status' => 403]
            );
        }

        require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
        require_once ABSPATH . 'wp-admin/includes/plugin-install.php';

        $api = plugins_api(
            'plugin_information',
            [
                'slug' => $slug,
                'fields' => ['sections' => false],
            ]
        );

        if (is_wp_error($api)) {
            return new WP_Error(
                'plugin_api_error',
                $api->get_error_message(),
                ['status' => 500]
            );
        }

        // Install the plugin
        $upgrader = new Plugin_Upgrader(new Automatic_Upgrader_Skin());
        $install = $upgrader->install($api->download_link);

        if (is_wp_error($install)) {
            return new WP_Error(
                'plugin_install_error',
                $install->get_error_message(),
                ['status' => 500]
            );
        }

        return [
            'success' => true,
            'message' => "Plugin installed successfully"
        ];
    }

    public function get_plugin_versions()
    {
        require_once ABSPATH . 'wp-admin/includes/plugin-install.php';

        $api = plugins_api(
            'plugin_information',
            [
                'slug'   => 'addons-for-divi',
                'fields' => ['versions' => true, 'sections' => false],
            ]
        );

        if (is_wp_error($api)) {
            return new WP_Error('plugin_api_error', $api->get_error_message(), ['status' => 500]);
        }

        $versions = isset($api->versions) && is_array($api->versions) ? array_keys($api->versions) : [];
        $versions = array_filter($versions, function ($v) {
            return $v !== 'trunk' && version_compare($v, '0.0.0', '>');
        });
        usort($versions, 'version_compare');
        $versions = array_reverse($versions);

        return new WP_REST_Response([
            'current'  => DIVI_TORQUE_LITE_VERSION,
            'versions' => array_values($versions),
        ], 200);
    }

    public function rollback_plugin(WP_REST_Request $request)
    {
        $version = sanitize_text_field(wp_unslash($request->get_param('version')));

        if (empty($version) || !preg_match('/^[0-9][0-9a-zA-Z\.\-]*$/', $version)) {
            return new WP_Error('invalid_version', __('Invalid version specified.', 'addons-for-divi'), ['status' => 400]);
        }

        if ($version === DIVI_TORQUE_LITE_VERSION) {
            return new WP_Error('same_version', __('That version is already installed.', 'addons-for-divi'), ['status' => 400]);
        }

        require_once ABSPATH . 'wp-admin/includes/file.php';
        require_once ABSPATH . 'wp-admin/includes/misc.php';
        require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';

        $package = sprintf('https://downloads.wordpress.org/plugin/addons-for-divi.%s.zip', $version);
        $plugin_basename = plugin_basename(DIVI_TORQUE_LITE_FILE);
        $was_active = is_plugin_active($plugin_basename);

        $upgrader = new Plugin_Upgrader(new Automatic_Upgrader_Skin());
        $result = $upgrader->install($package, ['overwrite_package' => true, 'clear_destination' => true]);

        if (is_wp_error($result)) {
            return new WP_Error('rollback_failed', $result->get_error_message(), ['status' => 500]);
        }

        if (!$result) {
            return new WP_Error('rollback_failed', __('Rollback failed.', 'addons-for-divi'), ['status' => 500]);
        }

        if ($was_active) {
            activate_plugin($plugin_basename);
        }

        return [
            'success' => true,
            'message' => sprintf(__('Rolled back to %s.', 'addons-for-divi'), $version),
            'version' => $version,
        ];
    }
}
