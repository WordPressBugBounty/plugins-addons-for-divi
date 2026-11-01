<?php

namespace DiviTorqueLite;

use WP_Error;
use WP_REST_Request;

/**
 * Recommended plugins on Lite's own dashboard: Saddle and Saddle Analytics,
 * free on WordPress.org and made by the same team.
 *
 * Ground rules (WordPress.org guidelines 8 and 11): nothing installs until
 * the user clicks, packages come only from WordPress.org, and the offer
 * appears only on Lite's own screens, never in an admin notice. Requirements
 * are checked before anything downloads, and each user can hide the offer
 * for good.
 */
class Recommended_Plugins
{
    const HIDDEN_META = 'divitorque_lite_hide_recommended';

    /**
     * Only these slugs can be installed through this route. `wp`/`php` decide
     * what the card offers up front; the install path re-checks the live
     * WordPress.org values and activate_plugin() checks the headers.
     * `requires` mirrors Saddle Analytics' "Requires Plugins: saddle".
     */
    const PLUGINS = [
        'saddle'           => [
            'name'     => 'Saddle',
            'file'     => 'saddle/saddle.php',
            'page'     => 'saddle',
            'wp'       => '6.9',
            'php'      => '7.4',
            'requires' => '',
        ],
        'saddle-analytics' => [
            'name'     => 'Saddle Analytics',
            'file'     => 'saddle-analytics/saddle-analytics.php',
            'page'     => 'saddle-analytics',
            'wp'       => '6.9',
            'php'      => '8.1',
            'requires' => 'saddle',
        ],
    ];

    private static $instance;

    public static function get_instance()
    {
        if (!isset(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct()
    {
        add_action('rest_api_init', [$this, 'register_routes']);
    }

    public function register_routes()
    {
        register_rest_route('divitorque-lite/v1', '/recommended_plugins', [
            'methods'             => 'POST',
            'callback'            => [$this, 'handle'],
            'permission_callback' => function () {
                return current_user_can('manage_options');
            },
            // validate_callback is what makes WordPress enforce `enum`; without
            // it any slug reaches the handler.
            'args'                => [
                'action' => [
                    'required'          => true,
                    'type'              => 'string',
                    'enum'              => ['install', 'hide'],
                    'validate_callback' => 'rest_validate_request_arg',
                ],
                'slug'   => [
                    'type'              => 'string',
                    'enum'              => array_keys(self::PLUGINS),
                    'validate_callback' => 'rest_validate_request_arg',
                ],
            ],
        ]);
    }

    /** Boot data for the dashboard app. */
    public static function boot_data()
    {
        return [
            'hidden'      => (bool) get_user_meta(get_current_user_id(), self::HIDDEN_META, true),
            'canInstall'  => current_user_can('install_plugins'),
            'canActivate' => current_user_can('activate_plugins'),
            'plugins'     => self::state(),
        ];
    }

    /** Per slug: missing | inactive | active, its admin page, and why it cannot run here (or ''). */
    public static function state()
    {
        require_once ABSPATH . 'wp-admin/includes/plugin.php';

        $state = [];
        foreach (self::PLUGINS as $slug => $plugin) {
            if (is_plugin_active($plugin['file'])) {
                $status = 'active';
            } elseif (file_exists(WP_PLUGIN_DIR . '/' . $plugin['file'])) {
                $status = 'inactive';
            } else {
                $status = 'missing';
            }

            $blocked = self::blocked_reason($plugin['wp'], $plugin['php']);
            if ('' === $blocked && $plugin['requires']) {
                $parent  = self::PLUGINS[$plugin['requires']];
                $blocked = self::blocked_reason($parent['wp'], $parent['php']);
            }

            $state[$slug] = [
                'status'  => $status,
                'url'     => admin_url('admin.php?page=' . $plugin['page']),
                'blocked' => $blocked,
            ];
        }
        return $state;
    }

    /** '' when this site meets the minimums, otherwise a sentence saying what is missing. */
    private static function blocked_reason($wp, $php)
    {
        if ($php && !is_php_version_compatible($php)) {
            /* translators: 1: required PHP version, 2: this site's PHP version. */
            return sprintf(__('Needs PHP %1$s or newer. This site runs PHP %2$s.', 'addons-for-divi'), $php, PHP_VERSION);
        }
        if ($wp && !is_wp_version_compatible($wp)) {
            /* translators: 1: required WordPress version, 2: this site's WordPress version. */
            return sprintf(__('Needs WordPress %1$s or newer. This site runs WordPress %2$s.', 'addons-for-divi'), $wp, get_bloginfo('version'));
        }
        return '';
    }

    public function handle(WP_REST_Request $request)
    {
        if ('hide' === $request->get_param('action')) {
            update_user_meta(get_current_user_id(), self::HIDDEN_META, 1);
            return rest_ensure_response(['hidden' => true]);
        }

        $slug = $request->get_param('slug');
        if (!$slug || !isset(self::PLUGINS[$slug])) {
            return new WP_Error('dtl_no_plugin', __('No plugin specified.', 'addons-for-divi'), ['status' => 400]);
        }

        // Saddle first: Saddle Analytics will not activate without it.
        $chain = array_values(array_filter([self::PLUGINS[$slug]['requires'], $slug]));

        // Install everything before activating anything. WordPress reads the
        // plugins' "Requires Plugins" headers once per request, at the first
        // activation, so both must be on disk by then.
        foreach ($chain as $item) {
            $installed = $this->install($item);
            if (is_wp_error($installed)) {
                return $this->fail($installed);
            }
        }
        foreach ($chain as $item) {
            $activated = $this->activate($item);
            if (is_wp_error($activated)) {
                return $this->fail($activated);
            }
        }

        return rest_ensure_response(['plugins' => self::state()]);
    }

    /** A 400 the app can show as-is, with fresh state (Saddle may have activated before Analytics failed). */
    private function fail(WP_Error $error)
    {
        return new WP_Error($error->get_error_code(), wp_strip_all_tags($error->get_error_message()), [
            'status'  => 400,
            'plugins' => self::state(),
        ]);
    }

    private function install($slug)
    {
        $plugin = self::PLUGINS[$slug];
        if (file_exists(WP_PLUGIN_DIR . '/' . $plugin['file'])) {
            return true;
        }
        if (!current_user_can('install_plugins')) {
            /* translators: %s: plugin name. */
            return new WP_Error('dtl_cannot_install', sprintf(__('Your account cannot install plugins. Ask a site administrator to install %s.', 'addons-for-divi'), $plugin['name']));
        }

        // Same includes as core's REST plugins controller.
        require_once ABSPATH . 'wp-admin/includes/file.php';
        require_once ABSPATH . 'wp-admin/includes/plugin.php';
        require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
        require_once ABSPATH . 'wp-admin/includes/plugin-install.php';

        $api = plugins_api('plugin_information', [
            'slug'   => $slug,
            'fields' => ['sections' => false],
        ]);
        if (is_wp_error($api)) {
            return $api;
        }

        // The live WordPress.org minimums: never download a plugin that cannot run here.
        $blocked = self::blocked_reason($api->requires ?? '', $api->requires_php ?? '');
        if ('' !== $blocked) {
            return new WP_Error('dtl_incompatible', $plugin['name'] . ': ' . $blocked);
        }

        $skin     = new \WP_Ajax_Upgrader_Skin();
        $upgrader = new \Plugin_Upgrader($skin);
        $result   = $upgrader->install($api->download_link);

        if (is_wp_error($result)) {
            return $result;
        }
        if (is_wp_error($skin->result)) {
            return $skin->result;
        }
        if ($skin->get_errors()->has_errors()) {
            return $skin->get_errors();
        }
        if (null === $result) {
            // The filesystem needs FTP credentials, which this screen cannot ask for.
            return new WP_Error('dtl_fs_credentials', sprintf(
                /* translators: %s: plugin name. */
                __('WordPress needs your server login to install plugins. Install %s from Plugins > Add New instead.', 'addons-for-divi'),
                $plugin['name']
            ));
        }
        return true;
    }

    private function activate($slug)
    {
        $plugin = self::PLUGINS[$slug];
        if (is_plugin_active($plugin['file'])) {
            return true;
        }
        if (!current_user_can('activate_plugin', $plugin['file'])) {
            /* translators: %s: plugin name. */
            return new WP_Error('dtl_cannot_activate', sprintf(__('Your account cannot activate plugins. Ask a site administrator to activate %s.', 'addons-for-divi'), $plugin['name']));
        }

        $result = activate_plugin($plugin['file']);
        return is_wp_error($result) ? $result : true;
    }
}
