<?php
/**
 * ImageCarouselItem: Module styles trait.
 *
 * @package DiviTorqueLite\Modules\ImageCarouselItem
 * @since   4.5.0
 */

namespace DiviTorqueLite\Modules\ImageCarouselItem\ImageCarouselItemTrait;

if (!defined('ABSPATH')) {
    exit;
}

use ET\Builder\FrontEnd\Module\Style;
use ET\Builder\Packages\Module\Options\Css\CssStyle;

trait ModuleStylesTrait
{
    /**
     * Convert a spacing field value to a CSS padding shorthand. Accepts both
     * the D5 spacing object and the legacy D4 pipe-string.
     *
     * @param mixed $val Raw spacing value.
     *
     * @return string
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
     * Build the dynamic custom style array for a slide, mirroring styles.jsx.
     *
     * @param string $order_class The module order class selector.
     * @param array  $advanced    The `module.advanced` attrs array.
     *
     * @return array
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

        $styles = [];
        $push   = function ($selector, $declaration) use (&$styles) {
            $styles[] = ['atRules' => false, 'selector' => $selector, 'declaration' => $declaration];
        };

        $content_alignment = $val('contentAlignment', 'left');
        $content_type      = $val('contentType', 'normal');
        $content_pos_x     = $val('contentPosX', 'center');
        $content_pos_y     = $val('contentPosY', 'center');
        $image_height      = $val('imageHeight', 'auto');

        $push($order_class . ' .dtq-image-carousel-item .content-inner', sprintf('text-align: %1$s;', $content_alignment));

        // Content padding. Mirrors D4: when unset, the default depends on the
        // content type (absolute over-image vs normal flow).
        $padding_css = self::dtq_spacing($advanced['contentPadding']['desktop']['value'] ?? null);
        if ('' === $padding_css) {
            $padding_css = 'absolute' === $content_type ? '10px 20px 10px 20px' : '15px 0px 15px 0px';
        }
        $push($order_class . ' .dtq-image-carousel-item .content .content-inner', sprintf('padding: %1$s;', $padding_css));

        $content_bg       = $val('contentBgColor', 'rgba(0,0,0,0)');
        $content_bg_hover = $hover('contentBgColor', $content_bg);
        $push($order_class . ' .dtq-image-carousel-item .content .content-inner', sprintf('background-color: %1$s;', $content_bg));
        $push($order_class . ' .dtq-image-carousel-item .content .content-inner:hover', sprintf('background-color: %1$s;', $content_bg_hover));

        // Content width.
        $push($order_class . ' .dtq-image-carousel-item .content .content-inner', sprintf('width: %1$s;', $val('contentWidth', '100%')));

        // Over-image (absolute) content placement + offsets.
        if ('absolute' === $content_type) {
            $push($order_class . ' .dtq-image-carousel-item .content--absolute', sprintf('align-items: %1$s; justify-content: %2$s;', $content_pos_x, $content_pos_y));
            if ('flex-start' === $content_pos_x) {
                $push($order_class . ' .dtq-image-carousel-item .content--absolute', sprintf('padding-left: %1$s;', $val('contentOffsetX', '0px')));
            } elseif ('flex-end' === $content_pos_x) {
                $push($order_class . ' .dtq-image-carousel-item .content--absolute', sprintf('padding-right: %1$s;', $val('contentOffsetX', '0px')));
            }
            if ('flex-start' === $content_pos_y) {
                $push($order_class . ' .dtq-image-carousel-item .content--absolute', sprintf('padding-top: %1$s;', $val('contentOffsetY', '0px')));
            } elseif ('flex-end' === $content_pos_y) {
                $push($order_class . ' .dtq-image-carousel-item .content--absolute', sprintf('padding-bottom: %1$s;', $val('contentOffsetY', '0px')));
            }
        }

        if ('auto' !== $image_height) {
            $push($order_class . ' .dtq-image-carousel-item figure', sprintf('height: %1$s;', $image_height));
            $push($order_class . ' .dtq-image-carousel-item figure img', 'height: 100%; object-fit: cover; width: 100%;');
        }

        $push($order_class . ' .dtq-image-carousel-item .dtq-image-title', sprintf('padding-bottom: %1$s;', $val('titleBottomSpacing', '5px')));

        if ('on' === $val('useButton', 'off')) {
            $push($order_class . ' .dtq-btn-wrap', sprintf('padding-top: %1$s!important;', $val('btnSpacingTop', '15px')));

            $btn             = $order_class . ' .dtq-btn-wrap .dtq-btn-img-carousel';
            $btn_text_color  = $val('buttonTextColor', '#2ea3f2');
            $btn_bg_color    = $val('buttonBgColor', 'rgba(0,0,0,0)');
            $btn_border_col  = $val('buttonBorderColor', '#2ea3f2');
            $btn_padding     = self::dtq_spacing($advanced['buttonPadding']['desktop']['value'] ?? null);
            if ('' === $btn_padding) {
                $btn_padding = '8px 20px 8px 20px';
            }
            $push(
                $btn,
                sprintf(
                    'color: %1$s!important; background-color: %2$s!important; font-size: %3$s!important; border-width: %4$s!important; border-color: %5$s!important; border-style: solid!important; border-radius: %6$s!important; padding: %7$s!important;',
                    $btn_text_color,
                    $btn_bg_color,
                    $val('buttonFontSize', '14px'),
                    $val('buttonBorderWidth', '2px'),
                    $btn_border_col,
                    $val('buttonRadius', '3px'),
                    $btn_padding
                )
            );
            $push(
                $btn . ':hover',
                sprintf(
                    'color: %1$s!important; background-color: %2$s!important; border-color: %3$s!important;',
                    $hover('buttonTextColor', $btn_text_color),
                    $hover('buttonBgColor', $btn_bg_color),
                    $hover('buttonBorderColor', $btn_border_col)
                )
            );
        }

        if ('on' === $val('overlayOnHover', 'off')) {
            $overlay_color            = $val('overlayColor', 'rgba(46,163,242,0.85)');
            $overlay_icon_color       = $val('overlayIconColor', '#ffffff');
            $overlay_icon_color_hover = $hover('overlayIconColor', $overlay_icon_color);
            $overlay_icon_size        = $val('overlayIconSize', '32px');
            $overlay_icon_opacity     = $val('overlayIconOpacity', '1');
            $overlay_speed            = $val('overlayHoverSpeed', '500ms');
            $push($order_class . ' .dtq-image-carousel-item figure', 'position: relative;');
            $push($order_class . ' .dtq-overlay', 'opacity: 0;');
            $push($order_class . ' .dtq-image-carousel-item:hover .dtq-overlay', 'opacity: 1;');
            $push($order_class . ' .dtq-overlay', sprintf('background-color: %1$s; color: %2$s; transition: all %3$s;', $overlay_color, $overlay_icon_color, $overlay_speed));
            $push($order_class . ' .dtq-image-carousel-item:hover .dtq-overlay', sprintf('color: %1$s;', $overlay_icon_color_hover));
            $push($order_class . ' .dtq-overlay .dtq-overlay-icon', sprintf('font-size: %1$s; opacity: %2$s;', $overlay_icon_size, $overlay_icon_opacity));
        }

        // Responsive (tablet / phone), additive.
        $breakpoints = [
            'tablet' => '@media only screen and (max-width: 980px)',
            'phone'  => '@media only screen and (max-width: 767px)',
        ];
        $bp_val = function ($key, $bp) use ($advanced) {
            $chain = 'phone' === $bp ? ['phone', 'tablet', 'desktop'] : ['tablet', 'desktop'];
            foreach ($chain as $level) {
                if (isset($advanced[$key][$level]['value'])) {
                    return $advanced[$key][$level]['value'];
                }
            }
            return null;
        };
        $bp_raw  = function ($key, $bp) use ($advanced) {
            return $advanced[$key][$bp]['value'] ?? null;
        };
        $push_at = function ($at_rule, $selector, $declaration) use (&$styles) {
            $styles[] = ['atRules' => $at_rule, 'selector' => $selector, 'declaration' => $declaration];
        };

        foreach ($breakpoints as $bp => $at_rule) {
            if (null !== $bp_raw('imageHeight', $bp)) {
                $h = $bp_val('imageHeight', $bp);
                if ('auto' !== $h) {
                    $push_at($at_rule, $order_class . ' .dtq-image-carousel-item figure', sprintf('height: %1$s!important;', $h));
                    $push_at($at_rule, $order_class . ' .dtq-image-carousel-item figure img', 'height: 100%; object-fit: cover; width: 100%;');
                }
            }
            if (null !== $bp_raw('contentPadding', $bp)) {
                $pad = self::dtq_spacing($bp_val('contentPadding', $bp));
                if ('' !== $pad) {
                    $push_at($at_rule, $order_class . ' .dtq-image-carousel-item .content .content-inner', sprintf('padding: %1$s;', $pad));
                }
            }
            if (null !== $bp_raw('contentWidth', $bp)) {
                $push_at($at_rule, $order_class . ' .dtq-image-carousel-item .content .content-inner', sprintf('width: %1$s;', $bp_val('contentWidth', $bp)));
            }
            if (null !== $bp_raw('titleBottomSpacing', $bp)) {
                $push_at($at_rule, $order_class . ' .dtq-image-carousel-item .dtq-image-title', sprintf('padding-bottom: %1$s;', $bp_val('titleBottomSpacing', $bp)));
            }
            if (null !== $bp_raw('btnSpacingTop', $bp) && 'on' === $val('useButton', 'off')) {
                $push_at($at_rule, $order_class . ' .dtq-btn-wrap', sprintf('padding-top: %1$s!important;', $bp_val('btnSpacingTop', $bp)));
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
            $elements->style(['attrName' => 'photo']),
            $elements->style(['attrName' => 'title']),
            $elements->style(['attrName' => 'subTitle']),
        ];

        if (!empty($custom_styles)) {
            $all_styles[] = $custom_styles;
        }

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
