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
        $push   = function ($selector, $declaration, $at_rules = false) use (&$styles) {
            $styles[] = ['atRules' => $at_rules, 'selector' => $selector, 'declaration' => $declaration];
        };
        // Author-controlled values land inside a declaration, so only a real
        // colour (or nothing) gets through.
        $color = function ($value) {
            $clean = dtq_css_color($value, '');
            return '' !== $clean ? $clean : null;
        };

        // Alignment is responsive: desktop, then tablet/phone at Divi's breakpoints.
        $breakpoints = [
            'desktop' => false,
            'tablet'  => '@media only screen and (max-width: 980px)',
            'phone'   => '@media only screen and (max-width: 767px)',
        ];
        foreach ($breakpoints as $bp => $at_rules) {
            $align = $advanced['alignment'][$bp]['value'] ?? null;
            if (in_array($align, ['left', 'center', 'right', 'justify'], true)) {
                $push($order_class . ' .dtq-breadcrumbs', sprintf('text-align: %1$s;', $align), $at_rules);
            }
        }

        $link = $color($advanced['linkColor']['desktop']['value'] ?? null);
        if ($link) {
            $push($order_class . ' .dtq-breadcrumbs__link', sprintf('color: %1$s;', $link));
        }
        // linkColor declares hover; D5 stores it beside the value.
        $link_hover = $color($advanced['linkColor']['desktop']['hover'] ?? null);
        if ($link_hover) {
            $push($order_class . ' .dtq-breadcrumbs__link:hover', sprintf('color: %1$s;', $link_hover));
        }
        $current = $color($advanced['currentColor']['desktop']['value'] ?? null);
        if ($current) {
            $push($order_class . ' .dtq-breadcrumbs__current', sprintf('color: %1$s;', $current));
        }
        $sep = $color($advanced['separatorColor']['desktop']['value'] ?? null);
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
