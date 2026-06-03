<?php
/**
 * GradientHeading: Module styles trait.
 *
 * @package DiviTorqueLite\Modules\GradientHeading
 * @since   4.3.0
 */

namespace DiviTorqueLite\Modules\GradientHeading\GradientHeadingTrait;

if (!defined('ABSPATH')) {
    exit;
}

use ET\Builder\FrontEnd\Module\Style;
use ET\Builder\Packages\Module\Options\Css\CssStyle;

trait ModuleStylesTrait
{
    /**
     * Build the gradient declaration string from flat advanced attrs.
     *
     * @param array $advanced The `module.advanced` attrs array.
     * @return string CSS declaration block (without selector wrapper).
     */
    public static function build_gradient_declaration($advanced)
    {
        if (!is_array($advanced)) {
            return '';
        }

        $gradient_type   = $advanced['gradientType']['desktop']['value']      ?? 'linear';
        $primary         = $advanced['primaryColor']['desktop']['value']      ?? '#5b2cff';
        $primary_loc     = $advanced['primaryLocation']['desktop']['value']   ?? 11;
        $secondary       = $advanced['secondaryColor']['desktop']['value']    ?? '#e02b20';
        $secondary_loc   = $advanced['secondaryLocation']['desktop']['value'] ?? 37;
        $angle           = $advanced['gradientAngle']['desktop']['value']     ?? 130;
        $radial_position = $advanced['radialPosition']['desktop']['value']    ?? 'center center';

        if ('radial' === $gradient_type) {
            $background_image = sprintf(
                'radial-gradient(at %1$s, %2$s %3$s%%, %4$s %5$s%%)',
                $radial_position,
                $primary,
                (int) $primary_loc,
                $secondary,
                (int) $secondary_loc
            );
        } else {
            $background_image = sprintf(
                'linear-gradient(%1$sdeg, %2$s %3$s%%, %4$s %5$s%%)',
                (int) $angle,
                $primary,
                (int) $primary_loc,
                $secondary,
                (int) $secondary_loc
            );
        }

        return sprintf(
            '-webkit-background-clip: text; -webkit-text-fill-color: transparent; background-color: transparent; background-image: %s;',
            $background_image
        );
    }

    /**
     * Generate the module styles.
     *
     * @param array $args Style args.
     * @return void
     */
    public static function module_styles($args)
    {
        $attrs       = $args['attrs'] ?? [];
        $elements    = $args['elements'];
        $settings    = $args['settings'] ?? [];
        $order_class = $args['orderClass'] ?? '';

        $advanced             = $attrs['module']['advanced'] ?? [];
        $gradient_declaration = self::build_gradient_declaration($advanced);

        $custom_styles = [];
        if (!empty($gradient_declaration)) {
            $custom_styles[] = [
                'atRules'     => false,
                'selector'    => $order_class . ' .dtq-gradient-heading',
                'declaration' => $gradient_declaration,
            ];
        }

        $all_styles = [
            // Module wrapper styles (background, spacing, etc.).
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

            // Title font / heading styles.
            $elements->style(
                [
                    'attrName' => 'title',
                ]
            ),
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
