<?php
/**
 * TeamBox: Module styles trait.
 *
 * @package DiviTorqueLite\Modules\TeamBox
 * @since   4.4.0
 */

namespace DiviTorqueLite\Modules\TeamBox\TeamBoxTrait;

if (!defined('ABSPATH')) {
    exit;
}

use ET\Builder\FrontEnd\Module\Style;
use ET\Builder\Packages\Module\Options\Css\CssStyle;

trait ModuleStylesTrait
{
    /**
     * Convert a spacing field value to a CSS padding/margin shorthand.
     *
     * After a D4->D5 migration, `convertSpacing` fields arrive as a D5 spacing
     * object `['top'=>..,'right'=>..,'bottom'=>..,'left'=>..]`, whereas legacy
     * data is the old pipe-string `"top|right|bottom|left"`. Accept both so the
     * styler never does an "Array to string conversion" on the object form.
     *
     * @param mixed $val The raw spacing value (object array or pipe-string).
     *
     * @return string The `top right bottom left` shorthand, or '' if empty.
     */
    public static function dtq_spacing($val)
    {
        if (empty($val)) {
            return '';
        }
        if (is_array($val)) {
            return sprintf(
                '%s %s %s %s',
                $val['top'] ?? '0px',
                $val['right'] ?? '0px',
                $val['bottom'] ?? '0px',
                $val['left'] ?? '0px'
            );
        }
        $p = explode('|', (string) $val);
        return 4 === count($p) ? implode(' ', $p) : (string) $val;
    }

    /**
     * Build the dynamic custom style array from flat advanced attrs.
     *
     * Mirrors the D4 `render_css()` output and the JS `styles.jsx`.
     *
     * @param string $order_class The module order class selector.
     * @param array  $advanced    The `module.advanced` attrs array.
     *
     * @return array Array of `['atRules' => false, 'selector' => ..., 'declaration' => ...]`.
     */
    public static function build_custom_styles($order_class, $advanced)
    {
        if (!is_array($advanced)) {
            $advanced = [];
        }

        $val = function ($key, $fallback) use ($advanced) {
            return $advanced[$key]['desktop']['value'] ?? $fallback;
        };
        $hover = function ($key, $fallback) use ($advanced, $val) {
            return $advanced[$key]['desktop']['hover'] ?? $val($key, $fallback);
        };

        $content_on_hover     = $val('contentOnHover', 'off');
        $hover_speed          = $val('hoverSpeed', '400ms');
        $links_position       = $val('linksPosition', 'content');
        $use_photo_abs        = $val('usePhotoAbs', 'off');
        $photo_placement      = $val('photoPlacement', 'left_top');
        $photo_offset_x       = $val('photoOffsetX', '50%');
        $photo_offset_y       = $val('photoOffsetY', '0px');
        $photo_alignment      = $val('photoAlignment', 'left');
        $content_alignment    = $val('contentAlignment', 'left');
        $links_margin_top     = $val('linksMarginTop', '15px');
        $links_margin_between = $val('linksMarginBetween', '5px');
        $links_bg             = $val('linksBg', '#e5e5e5');
        $links_radius         = $val('linksRadius', '4px');
        $links_height         = $val('linksHeight', '36px');
        $links_width          = $val('linksWidth', '36px');
        $social_icon_color    = $val('socialIconColor', '#333');
        $links_icon_size      = $val('linksIconSize', '16px');
        $link_bg_hover        = $hover('linksBg', $links_bg);
        $link_color_hover     = $hover('socialIconColor', $social_icon_color);

        $photo_width  = $val('photoWidth', 'auto');
        $photo_height = $val('photoHeight', 'auto');

        if ('off' === $content_on_hover && 'on' === $use_photo_abs) {
            $photo_width = 'auto' !== $photo_width ? $photo_width : '50%';
        }

        $styles = [];
        $push   = function ($selector, $declaration) use (&$styles) {
            $styles[] = [
                'atRules'     => false,
                'selector'    => $selector,
                'declaration' => $declaration,
            ];
        };

        if ('off' === $content_on_hover && 'photo' === $links_position) {
            $push(
                $order_class . ' .dtq-team-social',
                'position: absolute; top: 25px; left: 0px; width: 100%; z-index: 9999; justify-content: center;'
            );
            $push(
                $order_class . ' .dtq-team .dtq-team-social li',
                'transform: translateY(-20px); transition: .3s; opacity: 0;'
            );
            $push(
                $order_class . ' .dtq-team:hover .dtq-team-social li',
                'transform: translateX(0) translateY(0); transition: .3s; opacity: 1;'
            );
            for ($i = 0; $i < 10; $i++) {
                $push(
                    $order_class . ' .dtq-team .dtq-team-social li:nth-child(' . ($i + 1) . ')',
                    sprintf('transition-delay: .%1$ss;', $i)
                );
            }
        }

        if ('on' === $content_on_hover) {
            $push(
                $order_class . ' .dtq-team-content, ' . $order_class . ' .dtq-team-content *',
                sprintf('transition: %1$s all ease-in-out;', $hover_speed)
            );
        }

        // Text spacing.
        $push($order_class . ' .dtq-team-content h3', sprintf('padding-bottom: %1$s;', $val('nameBottomSpacing', '10px')));
        $push($order_class . ' .dtq-team-content-job-title', sprintf('padding-bottom: %1$s;', $val('jobBottomSpacing', '10px')));

        // Photo absolute placement.
        if ('off' === $content_on_hover && 'on' === $use_photo_abs) {
            $placement = explode('_', $photo_placement);
            $push($order_class . ' .dtq-team figure', 'position: absolute; z-index: 99;');
            $push($order_class . ' .dtq-team figure', sprintf('%1$s: %2$s;', $placement[0], $photo_offset_x));
            $push($order_class . ' .dtq-team figure', sprintf('%1$s: %2$s;', $placement[1], $photo_offset_y));

            $transforms = [
                'right_top'    => 'transform: translateX(50%) translateY(-50%);',
                'right_bottom' => 'transform: translateX(50%) translateY(50%);',
                'left_bottom'  => 'transform: translateX(-50%) translateY(50%);',
                'left_top'     => 'transform: translateX(-50%) translateY(-50%);',
            ];
            if (isset($transforms[$photo_placement])) {
                $push($order_class . ' .dtq-team figure', $transforms[$photo_placement]);
            }
        }

        // Photo width & height.
        $push($order_class . ' .dtq-team figure', sprintf('width: %1$s;', $photo_width));
        if ('auto' !== $photo_height) {
            $push($order_class . ' .dtq-team figure', sprintf('height: %1$s;', $photo_height));
            $push($order_class . ' .dtq-team figure img', 'height: 100%; object-fit: cover; width: 100%;');
        }

        // Photo alignment.
        if ('off' === $use_photo_abs) {
            if ('center' === $photo_alignment) {
                $push($order_class . ' .dtq-team figure', 'margin-left: auto; margin-right: auto;');
            } elseif ('right' === $photo_alignment) {
                $push($order_class . ' .dtq-team figure', 'margin-left: auto;');
            }
        }

        // Social icons.
        if ('on' === $content_on_hover || 'content' === $links_position) {
            $push($order_class . ' .dtq-team-social', sprintf('padding-top: %1$s!important;', $links_margin_top));
        }

        $push(
            $order_class . ' .dtq-icon',
            sprintf(
                'background-color: %1$s; border-radius: %2$s; height: %3$s; width: %4$s;',
                $links_bg,
                $links_radius,
                $links_height,
                $links_width
            )
        );
        $push(
            $order_class . ' .dtq-icon svg',
            sprintf('fill: %1$s!important; width: %2$s!important;', $social_icon_color, $links_icon_size)
        );

        if ('left' === $content_alignment) {
            $push($order_class . ' .dtq-team-social .dtq-icon', sprintf('margin-right: %1$s;', $links_margin_between));
        } elseif ('right' === $content_alignment) {
            $push($order_class . ' .dtq-team-social .dtq-icon', sprintf('margin-left: %1$s;', $links_margin_between));
        } else {
            $push(
                $order_class . ' .dtq-team-social .dtq-icon',
                sprintf('margin-left: %1$s; margin-right: %1$s;', $links_margin_between)
            );
        }

        // Social icons hover.
        $push($order_class . ' .dtq-team-social .dtq-icon:hover', sprintf('background-color: %1$s;', $link_bg_hover));
        $push($order_class . ' .dtq-team-social .dtq-icon:hover svg', sprintf('fill: %1$s!important;', $link_color_hover));

        // Content background.
        $content_bg       = $val('contentBgColor', '#ffffff');
        $content_bg_hover = $hover('contentBgColor', $content_bg);
        $push($order_class . ' .dtq-team-content', sprintf('background-color: %1$s;', $content_bg));
        $push($order_class . ':hover .dtq-team-content', sprintf('background-color: %1$s;', $content_bg_hover));

        // Content padding. Accept both the D5 spacing object and a pipe-string.
        $padding = $advanced['contentPadding']['desktop']['value'] ?? null;
        $padding_css = self::dtq_spacing($padding);
        if ('' !== $padding_css) {
            $push(
                $order_class . ' .dtq-team-content',
                sprintf('padding: %1$s;', $padding_css)
            );
        }

        // Overlay.
        if ('on' === $val('overlayOnHover', 'off')) {
            $overlay_color        = $val('overlayColor', '#2EA3F2');
            $overlay_icon_color   = $val('overlayIconColor', '#2EA3F2');
            $overlay_icon_size    = $val('overlayIconSize', '32px');
            $overlay_icon_opacity = $val('overlayIconOpacity', '1');
            $overlay_speed        = $val('overlayHoverSpeed', '500ms');

            $push(
                $order_class . ' .dtq-overlay',
                sprintf(
                    'opacity: 0; background-color: %1$s; color: %2$s; transition: all %3$s;',
                    $overlay_color,
                    $overlay_icon_color,
                    $overlay_speed
                )
            );
            $push($order_class . ':hover .dtq-overlay', 'opacity: 1;');
            $push(
                $order_class . ' .dtq-overlay .dtq-overlay-icon',
                sprintf('font-size: %1$s; opacity: %2$s;', $overlay_icon_size, $overlay_icon_opacity)
            );
        }

        // -------------------------------------------------------------------
        // Responsive (tablet / phone) output. Additive only: rebuilds the
        // declarations for fields with `"responsive": true` per breakpoint,
        // emitting an entry only when that breakpoint's value exists and
        // differs from the next-larger breakpoint. Desktop output above is
        // untouched.
        // -------------------------------------------------------------------
        $breakpoints = [
            'tablet' => '@media only screen and (max-width: 980px)',
            'phone'  => '@media only screen and (max-width: 767px)',
        ];

        // Value at a breakpoint, falling back up the chain (phone->tablet->desktop).
        $bp_val = function ($key, $bp) use ($advanced) {
            $chain = 'phone' === $bp ? ['phone', 'tablet', 'desktop'] : ['tablet', 'desktop'];
            foreach ($chain as $level) {
                if (isset($advanced[$key][$level]['value'])) {
                    return $advanced[$key][$level]['value'];
                }
            }
            return null;
        };

        // Raw value at exactly one breakpoint (no fallback) — used to decide
        // whether this breakpoint actually overrides the larger one.
        $bp_raw = function ($key, $bp) use ($advanced) {
            return $advanced[$key][$bp]['value'] ?? null;
        };

        $push_at = function ($at_rule, $selector, $declaration) use (&$styles) {
            $styles[] = [
                'atRules'     => $at_rule,
                'selector'    => $selector,
                'declaration' => $declaration,
            ];
        };

        foreach ($breakpoints as $bp => $at_rule) {
            // nameBottomSpacing -> .dtq-team-content h3 { padding-bottom }
            if (null !== $bp_raw('nameBottomSpacing', $bp)) {
                $push_at(
                    $at_rule,
                    $order_class . ' .dtq-team-content h3',
                    sprintf('padding-bottom: %1$s;', $bp_val('nameBottomSpacing', $bp))
                );
            }

            // jobBottomSpacing -> .dtq-team-content-job-title { padding-bottom }
            if (null !== $bp_raw('jobBottomSpacing', $bp)) {
                $push_at(
                    $at_rule,
                    $order_class . ' .dtq-team-content-job-title',
                    sprintf('padding-bottom: %1$s;', $bp_val('jobBottomSpacing', $bp))
                );
            }

            // photoWidth -> .dtq-team figure { width }
            // Mirror desktop abs-mode semantics: when content-on-hover is off
            // and the photo is absolutely placed, 'auto' resolves to '50%'.
            if (null !== $bp_raw('photoWidth', $bp)) {
                $bp_photo_width = $bp_val('photoWidth', $bp);
                if ('off' === $content_on_hover && 'on' === $use_photo_abs) {
                    $bp_photo_width = 'auto' !== $bp_photo_width ? $bp_photo_width : '50%';
                }
                $push_at(
                    $at_rule,
                    $order_class . ' .dtq-team figure',
                    sprintf('width: %1$s;', $bp_photo_width)
                );
            }

            // photoHeight -> .dtq-team figure { height } + img cover rule.
            // Desktop only emits height when value !== 'auto'; mirror that.
            if (null !== $bp_raw('photoHeight', $bp)) {
                $bp_photo_height = $bp_val('photoHeight', $bp);
                if ('auto' !== $bp_photo_height) {
                    $push_at(
                        $at_rule,
                        $order_class . ' .dtq-team figure',
                        sprintf('height: %1$s;', $bp_photo_height)
                    );
                    $push_at(
                        $at_rule,
                        $order_class . ' .dtq-team figure img',
                        'height: 100%; object-fit: cover; width: 100%;'
                    );
                }
            }

            // contentPadding -> .dtq-team-content { padding }. Accept both the
            // D5 spacing object and a pipe-string at this breakpoint.
            if (null !== $bp_raw('contentPadding', $bp)) {
                $bp_padding_css = self::dtq_spacing($bp_val('contentPadding', $bp));
                if ('' !== $bp_padding_css) {
                    $push_at(
                        $at_rule,
                        $order_class . ' .dtq-team-content',
                        sprintf('padding: %1$s;', $bp_padding_css)
                    );
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
        $custom_styles = self::build_custom_styles($order_class, $advanced);

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

            // Element font / decoration styles.
            $elements->style(['attrName' => 'photo']),
            $elements->style(['attrName' => 'contentWrap']),
            $elements->style(['attrName' => 'memberName']),
            $elements->style(['attrName' => 'jobTitle']),
            $elements->style(['attrName' => 'shortBio']),
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
