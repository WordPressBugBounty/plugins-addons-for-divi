<?php
/**
 * LogoCarouselItem: Module styles trait.
 *
 * @package DiviTorqueLite\Modules\LogoCarouselItem
 * @since   4.5.0
 */

namespace DiviTorqueLite\Modules\LogoCarouselItem\LogoCarouselItemTrait;

if (!defined('ABSPATH')) {
    exit;
}

use ET\Builder\FrontEnd\Module\Style;
use ET\Builder\Packages\Module\Options\Css\CssStyle;

trait ModuleStylesTrait
{
    /**
     * Build the overlay style array, mirroring styles.jsx.
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

        $val   = function ($key, $fallback) use ($advanced) {
            return $advanced[$key]['desktop']['value'] ?? $fallback;
        };
        $hover = function ($key, $fallback) use ($advanced, $val) {
            return $advanced[$key]['desktop']['hover'] ?? $val($key, $fallback);
        };

        $styles = [];
        $push   = function ($selector, $declaration) use (&$styles) {
            $styles[] = ['atRules' => false, 'selector' => $selector, 'declaration' => $declaration];
        };

        if ('on' === $val('overlayOnHover', 'off')) {
            $overlay_color            = $val('overlayColor', 'rgba(46,163,242,0.85)');
            $overlay_icon_color       = $val('overlayIconColor', '#ffffff');
            $overlay_icon_color_hover = $hover('overlayIconColor', $overlay_icon_color);
            $overlay_icon_size        = $val('overlayIconSize', '32px');
            $overlay_icon_opacity     = $val('overlayIconOpacity', '1');
            $overlay_speed            = $val('overlayHoverSpeed', '500ms');
            $push($order_class . ' .dtq-logo-carousel-item', 'position: relative;');
            $push($order_class . ' .dtq-overlay', 'opacity: 0;');
            $push($order_class . ' .dtq-logo-carousel-item:hover .dtq-overlay', 'opacity: 1;');
            $push($order_class . ' .dtq-overlay', sprintf('background-color: %1$s; color: %2$s; transition: all %3$s;', $overlay_color, $overlay_icon_color, $overlay_speed));
            $push($order_class . ' .dtq-logo-carousel-item:hover .dtq-overlay', sprintf('color: %1$s;', $overlay_icon_color_hover));
            $push($order_class . ' .dtq-overlay .dtq-overlay-icon', sprintf('font-size: %1$s; opacity: %2$s;', $overlay_icon_size, $overlay_icon_opacity));
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
            $elements->style(['attrName' => 'logo']),
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
