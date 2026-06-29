<?php
/**
 * FancyText: Module styles trait.
 *
 * @package DiviTorqueLite\Modules\FancyText
 * @since   4.7.0
 */

namespace DiviTorqueLite\Modules\FancyText\FancyTextTrait;

if (!defined('ABSPATH')) {
    exit;
}

use ET\Builder\FrontEnd\Module\Style;
use ET\Builder\Packages\Module\Options\Css\CssStyle;

trait ModuleStylesTrait
{
    /**
     * Build custom declarations (mirrors styles.jsx buildFancyStyles).
     *
     * @param string $order_class Order class selector.
     * @param array  $advanced    module.advanced attrs.
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

        $effect = $advanced['effect']['desktop']['value'] ?? 'highlight';

        $align = $val('alignment');
        if ($align) {
            $push($order_class . ' .dtq-fancy', sprintf('text-align: %1$s;', $align));
        }
        $hl = $val('highlightColor');
        if ($hl) {
            $push($order_class, sprintf('--dtq-fancy-hl: %1$s;', $hl));
        }
        $cursor = $val('cursorColor');
        if ($cursor) {
            $push($order_class, sprintf('--dtq-fancy-cursor: %1$s;', $cursor));
        }

        if ('gradient' === $effect) {
            $gs = $val('gradientStart') ?? '#7b5cff';
            $ge = $val('gradientEnd') ?? '#00c6ff';
            $ga = $val('gradientAngle') ?? '90deg';
            $push(
                $order_class . ' .dtq-fancy__highlight',
                sprintf('background-image: linear-gradient(%1$s, %2$s, %3$s); -webkit-background-clip: text; background-clip: text; -webkit-text-fill-color: transparent; color: transparent;', $ga, $gs, $ge)
            );
        } else {
            $fancy_color = $val('fancyColor');
            if ($fancy_color) {
                $push($order_class . ' .dtq-fancy__highlight', sprintf('color: %1$s;', $fancy_color));
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

        $custom_styles = self::build_custom_styles($order_class, $attrs['module']['advanced'] ?? []);

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
            $elements->style(['attrName' => 'text']),
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
