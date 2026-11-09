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
    /**
     * Spacing Between Items, per breakpoint (mirrors styles.jsx).
     *
     * The field is responsive, but only the desktop value used to be read, so
     * tablet and phone values did nothing. Each breakpoint that has its own
     * value now gets a media-query rule, the same way the Accordion does it.
     *
     * @param string $order_class The module order class selector.
     * @param mixed  $spacing     The `module.advanced.itemSpacing` attr.
     *
     * @return array
     */
    public static function item_spacing_styles($order_class, $spacing)
    {
        $spacing  = is_array($spacing) ? $spacing : [];
        // The class is repeated on purpose (specificity 0,5,0): items are Divi
        // modules, and on desktop Divi's `.et_pb_gutters3 .et_pb_column_4_4
        // .et_block_column .et_pb_module { margin-bottom: 2.75% }` (0,4,0)
        // otherwise beats the module's own Spacing Between Items. No
        // !important, so a margin set on one item still wins.
        $selector = $order_class . ' ' . str_repeat('.dtq-accordion__item', 4);

        $styles = [
            [
                'selector'    => $selector,
                'declaration' => sprintf('margin-bottom: %1$s;', $spacing['desktop']['value'] ?? '10px'),
            ],
        ];

        $at_rules = [
            'tablet' => '@media only screen and (max-width: 980px)',
            'phone'  => '@media only screen and (max-width: 767px)',
        ];
        foreach ($at_rules as $breakpoint => $at_rule) {
            $value = $spacing[$breakpoint]['value'] ?? '';
            if (is_string($value) && '' !== $value) {
                $styles[] = [
                    'atRules'     => $at_rule,
                    'selector'    => $selector,
                    'declaration' => sprintf('margin-bottom: %1$s;', $value),
                ];
            }
        }

        return $styles;
    }

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
            $elements->style(['attrName' => 'question']),
            $elements->style(['attrName' => 'answer']),
            self::item_spacing_styles($order_class, $attrs['module']['advanced']['itemSpacing'] ?? []),
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
