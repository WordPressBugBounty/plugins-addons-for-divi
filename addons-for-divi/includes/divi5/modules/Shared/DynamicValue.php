<?php
/**
 * Resolve Divi 5 dynamic-content attribute values.
 *
 * A field that declares `dynamicContent` in its module.json lets the author
 * pick a dynamic source (post title, an ACF field, the site tagline...) instead
 * of typing a literal. Divi stores that choice as a STRUCTURE, not a string:
 *
 *     [ 'name' => 'post_title', 'settings' => [ 'before' => '', 'after' => '' ] ]
 *
 * Our render callbacks read attribute values straight out of the attrs array
 * and print them, so before this class a dynamic value reached esc_html() as an
 * array and printed the literal word "Array" -- and a value carried over in
 * Divi 4's `$variable(...)$` string form printed the raw token. Thirty-four
 * Lite modules offer dynamic content on at least one field, so this was visible
 * anywhere an author used the feature.
 *
 * Resolution is deliberately centralised here rather than repeated in each
 * module: every module's value getter routes through resolve(), which is a
 * no-op for the overwhelmingly common case of a plain string.
 *
 * Ported from Pro, which fixed the identical defect. Keep the two copies in
 * step: they are the same class, in each plugin's own namespace.
 *
 * @package DiviTorqueLite\Modules\Shared
 * @since   4.12.0
 */

namespace DiviTorqueLite\Modules\Shared;

if (!defined('ABSPATH')) {
    exit;
}

class DynamicValue
{
    /**
     * Resolve one attribute value.
     *
     * Anything that is not a dynamic-content value is returned untouched, so
     * this is safe to wrap around every attribute read.
     *
     * @param mixed $value Raw attribute value.
     *
     * @return mixed The resolved string, or the original value.
     */
    public static function resolve($value)
    {
        if (is_array($value)) {
            return self::resolve_structure($value);
        }

        if (is_string($value) && false !== strpos($value, '$variable(')) {
            return self::resolve_legacy_token($value);
        }

        return $value;
    }

    /**
     * Resolve the structured form Divi 5 saves.
     *
     * Only arrays that actually name a dynamic source are touched. Plenty of
     * legitimate attribute values are arrays (an icon, a spacing object), and
     * those must come back unchanged.
     *
     * @param array $value Attribute value.
     *
     * @return mixed
     */
    protected static function resolve_structure(array $value)
    {
        if (!isset($value['name']) || !is_string($value['name']) || '' === $value['name']) {
            return $value;
        }

        if (!class_exists('\ET\Builder\Packages\Module\Layout\Components\DynamicContent\DynamicContentUtils')) {
            // Divi too old, or the builder not loaded. Returning an empty string
            // is the honest outcome: printing "Array" is strictly worse.
            return '';
        }

        try {
            return \ET\Builder\Packages\Module\Layout\Components\DynamicContent\DynamicContentUtils::get_processed_dynamic_content($value);
        } catch (\Throwable $e) {
            return '';
        }
    }

    /**
     * Resolve the `$variable({...})$` string form.
     *
     * This is what a Divi 4 dynamic value converts to, and what lands in an
     * attribute that was migrated rather than authored in Divi 5.
     *
     * @param string $value Attribute value.
     *
     * @return string
     */
    protected static function resolve_legacy_token($value)
    {
        return preg_replace_callback(
            '/\$variable\((.*?)\)\$/s',
            function ($matches) {
                $decoded = json_decode(html_entity_decode($matches[1], ENT_QUOTES), true);
                if (!is_array($decoded)) {
                    return '';
                }
                $resolved = self::resolve_structure($decoded);
                return is_string($resolved) ? $resolved : '';
            },
            $value
        );
    }
}
