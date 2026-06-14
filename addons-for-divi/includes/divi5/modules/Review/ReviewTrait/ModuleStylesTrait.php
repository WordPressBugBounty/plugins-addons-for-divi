<?php
/**
 * Review: Module styles trait.
 *
 * @package DiviTorqueLite\Modules\Review
 * @since   4.4.0
 */

namespace DiviTorqueLite\Modules\Review\ReviewTrait;

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
     * Build the flat custom-style declarations that mirror the D4 render_css()
     * `ET_Builder_Element::set_style()` calls. Keep this in lockstep with the
     * JS twin in src/divi5/modules/review/styles.jsx.
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

        $scale               = (float) $val('scale', '5');
        $rating              = (float) $val('rating', '5');
        $img_pos             = $val('imgPos', 'top');
        $img_height          = $val('imgHeight', '');
        $img_width           = $val('imgWidth', '50%');
        $img_padding         = $val('imgPadding', '0px|0px|0px|0px');
        $title_bottom        = $val('titleBottomSpacing', '0px');
        $content_align       = $val('contentAlignment', 'left');
        $content_padding     = $val('contentPadding', '15px|0px|0px|0px');
        $rating_bottom       = $val('ratingBottomSpacing', '10px');
        $star_size           = $val('starSize', '23px');
        $star_spacing        = $val('starSpacing', '0px');
        $star_color          = $val('starColor', '#2EA3F2');
        $star_active_color   = $val('starActiveColor', '#2EA3F2');
        $rating_text_size    = $val('ratingTextSize', '16px');
        $rating_text_color   = $val('ratingTextColor', '#2EA3F2');
        $rating_text_spacing = $val('ratingTextSpacing', '8px');
        $btn_spacing_top     = $val('btnSpacingTop', '15px');

        $styles = [];

        $push = function ($selector, $declaration, $at_rule = false) use (&$styles) {
            $styles[] = [
                'atRules'     => $at_rule,
                'selector'    => $selector,
                'declaration' => $declaration,
            ];
        };

        // Image position -> flex-direction (mirrors the four D4 branches).
        $flex_directions = [
            'top'    => 'column',
            'bottom' => 'column-reverse',
            'left'   => 'row',
            'right'  => 'row-reverse',
        ];
        $push($order_class . ' .dtq-review', sprintf('flex-direction: %s;', $flex_directions[$img_pos] ?? 'column'));

        // Image height.
        if (!empty($img_height)) {
            $push($order_class . ' .dtq-rating-figure img', sprintf('height: %s;', $img_height));
        }

        // Image width (left/right layouts only, like D4).
        if ('left' === $img_pos || 'right' === $img_pos) {
            $push($order_class . ' .dtq-rating-figure', sprintf('flex: 0 0 %1$s; max-width: %1$s;', $img_width));
        }

        // Image padding.
        $push($order_class . ' .dtq-rating-figure img', sprintf('padding: %s;', self::dtq_pad($img_padding)));

        // Title bottom spacing.
        $push($order_class . ' .dtq-rating-star-title', sprintf('padding-bottom: %s;', $title_bottom));

        // Content alignment (text-align !important + ratings justify-content, like D4).
        $push($order_class . ' .dtq-review-content', sprintf('text-align: %s !important;', $content_align));
        $push($order_class . ' .dtq-ratings', sprintf('justify-content: %s;', $content_align));

        // Content padding.
        $push($order_class . ' .dtq-review-content', sprintf('padding: %s;', self::dtq_pad($content_padding)));

        // Rating bottom spacing.
        $push($order_class . ' .dtq-ratings', sprintf('padding-bottom: %s;', $rating_bottom));

        // Star size.
        $push($order_class . ' .dtq-stars-wrap .dtq-star', sprintf('font-size: %s;', $star_size));

        // Star spacing (positive on stars, negative on the wrap, like D4).
        $push($order_class . ' .dtq-stars-wrap .dtq-star', sprintf('margin-left: %1$s; margin-right: %1$s;', $star_spacing));
        $push($order_class . ' .dtq-stars-wrap', sprintf('margin-left: -%1$s; margin-right: -%1$s;', $star_spacing));

        // Star colors.
        $push($order_class . ' .dtq-star', sprintf('color: %s;', $star_color));
        $push($order_class . ' .dtq-stars-act .dtq-star', sprintf('color: %s;', $star_active_color));

        // Star fill: the active-star overlay is clipped to (rating / scale * 100)%.
        // D4 did this via the `--active-width` inline custom property; D5 emits
        // the width directly (also emitted by styles.jsx in the VB).
        $fill_pct = $scale > 0 ? round((100 * $rating) / $scale, 4) : 100;
        $push($order_class . ' .dtq-stars-act', sprintf('width: %s%%;', $fill_pct));

        // Rating text.
        $push($order_class . ' .dtq-ratings-number', sprintf('font-size: %s;', $rating_text_size));
        $push($order_class . ' .dtq-ratings-number', sprintf('padding-left: %1$s; color: %2$s;', $rating_text_spacing, $rating_text_color));

        // Button spacing top.
        $push($order_class . ' .dtq-rating-btn-wrap', sprintf('padding-top: %s !important;', $btn_spacing_top));

        // Overlay (mirrors D4 get_overlay_style()).
        $overlay_on       = $val('overlayOnHover', 'on');
        $overlay_speed    = $val('overlayHoverSpeed', '500ms');
        $overlay_icon_col = $val('overlayIconColor', '#2EA3F2');
        $overlay_icon_sz  = $val('overlayIconSize', '32px');
        $overlay_icon_op  = $val('overlayIconOpacity', '1');
        $overlay_color    = $val('overlayColor', '#2EA3F2');
        if ('on' === $overlay_on) {
            $push($order_class . ' .dtq-overlay', 'opacity: 0;');
            $push($order_class . ':hover .dtq-overlay', 'opacity: 1;');
        }
        $push($order_class . ' .dtq-overlay', sprintf('color: %1$s; transition: all %2$s; background-color: %3$s;', $overlay_icon_col, $overlay_speed, $overlay_color));
        $push($order_class . ' .dtq-overlay .dtq-overlay-icon', sprintf('font-size: %1$s; opacity: %2$s;', $overlay_icon_sz, $overlay_icon_op));

        // Badge (absolute positioning + background + padding; mirrors D4
        // get_badge_styles() / get_absolute_element_styles()).
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

        $badge_decl = function ($off_x, $off_y, $pad_val) use ($pos_x, $pos_y, $badge_center_x, $badge_center_y, $badge_bg) {
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
                self::dtq_pad($pad_val),
                $badge_bg
            );
        };
        $push($order_class . ' .dtq-review-badge', $badge_decl($badge_offset_x, $badge_offset_y, $badge_padding));

        // ------------------------------------------------------------------
        // Responsive (tablet/phone) output. Purely additive: the desktop
        // declarations above are untouched. For each responsive field, we
        // append an @media entry only when the breakpoint value exists and
        // differs from the next-larger breakpoint.
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

            // Image height.
            if (!empty($img_height) && $bp_changed('imgHeight', $breakpoint)) {
                $push($order_class . ' .dtq-rating-figure img', sprintf('height: %s;', $bp_val('imgHeight', $breakpoint, $img_height)), $at_rule);
            }

            // Image width (left/right layouts only).
            if (('left' === $img_pos || 'right' === $img_pos) && $bp_changed('imgWidth', $breakpoint)) {
                $bp_w = $bp_val('imgWidth', $breakpoint, $img_width);
                $push($order_class . ' .dtq-rating-figure', sprintf('flex: 0 0 %1$s; max-width: %1$s;', $bp_w), $at_rule);
            }

            // Image padding.
            if ($bp_changed('imgPadding', $breakpoint)) {
                $push($order_class . ' .dtq-rating-figure img', sprintf('padding: %s;', self::dtq_pad($bp_val('imgPadding', $breakpoint, $img_padding))), $at_rule);
            }

            // Title bottom spacing.
            if ($bp_changed('titleBottomSpacing', $breakpoint)) {
                $push($order_class . ' .dtq-rating-star-title', sprintf('padding-bottom: %s;', $bp_val('titleBottomSpacing', $breakpoint, $title_bottom)), $at_rule);
            }

            // Content alignment.
            if ($bp_changed('contentAlignment', $breakpoint)) {
                $bp_align = $bp_val('contentAlignment', $breakpoint, $content_align);
                $push($order_class . ' .dtq-review-content', sprintf('text-align: %s !important;', $bp_align), $at_rule);
                $push($order_class . ' .dtq-ratings', sprintf('justify-content: %s;', $bp_align), $at_rule);
            }

            // Content padding.
            if ($bp_changed('contentPadding', $breakpoint)) {
                $push($order_class . ' .dtq-review-content', sprintf('padding: %s;', self::dtq_pad($bp_val('contentPadding', $breakpoint, $content_padding))), $at_rule);
            }

            // Rating bottom spacing.
            if ($bp_changed('ratingBottomSpacing', $breakpoint)) {
                $push($order_class . ' .dtq-ratings', sprintf('padding-bottom: %s;', $bp_val('ratingBottomSpacing', $breakpoint, $rating_bottom)), $at_rule);
            }

            // Star size.
            if ($bp_changed('starSize', $breakpoint)) {
                $push($order_class . ' .dtq-stars-wrap .dtq-star', sprintf('font-size: %s;', $bp_val('starSize', $breakpoint, $star_size)), $at_rule);
            }

            // Rating text size.
            if ($bp_changed('ratingTextSize', $breakpoint)) {
                $push($order_class . ' .dtq-ratings-number', sprintf('font-size: %s;', $bp_val('ratingTextSize', $breakpoint, $rating_text_size)), $at_rule);
            }

            // Button spacing top.
            if ($bp_changed('btnSpacingTop', $breakpoint)) {
                $push($order_class . ' .dtq-rating-btn-wrap', sprintf('padding-top: %s !important;', $bp_val('btnSpacingTop', $breakpoint, $btn_spacing_top)), $at_rule);
            }

            // Badge offsets + padding (rebuild full badge declaration per breakpoint).
            if ($bp_changed('badgeOffsetX', $breakpoint)
                || $bp_changed('badgeOffsetY', $breakpoint)
                || $bp_changed('badgePadding', $breakpoint)
            ) {
                $push(
                    $order_class . ' .dtq-review-badge',
                    $badge_decl(
                        $bp_val('badgeOffsetX', $breakpoint, $badge_offset_x),
                        $bp_val('badgeOffsetY', $breakpoint, $badge_offset_y),
                        $bp_val('badgePadding', $breakpoint, $badge_padding)
                    ),
                    $at_rule
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

            // Image border.
            $elements->style(['attrName' => 'image']),

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
