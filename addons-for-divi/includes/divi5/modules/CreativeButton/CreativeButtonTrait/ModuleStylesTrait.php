<?php
/**
 * CreativeButton: Module styles trait.
 *
 * @package DiviTorqueLite\Modules\CreativeButton
 * @since   4.9.0
 */

namespace DiviTorqueLite\Modules\CreativeButton\CreativeButtonTrait;

if (!defined('ABSPATH')) {
    exit;
}

use ET\Builder\FrontEnd\Module\Style;
use ET\Builder\Packages\Module\Options\Css\CssStyle;

trait ModuleStylesTrait
{
    /**
     * Easing curves offered by the Effect Easing field.
     *
     * This value is interpolated where CSS expects a timing function, so it is
     * matched against the list rather than pattern-checked — the same treatment
     * navBorderStyle gets in CarouselEngine, and for the same reason.
     */
    const EASINGS = [
        'ease',
        'ease-in',
        'ease-out',
        'ease-in-out',
        'linear',
        'cubic-bezier(0.4, 0, 0.2, 1)',
        'cubic-bezier(0.34, 1.56, 0.64, 1)',
    ];

    /**
     * Build the module's custom properties.
     *
     * PHP twin of buildCustomStyles() in
     * src/divi5/modules/creative-button/styles.jsx. The two must emit the same
     * properties from the same attributes, or the effect looks right in the
     * builder and disappears on publish.
     *
     * None of these fields are responsive, so there are no media queries.
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

        foreach (['effectColor' => '--dtq-cb-fx', 'effectColor2' => '--dtq-cb-fx2'] as $key => $property) {
            $raw = $advanced[$key]['desktop']['value'] ?? '';
            if ('' === $raw) {
                continue;
            }
            $colour = dtq_css_color(dtq_resolve_css_value($raw), '');
            if ('' !== $colour) {
                $declarations[] = $property . ': ' . $colour . ';';
            }
        }

        $speed = $advanced['effectSpeed']['desktop']['value'] ?? '';
        if ('' !== $speed) {
            // dtq_css_length accepts ms and s among its units.
            $duration = dtq_css_length($speed, '');
            if ('' !== $duration) {
                $declarations[] = '--dtq-cb-dur: ' . $duration . ';';
            }
        }

        $easing = $advanced['effectEasing']['desktop']['value'] ?? '';
        if (in_array($easing, self::EASINGS, true)) {
            $declarations[] = '--dtq-cb-ease: ' . $easing . ';';
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

            // Button decoration styles.
            $elements->style(['attrName' => 'button']),
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
