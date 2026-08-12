<?php
/**
 * Share My Post: settings storage, merging and sanitising.
 *
 * Pure apart from get_option()/update_option(), so the merge and sanitise rules
 * are unit-testable without a WordPress install.
 *
 * @package DiviTorqueLite
 * @since   4.9.0
 */

namespace DiviTorqueLite;

if (!defined('ABSPATH')) {
    exit;
}

class Share_My_Post_Settings
{
    /** Option name. */
    const OPTION = 'divitorque_share_my_post_settings';

    /**
     * Settings that are ordered lists, not nested maps.
     *
     * These must be replaced wholesale on merge, never merged key-by-key.
     * array_replace_recursive() is index-aware, so with defaults
     * ['facebook','x','linkedin'] and a saved ['reddit'] it produces
     * ['reddit','x','linkedin'] — two networks the author never chose. And with
     * a saved [] it produces the defaults back, so "disable every network"
     * silently re-enables them.
     *
     * Restoring these by hand after the merge fixes both directions.
     */
    const ATOMIC_PATHS = array(
        array('general', 'post_types'),
        array('inline', 'networks'),
        array('floating', 'networks'),
    );

    /** Floating bar hides below this width unless the author says otherwise. */
    const BREAKPOINT_MIN = 320;
    const BREAKPOINT_MAX = 1600;

    /**
     * Factory defaults.
     *
     * `general.enabled` is false. This ships into an existing install base, and
     * defaulting it on would inject a share bar into every post on every site at
     * update time — a visible, unrequested change to their front end.
     *
     * @return array
     */
    public static function defaults()
    {
        $starter_networks = array('facebook', 'x', 'linkedin', 'whatsapp', 'email', 'copy');

        $defaults = array(
            'general' => array(
                'enabled'    => false,
                'post_types' => array('post'),
            ),
            'inline' => array(
                'enabled'      => true,
                'position'     => 'below',
                'networks'     => $starter_networks,
                'show_heading' => true,
                'heading'      => __('Share this post', 'addons-for-divi'),
            ),
            'floating' => array(
                'enabled'  => false,
                'side'     => 'left',
                'networks' => $starter_networks,
                'offset'   => 50,
            ),
            'appearance' => array(
                'display'    => 'icon',
                'color_mode' => 'brand',
                'shape'      => 'rounded',
                'size'       => 'medium',
                'custom_bg'  => '#2b6cb0',
                'custom_fg'  => '#ffffff',
            ),
            'mobile' => array(
                'show_inline'   => true,
                'show_floating' => false,
                'breakpoint'    => 768,
            ),
            'advanced' => array(
                'mastodon_instance' => 'mastodon.social',
                'open_in_popup'     => true,
            ),
        );

        /**
         * Filters the Share My Post factory defaults.
         *
         * @param array $defaults Default settings tree.
         */
        return apply_filters('divitorque_share_my_post_defaults', $defaults);
    }

    /** Allowed values for the enum fields, in UI order. */
    public static function enums()
    {
        return array(
            'inline.position'       => array('above', 'below', 'both'),
            'floating.side'         => array('left', 'right'),
            'appearance.display'    => array('icon', 'label', 'both'),
            'appearance.color_mode' => array('brand', 'custom'),
            'appearance.shape'      => array('square', 'rounded', 'circle'),
            'appearance.size'       => array('small', 'medium', 'large'),
        );
    }

    /**
     * Saved settings, merged over the defaults.
     *
     * Works with the option absent: register_activation_hook does not fire on
     * plugin *updates*, so an install that upgrades into this feature never runs
     * the seeder and must still get a complete tree.
     *
     * @return array
     */
    public static function get()
    {
        $saved = get_option(self::OPTION, array());

        return self::merge(self::defaults(), is_array($saved) ? $saved : array());
    }

    /**
     * Merge saved settings over defaults, treating ATOMIC_PATHS as opaque.
     *
     * @param array $defaults Default tree.
     * @param array $saved    Stored tree.
     *
     * @return array
     */
    public static function merge(array $defaults, array $saved)
    {
        $merged = array_replace_recursive($defaults, $saved);

        foreach (self::ATOMIC_PATHS as $path) {
            list($section, $key) = $path;

            // array_key_exists, not isset: it is the only way to tell "the
            // author never touched this" (absent → keep the default) from "the
            // author cleared it" (present but empty → keep it empty). isset()
            // would collapse both, and null is a legitimate stored value here.
            if (isset($saved[$section]) && is_array($saved[$section]) && array_key_exists($key, $saved[$section])) {
                $merged[$section][$key] = is_array($saved[$section][$key]) ? $saved[$section][$key] : array();
            }
        }

        return $merged;
    }

    /**
     * Persist a settings tree.
     *
     * @param array $settings Raw (unsanitised) tree.
     *
     * @return array The sanitised tree that was stored.
     */
    public static function update(array $settings)
    {
        $clean = self::sanitize($settings);
        update_option(self::OPTION, $clean);

        return $clean;
    }

    /**
     * Sanitise a settings tree.
     *
     * Built up from the defaults rather than filtered down from the input, so a
     * key that is not in the schema cannot survive at any depth — no allow-list
     * to forget to update when a section is added.
     *
     * Every value is re-checked here even though the REST layer also validates:
     * options can be written by WP-CLI, a filter, or a settings import, none of
     * which pass through REST.
     *
     * @param mixed $input Candidate tree.
     *
     * @return array
     */
    public static function sanitize($input)
    {
        $defaults = self::defaults();
        $input    = is_array($input) ? $input : array();

        $section = function ($name) use ($input) {
            return isset($input[$name]) && is_array($input[$name]) ? $input[$name] : array();
        };

        $general    = $section('general');
        $inline     = $section('inline');
        $floating   = $section('floating');
        $appearance = $section('appearance');
        $mobile     = $section('mobile');
        $advanced   = $section('advanced');

        return array(
            'general' => array(
                'enabled'    => self::bool($general, 'enabled', $defaults['general']['enabled']),
                'post_types' => self::post_types($general, 'post_types', $defaults['general']['post_types']),
            ),
            'inline' => array(
                'enabled'      => self::bool($inline, 'enabled', $defaults['inline']['enabled']),
                'position'     => self::enum($inline, 'position', 'inline.position', $defaults['inline']['position']),
                'networks'     => self::networks($inline, 'networks', $defaults['inline']['networks']),
                'show_heading' => self::bool($inline, 'show_heading', $defaults['inline']['show_heading']),
                'heading'      => self::text($inline, 'heading', $defaults['inline']['heading']),
            ),
            'floating' => array(
                'enabled'  => self::bool($floating, 'enabled', $defaults['floating']['enabled']),
                'side'     => self::enum($floating, 'side', 'floating.side', $defaults['floating']['side']),
                'networks' => self::networks($floating, 'networks', $defaults['floating']['networks']),
                'offset'   => self::int($floating, 'offset', 0, 100, $defaults['floating']['offset']),
            ),
            'appearance' => array(
                'display'    => self::enum($appearance, 'display', 'appearance.display', $defaults['appearance']['display']),
                'color_mode' => self::enum($appearance, 'color_mode', 'appearance.color_mode', $defaults['appearance']['color_mode']),
                'shape'      => self::enum($appearance, 'shape', 'appearance.shape', $defaults['appearance']['shape']),
                'size'       => self::enum($appearance, 'size', 'appearance.size', $defaults['appearance']['size']),
                'custom_bg'  => dtq_css_color($appearance['custom_bg'] ?? '', $defaults['appearance']['custom_bg']),
                'custom_fg'  => dtq_css_color($appearance['custom_fg'] ?? '', $defaults['appearance']['custom_fg']),
            ),
            'mobile' => array(
                'show_inline'   => self::bool($mobile, 'show_inline', $defaults['mobile']['show_inline']),
                'show_floating' => self::bool($mobile, 'show_floating', $defaults['mobile']['show_floating']),
                // absint + clamp, deliberately NOT dtq_css_length(): that accepts
                // '80%' and '5vw', which are meaningless in a media condition and
                // would make the generated block invalid, taking every rule
                // inside it down with them.
                'breakpoint'    => self::int($mobile, 'breakpoint', self::BREAKPOINT_MIN, self::BREAKPOINT_MAX, $defaults['mobile']['breakpoint']),
            ),
            'advanced' => array(
                'mastodon_instance' => Share_My_Post_Url::sanitize_instance(
                    $advanced['mastodon_instance'] ?? $defaults['advanced']['mastodon_instance']
                ),
                'open_in_popup'     => self::bool($advanced, 'open_in_popup', $defaults['advanced']['open_in_popup']),
            ),
        );
    }

    /* ── Field sanitisers ──────────────────────────────────────────────── */

    /**
     * Coerce to bool.
     *
     * filter_var, not truthiness: a REST payload delivers '0'/'false'/'on' as
     * strings, and '0' and 'false' are both truthy to PHP.
     *
     * @param array  $source   Section array.
     * @param string $key      Field name.
     * @param bool   $fallback Value when absent.
     *
     * @return bool
     */
    private static function bool($source, $key, $fallback)
    {
        if (!array_key_exists($key, $source)) {
            return (bool) $fallback;
        }

        return filter_var($source[$key], FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * Constrain to a known enum.
     *
     * @param array  $source   Section array.
     * @param string $key      Field name.
     * @param string $enum     Enum key in enums().
     * @param string $fallback Value when absent or invalid.
     *
     * @return string
     */
    private static function enum($source, $key, $enum, $fallback)
    {
        $allowed = self::enums()[$enum];
        $value   = isset($source[$key]) ? (string) $source[$key] : '';

        return in_array($value, $allowed, true) ? $value : $fallback;
    }

    /**
     * Clamp an integer.
     *
     * @param array  $source   Section array.
     * @param string $key      Field name.
     * @param int    $min      Lower bound.
     * @param int    $max      Upper bound.
     * @param int    $fallback Value when absent or non-numeric.
     *
     * @return int
     */
    private static function int($source, $key, $min, $max, $fallback)
    {
        if (!isset($source[$key]) || !is_numeric($source[$key])) {
            return (int) $fallback;
        }

        return max($min, min($max, (int) $source[$key]));
    }

    /**
     * Trim and strip a short text field.
     *
     * @param array  $source   Section array.
     * @param string $key      Field name.
     * @param string $fallback Value when absent.
     *
     * @return string
     */
    private static function text($source, $key, $fallback)
    {
        if (!array_key_exists($key, $source)) {
            return (string) $fallback;
        }

        // Not a fallback when empty: an empty heading is how an author removes it.
        return sanitize_text_field((string) $source[$key]);
    }

    /**
     * Filter a network list against the catalog.
     *
     * @param array  $source   Section array.
     * @param string $key      Field name.
     * @param array  $fallback Value when absent.
     *
     * @return string[]
     */
    private static function networks($source, $key, $fallback)
    {
        if (!array_key_exists($key, $source)) {
            return $fallback;
        }

        return Share_My_Post_Networks::filter($source[$key]);
    }

    /**
     * Filter a post-type list.
     *
     * Existence is checked at save time only. A post type registered by a plugin
     * that is later deactivated would otherwise be silently dropped from the
     * author's selection on the next save, and silently re-added is worse than
     * an entry that simply never matches.
     *
     * @param array  $source   Section array.
     * @param string $key      Field name.
     * @param array  $fallback Value when absent.
     *
     * @return string[]
     */
    private static function post_types($source, $key, $fallback)
    {
        if (!array_key_exists($key, $source)) {
            return $fallback;
        }

        if (!is_array($source[$key])) {
            return array();
        }

        $out = array();
        foreach ($source[$key] as $type) {
            $type = sanitize_key((string) $type);
            if ('' !== $type && !in_array($type, $out, true)) {
                $out[] = $type;
            }
        }

        return $out;
    }

    /**
     * Post types an author may choose from.
     *
     * Public types only — sharing a link to a non-public type produces a 404 for
     * everyone who follows it. Attachments are excluded for the same reason
     * they are excluded from most content UIs: they are not posts.
     *
     * @return array<int, array{value: string, label: string}>
     */
    public static function selectable_post_types()
    {
        $types = get_post_types(array('public' => true), 'objects');
        $out   = array();

        foreach ($types as $type) {
            if ('attachment' === $type->name) {
                continue;
            }

            $out[] = array(
                'value' => $type->name,
                'label' => $type->labels->name ?? $type->name,
            );
        }

        return $out;
    }
}
