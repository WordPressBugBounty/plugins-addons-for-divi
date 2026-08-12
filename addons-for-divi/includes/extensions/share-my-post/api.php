<?php
/**
 * Share My Post: REST routes.
 *
 * Registered unconditionally — see the note in Share_My_Post::__construct().
 *
 * @package DiviTorqueLite
 * @since   4.9.0
 */

namespace DiviTorqueLite;

if (!defined('ABSPATH')) {
    exit;
}

class Share_My_Post_Api
{
    /** @var Share_My_Post_Api|null */
    private static $instance = null;

    /** @var string */
    private $namespace = 'divitorque-lite/v1';

    /**
     * @return Share_My_Post_Api
     */
    public static function get_instance()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    private function __construct()
    {
        add_action('rest_api_init', array($this, 'register_routes'));
    }

    /**
     * @return void
     */
    public function register_routes()
    {
        register_rest_route($this->namespace, '/share_my_post_settings', array(
            array(
                'methods'             => \WP_REST_Server::READABLE,
                'callback'            => array($this, 'get_settings'),
                'permission_callback' => array($this, 'permissions_check'),
            ),
            array(
                'methods'             => \WP_REST_Server::EDITABLE,
                'callback'            => array($this, 'save_settings'),
                'permission_callback' => array($this, 'permissions_check'),
                'args'                => array(
                    'settings' => array(
                        'required' => true,
                        'type'     => 'object',
                    ),
                ),
            ),
        ));
    }

    /**
     * These settings change what every visitor sees on every post, so the bar is
     * manage_options rather than edit_posts.
     *
     * @return bool
     */
    public function permissions_check()
    {
        return current_user_can('manage_options');
    }

    /**
     * @return \WP_REST_Response
     */
    public function get_settings()
    {
        return rest_ensure_response(array(
            'settings' => Share_My_Post_Settings::get(),
        ));
    }

    /**
     * @param \WP_REST_Request $request Request.
     *
     * @return \WP_REST_Response
     */
    public function save_settings($request)
    {
        $settings = $request->get_param('settings');

        // Sanitised inside update(), which is also what runs for a WP-CLI or
        // filter-driven write — the REST layer is not the only door.
        return rest_ensure_response(array(
            'settings' => Share_My_Post_Settings::update(is_array($settings) ? $settings : array()),
        ));
    }
}
