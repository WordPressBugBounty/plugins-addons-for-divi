<?php
/**
 * LogoCarousel: Module styles trait (Swiper) — thin over CarouselEngine.
 *
 * @package DiviTorqueLite\Modules\LogoCarousel
 * @since   4.5.0
 */

namespace DiviTorqueLite\Modules\LogoCarousel\LogoCarouselTrait;

if (!defined('ABSPATH')) {
    exit;
}

use ET\Builder\FrontEnd\Module\Style;
use ET\Builder\Packages\Module\Options\Css\CssStyle;
use DiviTorqueLite\Modules\SharedCarousel\CarouselEngine;

trait ModuleStylesTrait
{
    /**
     * Logo-specific sizing CSS (mirrors styles.jsx buildLogoSizingStyles).
     *
     * @param string $order_class The module order class selector.
     * @param array  $advanced    The `module.advanced` attrs array.
     *
     * @return array
     */
    public static function build_logo_sizing_styles($order_class, $advanced)
    {
        if (!is_array($advanced)) {
            $advanced = [];
        }
        $val    = function ($key, $fallback) use ($advanced) {
            return $advanced[$key]['desktop']['value'] ?? $fallback;
        };
        $bp_raw = function ($key, $bp) use ($advanced) {
            return $advanced[$key][$bp]['value'] ?? null;
        };

        $styles  = [];
        $push    = function ($selector, $declaration) use (&$styles) {
            $styles[] = ['atRules' => false, 'selector' => $selector, 'declaration' => $declaration];
        };
        $push_at = function ($at_rule, $selector, $declaration) use (&$styles) {
            $styles[] = ['atRules' => $at_rule, 'selector' => $selector, 'declaration' => $declaration];
        };
        $tablet = '@media only screen and (max-width: 980px)';
        $phone  = '@media only screen and (max-width: 767px)';
        $dtq    = $order_class . ' .dtq-swiper-carousel';

        $logo_height = $val('logoHeight', 'auto');
        if ('auto' !== $logo_height) {
            $push($dtq . ' .dtq-logo-carousel-item', sprintf('height: %1$s; display: flex; align-items: center; justify-content: center;', $logo_height));
            if ($bp_raw('logoHeight', 'tablet')) $push_at($tablet, $dtq . ' .dtq-logo-carousel-item', sprintf('height: %1$s;', $bp_raw('logoHeight', 'tablet')));
            if ($bp_raw('logoHeight', 'phone')) $push_at($phone, $dtq . ' .dtq-logo-carousel-item', sprintf('height: %1$s;', $bp_raw('logoHeight', 'phone')));
        }
        $logo_width = $val('logoWidth', 'auto');
        if ('auto' !== $logo_width) {
            $push($dtq . ' .dtq-logo-carousel-item img', sprintf('width: %1$s;', $logo_width));
            if ($bp_raw('logoWidth', 'tablet')) $push_at($tablet, $dtq . ' .dtq-logo-carousel-item img', sprintf('width: %1$s;', $bp_raw('logoWidth', 'tablet')));
            if ($bp_raw('logoWidth', 'phone')) $push_at($phone, $dtq . ' .dtq-logo-carousel-item img', sprintf('width: %1$s;', $bp_raw('logoWidth', 'phone')));
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
        $custom_styles = array_merge(
            CarouselEngine::build_carousel_styles($order_class, $advanced),
            self::build_logo_sizing_styles($order_class, $advanced)
        );

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
