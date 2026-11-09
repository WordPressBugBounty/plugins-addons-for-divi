<?php
/**
 * Read a standalone divi/spacing field (padding or margin) for one breakpoint.
 *
 * @package DiviTorqueLite\Modules\Shared
 * @since   4.15.1
 */

namespace DiviTorqueLite\Modules\Shared;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Resolves the CSS for a spacing field stored in `module.advanced.*`.
 *
 * WHY THIS EXISTS
 *
 * A standalone divi/spacing field (`"type": "field"` in module.json) only
 * saves {top, right, bottom, left} when the module's callbacks give it Divi's
 * onSpacingChange handler. Until 4.15.1 ten Lite modules did not, so the
 * builder saved the raw edit {side, value, syncVertical, syncHorizontal} and
 * every renderer found no side: the first edit wiped the padding. Live pages
 * still hold that shape, so it stays readable here.
 *
 * Three shapes reach a renderer:
 *
 *   "10px|20px|10px|20px"                   module.json defaults, placeholders
 *   {top, right, bottom, left, sync...}     onSpacingChange and D4 migration
 *   {side, value, syncVertical, ...}        the raw edit saved before the fix
 *
 * How a side resolves at a breakpoint, the way Divi's own spacing does:
 *
 *   - A MISSING side (onSpacingChange stores only the sides the user set)
 *     comes from the next larger breakpoint, then from the default. It never
 *     becomes 0px.
 *   - A BLANK desktop side ("", which D4 migration keeps for a side the
 *     author left empty in Divi 4) is left out of the CSS, so the stylesheet
 *     keeps it, as Divi 4 did. A blank tablet or phone side inherits from the
 *     larger breakpoint.
 *   - With no full set of sides, the CSS is one longhand per known side.
 *
 * Divi merges the module.json default into the attrs before a render
 * callback runs (array_replace_recursive). With an object default, a raw
 * edit {side: "top", value: "10px"} arrives as {top: <default>, ...,
 * side: "top", value: "10px"}, so on desktop the raw side wins over a named
 * side that still equals the default. Otherwise a named side wins: it is
 * the newer edit.
 *
 * A static class rather than a trait or a global function: modules already
 * `use` five traits, and Lite and Pro share global function names.
 *
 * src/divi5/shared/spacing.js is the JavaScript twin used by the builder's
 * styles.jsx files. Keep the two in step.
 */
class Spacing
{
    /**
     * Side names in CSS shorthand order.
     */
    const SIDES = ['top', 'right', 'bottom', 'left'];

    /**
     * Breakpoints from largest to smallest.
     */
    const BREAKPOINTS = ['desktop', 'tablet', 'phone'];

    /**
     * The sides one stored value sets, as side => CSS value. Missing and
     * blank sides are absent.
     *
     * @param mixed $value A pipe string, a spacing object or the raw edit.
     * @return array
     */
    public static function sides($value)
    {
        return array_filter(
            self::parse($value),
            function ($css) {
                return '' !== $css;
            }
        );
    }

    /**
     * The sides that apply at a breakpoint: the default, then each breakpoint
     * from desktop down to the one asked for.
     *
     * @param mixed  $attr       The responsive attr, e.g. $advanced['contentPadding'].
     * @param string $breakpoint desktop, tablet or phone.
     * @param mixed  $default    Default value in any accepted shape, or ''.
     * @return array
     */
    public static function resolve($attr, $breakpoint = 'desktop', $default = '')
    {
        $defaults = self::sides($default);
        $sides    = $defaults;
        foreach (self::BREAKPOINTS as $bp) {
            $stored = is_array($attr) ? ($attr[$bp]['value'] ?? null) : null;
            $is_top = 'desktop' === $bp;
            foreach (self::parse($stored, $is_top ? $defaults : []) as $name => $css) {
                if ('' !== $css) {
                    $sides[$name] = $css;
                } elseif ($is_top) {
                    unset($sides[$name]);
                }
            }
            if ($bp === $breakpoint) {
                break;
            }
        }
        return $sides;
    }

    /**
     * CSS shorthand ("top right bottom left") at a breakpoint, or '' when a
     * side has no value.
     *
     * @param mixed  $attr       The responsive attr.
     * @param string $breakpoint desktop, tablet or phone.
     * @param mixed  $default    Default value.
     * @return string
     */
    public static function value($attr, $breakpoint = 'desktop', $default = '')
    {
        return self::shorthand(self::resolve($attr, $breakpoint, $default));
    }

    /**
     * Declarations for a spacing property at a breakpoint: the shorthand when
     * all four sides are known, otherwise one longhand per known side, and ''
     * when none is.
     *
     * @param string $property   padding or margin.
     * @param mixed  $attr       The responsive attr.
     * @param string $breakpoint desktop, tablet or phone.
     * @param mixed  $default    Default value.
     * @param string $important  Appended to each value, e.g. '!important'.
     * @return string
     */
    public static function css($property, $attr, $breakpoint = 'desktop', $default = '', $important = '')
    {
        $sides     = self::resolve($attr, $breakpoint, $default);
        $shorthand = self::shorthand($sides);
        if ('' !== $shorthand) {
            return sprintf('%1$s: %2$s%3$s;', $property, $shorthand, $important);
        }

        $declarations = [];
        foreach (self::SIDES as $name) {
            if (isset($sides[$name])) {
                $declarations[] = sprintf('%1$s-%2$s: %3$s%4$s;', $property, $name, $sides[$name], $important);
            }
        }
        return implode(' ', $declarations);
    }

    /**
     * Whether a breakpoint stores a value that sets at least one side.
     *
     * @param mixed  $attr       The responsive attr.
     * @param string $breakpoint desktop, tablet or phone.
     * @return bool
     */
    public static function has($attr, $breakpoint)
    {
        return is_array($attr) && [] !== self::sides($attr[$breakpoint]['value'] ?? null);
    }

    /**
     * The sides a stored value mentions, as side => CSS value, with '' for a
     * side that is present but blank. Missing sides are absent.
     *
     * @param mixed $value    A pipe string, a spacing object or the raw edit.
     * @param array $defaults Default sides a raw edit may override (desktop).
     * @return array
     */
    private static function parse($value, $defaults = [])
    {
        $sides = [];

        if (is_array($value)) {
            foreach (self::SIDES as $name) {
                if (isset($value[$name])) {
                    $sides[$name] = self::clean($value[$name]);
                }
            }
            // Raw field edit {side, value}: use it unless that side is set
            // too, to something other than the merged-in default.
            $side = $value['side'] ?? null;
            $raw  = self::clean($value['value'] ?? null);
            if (is_string($side) && in_array($side, self::SIDES, true) && '' !== $raw) {
                $named = $sides[$side] ?? '';
                if ('' === $named || (isset($defaults[$side]) && $named === $defaults[$side])) {
                    $sides[$side] = $raw;
                }
            }
        } elseif (is_string($value) && '' !== trim($value)) {
            $names = self::SIDES;
            if (false === strpos($value, '|')) {
                // No pipe: a CSS shorthand ("20px", "10px 20px"), as typed by
                // hand or written by a layout pack. One to four values, CSS order.
                $parts = preg_split('/\s+/', trim($value));
                if (count($parts) <= 4) {
                    $t = $parts[0];
                    $r = $parts[1] ?? $t;
                    $b = $parts[2] ?? $t;
                    $l = $parts[3] ?? $r;
                    foreach ([$t, $r, $b, $l] as $i => $part) {
                        $sides[$names[$i]] = self::clean($part);
                    }
                }
            } else {
                foreach (explode('|', $value) as $i => $part) {
                    if (isset($names[$i])) {
                        $sides[$names[$i]] = self::clean($part);
                    }
                }
            }
        }

        return $sides;
    }

    /**
     * A side value as trimmed CSS, or '' when it is blank or not a value.
     *
     * @param mixed $part Candidate side value.
     * @return string
     */
    private static function clean($part)
    {
        if (is_string($part) || is_int($part) || is_float($part)) {
            return trim((string) $part);
        }
        return '';
    }

    /**
     * Shorthand for four resolved sides, or '' when one is missing.
     *
     * @param array $sides side => CSS value.
     * @return string
     */
    private static function shorthand($sides)
    {
        $values = [];
        foreach (self::SIDES as $name) {
            if (!isset($sides[$name])) {
                return '';
            }
            $values[] = $sides[$name];
        }
        return implode(' ', $values);
    }
}
