<?php
/**
 * Tooltip: Module styles trait.
 *
 * @package DiviTorqueLite\Modules\Tooltip
 * @since   4.9.0
 */

namespace DiviTorqueLite\Modules\Tooltip\TooltipTrait;

if (!defined('ABSPATH')) {
    exit;
}

use ET\Builder\FrontEnd\Module\Style;
use ET\Builder\Packages\Module\Options\Css\CssStyle;

trait ModuleStylesTrait
{
    /**
     * Build the module's custom properties.
     *
     * PHP twin of buildCustomStyles() in src/divi5/modules/tooltip/styles.jsx.
     * The two must emit the same properties from the same attributes, or the
     * styling looks right in the builder and disappears on publish.
     *
     * Every value is interpolated into a stylesheet, so every value goes
     * through the sanitisers in includes/functions.php first.
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

        $colours = [
            'tipBg'     => '--dtq-tt-bg',
            'tipColor'  => '--dtq-tt-fg',
            'iconColor' => '--dtq-tt-icon',
        ];

        $lengths = [
            'tipRadius'  => '--dtq-tt-radius',
            'maxWidth'   => '--dtq-tt-maxw',
            'iconSize'   => '--dtq-tt-icon-size',
            'imageWidth' => '--dtq-tt-img-w',
        ];

        $declarations = [];

        foreach ($colours as $key => $property) {
            $raw = $advanced[$key]['desktop']['value'] ?? '';
            if ('' === $raw) {
                continue;
            }
            $colour = dtq_css_color(dtq_resolve_css_value($raw), '');
            if ('' !== $colour) {
                $declarations[] = $property . ': ' . $colour . ';';
            }
        }

        foreach ($lengths as $key => $property) {
            $raw = $advanced[$key]['desktop']['value'] ?? '';
            if ('' === $raw) {
                continue;
            }
            $length = dtq_css_length($raw, '');
            if ('' !== $length) {
                $declarations[] = $property . ': ' . $length . ';';
            }
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
            // Module wrapper styles (background, spacing, border, etc.).
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

            // Trigger and tooltip typography.
            $elements->style(['attrName' => 'triggerText']),
            $elements->style(['attrName' => 'tipContent']),
        ];

        if (!empty($custom_styles)) {
            $all_styles[] = $custom_styles;
        }

        // Custom CSS (Advanced tab).
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
