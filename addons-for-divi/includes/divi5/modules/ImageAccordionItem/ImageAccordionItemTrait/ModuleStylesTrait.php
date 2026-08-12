<?php
/**
 * ImageAccordionItem: Module styles trait.
 *
 * @package DiviTorqueLite\Modules\ImageAccordionItem
 * @since   4.9.0
 */

namespace DiviTorqueLite\Modules\ImageAccordionItem\ImageAccordionItemTrait;

if (!defined('ABSPATH')) {
    exit;
}

use ET\Builder\FrontEnd\Module\Style;
use ET\Builder\Packages\Module\Options\Css\CssStyle;

trait ModuleStylesTrait
{
    /**
     * How the panel image fills its box.
     *
     * Interpolated where CSS expects an object-fit keyword, so matched against
     * a list rather than pattern-checked.
     */
    const FITS = ['cover', 'contain'];

    /**
     * Build the panel's custom properties.
     *
     * PHP twin of buildCustomStyles() in
     * src/divi5/modules/image-accordion-item/styles.jsx.
     *
     * Only what is genuinely per-panel lives here — the image fit and focal
     * point. Everything else about how a panel looks is inherited from the
     * parent's order class.
     *
     * @param array  $advanced    The `module.advanced` attrs array.
     * @param string $order_class Module order class selector.
     *
     * @return array List of ['atRules' => string|false, 'selector' => string, 'declaration' => string].
     */
    public static function build_custom_styles($advanced, $order_class)
    {
        if (!is_array($advanced)) {
            $advanced = [];
        }

        $declarations = [];

        $fit = $advanced['imageFit']['desktop']['value'] ?? '';
        if (in_array($fit, self::FITS, true)) {
            $declarations[] = '--dtq-ia-fit: ' . $fit . ';';
        }

        $x_raw = $advanced['focalX']['desktop']['value'] ?? '';
        $y_raw = $advanced['focalY']['desktop']['value'] ?? '';

        if ('' !== $x_raw || '' !== $y_raw) {
            $x = '' !== $x_raw ? dtq_css_length($x_raw, '50%') : '50%';
            $y = '' !== $y_raw ? dtq_css_length($y_raw, '50%') : '50%';
            $declarations[] = '--dtq-ia-focal: ' . $x . ' ' . $y . ';';
        }

        if (empty($declarations)) {
            return [];
        }

        return [
            [
                'atRules'     => false,
                'selector'    => $order_class,
                'declaration' => implode(' ', $declarations),
            ],
        ];
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
        $custom_styles = self::build_custom_styles($advanced, $order_class);

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

            $elements->style(['attrName' => 'title']),
            $elements->style(['attrName' => 'content']),
            $elements->style(['attrName' => 'button']),
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
