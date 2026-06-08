<?php
/**
 * InfoBox: Module styles trait.
 *
 * @package DiviTorqueLite\Modules\InfoBox
 * @since   4.4.0
 */

namespace DiviTorqueLite\Modules\InfoBox\InfoBoxTrait;

if (!defined('ABSPATH')) {
    exit;
}

use ET\Builder\FrontEnd\Module\Style;
use ET\Builder\Packages\Module\Options\Css\CssStyle;

trait ModuleStylesTrait
{
    /**
     * Build the static custom-style declarations that mirror the D4
     * `render_css()` `ET_Builder_Element::set_style` calls.
     *
     * @param array  $advanced    The `module.advanced` attrs array.
     * @param string $order_class The module order selector.
     *
     * @return array List of `['atRules','selector','declaration']` rows.
     */
    public static function build_custom_styles($advanced, $order_class)
    {
        $styles = [];
        if (!is_array($advanced)) {
            return $styles;
        }

        $main_figure       = $advanced['mainFigure']['desktop']['value']         ?? 'image';
        $figure_placement  = $advanced['figurePlacement']['desktop']['value']    ?? 'top';
        $equalize_content  = $advanced['equalizeContent']['desktop']['value']    ?? 'off';
        $align_items       = $advanced['alignItems']['desktop']['value']         ?? 'flex-start';
        $image_width       = $advanced['imageWidth']['desktop']['value']         ?? '100%';
        $image_height      = $advanced['imageHeight']['desktop']['value']        ?? 'auto';
        $use_icon_box      = $advanced['useIconBox']['desktop']['value']         ?? 'off';
        $icon_bg           = $advanced['iconBg']['desktop']['value']             ?? 'transparent';
        $icon_color        = $advanced['iconColor']['desktop']['value']          ?? '#333';
        $icon_size         = $advanced['iconSize']['desktop']['value']           ?? '45px';
        $icon_height       = $advanced['iconHeight']['desktop']['value']         ?? '80px';
        $icon_width        = $advanced['iconWidth']['desktop']['value']          ?? '80px';
        $btn_spacing_top   = $advanced['btnSpacingTop']['desktop']['value']      ?? '15px';
        $title_spacing     = $advanced['titleBottomSpacing']['desktop']['value'] ?? '10px';
        $content_alignment = $advanced['contentAlignment']['desktop']['value']   ?? 'left';
        $content_padding   = $advanced['contentPadding']['desktop']['value']     ?? '15px|0px|0px|0px';
        $icon_padding      = $advanced['iconPadding']['desktop']['value']        ?? '0px|0px|0px|0px';
        $vo_icon_color     = $advanced['voIconColor']['desktop']['value']        ?? '';
        $vo_icon_size      = $advanced['voIconSize']['desktop']['value']         ?? '';
        $vo_bg             = $advanced['voBg']['desktop']['value']               ?? '';

        // Convert a D4 "top|right|bottom|left" spacing string OR a migrated D5
        // spacing object {top,right,bottom,left} into a CSS `padding`
        // shorthand. Empty segments fall back to 0.
        $padding_css = function ($value) {
            if (is_array($value)) {
                if (empty($value)) {
                    return '';
                }
                $top    = ('' !== ($value['top'] ?? ''))    ? $value['top']    : '0px';
                $right  = ('' !== ($value['right'] ?? ''))  ? $value['right']  : '0px';
                $bottom = ('' !== ($value['bottom'] ?? '')) ? $value['bottom'] : '0px';
                $left   = ('' !== ($value['left'] ?? ''))   ? $value['left']   : '0px';
                return sprintf('%1$s %2$s %3$s %4$s', $top, $right, $bottom, $left);
            }
            if (!is_string($value) || '' === $value) {
                return '';
            }
            $parts = explode('|', $value);
            $top    = ('' !== ($parts[0] ?? '')) ? $parts[0] : '0px';
            $right  = ('' !== ($parts[1] ?? '')) ? $parts[1] : '0px';
            $bottom = ('' !== ($parts[2] ?? '')) ? $parts[2] : '0px';
            $left   = ('' !== ($parts[3] ?? '')) ? $parts[3] : '0px';
            return sprintf('%1$s %2$s %3$s %4$s', $top, $right, $bottom, $left);
        };

        $push = function ($selector, $declaration) use (&$styles) {
            $styles[] = [
                'atRules'     => false,
                'selector'    => $selector,
                'declaration' => $declaration,
            ];
        };

        // Flex layout for non-video, non-top placement.
        if ('video' !== $main_figure) {
            if ('top' !== $figure_placement) {
                $push($order_class . ' .dtq-info-box', 'display: flex;');
                $push($order_class . ' .dtq-info-box-content', 'flex: 1 1;');
            }
            if ('right' === $figure_placement) {
                $push($order_class . ' .dtq-info-box', 'flex-direction: row-reverse;');
            }
        }

        // Media image sizing.
        if ('image' === $main_figure) {
            $push($order_class . ' .dtq-info-box .dtq-info-box-figure', sprintf('width: %1$s !important;', $image_width));
            $push($order_class . ' .dtq-info-box .dtq-info-box-figure', sprintf('flex: %1$s !important;', $image_width));

            if ('off' === $equalize_content) {
                if ('top' !== $figure_placement && 'auto' !== $image_height) {
                    $push($order_class . ' .dtq-info-box .dtq-info-box-figure', sprintf('height: %1$s !important;', $image_height));
                    $push($order_class . ' .dtq-info-box .dtq-info-box-figure img', 'height: 100%; object-fit: cover;width:100%;');
                    $push($order_class . ' .dtq-info-box', sprintf('align-items: %1$s !important;', $align_items));
                }
            } else {
                $push($order_class . ' .dtq-info-box .dtq-info-box-figure img', 'height: 100%; object-fit: cover;width:100%;');
            }
        }

        // Button spacing.
        $push($order_class . ' .dtq-info-box-btn', sprintf('padding-top: %1$s;', $btn_spacing_top));

        // Title spacing.
        $push($order_class . ' .dtq-info-box-title', sprintf('padding-bottom: %1$s !important;', $title_spacing));

        // Content alignment.
        $push($order_class, sprintf('text-align: %1$s;', $content_alignment));

        // Content padding.
        $content_padding_css = $padding_css($content_padding);
        if ('' !== $content_padding_css) {
            $push($order_class . ' .dtq-info-box-content', sprintf('padding: %1$s;', $content_padding_css));
        }

        // Icon.
        if ('icon' === $main_figure) {
            if ('on' === $use_icon_box) {
                $push($order_class . ' .dtq-info-box-icon', sprintf('background: %1$s;', $icon_bg));
                $push($order_class . ' .dtq-info-box-icon', sprintf('height: %1$s;', $icon_height));
                $push($order_class . ' .dtq-info-box-icon', sprintf('width: %1$s;', $icon_width));
            }

            // Icon padding.
            $icon_padding_css = $padding_css($icon_padding);
            if ('' !== $icon_padding_css) {
                $push($order_class . ' .dtq-info-box-icon', sprintf('padding: %1$s;', $icon_padding_css));
            }
        }

        $push($order_class . ' .dtq-info-box-icon i', sprintf('color: %1$s;', $icon_color));
        $push($order_class . ' .dtq-info-box-icon i', sprintf('font-size: %1$s;', $icon_size));

        // Video overlay.
        if (!empty($vo_icon_color)) {
            $push($order_class . ' .et_pb_video_overlay .et_pb_video_play', sprintf('color: %1$s;', $vo_icon_color));
        }
        if (!empty($vo_icon_size)) {
            $push($order_class . ' .et_pb_video_overlay .et_pb_video_play', sprintf('font-size: %1$s;', $vo_icon_size));
        }
        if (!empty($vo_bg)) {
            $push($order_class . ' .et_pb_video_overlay_hover:hover', sprintf('background: %1$s;', $vo_bg));
        }

        // --- Additive responsive (tablet/phone) output. ---------------------
        // Mirrors the desktop declarations above for the responsive fields,
        // wrapped in @media at-rules. Each breakpoint entry is only emitted
        // when its saved value exists and differs from the next-larger
        // breakpoint, so the desktop output above is never altered.
        $media = [
            'tablet' => '@media only screen and (max-width: 980px)',
            'phone'  => '@media only screen and (max-width: 767px)',
        ];

        // Return the value for a breakpoint only when it exists AND differs
        // from the next-larger breakpoint; otherwise null (skip the entry).
        $bp_value = function ($key, $breakpoint) use ($advanced) {
            $current = $advanced[$key][$breakpoint]['value'] ?? null;
            if (null === $current || '' === $current) {
                return null;
            }
            $larger = ('phone' === $breakpoint)
                ? ($advanced[$key]['tablet']['value'] ?? $advanced[$key]['desktop']['value'] ?? null)
                : ($advanced[$key]['desktop']['value'] ?? null);
            if ($current === $larger) {
                return null;
            }
            return $current;
        };

        $push_media = function ($at_rule, $selector, $declaration) use (&$styles) {
            $styles[] = [
                'atRules'     => $at_rule,
                'selector'    => $selector,
                'declaration' => $declaration,
            ];
        };

        foreach ($media as $breakpoint => $at_rule) {
            // Image width / flex.
            if ('image' === $main_figure) {
                $r_image_width = $bp_value('imageWidth', $breakpoint);
                if (null !== $r_image_width) {
                    $push_media($at_rule, $order_class . ' .dtq-info-box .dtq-info-box-figure', sprintf('width: %1$s !important;', $r_image_width));
                    $push_media($at_rule, $order_class . ' .dtq-info-box .dtq-info-box-figure', sprintf('flex: %1$s !important;', $r_image_width));
                }

                // Image height (only in the same branch desktop emits it).
                if ('off' === $equalize_content && 'top' !== $figure_placement) {
                    $r_image_height = $bp_value('imageHeight', $breakpoint);
                    if (null !== $r_image_height && 'auto' !== $r_image_height) {
                        $push_media($at_rule, $order_class . ' .dtq-info-box .dtq-info-box-figure', sprintf('height: %1$s !important;', $r_image_height));
                    }
                }
            }

            // Button spacing.
            $r_btn_spacing_top = $bp_value('btnSpacingTop', $breakpoint);
            if (null !== $r_btn_spacing_top) {
                $push_media($at_rule, $order_class . ' .dtq-info-box-btn', sprintf('padding-top: %1$s;', $r_btn_spacing_top));
            }

            // Title spacing.
            $r_title_spacing = $bp_value('titleBottomSpacing', $breakpoint);
            if (null !== $r_title_spacing) {
                $push_media($at_rule, $order_class . ' .dtq-info-box-title', sprintf('padding-bottom: %1$s !important;', $r_title_spacing));
            }

            // Content alignment.
            $r_content_alignment = $bp_value('contentAlignment', $breakpoint);
            if (null !== $r_content_alignment) {
                $push_media($at_rule, $order_class, sprintf('text-align: %1$s;', $r_content_alignment));
            }

            // Icon box height / width.
            if ('icon' === $main_figure && 'on' === $use_icon_box) {
                $r_icon_height = $bp_value('iconHeight', $breakpoint);
                if (null !== $r_icon_height) {
                    $push_media($at_rule, $order_class . ' .dtq-info-box-icon', sprintf('height: %1$s;', $r_icon_height));
                }
                $r_icon_width = $bp_value('iconWidth', $breakpoint);
                if (null !== $r_icon_width) {
                    $push_media($at_rule, $order_class . ' .dtq-info-box-icon', sprintf('width: %1$s;', $r_icon_width));
                }
            }

            // Icon size.
            $r_icon_size = $bp_value('iconSize', $breakpoint);
            if (null !== $r_icon_size) {
                $push_media($at_rule, $order_class . ' .dtq-info-box-icon i', sprintf('font-size: %1$s;', $r_icon_size));
            }

            // Video overlay icon size.
            $r_vo_icon_size = $bp_value('voIconSize', $breakpoint);
            if (null !== $r_vo_icon_size) {
                $push_media($at_rule, $order_class . ' .et_pb_video_overlay .et_pb_video_play', sprintf('font-size: %1$s;', $r_vo_icon_size));
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

        $advanced      = $attrs['module']['advanced'] ?? [];
        $custom_styles = self::build_custom_styles($advanced, $order_class);

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

            // Title font / heading styles.
            $elements->style(['attrName' => 'title']),

            // Body content font styles.
            $elements->style(['attrName' => 'content']),

            // Button decoration styles.
            $elements->style(['attrName' => 'button']),
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
