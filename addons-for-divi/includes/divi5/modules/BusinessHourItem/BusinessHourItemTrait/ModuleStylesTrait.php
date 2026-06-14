<?php
/**
 * BusinessHourItem: Module styles trait.
 *
 * @package DiviTorqueLite\Modules\BusinessHourItem
 * @since   4.4.0
 */

namespace DiviTorqueLite\Modules\BusinessHourItem\BusinessHourItemTrait;

if (!defined('ABSPATH')) {
    exit;
}

use ET\Builder\FrontEnd\Module\Style;
use ET\Builder\Packages\Module\Options\Css\CssStyle;

trait ModuleStylesTrait
{
    /**
     * Build the Business Hour Item declarations, mirroring styles.jsx.
     *
     * Ports the D4 child render_css():
     * - separatorGap  -> side margins on the separator (always, like D4)
     * - separatorType -> when not "relative" (relative = inherit parent styling):
     *   border types  -> border-top + reset of pattern props
     *   pattern types -> SVG pattern background + reset of border-top
     *
     * The child selectors are prefixed with `.dtq-business-hour` so they beat
     * the parent-level separator declarations, exactly like D4.
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

        $val = function ($key, $fallback) use ($advanced) {
            return $advanced[$key]['desktop']['value'] ?? $fallback;
        };

        $styles = [];
        $push   = function ($selector, $declaration, $at_rules = false) use (&$styles) {
            $styles[] = ['atRules' => $at_rules, 'selector' => $selector, 'declaration' => $declaration];
        };

        $separator_selector = '.dtq-business-hour ' . $order_class . ' .dtq-business-hour-separator';

        $type             = $val('separatorType', 'relative');
        $separator_height = $val('separatorHeight', '10px');
        $separator_color  = $val('separatorColor', '#dddddd');
        $separator_gap    = $val('separatorGap', '15px');
        $separator_weight = $val('separatorWeight', '1px');

        if ('' !== $separator_gap && null !== $separator_gap) {
            $push(
                $separator_selector,
                sprintf('margin-right: %1$s; margin-left: %1$s;', $separator_gap)
            );
        }

        if ('relative' !== $type) {
            if ('#' === substr((string) $separator_color, 0, 1)) {
                $separator_color = self::hex_to_rgb($separator_color);
            }

            $sep_type = explode('_', (string) $type);

            if ('border' === ($sep_type[1] ?? '')) {
                $push(
                    $separator_selector,
                    sprintf(
                        'border-top: %1$s %2$s %3$s; height: initial!important; background-image: initial!important;',
                        $separator_weight,
                        $sep_type[0],
                        $separator_color
                    )
                );
            } else {
                $pattern_bg = '';
                if ('curved' === $sep_type[0] || 'zigzag' === $sep_type[0]) {
                    $pattern_bg = self::get_pattern($sep_type[0], $separator_color, $separator_weight);
                }

                $push(
                    $separator_selector,
                    sprintf(
                        'background-image: url("%1$s"); height: %2$s; border-top: 0!important; background-size: %2$s 100%%;',
                        $pattern_bg,
                        $separator_height
                    )
                );
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
            $elements->style(['attrName' => 'day']),
            $elements->style(['attrName' => 'time']),
            $elements->style(['attrName' => 'item']),
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
