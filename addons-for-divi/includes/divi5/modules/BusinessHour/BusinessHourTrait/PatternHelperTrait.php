<?php
/**
 * BusinessHour: SVG separator/divider pattern helpers.
 *
 * Copied verbatim from the D4 helpers in
 * includes/modules/base/BuilderModules.php (hex_to_rgb / get_pattern) so the
 * D5 frontend output matches D4 byte-for-byte.
 *
 * MUST STAY IN SYNC with the JS twin:
 * src/divi5/modules/business-hour/pattern.js
 * The SVG string templates and URL-encoding are byte-identical between the
 * two files — do not reformat them.
 *
 * @package DiviTorqueLite\Modules\BusinessHour
 * @since   4.4.0
 */

namespace DiviTorqueLite\Modules\BusinessHour\BusinessHourTrait;

if (!defined('ABSPATH')) {
    exit;
}

trait PatternHelperTrait
{
    /**
     * Convert a hex color (#rgb or #rrggbb) to an rgba() string.
     *
     * @param string $hex Hex color string.
     *
     * @return string rgba(r,g,b,1) string.
     */
    public static function hex_to_rgb($hex)
    {
        $hex      = str_replace('#', '', $hex);
        $length   = strlen($hex);
        $rgb['r'] = hexdec($length == 6 ? substr($hex, 0, 2) : ($length == 3 ? str_repeat(substr($hex, 0, 1), 2) : 0));
        $rgb['g'] = hexdec($length == 6 ? substr($hex, 2, 2) : ($length == 3 ? str_repeat(substr($hex, 1, 1), 2) : 0));
        $rgb['b'] = hexdec($length == 6 ? substr($hex, 4, 2) : ($length == 3 ? str_repeat(substr($hex, 2, 1), 2) : 0));

        return sprintf('rgba(%1$s,%2$s,%3$s,1)', $rgb['r'], $rgb['g'], $rgb['b']);
    }

    /**
     * Build the SVG data-URI for a separator/divider pattern.
     *
     * The string templates below are copied EXACTLY from the D4 get_pattern()
     * and the JS pattern.js — keep them byte-identical.
     *
     * @param string $name   Pattern name: curved | zigzag | square | curly.
     * @param string $color  Stroke color (already converted from hex).
     * @param string $weight Stroke width (e.g. "1px").
     *
     * @return string data:image/svg+xml URI.
     */
    public static function get_pattern($name, $color, $weight)
    {
        $pattern = array(
            'curved' => "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' preserveAspectRatio='none' overflow='visible' height='100%' viewBox='0 0 24 24' stroke='" . $color . "' stroke-width='" . $weight . "' fill='none' stroke-linecap='square' stroke-miterlimit='10'%3E%3Cpath d='M0,6c6,0,6,13,12,13S18,6,24,6'/%3E%3C/svg%3E",

            'zigzag' => "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' preserveAspectRatio='none' overflow='visible' height='100%' viewBox='0 0 24 24' stroke='" . $color . "' stroke-width='" . $weight . "' fill='none' stroke-linecap='square' stroke-miterlimit='10'%3E%3Cpolyline points='0,18 12,6 24,18 '/%3E%3C/svg%3E",

            'square' => "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' preserveAspectRatio='none' overflow='visible' height='100%' viewBox='0 0 24 24' fill='none' stroke='" . $color . "' stroke-width='" . $weight . "' stroke-linecap='square' stroke-miterlimit='10'%3E%3Cpolyline points='0,6 6,6 6,18 18,18 18,6 24,6 '/%3E%3C/svg%3E",

            'curly'  => "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' preserveAspectRatio='none' overflow='visible' height='100%' viewBox='0 0 24 24' fill='none' stroke='" . $color . "' stroke-width='" . $weight . "' stroke-linecap='square' stroke-miterlimit='10'%3E%3Cpath d='M0,21c3.3,0,8.3-0.9,15.7-7.1c6.6-5.4,4.4-9.3,2.4-10.3c-3.4-1.8-7.7,1.3-7.3,8.8C11.2,20,17.1,21,24,21'/%3E%3C/svg%3E",
        );

        return $pattern[$name];
    }
}
