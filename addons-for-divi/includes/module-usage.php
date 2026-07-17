<?php

namespace DiviTorqueLite;

/**
 * Module usage tracking — which pages use which Divi Torque module.
 * Lite variant of the Pro scanner (divi-torque-pro/includes/module-usage.php):
 * D4 shortcodes are ba_*, D5 blocks are wp:divitorque/*.
 */
class Module_Usage
{
    const TRANSIENT   = 'dtl_module_usage';
    const TTL         = HOUR_IN_SECONDS;
    const TIME_BUDGET = 5.0;
    const BATCH       = 200;
    const MAX_PAGES   = 20;

    /** D5 folder slug → Module Manager name (only where they differ). */
    const ALIASES = [
        'contact-form-7' => 'contact-form7',
    ];

    /** Post types the scanner ignores — mirrored by the flush guard. */
    const SKIP_TYPES = ['revision', 'attachment', 'nav_menu_item'];

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

        // Keep the cache honest between the hourly expiries: bust it whenever
        // builder content changes.
        add_action('save_post', [$this, 'maybe_flush'], 10, 2);
        add_action('trashed_post', [$this, 'flush_for_post'], 10, 1);
        add_action('untrashed_post', [$this, 'flush_for_post'], 10, 1);
        add_action('deleted_post', [$this, 'flush_for_post'], 10, 2);
    }

    /** save_post guard: skip revisions/autosaves, then flush on builder content. */
    public function maybe_flush($post_id, $post)
    {
        if (wp_is_post_revision($post_id) || wp_is_post_autosave($post_id)) {
            return;
        }
        $this->flush_if_builder_content($post);
    }

    /** trashed/untrashed/deleted — post object is passed on deleted_post only. */
    public function flush_for_post($post_id, $post = null)
    {
        $post = $post ?: get_post($post_id);
        if ($post) {
            $this->flush_if_builder_content($post);
        } else {
            // Post already gone and unknowable — a cheap delete beats a stale count.
            delete_transient(self::TRANSIENT);
        }
    }

    private function flush_if_builder_content($post)
    {
        if (!$post || in_array($post->post_type, self::SKIP_TYPES, true)) {
            return;
        }
        $content = (string) $post->post_content;

        // Scan markers now, or builder-enabled (covers "module just removed").
        $relevant = strpos($content, '[ba_') !== false
            || strpos($content, 'wp:divitorque') !== false
            || strpos($content, 'wp:divi/') !== false
            || get_post_meta($post->ID, '_et_pb_use_builder', true) === 'on';

        if ($relevant) {
            delete_transient(self::TRANSIENT);
        }
    }

    public function register_routes()
    {
        register_rest_route('divitorque-lite/v1', '/module_usage', [
            'methods'             => 'GET',
            'callback'            => [$this, 'get_usage'],
            'permission_callback' => function () {
                return current_user_can('manage_options');
            },
        ]);

        register_rest_route('divitorque-lite/v1', '/module_usage/rescan', [
            'methods'             => 'POST',
            'callback'            => function () {
                delete_transient(self::TRANSIENT);
                return $this->get_usage();
            },
            'permission_callback' => function () {
                return current_user_can('manage_options');
            },
        ]);
    }

    public function get_usage()
    {
        $state = get_transient(self::TRANSIENT);

        if (!is_array($state) || !isset($state['modules'])) {
            $state = [
                'scanned_at' => time(),
                'complete'   => false,
                'cursor'     => 0,
                'modules'    => [],
            ];
        }

        if (!$state['complete']) {
            $state = $this->scan($state);
            set_transient(self::TRANSIENT, $state, self::TTL);
        }

        return rest_ensure_response($state);
    }

    private function scan(array $state)
    {
        global $wpdb;

        $started = microtime(true);

        // Carry the map across the cursor-resumed polling requests so each
        // poll doesn't re-glob the module.json files; dropped once complete.
        $map = isset($state['map']) && is_array($state['map']) && $state['map']
            ? $state['map']
            : $this->identifier_map();
        $state['map'] = $map;

        $like_ba = '%' . $wpdb->esc_like('[ba_') . '%';
        $like_d5 = '%' . $wpdb->esc_like('wp:divitorque/') . '%';

        while (microtime(true) - $started < self::TIME_BUDGET) {
            $rows = $wpdb->get_results($wpdb->prepare(
                "SELECT ID, post_title, post_type, post_status, post_content FROM {$wpdb->posts}
                 WHERE ID > %d
                   AND post_status IN ('publish', 'draft', 'private')
                   AND post_type NOT IN ('revision', 'attachment', 'nav_menu_item')
                   AND (post_content LIKE %s OR post_content LIKE %s)
                 ORDER BY ID ASC
                 LIMIT %d",
                $state['cursor'],
                $like_ba,
                $like_d5,
                self::BATCH
            ));

            if (!$rows) {
                $state['complete']   = true;
                $state['scanned_at'] = time();
                unset($state['map']); // keep the final cached payload lean
                break;
            }

            foreach ($rows as $row) {
                $state['cursor'] = (int) $row->ID;
                $found           = [];

                if (preg_match_all('/\[(ba_[a-z0-9_]+)\b/', $row->post_content, $m)) {
                    $found = $m[1];
                }
                if (preg_match_all('#wp:(divitorque/[a-z0-9-]+)#', $row->post_content, $m)) {
                    $found = array_merge($found, $m[1]);
                }

                $names = [];
                foreach (array_unique($found) as $identifier) {
                    if (isset($map[$identifier])) {
                        $names[$map[$identifier]] = true;
                    }
                }

                foreach (array_keys($names) as $name) {
                    if (!isset($state['modules'][$name])) {
                        $state['modules'][$name] = ['count' => 0, 'pages' => []];
                    }
                    $state['modules'][$name]['count']++;
                    if (count($state['modules'][$name]['pages']) < self::MAX_PAGES) {
                        $state['modules'][$name]['pages'][] = [
                            'id'        => (int) $row->ID,
                            'title'     => $row->post_title !== '' ? $row->post_title : __('(no title)', 'addons-for-divi'),
                            'post_type'   => $row->post_type,
                            'post_status' => $row->post_status,
                            'edit_url'    => get_edit_post_link($row->ID, 'raw'),
                            'view_url'    => get_permalink($row->ID),
                        ];
                    }
                }
            }
        }

        return $state;
    }

    private function identifier_map()
    {
        static $memo = null;
        if (is_array($memo)) {
            return $memo;
        }

        $map     = [];
        $manager = [];
        foreach (ModulesManager::get_all_modules() as $module) {
            $manager[$module['name']] = true;
        }

        foreach (array_keys($manager) as $name) {
            $snake                       = str_replace('-', '_', $name);
            $map["ba_{$snake}"]          = $name;
            $map["divitorque/{$name}"]   = $name;
        }

        // modules-json overrides (authoritative d4Shortcode + D5 name).
        foreach (glob(DIVI_TORQUE_LITE_MODULES_JSON_PATH . '*/module.json') as $file) {
            $json = json_decode((string) file_get_contents($file), true);
            if (!is_array($json) || empty($json['name'])) {
                continue;
            }
            $d5_slug = substr($json['name'], strpos($json['name'], '/') + 1);
            if (substr($d5_slug, -5) === '-item') {
                continue;
            }
            $name = self::ALIASES[$d5_slug] ?? $d5_slug;
            if (!isset($manager[$name])) {
                continue;
            }
            $map[$json['name']] = $name;
            if (!empty($json['d4Shortcode'])) {
                $map[$json['d4Shortcode']] = $name;
            }
        }

        $memo = $map;
        return $memo;
    }
}
