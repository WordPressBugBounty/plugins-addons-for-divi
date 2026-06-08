<?php
/**
 * InfoCard: Module styles trait.
 *
 * @package DiviTorqueLite\Modules\InfoCard
 * @since   4.4.0
 */

namespace DiviTorqueLite\Modules\InfoCard\InfoCardTrait;

if (!defined('ABSPATH')) {
    exit;
}

use ET\Builder\FrontEnd\Module\Style;
use ET\Builder\Packages\Module\Options\Css\CssStyle;

trait ModuleStylesTrait
{
    /**
     * Convert a D4 padding string ("t|r|b|l") to a CSS padding shorthand.
     *
     * @param string $val Pipe-delimited padding value.
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
     * Build the flat custom-style declarations that mirror the D4 render_css()
     * `ET_Builder_Element::set_style()` calls.
     *
     * @param array  $attrs       Module attributes.
     * @param string $order_class Module order class selector.
     * @return array Array of ['atRules'=>false,'selector'=>..,'declaration'=>..].
     */
    public static function build_custom_styles($attrs, $order_class)
    {
        $advanced = $attrs['module']['advanced'] ?? [];

        $val = function ($key, $fallback) use ($advanced) {
            return $advanced[$key]['desktop']['value'] ?? $fallback;
        };

        $use_icon         = $val('useIcon', 'off');
        $image_position   = $val('imagePosition', 'top');
        $image_overflow   = $val('imageOverflow', 'hidden');
        $content_overflow = $val('contentOverflow', 'visible');
        $content_align    = $val('contentAlignment', 'left');
        $icon_color       = $val('iconColor', '#333');
        $icon_size        = $val('iconSize', '45px');
        $image_width      = $val('imageWidth', 'auto');
        $custom_height    = $val('customHeight', 'on');
        $image_height     = $val('imageHeight', '300px');
        $icon_padding     = $val('iconPadding', '25px|25px|25px|25px');
        $image_padding    = $val('imagePadding', '0px|0px|0px|0px');
        $content_padding  = $val('contentPadding', '25px|25px|25px|25px');
        $btn_spacing_top  = $val('btnSpacingTop', '10px');
        $title_bottom     = $val('titleBottomSpacing', '10px');

        if ('auto' === $image_width) {
            $image_width = ('top' !== $image_position) ? '50%' : '100%';
        }

        $styles = [];

        $push = function ($selector, $declaration) use (&$styles) {
            $styles[] = [
                'atRules'     => false,
                'selector'    => $selector,
                'declaration' => $declaration,
            ];
        };

        // Content alignment.
        $push($order_class . ' .dtq-card', sprintf('text-align: %s;', $content_align));
        $push($order_class . ' .dtq-card-figure', sprintf('align-self: %s;', $content_align));

        // Image/Icon position -> flex-direction.
        if ('top' === $image_position) {
            $push($order_class . ' .dtq-card', 'flex-direction: column;');
        } elseif ('right' === $image_position) {
            $push($order_class . ' .dtq-card', 'flex-direction: row-reverse;');
        }

        // Overflow.
        $push($order_class . ' .dtq-card-figure', sprintf('overflow: %s !important;', $image_overflow));
        $push($order_class, sprintf('overflow: %s !important;', $content_overflow));

        // Button spacing top.
        $push($order_class . ' .dtq-btn-card-wrap', sprintf('padding-top: %s;', $btn_spacing_top));

        // Title bottom spacing.
        $push($order_class . ' .dtq-card-title', sprintf('padding-bottom: %s;', $title_bottom));

        if ('on' === $use_icon) {
            $push($order_class . ' .dtq-card .dtq-card-icon', sprintf('padding: %s;', self::dtq_pad($icon_padding)));
        } else {
            $push($order_class . ' .dtq-card-figure img', sprintf('padding: %s;', self::dtq_pad($image_padding)));
            if ('on' === $custom_height) {
                $push($order_class . ' .dtq-card-figure', sprintf('height: %s;', $image_height));
            }
            $push($order_class . ' .dtq-card-figure', sprintf('width: %1$s; max-width: %1$s;', $image_width));
        }

        // Content padding.
        $push($order_class . ' .dtq-card-content', sprintf('padding: %s;', self::dtq_pad($content_padding)));

        // Icon color + size.
        $push($order_class . ' .dtq-card-icon i', sprintf('color: %1$s; font-size: %2$s;', $icon_color, $icon_size));

        // Icon background color.
        $icon_bg = $val('iconBgColor', '');
        if ('on' === $use_icon && !empty($icon_bg)) {
            $push($order_class . ' .dtq-card-icon', sprintf('background-color: %s;', $icon_bg));
        }

        // Overlay.
        $overlay_color    = $val('overlayColor', '#2EA3F2');
        $overlay_icon_col = $val('overlayIconColor', '#2EA3F2');
        $overlay_icon_sz  = $val('overlayIconSize', '32px');
        $overlay_icon_op  = $val('overlayIconOpacity', '1');
        $overlay_speed    = $val('overlayHoverSpeed', '500ms');
        $push($order_class . ' .dtq-overlay', sprintf('background-color: %1$s; transition: all %2$s;', $overlay_color, $overlay_speed));
        $push(
            $order_class . ' .dtq-overlay .dtq-overlay-icon',
            sprintf('color: %1$s; font-size: %2$s; opacity: %3$s;', $overlay_icon_col, $overlay_icon_sz, $overlay_icon_op)
        );

        // Badge (absolute positioning + background + padding).
        $badge_pos      = $val('badgePosition', 'right_top');
        $badge_offset_x = $val('badgeOffsetX', '15px');
        $badge_offset_y = $val('badgeOffsetY', '15px');
        $badge_padding  = $val('badgePadding', '5px|15px|5px|15px');
        $badge_bg       = $val('badgeBgColor', '#ffffff');
        $badge_center_x = $val('badgeIsCenterX', 'off');
        $badge_center_y = $val('badgeIsCenterY', 'off');
        $pos_parts      = explode('_', (string) $badge_pos);
        $pos_x          = $pos_parts[0] ?? 'right';
        $pos_y          = $pos_parts[1] ?? 'top';

        // Mirror D4 get_absolute_element_styles() centering + translate logic.
        $badge_axis_decl = function ($off_x, $off_y) use ($pos_x, $pos_y, $badge_center_x, $badge_center_y, $badge_padding, $badge_bg) {
            $x_val = ('on' === $badge_center_x) ? '50%' : $off_x;
            $y_val = ('on' === $badge_center_y) ? '50%' : $off_y;
            $tx    = '0';
            $ty    = '0';
            if ('on' === $badge_center_x) {
                $tx = ('right' === $pos_x) ? '50%' : '-50%';
            }
            if ('on' === $badge_center_y) {
                $ty = ('top' === $pos_y) ? '-50%' : '50%';
            }
            return sprintf(
                'position: absolute; z-index: 999; %1$s: %2$s; %3$s: %4$s; transform: translateX(%5$s) translateY(%6$s); padding: %7$s; background-color: %8$s;',
                $pos_x,
                $x_val,
                $pos_y,
                $y_val,
                $tx,
                $ty,
                self::dtq_pad($badge_padding),
                $badge_bg
            );
        };
        $push($order_class . ' .dtq-card-badge', $badge_axis_decl($badge_offset_x, $badge_offset_y));

        // ------------------------------------------------------------------
        // Responsive (tablet/phone) output. Purely additive: the desktop
        // declarations above are untouched. For each responsive field, we
        // append an @media entry only when the breakpoint value exists and
        // differs from the next-larger breakpoint.
        // ------------------------------------------------------------------

        // Resolve a breakpoint value, walking up the chain (phone->tablet->desktop).
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

        // Whether a breakpoint has its own value that differs from the next-larger one.
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

        $push_media = function ($at_rule, $selector, $declaration) use (&$styles) {
            $styles[] = [
                'atRules'     => $at_rule,
                'selector'    => $selector,
                'declaration' => $declaration,
            ];
        };

        foreach (['tablet', 'phone'] as $breakpoint) {
            $at_rule = $at_rules[$breakpoint];

            // Content alignment (text-align + align-self).
            if ($bp_changed('contentAlignment', $breakpoint)) {
                $bp_align = $bp_val('contentAlignment', $breakpoint, $content_align);
                $push_media($at_rule, $order_class . ' .dtq-card', sprintf('text-align: %s;', $bp_align));
                $push_media($at_rule, $order_class . ' .dtq-card-figure', sprintf('align-self: %s;', $bp_align));
            }

            // Button spacing top.
            if ($bp_changed('btnSpacingTop', $breakpoint)) {
                $bp_btn_top = $bp_val('btnSpacingTop', $breakpoint, $btn_spacing_top);
                $push_media($at_rule, $order_class . ' .dtq-btn-card-wrap', sprintf('padding-top: %s;', $bp_btn_top));
            }

            // Image/Icon padding, height, width (mirror desktop useIcon branch).
            if ('on' === $use_icon) {
                if ($bp_changed('iconPadding', $breakpoint)) {
                    $bp_icon_pad = $bp_val('iconPadding', $breakpoint, $icon_padding);
                    $push_media($at_rule, $order_class . ' .dtq-card .dtq-card-icon', sprintf('padding: %s;', self::dtq_pad($bp_icon_pad)));
                }
            } else {
                if ($bp_changed('imagePadding', $breakpoint)) {
                    $bp_image_pad = $bp_val('imagePadding', $breakpoint, $image_padding);
                    $push_media($at_rule, $order_class . ' .dtq-card-figure img', sprintf('padding: %s;', self::dtq_pad($bp_image_pad)));
                }
                if ('on' === $custom_height && $bp_changed('imageHeight', $breakpoint)) {
                    $bp_image_height = $bp_val('imageHeight', $breakpoint, $image_height);
                    $push_media($at_rule, $order_class . ' .dtq-card-figure', sprintf('height: %s;', $bp_image_height));
                }
                if ($bp_changed('imageWidth', $breakpoint)) {
                    $bp_image_width = $bp_val('imageWidth', $breakpoint, $image_width);
                    if ('auto' === $bp_image_width) {
                        $bp_image_width = ('top' !== $image_position) ? '50%' : '100%';
                    }
                    $push_media($at_rule, $order_class . ' .dtq-card-figure', sprintf('width: %1$s; max-width: %1$s;', $bp_image_width));
                }
            }

            // Content padding.
            if ($bp_changed('contentPadding', $breakpoint)) {
                $bp_content_pad = $bp_val('contentPadding', $breakpoint, $content_padding);
                $push_media($at_rule, $order_class . ' .dtq-card-content', sprintf('padding: %s;', self::dtq_pad($bp_content_pad)));
            }

            // Icon size (rebuild color + size declaration; color is non-responsive).
            if ($bp_changed('iconSize', $breakpoint)) {
                $bp_icon_size = $bp_val('iconSize', $breakpoint, $icon_size);
                $push_media($at_rule, $order_class . ' .dtq-card-icon i', sprintf('color: %1$s; font-size: %2$s;', $icon_color, $bp_icon_size));
            }

            // Badge offsets + padding (rebuild full badge declaration per breakpoint).
            if ($bp_changed('badgeOffsetX', $breakpoint)
                || $bp_changed('badgeOffsetY', $breakpoint)
                || $bp_changed('badgePadding', $breakpoint)
            ) {
                $bp_badge_x   = $bp_val('badgeOffsetX', $breakpoint, $badge_offset_x);
                $bp_badge_y   = $bp_val('badgeOffsetY', $breakpoint, $badge_offset_y);
                $bp_badge_pad = $bp_val('badgePadding', $breakpoint, $badge_padding);
                $x_val        = ('on' === $badge_center_x) ? '50%' : $bp_badge_x;
                $y_val        = ('on' === $badge_center_y) ? '50%' : $bp_badge_y;
                $tx           = '0';
                $ty           = '0';
                if ('on' === $badge_center_x) {
                    $tx = ('right' === $pos_x) ? '50%' : '-50%';
                }
                if ('on' === $badge_center_y) {
                    $ty = ('top' === $pos_y) ? '-50%' : '50%';
                }
                $push_media(
                    $at_rule,
                    $order_class . ' .dtq-card-badge',
                    sprintf(
                        'position: absolute; z-index: 999; %1$s: %2$s; %3$s: %4$s; transform: translateX(%5$s) translateY(%6$s); padding: %7$s; background-color: %8$s;',
                        $pos_x,
                        $x_val,
                        $pos_y,
                        $y_val,
                        $tx,
                        $ty,
                        self::dtq_pad($bp_badge_pad),
                        $badge_bg
                    )
                );
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
            // Module wrapper styles (background, spacing, border, etc.).
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

            // Photo (image/icon) border.
            $elements->style(['attrName' => 'photo']),

            // Badge font + border.
            $elements->style(['attrName' => 'badge']),

            // Title font / heading.
            $elements->style(['attrName' => 'title']),

            // Description body font.
            $elements->style(['attrName' => 'description']),

            // Button styles.
            $elements->style(['attrName' => 'button']),
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
