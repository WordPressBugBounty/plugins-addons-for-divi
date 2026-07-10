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
    const TTL         = 12 * HOUR_IN_SECONDS;
    const TIME_BUDGET = 5.0;
    const BATCH       = 200;
    const MAX_PAGES   = 20;

    /** D5 folder slug → Module Manager name (only where they differ). */
    const ALIASES = [
        'contact-form-7' => 'contact-form7',
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
        $map     = $this->identifier_map();

        $like_ba = '%' . $wpdb->esc_like('[ba_') . '%';
        $like_d5 = '%' . $wpdb->esc_like('wp:divitorque/') . '%';

        while (microtime(true) - $started < self::TIME_BUDGET) {
            $rows = $wpdb->get_results($wpdb->prepare(
                "SELECT ID, post_title, post_type, post_content FROM {$wpdb->posts}
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
                            'post_type' => $row->post_type,
                            'edit_url'  => get_edit_post_link($row->ID, 'raw'),
                            'view_url'  => get_permalink($row->ID),
                        ];
                    }
                }
            }
        }

        return $state;
    }

    private function identifier_map()
    {
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

        return $map;
    }
}
