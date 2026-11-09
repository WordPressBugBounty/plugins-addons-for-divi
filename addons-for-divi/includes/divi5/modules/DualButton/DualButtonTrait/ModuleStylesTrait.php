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

        // Gap, connector and alignment CSS (desktop, tablet and phone) is printed
        // as an inline <style> by RenderCallbackTrait::render_inline_styles(),
        // which sanitizes every value. The two helpers above serve it.

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
