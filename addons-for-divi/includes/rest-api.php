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
            // Post query for D5 dynamic modules (NewsTicker, …) — live VB
            // preview only. The front end and saved layout render server-side
            // in the module's RenderCallbackTrait.
            '/posts' => [
                'methods' => \WP_REST_Server::CREATABLE,
                'callback' => [$this, 'get_posts'],
                'permission_callback' => function () {
                    return current_user_can('edit_posts');
                },
            ],
            // Richer post query for the Post Carousel VB preview (returns
            // { posts: [ { …, thumbnail, excerpt, author{}, categories[] } ] }).
            // The front end renders server-side in the module's
            // RenderCallbackTrait; this is VB preview only.
            '/carousel-posts' => [
                'methods' => \WP_REST_Server::CREATABLE,
                'callback' => [$this, 'get_carousel_posts'],
                'permission_callback' => function () {
                    return current_user_can('edit_posts');
                },
            ],
            // Saved layouts / pages / posts list for the Modal Popup layout
            // picker (VB only). The chosen item renders server-side in the
            // module's RenderCallbackTrait::render_saved_layout().
            '/layouts' => [
                'methods' => \WP_REST_Server::READABLE,
                'callback' => [$this, 'get_layouts'],
                'permission_callback' => function () {
                    return current_user_can('edit_posts');
                },
            ],
        ];

        foreach ($routes as $route => $args) {
            register_rest_route($this->namespace, $route, $args);
        }
    }

    /**
     * Return published Divi Library layouts, pages and posts for the Modal
     * Popup layout picker. Layouts are listed first (the primary use case).
     *
     * @return \WP_REST_Response List of { id, title, type }.
     */
    public function get_layouts()
    {
        $items = [];

        $groups = [
            ['type' => 'et_pb_layout', 'label' => 'Layout'],
            ['type' => 'page', 'label' => 'Page'],
            ['type' => 'post', 'label' => 'Post'],
        ];

        foreach ($groups as $group) {
            if (!post_type_exists($group['type'])) {
                continue;
            }
            $posts = get_posts(
                [
                    'post_type'      => $group['type'],
                    'post_status'    => 'publish',
                    'posts_per_page' => 100,
                    'orderby'        => 'title',
                    'order'          => 'ASC',
                    'suppress_filters' => true,
                ]
            );
            foreach ($posts as $post) {
                $items[] = [
                    'id'    => $post->ID,
                    'title' => $post->post_title !== '' ? $post->post_title : sprintf('#%d', $post->ID),
                    'type'  => $group['label'],
                ];
            }
        }

        return new \WP_REST_Response($items, 200);
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

    /**
     * Query posts for D5 dynamic modules' Visual Builder preview.
     *
     * Returns a lightweight list ({ id, title, permalink, date }). Mirrors the
     * WP_Query built in the modules' RenderCallbackTrait so the preview matches
     * the rendered output.
     *
     * @param WP_REST_Request $request REST request.
     * @return WP_REST_Response
     */
    public function get_posts(WP_REST_Request $request)
    {
        $post_type  = sanitize_text_field((string) $request->get_param('post_type'));
        $post_type  = '' !== $post_type ? $post_type : 'post';
        $categories = sanitize_text_field((string) $request->get_param('categories'));
        $order_by   = sanitize_text_field((string) $request->get_param('order_by'));
        $order_by   = '' !== $order_by ? $order_by : 'date';
        $order      = sanitize_text_field((string) $request->get_param('order'));
        $order      = '' !== $order ? $order : 'ASC';
        $post_count = absint($request->get_param('post_count'));
        $post_count = $post_count > 0 ? $post_count : 5;
        $offset     = absint($request->get_param('offset'));
        $exclude    = sanitize_text_field((string) $request->get_param('exclude_posts'));
        $excerpt_l  = absint($request->get_param('excerpt_length'));
        $excerpt_l  = $excerpt_l > 0 ? $excerpt_l : 150;
        $only_image = sanitize_text_field((string) $request->get_param('only_with_image'));

        $query_args = [
            'posts_per_page' => $post_count,
            'post_type'      => $post_type,
            'post_status'    => 'publish',
            'orderby'        => $order_by,
            'order'          => $order,
            'offset'         => $offset,
        ];

        if ('post' === $post_type && '' !== $categories) {
            $query_args['cat'] = $categories;
        }

        if ('on' === $only_image) {
            $query_args['meta_key'] = '_thumbnail_id';
        }

        if ('' !== $exclude) {
            $ids = array_filter(array_map('absint', array_map('trim', explode(',', $exclude))));
            if (!empty($ids)) {
                $query_args['post__not_in'] = $ids;
            }
        }

        $query = new \WP_Query($query_args);
        $items = [];

        foreach ($query->posts as $post) {
            $items[] = [
                'id'        => $post->ID,
                'title'     => get_the_title($post),
                'permalink' => get_permalink($post),
                'date'      => get_the_date('', $post),
                'author'    => get_the_author_meta('display_name', $post->post_author),
                'thumbnail' => get_the_post_thumbnail_url($post, 'full') ?: '',
                'excerpt'   => mb_strimwidth(wp_strip_all_tags(get_the_excerpt($post)), 0, $excerpt_l, '...'),
            ];
        }

        wp_reset_postdata();

        return new WP_REST_Response($items, 200);
    }

    /**
     * Richer post query for the Post Carousel VB preview. Returns
     * { posts: [ { id, title, excerpt, permalink, date, thumbnail,
     * author{id,name,avatar,link}, categories[{id,name,link}] } ], total,
     * total_pages }. Mirrors the WP_Query built in the module's
     * RenderCallbackTrait so the live preview matches the rendered output.
     *
     * @param WP_REST_Request $request REST request.
     * @return WP_REST_Response
     */
    public function get_carousel_posts(WP_REST_Request $request)
    {
        $post_type      = sanitize_text_field((string) $request->get_param('post_type')) ?: 'post';
        $categories     = sanitize_text_field((string) $request->get_param('categories'));
        $taxonomy       = sanitize_text_field((string) $request->get_param('taxonomy'));
        $taxonomy_terms = sanitize_text_field((string) $request->get_param('taxonomy_terms'));
        $order_by       = sanitize_text_field((string) $request->get_param('order_by')) ?: 'date';
        $order          = sanitize_text_field((string) $request->get_param('order')) ?: 'DESC';
        $post_count     = absint($request->get_param('post_count')) ?: 6;
        $offset         = absint($request->get_param('offset'));
        $include_posts  = sanitize_text_field((string) $request->get_param('include_posts'));
        $exclude_posts  = sanitize_text_field((string) $request->get_param('exclude_posts'));
        $date_format    = sanitize_text_field((string) $request->get_param('date_format')) ?: 'M d, Y';
        $content_length = absint($request->get_param('content_length')) ?: 150;
        $thumb_size     = sanitize_text_field((string) $request->get_param('thumb_size')) ?: 'full';

        $query_args = [
            'posts_per_page' => (int) $post_count,
            'post_type'      => $post_type,
            'post_status'    => ['publish'],
            'orderby'        => $order_by,
            'order'          => $order,
            'offset'         => (int) $offset,
        ];

        // Category filter — comma-separated IDs OR slugs.
        if ('' !== $categories) {
            $terms = array_filter(array_map('trim', explode(',', $categories)));
            if (!empty($terms)) {
                if (ctype_digit((string) $terms[0])) {
                    $query_args['category__in'] = array_map('intval', $terms);
                } else {
                    $query_args['category_name'] = implode(',', $terms);
                }
            }
        }

        // Custom taxonomy filter.
        if ('' !== $taxonomy && '' !== $taxonomy_terms) {
            $terms = array_filter(array_map('trim', explode(',', $taxonomy_terms)));
            if (!empty($terms)) {
                $field = ctype_digit((string) $terms[0]) ? 'term_id' : 'slug';
                $query_args['tax_query'][] = [
                    'taxonomy' => $taxonomy,
                    'field'    => $field,
                    'terms'    => $terms,
                ];
            }
        }

        if ('' !== $include_posts) {
            $query_args['post__in'] = array_map('absint', array_filter(explode(',', $include_posts)));
        }
        if ('' !== $exclude_posts) {
            $query_args['post__not_in'] = array_map('absint', array_filter(explode(',', $exclude_posts)));
        }

        $query = new \WP_Query($query_args);
        $posts = [];

        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();

                $post_id   = get_the_ID();
                $thumb_id  = get_post_thumbnail_id($post_id);
                $thumb_url = '';
                if ($thumb_id) {
                    $thumb_data = wp_get_attachment_image_src($thumb_id, $thumb_size);
                    $thumb_url  = $thumb_data ? $thumb_data[0] : '';
                }

                $excerpt = get_the_excerpt();
                if (empty($excerpt)) {
                    $excerpt = get_the_content();
                }
                $excerpt = wp_strip_all_tags(strip_shortcodes($excerpt));
                if ($content_length > 0 && mb_strlen($excerpt) > $content_length) {
                    $excerpt = mb_substr($excerpt, 0, $content_length) . '...';
                }

                $categories_list = [];
                $terms_list      = get_the_terms($post_id, 'category');
                if ($terms_list && !is_wp_error($terms_list)) {
                    foreach ($terms_list as $term) {
                        $categories_list[] = [
                            'id'   => $term->term_id,
                            'name' => $term->name,
                            'link' => get_term_link($term),
                        ];
                    }
                }

                $author_id = get_the_author_meta('ID');

                $posts[] = [
                    'id'         => $post_id,
                    'title'      => get_the_title(),
                    'excerpt'    => $excerpt,
                    'permalink'  => get_the_permalink(),
                    'date'       => get_the_date($date_format),
                    'thumbnail'  => $thumb_url,
                    'author'     => [
                        'id'     => $author_id,
                        'name'   => get_the_author(),
                        'avatar' => get_avatar_url($author_id, ['size' => 52]),
                        'link'   => get_author_posts_url($author_id),
                    ],
                    'categories' => $categories_list,
                ];
            }
        }

        wp_reset_postdata();

        return new WP_REST_Response(
            [
                'posts'       => $posts,
                'total'       => $query->found_posts,
                'total_pages' => $query->max_num_pages,
            ],
            200
        );
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
