<?php
/**
 * LogoGridItem: Module styles trait.
 *
 * @package DiviTorqueLite\Modules\LogoGridItem
 * @since   4.4.0
 */

namespace DiviTorqueLite\Modules\LogoGridItem\LogoGridItemTrait;

if (!defined('ABSPATH')) {
    exit;
}

use ET\Builder\FrontEnd\Module\Style;
use ET\Builder\Packages\Module\Options\Css\CssStyle;

trait ModuleStylesTrait
{
    /**
     * Generate the module styles, mirroring styles.jsx.
     *
     * The D4 child had no render-time CSS of its own; its background, border
     * and margin/padding advanced options targeted the inner
     * `.dtq-logo-grid__item`, which is the `item` element attr here.
     * Everything layout-related (height, flex basis, gap padding) is emitted
     * by the parent Logo Grid.
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
            $elements->style(['attrName' => 'item']),
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
