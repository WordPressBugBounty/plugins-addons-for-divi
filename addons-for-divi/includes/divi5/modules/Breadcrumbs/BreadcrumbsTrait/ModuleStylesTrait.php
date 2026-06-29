<?php
/**
 * Breadcrumbs: Module styles trait.
 *
 * @package DiviTorqueLite\Modules\Breadcrumbs
 * @since   4.7.0
 */

namespace DiviTorqueLite\Modules\Breadcrumbs\BreadcrumbsTrait;

if (!defined('ABSPATH')) {
    exit;
}

use ET\Builder\FrontEnd\Module\Style;
use ET\Builder\Packages\Module\Options\Css\CssStyle;

trait ModuleStylesTrait
{
    /**
     * Build alignment + color declarations (mirrors styles.jsx).
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

        $align = $val('alignment');
        if ($align) {
            $push($order_class . ' .dtq-breadcrumbs', sprintf('text-align: %1$s;', $align));
        }
        $link = $val('linkColor');
        if ($link) {
            $push($order_class . ' .dtq-breadcrumbs__link', sprintf('color: %1$s;', $link));
        }
        $current = $val('currentColor');
        if ($current) {
            $push($order_class . ' .dtq-breadcrumbs__current', sprintf('color: %1$s;', $current));
        }
        $sep = $val('separatorColor');
        if ($sep) {
            $push($order_class . ' .dtq-breadcrumbs__sep', sprintf('color: %1$s;', $sep));
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
