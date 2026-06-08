<?php
/**
 * Divider: Module styles trait.
 *
 * @package DiviTorqueLite\Modules\Divider
 * @since   4.4.0
 */

namespace DiviTorqueLite\Modules\Divider\DividerTrait;

if (!defined('ABSPATH')) {
    exit;
}

use ET\Builder\FrontEnd\Module\Style;
use ET\Builder\Packages\Module\Options\Css\CssStyle;

trait ModuleStylesTrait
{
    /**
     * Build the data-uri SVG background for a "pattern" border style.
     *
     * Ported verbatim from the D4 helper get_pattern().
     *
     * @param string $name   Pattern slug.
     * @param string $color  Stroke color.
     * @param string $weight Stroke weight.
     *
     * @return string
     */
    public static function get_pattern($name, $color, $weight)
    {
        $patterns = [
            'curved' => "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' preserveAspectRatio='none' overflow='visible' height='100%' viewBox='0 0 24 24' stroke='" . $color . "' stroke-width='" . $weight . "' fill='none' stroke-linecap='square' stroke-miterlimit='10'%3E%3Cpath d='M0,6c6,0,6,13,12,13S18,6,24,6'/%3E%3C/svg%3E",
            'zigzag' => "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' preserveAspectRatio='none' overflow='visible' height='100%' viewBox='0 0 24 24' stroke='" . $color . "' stroke-width='" . $weight . "' fill='none' stroke-linecap='square' stroke-miterlimit='10'%3E%3Cpolyline points='0,18 12,6 24,18 '/%3E%3C/svg%3E",
            'square' => "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' preserveAspectRatio='none' overflow='visible' height='100%' viewBox='0 0 24 24' fill='none' stroke='" . $color . "' stroke-width='" . $weight . "' stroke-linecap='square' stroke-miterlimit='10'%3E%3Cpolyline points='0,6 6,6 6,18 18,18 18,6 24,6 '/%3E%3C/svg%3E",
            'curly'  => "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' preserveAspectRatio='none' overflow='visible' height='100%' viewBox='0 0 24 24' fill='none' stroke='" . $color . "' stroke-width='" . $weight . "' stroke-linecap='square' stroke-miterlimit='10'%3E%3Cpath d='M0,21c3.3,0,8.3-0.9,15.7-7.1c6.6-5.4,4.4-9.3,2.4-10.3c-3.4-1.8-7.7,1.3-7.3,8.8C11.2,20,17.1,21,24,21'/%3E%3C/svg%3E",
        ];

        return $patterns[$name] ?? $patterns['curved'];
    }

    /**
     * Convert a spacing field value into a CSS shorthand, accepting BOTH the
     * legacy D4 pipe-string (`top|right|bottom|left`) and the D5 spacing object
     * (`{top,right,bottom,left}`) that a D4->D5 migration produces.
     *
     * @param mixed $val Spacing value (object array or pipe string).
     *
     * @return string CSS shorthand, or empty string when nothing to emit.
     */
    public static function dtq_spacing($val)
    {
        if (empty($val)) {
            return '';
        }
        if (is_array($val)) {
            return sprintf('%s %s %s %s', $val['top'] ?? '0px', $val['right'] ?? '0px', $val['bottom'] ?? '0px', $val['left'] ?? '0px');
        }
        $p = explode('|', (string) $val);
        return 4 === count($p) ? implode(' ', $p) : (string) $val;
    }

    /**
     * Read a desktop attr value with a fallback.
     *
     * @param array  $attr     The attr array.
     * @param string $key      Sub-key under module.advanced.
     * @param mixed  $fallback Fallback value.
     *
     * @return mixed
     */
    protected static function attr_value($attr, $key, $fallback)
    {
        return $attr[$key]['desktop']['value'] ?? $fallback;
    }

    /**
     * Build the custom style array ported from the D4 render_css() set_style()
     * calls. Each entry is `{ atRules, selector, declaration }` scoped to the
     * module order class.
     *
     * @param string $order_class The module order class.
     * @param array  $advanced    The `module.advanced` attrs.
     *
     * @return array
     */
    public static function build_divider_styles($order_class, $advanced)
    {
        $styles = [];

        $add = function ($selector, $declaration) use (&$styles) {
            if (!empty($declaration)) {
                $styles[] = [
                    'atRules'     => false,
                    'selector'    => $selector,
                    'declaration' => $declaration,
                ];
            }
        };

        $active_element  = self::attr_value($advanced, 'activeElement', 'icon');
        $use_mask        = self::attr_value($advanced, 'useMask', 'off');
        $mask_url        = self::attr_value($advanced, 'maskUrl', '');
        $content_align   = self::attr_value($advanced, 'contentAlignment', 'center');
        $border_gap      = self::attr_value($advanced, 'borderGap', '20px');
        $mask_size       = self::attr_value($advanced, 'maskSize', 'cover');
        $mask_pos        = self::attr_value($advanced, 'maskPos', 'center center');
        $mask_hz_pos     = self::attr_value($advanced, 'maskHzPos', '0%');
        $mask_vr_pos     = self::attr_value($advanced, 'maskVrPos', '0%');
        $mask_repeat     = self::attr_value($advanced, 'maskRepeat', 'repeat');
        $icon_color      = self::attr_value($advanced, 'iconColor', '#5b2cff');
        $icon_size       = self::attr_value($advanced, 'iconSize', '40px');
        $icon_bg         = self::attr_value($advanced, 'iconBg', '');
        $img_width       = self::attr_value($advanced, 'imgWidth', '100px');
        $border_type     = self::attr_value($advanced, 'borderType', 'classic');
        $border_classic  = self::attr_value($advanced, 'borderStyleClassic', 'double');
        $border_pattern  = self::attr_value($advanced, 'borderStylePattern', 'curved');
        $border_height   = self::attr_value($advanced, 'borderHeight', '10px');
        $border_color    = self::attr_value($advanced, 'borderColor', '#5b2cff');
        $border_weight   = self::attr_value($advanced, 'borderWeight', '6px');
        $border_offset   = self::attr_value($advanced, 'borderOffset', '0px');
        $use_shape       = self::attr_value($advanced, 'useShape', 'off');
        $shape_weight    = self::attr_value($advanced, 'shapeWeight', '1');
        $shape_color     = self::attr_value($advanced, 'shapeColor', '#333333');
        $shape_width     = self::attr_value($advanced, 'shapeWidth', '280px');
        $shape_margin    = self::attr_value($advanced, 'shapeMargin', '');
        $text_background = self::attr_value($advanced, 'textBackground', '');
        $text_radius     = self::attr_value($advanced, 'textRadius', 'off|0|0|0|0');
        $icon_padding    = self::attr_value($advanced, 'iconPadding', '');
        $text_padding    = self::attr_value($advanced, 'textPadding', '');

        // Icon font-family: divi icons use ETmodules, FontAwesome icons use FontAwesome.
        $icon_attr   = $advanced['icon']['desktop']['value'] ?? '';
        $icon_type   = '';
        if (is_array($icon_attr)) {
            $icon_type = $icon_attr['type'] ?? '';
        } elseif (is_string($icon_attr) && false !== strpos($icon_attr, '||')) {
            // Legacy D4 format: glyph||font||weight (font = "divi" or "fa").
            $parts     = explode('||', $icon_attr);
            $icon_type = isset($parts[1]) && 'fa' === $parts[1] ? 'fa' : 'divi';
        }
        $icon_font_family = 'fa' === $icon_type ? 'FontAwesome' : 'ETmodules';

        $root = $order_class . ' .dtq-divider';

        // Mask styles.
        if ('image' !== $active_element && 'on' === $use_mask && !empty($mask_url)) {
            $selector = $root . '__icon i';
            if ('text' === $active_element) {
                $selector = $root . '__text';
            }

            $add(
                $selector,
                sprintf(
                    'color: transparent!important; background-image: url("%1$s"); background-size: %2$s; background-repeat: %3$s; -webkit-background-clip: text; -moz-background-clip: text; -o-background-clip: text; background-clip: text;',
                    $mask_url,
                    $mask_size,
                    $mask_repeat
                )
            );

            if ('custom' !== $mask_pos) {
                $add($selector, sprintf('background-position: %1$s;', $mask_pos));
            } else {
                $add($selector, sprintf('background-position: %1$s %2$s;', $mask_vr_pos, $mask_hz_pos));
            }
        }

        if ('off' === $use_shape) {
            $add($root, 'align-items: center;');

            if ('left' === $content_align) {
                $add($root . '__element', sprintf('padding-right: %1$s;', $border_gap));
                $add($root . '__border-start', 'display: none;');
            } elseif ('right' === $content_align) {
                $add($root . '__element', sprintf('padding-left: %1$s;', $border_gap));
            } elseif ('center' === $content_align) {
                $add($root . '__element', sprintf('padding-left: %1$s; padding-right: %1$s;', $border_gap));
            }

            // Border offset top.
            $add($root . '__border', sprintf('margin-top: %1$s;', $border_offset));

            // Border type.
            if ('none' !== $border_type) {
                if ('classic' === $border_type) {
                    $add(
                        $root . '__border',
                        sprintf('border-top: %1$s %2$s %3$s;', $border_weight, $border_classic, $border_color)
                    );
                } elseif ('pattern' === $border_type) {
                    $pattern_bg = self::get_pattern($border_pattern, $border_color, $border_weight);
                    $add(
                        $root . '__border',
                        sprintf(
                            'background-image: url("%1$s"); height: %2$s; background-size: %2$s 100%%;',
                            $pattern_bg,
                            $border_height
                        )
                    );
                }
            }
        } else {
            $add($root, 'flex-direction: column;');
            $add($root, sprintf('align-items: %1$s;', $content_align));

            // Shape margin (pipe string or D5 spacing object -> shorthand).
            $shape_margin_css = self::dtq_spacing($shape_margin);
            if ('' !== $shape_margin_css) {
                $add($root . '__shape', sprintf('margin: %1$s;', $shape_margin_css));
            }

            // Shape width.
            $add($root . '__shape svg', sprintf('width: %1$s!important;', $shape_width));

            // Shape weight & color.
            $add(
                $root . '__shape svg *',
                sprintf('stroke-width: %1$s!important; stroke: %2$s!important;', $shape_weight, $shape_color)
            );
        }

        // Icon.
        if ('icon' === $active_element) {
            // Icon font-family so the glyph renders in the correct icon font.
            $add($root . '__icon i', sprintf('font-family: "%1$s";', $icon_font_family));
            $add($root . '__icon i', sprintf('font-size: %1$s;', $icon_size));

            // Icon padding (pipe string or D5 spacing object -> shorthand).
            $icon_pad = self::dtq_spacing($icon_padding);
            if ('' !== $icon_pad) {
                $add($root . '__icon i', sprintf('padding: %1$s;', $icon_pad));
            }

            if ('off' === $use_mask) {
                if (!empty($icon_bg)) {
                    $add($root . '__icon i', sprintf('background: %1$s;', $icon_bg));
                }
                $add($root . '__icon i', sprintf('color: %1$s;', $icon_color));
            }
        }

        // Image.
        if ('image' === $active_element) {
            $add($root . '__element img', sprintf('width: %1$s;', $img_width));
        }

        // Text padding (pipe string or D5 spacing object -> shorthand).
        $text_pad = self::dtq_spacing($text_padding);
        if ('' !== $text_pad) {
            $add($root . '__text span', sprintf('padding: %1$s;', $text_pad));
        }

        // Text box background.
        if (!empty($text_background)) {
            $add($root . '__text span', sprintf('background: %1$s;', $text_background));
        }

        // Text radius (border-radius pipe format: on|tl|tr|br|bl).
        if (is_string($text_radius)) {
            $r = explode('|', $text_radius);
            if (count($r) >= 5) {
                $add(
                    $root . '__text span',
                    sprintf('border-radius: %1$s %2$s %3$s %4$s;', $r[1], $r[2], $r[3], $r[4])
                );
            }
        }

        // ------------------------------------------------------------------
        // Additive responsive (tablet/phone) output.
        //
        // For each responsive field that drives a desktop declaration above,
        // emit an extra @media-scoped entry ONLY when the breakpoint value
        // exists AND differs from the next-larger breakpoint. Desktop output
        // above is left untouched.
        // ------------------------------------------------------------------
        $media = [
            'tablet' => '@media only screen and (max-width: 980px)',
            'phone'  => '@media only screen and (max-width: 767px)',
        ];

        // Resolve a breakpoint value, falling back up the chain
        // (phone -> tablet -> desktop), returning null when nothing is set.
        $bp_value = function ($key, $breakpoint) use ($advanced) {
            $node = $advanced[$key] ?? [];
            if (isset($node[$breakpoint]['value'])) {
                return $node[$breakpoint]['value'];
            }
            if ('phone' === $breakpoint && isset($node['tablet']['value'])) {
                return $node['tablet']['value'];
            }
            return $node['desktop']['value'] ?? null;
        };

        // Raw (no-fallback) breakpoint value, used to decide whether this
        // breakpoint actually differs from the next-larger one.
        $bp_raw = function ($key, $breakpoint) use ($advanced) {
            return $advanced[$key][$breakpoint]['value'] ?? null;
        };

        $add_media = function ($at_rules, $selector, $declaration) use (&$styles) {
            if (!empty($declaration)) {
                $styles[] = [
                    'atRules'     => $at_rules,
                    'selector'    => $selector,
                    'declaration' => $declaration,
                ];
            }
        };

        foreach (['tablet', 'phone'] as $breakpoint) {
            $larger     = ('tablet' === $breakpoint) ? 'desktop' : 'tablet';
            $at_rules   = $media[$breakpoint];

            // contentAlignment (responsive) -> alignment declarations.
            $align_raw   = $bp_raw('contentAlignment', $breakpoint);
            $align_prev  = ('tablet' === $breakpoint)
                ? $content_align
                : $bp_value('contentAlignment', $larger);
            if (null !== $align_raw && $align_raw !== $align_prev) {
                $align_val = $bp_value('contentAlignment', $breakpoint);
                $gap_val   = $bp_value('borderGap', $breakpoint) ?? $border_gap;
                if ('off' === $use_shape) {
                    if ('left' === $align_val) {
                        $add_media($at_rules, $root . '__element', sprintf('padding-right: %1$s;', $gap_val));
                        $add_media($at_rules, $root . '__border-start', 'display: none;');
                    } elseif ('right' === $align_val) {
                        $add_media($at_rules, $root . '__element', sprintf('padding-left: %1$s;', $gap_val));
                    } elseif ('center' === $align_val) {
                        $add_media($at_rules, $root . '__element', sprintf('padding-left: %1$s; padding-right: %1$s;', $gap_val));
                    }
                } else {
                    $add_media($at_rules, $root, sprintf('align-items: %1$s;', $align_val));
                }
            }

            if ('off' !== $use_shape) {
                // shapeMargin (responsive) -> margin shorthand.
                $margin_raw  = $bp_raw('shapeMargin', $breakpoint);
                $margin_prev = ('tablet' === $breakpoint)
                    ? $shape_margin
                    : $bp_value('shapeMargin', $larger);
                if (null !== $margin_raw && $margin_raw !== $margin_prev) {
                    $margin_css = self::dtq_spacing($bp_value('shapeMargin', $breakpoint));
                    if ('' !== $margin_css) {
                        $add_media($at_rules, $root . '__shape', sprintf('margin: %1$s;', $margin_css));
                    }
                }

                // shapeWidth (responsive) -> width.
                $width_raw  = $bp_raw('shapeWidth', $breakpoint);
                $width_prev = ('tablet' === $breakpoint)
                    ? $shape_width
                    : $bp_value('shapeWidth', $larger);
                if (null !== $width_raw && $width_raw !== $width_prev) {
                    $width_val = $bp_value('shapeWidth', $breakpoint);
                    $add_media($at_rules, $root . '__shape svg', sprintf('width: %1$s!important;', $width_val));
                }
            }

            // textRadius (responsive) -> border-radius.
            $radius_raw  = $bp_raw('textRadius', $breakpoint);
            $radius_prev = ('tablet' === $breakpoint)
                ? $text_radius
                : $bp_value('textRadius', $larger);
            if (null !== $radius_raw && $radius_raw !== $radius_prev) {
                $radius_val = $bp_value('textRadius', $breakpoint);
                if (is_string($radius_val)) {
                    $r = explode('|', $radius_val);
                    if (count($r) >= 5) {
                        $add_media($at_rules, $root . '__text span', sprintf('border-radius: %1$s %2$s %3$s %4$s;', $r[1], $r[2], $r[3], $r[4]));
                    }
                }
            }

            // iconPadding (responsive) -> padding.
            if ('icon' === $active_element) {
                $icon_pad_raw  = $bp_raw('iconPadding', $breakpoint);
                $icon_pad_prev = ('tablet' === $breakpoint)
                    ? $icon_padding
                    : $bp_value('iconPadding', $larger);
                if (null !== $icon_pad_raw && $icon_pad_raw !== $icon_pad_prev) {
                    $icon_pad_val = self::dtq_spacing($bp_value('iconPadding', $breakpoint));
                    if ('' !== $icon_pad_val) {
                        $add_media($at_rules, $root . '__icon i', sprintf('padding: %1$s;', $icon_pad_val));
                    }
                }
            }

            // textPadding (responsive) -> padding.
            $text_pad_raw  = $bp_raw('textPadding', $breakpoint);
            $text_pad_prev = ('tablet' === $breakpoint)
                ? $text_padding
                : $bp_value('textPadding', $larger);
            if (null !== $text_pad_raw && $text_pad_raw !== $text_pad_prev) {
                $text_pad_val = self::dtq_spacing($bp_value('textPadding', $breakpoint));
                if ('' !== $text_pad_val) {
                    $add_media($at_rules, $root . '__text span', sprintf('padding: %1$s;', $text_pad_val));
                }
            }
        }

        return $styles;
    }

    /**
     * Generate the module styles.
     *
     * @param array $args Style args.
     *
     * @return void
     */
    public static function module_styles($args)
    {
        $attrs       = $args['attrs'] ?? [];
        $elements    = $args['elements'];
        $settings    = $args['settings'] ?? [];
        $order_class = $args['orderClass'] ?? '';

        $advanced      = $attrs['module']['advanced'] ?? [];
        $custom_styles = self::build_divider_styles($order_class, $advanced);

        $all_styles = [
            // Module wrapper styles (background, spacing, etc.).
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

            // Title font / heading styles.
            $elements->style(
                [
                    'attrName' => 'title',
                ]
            ),

            // Icon/Image border + radius decoration.
            $elements->style(
                [
                    'attrName' => 'icon',
                ]
            ),
        ];

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
