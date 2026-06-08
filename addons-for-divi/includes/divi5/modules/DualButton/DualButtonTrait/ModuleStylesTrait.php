<?php
/**
 * DualButton: Module styles trait.
 *
 * @package DiviTorqueLite\Modules\DualButton
 * @since   4.4.0
 */

namespace DiviTorqueLite\Modules\DualButton\DualButtonTrait;

if (!defined('ABSPATH')) {
    exit;
}

use ET\Builder\FrontEnd\Module\Style;
use ET\Builder\Packages\Module\Options\Css\CssStyle;

trait ModuleStylesTrait
{
    /**
     * Build the list of custom style rules ported from the D4 `render_css()`.
     *
     * @param array  $advanced    The `module.advanced` attrs array.
     * @param string $order_class The module order class selector.
     *
     * @return array
     */
    public static function build_custom_styles($advanced, $order_class)
    {
        $custom_styles = [];

        $connector_type = $advanced['connectorType']['desktop']['value'] ?? 'empty';

        if ('empty' !== $connector_type) {
            $size         = $advanced['connectorSize']['desktop']['value']        ?? '30px';
            $bg           = $advanced['connectorBg']['desktop']['value']          ?? 'transparent';
            $text_color   = $advanced['connectorTextColor']['desktop']['value']   ?? '#333';
            $text_size    = $advanced['connectorTextSize']['desktop']['value']    ?? '14px';
            $radius       = $advanced['connectorRadius']['desktop']['value']      ?? '0px';
            $border_width = $advanced['connectorBorderWidth']['desktop']['value'] ?? '0px';
            $border_color = $advanced['connectorBorderColor']['desktop']['value'] ?? 'transparent';

            $custom_styles[] = [
                'atRules'     => false,
                'selector'    => $order_class . ' .dtq-btn__connector',
                'declaration' => sprintf(
                    'width: %1$s; height: %2$s; background: %3$s; color: %4$s; font-size: %5$s; border-radius: %6$s; box-shadow: 0 0 0 %7$s %8$s;',
                    $size,
                    $size,
                    $bg,
                    $text_color,
                    $text_size,
                    $radius,
                    $border_width,
                    $border_color
                ),
            ];
        }

        // Button gap: split between the two buttons.
        $button_gap = $advanced['buttonGap']['desktop']['value'] ?? '40px';

        $custom_styles[] = [
            'atRules'     => false,
            'selector'    => $order_class . ' .btn-el--primary',
            'declaration' => sprintf('margin-right: calc(%1$s / 2);', $button_gap),
        ];

        $custom_styles[] = [
            'atRules'     => false,
            'selector'    => $order_class . ' .btn-el--secondary',
            'declaration' => sprintf('margin-left: calc(%1$s / 2);', $button_gap),
        ];

        // Button alignment.
        $alignment = $advanced['btnAlignment']['desktop']['value'] ?? 'left';
        $justify   = self::dtq_alignment_to_justify($alignment);

        $custom_styles[] = [
            'atRules'     => false,
            'selector'    => $order_class . ' .dtq-dual-btn',
            'declaration' => sprintf('justify-content: %1$s !important;', $justify),
        ];

        // -- Additive responsive (tablet/phone) output. ------------------------
        // Only the value and atRules differ from the desktop entries above;
        // selectors and property names stay identical.
        $breakpoints = [
            'tablet' => '@media only screen and (max-width: 980px)',
            'phone'  => '@media only screen and (max-width: 767px)',
        ];

        foreach ($breakpoints as $breakpoint => $at_rule) {
            // Button gap (responsive): split between the two buttons.
            $gap_value = self::dtq_responsive_value($advanced, 'buttonGap', $breakpoint);
            if (null !== $gap_value) {
                $custom_styles[] = [
                    'atRules'     => $at_rule,
                    'selector'    => $order_class . ' .btn-el--primary',
                    'declaration' => sprintf('margin-right: calc(%1$s / 2);', $gap_value),
                ];

                $custom_styles[] = [
                    'atRules'     => $at_rule,
                    'selector'    => $order_class . ' .btn-el--secondary',
                    'declaration' => sprintf('margin-left: calc(%1$s / 2);', $gap_value),
                ];
            }

            // Button alignment (responsive).
            $alignment_value = self::dtq_responsive_value($advanced, 'btnAlignment', $breakpoint);
            if (null !== $alignment_value) {
                $custom_styles[] = [
                    'atRules'     => $at_rule,
                    'selector'    => $order_class . ' .dtq-dual-btn',
                    'declaration' => sprintf(
                        'justify-content: %1$s !important;',
                        self::dtq_alignment_to_justify($alignment_value)
                    ),
                ];
            }
        }

        return $custom_styles;
    }

    /**
     * Map a button alignment keyword to a flexbox justify-content value.
     *
     * @param string $alignment Alignment keyword (left|center|right).
     *
     * @return string
     */
    protected static function dtq_alignment_to_justify($alignment)
    {
        if ('center' === $alignment) {
            return 'center';
        }

        if ('right' === $alignment) {
            return 'flex-end';
        }

        return 'flex-start';
    }

    /**
     * Get a responsive field value for a breakpoint, but only when it exists
     * and differs from the next-larger breakpoint. Returns null otherwise so
     * the caller can skip emitting a redundant media-query entry.
     *
     * @param array  $advanced   The `module.advanced` attrs array.
     * @param string $key        The field key.
     * @param string $breakpoint Either 'tablet' or 'phone'.
     *
     * @return string|null
     */
    protected static function dtq_responsive_value($advanced, $key, $breakpoint)
    {
        $value = $advanced[$key][$breakpoint]['value'] ?? null;

        if (null === $value) {
            return null;
        }

        // Compare against the next-larger breakpoint (phone -> tablet -> desktop).
        if ('phone' === $breakpoint) {
            $parent = $advanced[$key]['tablet']['value']
                ?? $advanced[$key]['desktop']['value']
                ?? null;
        } else {
            $parent = $advanced[$key]['desktop']['value'] ?? null;
        }

        if ($value === $parent) {
            return null;
        }

        return $value;
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

        $advanced = $attrs['module']['advanced'] ?? [];

        // Gap, connector, and alignment CSS is emitted as an inline <style> block in
        // RenderCallbackTrait::render_inline_styles() because Style::add() silently
        // drops raw array items that aren't returned by $elements->style().

        $all_styles = [
            // Module wrapper styles (background, spacing, etc.).
            $elements->style([
                'attrName'   => 'module',
                'styleProps' => [
                    'disabledOn' => [
                        'disabledModuleVisibility' => $settings['disabledModuleVisibility'] ?? null,
                    ],
                ],
            ]),

            // Primary button styles (Divi button group + border + box-shadow).
            $elements->style(['attrName' => 'btnA']),

            // Secondary button styles.
            $elements->style(['attrName' => 'btnB']),
        ];

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
