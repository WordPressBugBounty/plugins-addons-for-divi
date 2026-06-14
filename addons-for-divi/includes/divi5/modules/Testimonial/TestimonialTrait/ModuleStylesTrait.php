<?php
/**
 * Testimonial: Module styles trait.
 *
 * @package DiviTorqueLite\Modules\Testimonial
 * @since   4.4.0
 */

namespace DiviTorqueLite\Modules\Testimonial\TestimonialTrait;

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
     * Shared absolute-element declaration builder (mirrors D4
     * get_absolute_element_styles(); also used for the absolute reviewer
     * image). D4 stores the offsets as SEPARATE fields (position select +
     * is_center_x/y toggles + offset_x/y ranges) — nothing is packed.
     *
     * @param string $position  left_top|left_bottom|right_top|right_bottom.
     * @param string $is_center_x 'on'|'off'.
     * @param string $is_center_y 'on'|'off'.
     * @param string $offset_x  Horizontal offset.
     * @param string $offset_y  Vertical offset.
     * @param string $z_index   z-index value.
     * @return string
     */
    public static function absolute_decl($position, $is_center_x, $is_center_y, $offset_x, $offset_y, $z_index)
    {
        $pos_parts = explode('_', (string) $position);
        $pos_x     = $pos_parts[0] ?? 'right';
        $pos_y     = $pos_parts[1] ?? 'top';
        $x_val     = ('on' === $is_center_x) ? '50%' : $offset_x;
        $y_val     = ('on' === $is_center_y) ? '50%' : $offset_y;
        $tx        = '0';
        $ty        = '0';
        if ('on' === $is_center_x) {
            $tx = ('right' === $pos_x) ? '50%' : '-50%';
        }
        if ('on' === $is_center_y) {
            $ty = ('top' === $pos_y) ? '-50%' : '50%';
        }
        return sprintf(
            'position: absolute; z-index: %1$s; %2$s: %3$s; %4$s: %5$s; transform: translateX(%6$s) translateY(%7$s);',
            $z_index,
            $pos_x,
            $x_val,
            $pos_y,
            $y_val,
            $tx,
            $ty
        );
    }

    /**
     * Build the flat custom-style declarations that mirror the D4 render_css()
     * `ET_Builder_Element::set_style()` calls. Keep this in lockstep with the
     * JS twin in src/divi5/modules/testimonial/styles.jsx.
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

        // D4 parity (dt_if_not_migrated()): legacy installs (the `ba_version`
        // option exists) shipped different D4 defaults for review_top_spacing
        // (0px vs 10px) and reviewer_position (bottom vs top — it drives the
        // bubble arrow direction). module.json carries the NEW-USER default;
        // absent attrs on legacy installs fall back to the legacy default so
        // migrated layouts render unchanged.
        $is_legacy = function_exists('get_option') && get_option('ba_version');

        $alignment             = $val('alignment', 'center');
        $img_position          = $val('imgPosition', 'relative');
        $reviewer_position     = $advanced['reviewerPosition']['desktop']['value'] ?? ($is_legacy ? 'bottom' : 'top');
        $content_padding       = $val('contentPadding', '30px|30px|30px|30px');
        $icon_alignment        = $val('iconAlignment', 'center');
        $use_custom_icon       = $val('useCustomIcon', 'off');
        $icon_img_val          = $val('iconImg', '');
        $icon_img              = is_array($icon_img_val) ? ($icon_img_val['src'] ?? '') : $icon_img_val;
        $icon_color            = $val('iconColor', '#333');
        $icon_bg               = $val('iconBg', 'transparent');
        $icon_bg_color         = $val('iconBgColor', '');
        $icon_size             = $val('iconSize', '70px');
        $icon_opacity          = $val('iconOpacity', '.2');
        $icon_padding          = $val('iconPadding', '0px|0px|0px|0px');
        $icon_top_spacing      = $val('iconTopSpacing', '40px');
        $icon_bottom_spacing   = $val('iconBottomSpacing', '5px');
        $icon_placement        = $val('iconPlacement', 'background');
        $image_placement_alt   = $val('imagePlacementAlt', 'top');
        $image_spacing         = $val('imageSpacing', '15px');
        $image_spacing_top     = $val('imageSpacingTop', '10px');
        $image_spacing_bottom  = $val('imageSpacingBottom', '10px');
        $image_width           = $val('imageWidth', '');
        $image_height          = $val('imageHeight', '');
        $ratings_spacing_top   = $val('ratingsSpacingTop', '0px');
        $ratings_spacing_bot   = $val('ratingsSpacingBottom', '0px');
        $stars_size            = $val('starsSize', '20px');
        $stars_color           = $val('starsColor', '#F3B325');
        $stars_spacing_between = $val('starsSpacingBetween', '5px');
        $name_bottom_spacing   = $val('nameBottomSpacing', '5px');
        $title_bottom_spacing  = $val('titleBottomSpacing', '5px');
        $review_top_spacing    = $advanced['reviewTopSpacing']['desktop']['value'] ?? ($is_legacy ? '0px' : '10px');
        $review_bottom_spacing = $val('reviewBottomSpacing', '20px');

        $styles = [];

        $push = function ($selector, $declaration, $at_rule = false) use (&$styles) {
            $styles[] = [
                'atRules'     => $at_rule,
                'selector'    => $selector,
                'declaration' => $declaration,
            ];
        };

        // Content (image left/right layouts only, like D4).
        if ('left' === $img_position || 'right' === $img_position) {
            $push($order_class . ' .dtq-testimonial-content', 'flex: 1 1;');
            $push($order_class . ' .dtq-testimonial-content', sprintf('padding: %s;', self::dtq_pad($content_padding)));
        }

        // Quote icon alignment.
        $push($order_class . ' .dtq-testimonial-inner .dtq-testimonial-icon', sprintf('text-align: %s!important;', $icon_alignment));
        if ('right' === $icon_alignment) {
            $push($order_class . ' .dtq-testimonial-inner .dtq-testimonial-icon', 'justify-content: flex-end!important;');
        } elseif ('center' === $icon_alignment) {
            $push($order_class . ' .dtq-testimonial-inner .dtq-testimonial-icon', 'justify-content: center!important;');
        }

        if ('off' === $use_custom_icon) {
            // Built-in SVG icon: background color (D4 get_custom_bg_style('icon')),
            // padding + opacity, fill color and responsive size (width only).
            if (!empty($icon_bg_color)) {
                $push($order_class . ' .dtq-testimonial-icon span', sprintf('background-color: %s !important;', $icon_bg_color));
            }
            $push($order_class . ' .dtq-testimonial-icon span', sprintf('padding: %1$s; opacity: %2$s;', self::dtq_pad($icon_padding), $icon_opacity));
            $push($order_class . ' .dtq-testimonial-icon svg', sprintf('fill: %s;', $icon_color));
            $push($order_class . ' .dtq-testimonial-icon svg', sprintf('width: %s;', $icon_size));
        } else {
            // Custom icon image rendered as the span background.
            $push(
                $order_class . ' .dtq-testimonial-icon span',
                sprintf(
                    'background: %1$s; opacity: %2$s; background-image: url(%3$s); background-position: center; background-repeat: no-repeat; background-size: contain;',
                    $icon_bg,
                    $icon_opacity,
                    $icon_img
                )
            );
            $push($order_class . ' .dtq-testimonial-icon span', sprintf('width: %s;', $icon_size));
            $push($order_class . ' .dtq-testimonial-icon span', sprintf('height: %s;', $icon_size));
        }

        // Quote icon placement: flow spacing vs absolute offsets.
        if ('absolute' !== $icon_placement) {
            $push($order_class . ' .dtq-testimonial-icon span', sprintf('margin-top: %1$s; margin-bottom: %2$s;', $icon_top_spacing, $icon_bottom_spacing));
        } else {
            $push(
                $order_class . ' .dtq-icon-absolute',
                self::absolute_decl(
                    $val('iconPosition', 'right_top'),
                    $val('iconIsCenterX', 'off'),
                    $val('iconIsCenterY', 'off'),
                    $val('iconOffsetX', '15px'),
                    $val('iconOffsetY', '15px'),
                    '999'
                )
            );
        }

        // Reviewer image: absolute position.
        if ('absolute' === $img_position) {
            $push(
                $order_class . ' .dtq-testimonial-img',
                self::absolute_decl(
                    $val('imagePlacement', 'left_top'),
                    $val('imgIsCenterX', 'off'),
                    $val('imgIsCenterY', 'off'),
                    $val('imgOffsetX', '50%'),
                    $val('imgOffsetY', '0px'),
                    '99'
                )
            );
        }

        // Reviewer image: relative (left/right of the reviewer text).
        if ('relative' === $img_position) {
            if ('top' !== $image_placement_alt) {
                $push($order_class . ' .dtq-testimonial-reviewer', 'display: flex; align-items: center;');
                $push($order_class . ' .dtq-testimonial-inner .dtq-testimonial-reviewer *', sprintf('text-align: %s;', $image_placement_alt));
            }
            if ('right' === $image_placement_alt) {
                $push($order_class . ' .dtq-testimonial-reviewer', 'flex-direction: row-reverse;');
            }
        }

        // Image height/width (D4 conditional defaults: left/right layouts get
        // initial height + 50% width, every other layout 65px).
        $is_side          = ('left' === $img_position || 'right' === $img_position);
        $image_height_val = !empty($image_height) ? $image_height : ($is_side ? 'initial' : '65px');
        $image_width_val  = !empty($image_width) ? $image_width : ($is_side ? '50%' : '65px');
        $push($order_class . ' .dtq-testimonial-img', sprintf('height: %s;', $image_height_val));
        $push($order_class . ' .dtq-testimonial-img', sprintf('width: %s;', $image_width_val));

        // Image spacing.
        if ('relative' === $img_position) {
            if ('top' === $image_placement_alt) {
                $push($order_class . ' .dtq-testimonial-img', sprintf('margin-bottom: %1$s; margin-top: %2$s;', $image_spacing_bottom, $image_spacing_top));
            } elseif ('left' === $image_placement_alt) {
                $push($order_class . ' .dtq-testimonial-img', sprintf('margin-right: %s;', $image_spacing));
            } elseif ('right' === $image_placement_alt) {
                $push($order_class . ' .dtq-testimonial-img', sprintf('margin-left: %s;', $image_spacing));
            }
        } elseif ('top' === $img_position) {
            $push($order_class . ' .dtq-testimonial-img', sprintf('margin-bottom: %1$s; margin-top: %2$s;', $image_spacing_bottom, $image_spacing_top));
        }

        // Ratings.
        $push($order_class . ' .dtq-testimonial-rating', sprintf('padding-bottom: %1$s; padding-top: %2$s;', $ratings_spacing_bot, $ratings_spacing_top));
        $push($order_class . ' .dtq-testimonial-rating span', sprintf('color: %1$s; font-size: %2$s;', $stars_color, $stars_size));
        if ('center' === $alignment) {
            $push($order_class . ' .dtq-testimonial-rating span', sprintf('margin: 0 calc(%s / 2);', $stars_spacing_between));
        } elseif ('right' === $alignment) {
            $push($order_class . ' .dtq-testimonial-rating span', sprintf('margin-left: %s;', $stars_spacing_between));
        } else {
            $push($order_class . ' .dtq-testimonial-rating span', sprintf('margin-right: %s;', $stars_spacing_between));
        }

        // Texts.
        $push($order_class . ' .dtq-testimonial-reviewer-text h3', sprintf('padding-bottom: %s;', $name_bottom_spacing));
        $push($order_class . ' .dtq-testimonial-title', sprintf('padding-bottom: %s;', $title_bottom_spacing));
        $push($order_class . ' .dtq-testimonial-review', sprintf('margin-bottom: %1$s; margin-top: %2$s;', $review_bottom_spacing, $review_top_spacing));

        // Bubble design.
        $review_design = $val('reviewDesign', 'normal');
        if ('bubble' === $review_design) {
            $bubble_bg_color  = $val('bubbleBgColor', '#efefef');
            $bubble_padding   = $val('bubblePadding', '15px|15px|15px|15px');
            $arrow_color      = $val('arrowColor', '#efefef');
            $arrow_placement  = $val('arrowPlacement', 'center');
            $arrow_position_x = $val('arrowPositionX', '15px');
            $radius           = explode('|', (string) $val('bubbleRadius', 'off|6px|6px|6px|6px'));
            $r                = function ($i) use ($radius) {
                return !empty($radius[$i]) ? $radius[$i] : '0px';
            };

            $push($order_class . ' .dtq-testimonial-review', sprintf('background-color: %s !important;', $bubble_bg_color));
            $push(
                $order_class . ' .dtq-testimonial-review',
                sprintf(
                    'position: relative; border-radius: %1$s %2$s %3$s %4$s; padding: %5$s;',
                    $r(1),
                    $r(2),
                    $r(3),
                    $r(4),
                    self::dtq_pad($bubble_padding)
                )
            );
            $push($order_class . ' .dtq-testimonial-review:after', 'content: ""; width: 0;height: 0;position: absolute;border-style: solid;');

            if ('bottom' === $reviewer_position) {
                $push($order_class . ' .dtq-testimonial-review:after', sprintf('border-width: 13px 13px 0 13px; border-color: %s transparent transparent transparent; top: 100%%;', $arrow_color));
            } elseif ('top' === $reviewer_position) {
                $push($order_class . ' .dtq-testimonial-review:after', sprintf('border-width: 0 13px 13px 13px; border-color: transparent transparent %s transparent; bottom: 100%%;', $arrow_color));
            }

            if ('left' === $arrow_placement) {
                $push($order_class . ' .dtq-testimonial-review:after', sprintf('left: %s;', $arrow_position_x));
            } elseif ('right' === $arrow_placement) {
                $push($order_class . ' .dtq-testimonial-review:after', sprintf('right: %s;', $arrow_position_x));
            } elseif ('center' === $arrow_placement) {
                $push($order_class . ' .dtq-testimonial-review:after', 'left: 50%; transform: translateX(-13px);');
            }
        }

        // ------------------------------------------------------------------
        // Responsive (tablet/phone) output for the D4 mobile-enabled fields:
        // content_padding, icon_size, icon_offset_x/y, image_width,
        // image_height. Purely additive: desktop declarations are untouched.
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
                return $node['tablet']['value'] !== ($node['desktop']['value'] ?? null);
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

            // Content padding (left/right image layouts only, like desktop).
            if ($is_side && $bp_changed('contentPadding', $breakpoint)) {
                $push($order_class . ' .dtq-testimonial-content', sprintf('padding: %s;', self::dtq_pad($bp_val('contentPadding', $breakpoint, $content_padding))), $at_rule);
            }

            // Icon size.
            if ($bp_changed('iconSize', $breakpoint)) {
                $bp_size = $bp_val('iconSize', $breakpoint, $icon_size);
                if ('off' === $use_custom_icon) {
                    $push($order_class . ' .dtq-testimonial-icon svg', sprintf('width: %s;', $bp_size), $at_rule);
                } else {
                    $push($order_class . ' .dtq-testimonial-icon span', sprintf('width: %s;', $bp_size), $at_rule);
                    $push($order_class . ' .dtq-testimonial-icon span', sprintf('height: %s;', $bp_size), $at_rule);
                }
            }

            // Absolute icon offsets.
            if ('absolute' === $icon_placement
                && ($bp_changed('iconOffsetX', $breakpoint) || $bp_changed('iconOffsetY', $breakpoint))
            ) {
                $push(
                    $order_class . ' .dtq-icon-absolute',
                    self::absolute_decl(
                        $val('iconPosition', 'right_top'),
                        $val('iconIsCenterX', 'off'),
                        $val('iconIsCenterY', 'off'),
                        $bp_val('iconOffsetX', $breakpoint, $val('iconOffsetX', '15px')),
                        $bp_val('iconOffsetY', $breakpoint, $val('iconOffsetY', '15px')),
                        '999'
                    ),
                    $at_rule
                );
            }

            // Image width/height.
            if ($bp_changed('imageHeight', $breakpoint)) {
                $push($order_class . ' .dtq-testimonial-img', sprintf('height: %s;', $bp_val('imageHeight', $breakpoint, $image_height_val)), $at_rule);
            }
            if ($bp_changed('imageWidth', $breakpoint)) {
                $push($order_class . ' .dtq-testimonial-img', sprintf('width: %s;', $bp_val('imageWidth', $breakpoint, $image_width_val)), $at_rule);
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
            // Module wrapper styles (background, spacing, border, box-shadow
            // are redirected to .dtq-testimonial-inner via module.json
            // styleProps, mirroring D4's advanced_fields css main).
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

            // Reviewer image border.
            $elements->style(['attrName' => 'image']),

            // Name font.
            $elements->style(['attrName' => 'name']),

            // Title font.
            $elements->style(['attrName' => 'title']),

            // Review body font.
            $elements->style(['attrName' => 'testimonial']),

            // Quote icon border.
            $elements->style(['attrName' => 'quoteIcon']),
        ];

        // Flat custom declarations ported from D4 render_css().
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
