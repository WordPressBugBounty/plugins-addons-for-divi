<?php
/**
 * Tabs: Module styles trait.
 *
 * @package DiviTorqueLite\Modules\Tabs
 * @since   4.7.0
 */

namespace DiviTorqueLite\Modules\Tabs\TabsTrait;

if (!defined('ABSPATH')) {
    exit;
}

use ET\Builder\FrontEnd\Module\Style;
use ET\Builder\Packages\Module\Options\Css\CssStyle;

trait ModuleStylesTrait
{
    /**
     * Build nav gap + color declarations (mirrors styles.jsx).
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

        $styles = [];
        $push   = function ($selector, $declaration) use (&$styles) {
            $styles[] = ['selector' => $selector, 'declaration' => $declaration];
        };
        $val = function ($key) use ($advanced) {
            $v = $advanced[$key]['desktop']['value'] ?? null;
            return (null !== $v && '' !== $v) ? $v : null;
        };

        $gap = $val('navGap');
        if ($gap) {
            $push($order_class . ' .dtq-tabs__nav', sprintf('gap: %1$s;', $gap));
        }
        $inactive = $val('inactiveColor');
        if ($inactive) {
            $push($order_class . ' .dtq-tabs__nav-item', sprintf('color: %1$s;', $inactive));
        }
        $active = $val('activeColor');
        if ($active) {
            $push($order_class . ' .dtq-tabs__nav-item--active', sprintf('color: %1$s;', $active));
        }
        $active_bg = $val('activeBg');
        if ($active_bg) {
            $push($order_class, sprintf('--dtq-tabs-active: %1$s;', $active_bg));
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
            $elements->style(['attrName' => 'nav']),
            $elements->style(['attrName' => 'panel']),
            $elements->style(['attrName' => 'content']),
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
