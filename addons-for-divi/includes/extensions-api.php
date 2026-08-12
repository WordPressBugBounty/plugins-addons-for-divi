<?php
/**
 * Extensions: REST routes.
 *
 * Deliberately the same shape Pro will expose at divitorque-pro/v1/extensions,
 * so the Extensions screen can move into the shared admin core as one
 * implementation rather than two that drifted.
 *
 * @package addons-for-divi
 */

namespace DiviTorqueLite;

if (!defined('ABSPATH')) {
    exit;
}

class Extensions_Api
{
    /** @var Extensions_Api|null */
    private static $instance = null;

    /** @var string */
    private $namespace = 'divitorque-lite/v1';

    /**
     * @return Extensions_Api
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
        register_rest_route($this->namespace, '/extensions', array(
            array(
                'methods'             => \WP_REST_Server::READABLE,
                'callback'            => array($this, 'get_extensions'),
                'permission_callback' => array($this, 'permissions_check'),
            ),
            array(
                'methods'             => \WP_REST_Server::EDITABLE,
                'callback'            => array($this, 'set_extension'),
                'permission_callback' => array($this, 'permissions_check'),
                'args'                => array(
                    'slug'   => array(
                        'required' => true,
                        'type'     => 'string',
                    ),
                    'active' => array(
                        'required' => true,
                        'type'     => 'boolean',
                    ),
                ),
            ),
        ));
    }

    /**
     * An extension changes what visitors see on the front end, so the bar is
     * manage_options — same as Sharing Buttons' own settings route.
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
    public function get_extensions()
    {
        return rest_ensure_response(array(
            'extensions' => Extensions_Manager::all(),
        ));
    }

    /**
     * Returns the whole list, not just the changed row: the client re-renders
     * from the response, so anything the manager refused or coerced shows up
     * immediately instead of leaving the UI asserting a state the server never
     * accepted.
     *
     * @param \WP_REST_Request $request Request.
     *
     * @return \WP_REST_Response|\WP_Error
     */
    public function set_extension($request)
    {
        $slug = sanitize_key($request->get_param('slug'));

        if (!Extensions_Manager::set_active($slug, (bool) $request->get_param('active'))) {
            return new \WP_Error(
                'divitorque_extension_not_toggleable',
                __('That extension cannot be switched on or off.', 'addons-for-divi'),
                array('status' => 400)
            );
        }

        return rest_ensure_response(array(
            'extensions' => Extensions_Manager::all(),
        ));
    }
}
