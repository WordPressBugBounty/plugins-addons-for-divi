<?php
/**
 * Compare Image: Module styles trait.
 *
 * @package DiviTorqueLite\Modules\CompareImage
 * @since   4.4.0
 */

namespace DiviTorqueLite\Modules\CompareImage\CompareImageTrait;

if (!defined('ABSPATH')) {
    exit;
}

use ET\Builder\FrontEnd\Module\Style;
use ET\Builder\Packages\Module\Options\Css\CssStyle;

trait ModuleStylesTrait
{
    /**
     * Convert a D4 padding string ("t|r|b|l") or D5 spacing object to a CSS
     * padding shorthand.
     *
     * @param string|array $val Pipe-delimited padding value or spacing object.
     * @return string
     */
    public static function dtq_pad($val)
    {
        if (empty($val)) {
            return '';
        }
        // D5 spacing object {top,right,bottom,left} (the migrated/native format).
        if (is_array($val)) {
            return sprintf(
                '%1$s %2$s %3$s %4$s',
                $val['top'] ?? '0px',
                $val['right'] ?? '0px',
                $val['bottom'] ?? '0px',
                $val['left'] ?? '0px'
            );
        }
        // Legacy D4 pipe-delimited string "top|right|bottom|left".
        $parts = explode('|', (string) $val);
        if (4 === count($parts)) {
            return sprintf('%1$s %2$s %3$s %4$s', $parts[0], $parts[1], $parts[2], $parts[3]);
        }
        return (string) $val;
    }

    /**
     * Build the flat custom-style declarations that mirror the D4 apply_css()
     * `ET_Builder_Element::set_style()` calls. Keep this in lockstep with the
     * JS twin in src/divi5/modules/compare-image/styles.jsx.
     *
     * @param array  $attrs       Module attributes.
     * @param string $order_class Module order class selector.
     * @return array Array of ['atRules'=>...,'selector'=>..,'declaration'=>..].
     */
    public static function build_custom_styles($attrs, $order_class)
    {
        $advanced = $attrs['module']['advanced'] ?? [];

        $val = function ($key, $fallback) use ($advanced) {
            return $advanced[$key]['desktop']['value'] ?? $fallback;
        };

        $before_label_bg = $val('beforeLabelBg', '#5b2cff');
        $after_label_bg  = $val('afterLabelBg', '#5b2cff');
        $show_label      = $val('showLabel', 'always');
        $handle_style    = $val('handleStyle', 'handle-1');
        $handle_color    = $val('handleColor', '#ffffff');
        $label_padding   = $val('labelPadding', '5px|20px|5px|20px');
        $label_height    = $val('labelHeight', '');
        $label_width     = $val('labelWidth', '');

        $styles = [];

        $push = function ($selector, $declaration, $at_rule = false) use (&$styles) {
            $styles[] = [
                'atRules'     => $at_rule,
                'selector'    => $selector,
                'declaration' => $declaration,
            ];
        };

        // Handle color (mirrors the D4 handle-1/handle-2 branches): handle-1
        // colors the arrows with the handle color; handle-2 also fills the
        // knob and colors the arrows with the dedicated arrow color.
        $arrow_color = $handle_color;
        if ('handle-2' === $handle_style) {
            $arrow_color = $val('arrowColor', '#ffffff');
            $push($order_class . ' .dtq-image-compare .twentytwenty-handle', sprintf('background-color: %s;', $handle_color));
        }

        $push(
            sprintf(
                '%1$s .dtq-image-compare .twentytwenty-horizontal .twentytwenty-handle:before, %1$s .dtq-image-compare .twentytwenty-horizontal .twentytwenty-handle:after, %1$s .dtq-image-compare .twentytwenty-vertical .twentytwenty-handle:before, %1$s .dtq-image-compare .twentytwenty-vertical .twentytwenty-handle:after',
                $order_class
            ),
            sprintf('background: %s;', $handle_color)
        );
        $push($order_class . ' .dtq-image-compare .twentytwenty-handle', sprintf('border: 3px solid %s;', $handle_color));
        $push($order_class . ' .dtq-image-compare .twentytwenty-right-arrow', sprintf('border-left: 6px solid %s;', $arrow_color));
        $push($order_class . ' .dtq-image-compare .twentytwenty-left-arrow', sprintf('border-right: 6px solid %s;', $arrow_color));
        $push($order_class . ' .dtq-image-compare .twentytwenty-up-arrow', sprintf('border-bottom: 6px solid %s;', $arrow_color));
        $push($order_class . ' .dtq-image-compare .twentytwenty-down-arrow', sprintf('border-top: 6px solid %s;', $arrow_color));

        // Label box (height/width only when set — D4 default was 'initial').
        $label_box_selector = $order_class . ' .twentytwenty-overlay div:before';
        if (!empty($label_height) && 'initial' !== $label_height) {
            $push($label_box_selector, sprintf('height: %s;', $label_height));
        }
        if (!empty($label_width) && 'initial' !== $label_width) {
            $push($label_box_selector, sprintf('width: %s;', $label_width));
        }
        $push($label_box_selector, sprintf('padding: %s;', self::dtq_pad($label_padding)));

        // Label visibility (show on module hover only).
        if ('on_hover' === $show_label) {
            $push(sprintf('%1$s .twentytwenty-before-label, %1$s .twentytwenty-after-label', $order_class), 'opacity: 0;');
            $push(sprintf('%1$s:hover .twentytwenty-before-label, %1$s:hover .twentytwenty-after-label', $order_class), 'opacity: 1;');
        }

        // Label backgrounds (!important like D4 — the ported base CSS sets a
        // translucent default background on the same selector).
        if (!empty($before_label_bg)) {
            $push($order_class . ' .twentytwenty-before-label:before', sprintf('background-color: %s !important;', $before_label_bg));
        }
        if (!empty($after_label_bg)) {
            $push($order_class . ' .twentytwenty-after-label:before', sprintf('background-color: %s !important;', $after_label_bg));
        }

        // ------------------------------------------------------------------
        // Responsive (tablet/phone) output for the mobile_options fields
        // (label_padding/label_height/label_width in D4). Purely additive.
        // ------------------------------------------------------------------

        $bp_val = function ($key, $breakpoint, $fallback) use ($advanced) {
            $node = $advanced[$key] ?? [];
            if ('phone' === $breakpoint) {
                return $node['phone']['value']
                    ?? $node['tablet']['value']
                    ?? $node['desktop']['value']
                    ?? $fallback;
            }
            if ('tablet' === $breakpoint) {
                return $node['tablet']['value']
                    ?? $node['desktop']['value']
                    ?? $fallback;
            }
            return $node['desktop']['value'] ?? $fallback;
        };

        $bp_changed = function ($key, $breakpoint) use ($advanced) {
            $node = $advanced[$key] ?? [];
            if ('tablet' === $breakpoint) {
                if (!isset($node['tablet']['value'])) {
                    return false;
                }
                $desktop = $node['desktop']['value'] ?? null;
                return $node['tablet']['value'] !== $desktop;
            }
            if ('phone' === $breakpoint) {
                if (!isset($node['phone']['value'])) {
                    return false;
                }
                $larger = $node['tablet']['value'] ?? ($node['desktop']['value'] ?? null);
                return $node['phone']['value'] !== $larger;
            }
            return false;
        };

        $at_rules = [
            'tablet' => '@media only screen and (max-width: 980px)',
            'phone'  => '@media only screen and (max-width: 767px)',
        ];

        foreach (['tablet', 'phone'] as $breakpoint) {
            $at_rule = $at_rules[$breakpoint];

            if ($bp_changed('labelPadding', $breakpoint)) {
                $push($label_box_selector, sprintf('padding: %s;', self::dtq_pad($bp_val('labelPadding', $breakpoint, $label_padding))), $at_rule);
            }
            if ($bp_changed('labelHeight', $breakpoint)) {
                $bp_h = $bp_val('labelHeight', $breakpoint, $label_height);
                if (!empty($bp_h) && 'initial' !== $bp_h) {
                    $push($label_box_selector, sprintf('height: %s;', $bp_h), $at_rule);
                }
            }
            if ($bp_changed('labelWidth', $breakpoint)) {
                $bp_w = $bp_val('labelWidth', $breakpoint, $label_width);
                if (!empty($bp_w) && 'initial' !== $bp_w) {
                    $push($label_box_selector, sprintf('width: %s;', $bp_w), $at_rule);
                }
            }
        }

        return $styles;
    }

    /**
     * Generate the module styles.
     *
     * @param array $args Style args.
     * @return void
     */
    public static function module_styles($args)
    {
        $attrs       = $args['attrs'] ?? [];
        $elements    = $args['elements'];
        $settings    = $args['settings'] ?? [];
        $order_class = $args['orderClass'] ?? '';

        $all_styles = [
            // Module wrapper styles (background, spacing, main border, etc.).
            $elements->style(
                [
                    'attrName'   => 'module',
                    'styleProps' => [
                        'disabledOn' => [
                            'disabledModuleVisibility' => $settings['disabledModuleVisibility'] ?? null,
                        ],
                    ],
                ]
            ),

            // Label font.
            $elements->style(['attrName' => 'label']),

            // Label border (D4 borders.label).
            $elements->style(['attrName' => 'labelBox']),
        ];

        // Flat custom declarations ported from D4 apply_css().
        $custom_styles = self::build_custom_styles($attrs, $order_class);
        if (!empty($custom_styles)) {
            $all_styles[] = $custom_styles;
        }

        // Custom CSS (Advanced tab).
        $all_styles[] = CssStyle::style(
            [
                'selector'  => $order_class,
                'attr'      => $attrs['css'] ?? [],
                'cssFields' => self::custom_css_fields(),
            ]
        );

        Style::add(
            [
                'id'            => $args['id'],
                'name'          => $args['name'],
                'orderIndex'    => $args['orderIndex'],
                'storeInstance' => $args['storeInstance'],
                'styles'        => $all_styles,
            ]
        );
    }
}
