<?php

namespace DiviTorqueLite;

use WP_REST_Request;

/**
 * The Snapback card on Lite's own Overview (admin/src/components/SnapbackCard.jsx).
 * Snapback is PlugPress's backup and recovery plugin.
 *
 * WordPress.org guideline 11: it shows on Lite's own screen only, never in an
 * admin notice. Nothing here calls a remote server; the only link out is the
 * UTM-tagged "See how it works" button. Each user can hide it for good.
 */
class Snapback_Card
{
    const HIDDEN_META = 'divitorque_lite_hide_snapback';

    /** The half-price line shows until 2027-01-01T07:59:59Z (Dec 31, 23:59:59 in California). */
    const OFFER_ENDS = 1798790399;

    const URL = 'https://snapbackwp.com/?utm_source=addons-for-divi&utm_medium=plugin-card&utm_campaign=bf26';

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
        register_rest_route('divitorque-lite/v1', '/snapback_card', [
            'methods'             => 'POST',
            'callback'            => [$this, 'handle'],
            'permission_callback' => function () {
                return current_user_can('manage_options');
            },
            'args'                => [
                'action' => [
                    'required'          => true,
                    'type'              => 'string',
                    'enum'              => ['hide'],
                    'validate_callback' => 'rest_validate_request_arg',
                ],
            ],
        ]);
    }

    /**
     * Boot data for the dashboard app.
     *
     * @param int|null $now Unix time to test against; defaults to now.
     */
    public static function boot_data($now = null)
    {
        $now    = null === $now ? time() : (int) $now;
        $active = defined('SNAPBACK_PRO_FILE');

        return [
            'hidden'  => (bool) get_user_meta(get_current_user_id(), self::HIDDEN_META, true),
            'active'  => $active,
            'offer'   => $now <= self::OFFER_ENDS,
            'url'     => self::URL,
            'openUrl' => $active ? admin_url('admin.php?page=snapback') : '',
        ];
    }

    public function handle(WP_REST_Request $request)
    {
        update_user_meta(get_current_user_id(), self::HIDDEN_META, 1);
        return rest_ensure_response(['hidden' => true]);
    }
}
