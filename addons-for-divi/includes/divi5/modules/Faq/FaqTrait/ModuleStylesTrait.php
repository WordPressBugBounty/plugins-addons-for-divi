<?php
/**
 * Faq: Module styles trait.
 *
 * @package DiviTorqueLite\Modules\Faq
 * @since   4.7.0
 */

namespace DiviTorqueLite\Modules\Faq\FaqTrait;

if (!defined('ABSPATH')) {
    exit;
}

use ET\Builder\FrontEnd\Module\Style;
use ET\Builder\Packages\Module\Options\Css\CssStyle;

trait ModuleStylesTrait
{
    public static function module_styles($args)
    {
        $attrs       = $args['attrs'] ?? [];
        $elements    = $args['elements'];
        $settings    = $args['settings'] ?? [];
        $order_class = $args['orderClass'] ?? '';

        $spacing = $attrs['module']['advanced']['itemSpacing']['desktop']['value'] ?? '10px';

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
            $elements->style(['attrName' => 'question']),
            $elements->style(['attrName' => 'answer']),
            [
                [
                    'selector'    => $order_class . ' .dtq-accordion__item',
                    'declaration' => sprintf('margin-bottom: %1$s;', $spacing),
                ],
            ],
        ];

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
