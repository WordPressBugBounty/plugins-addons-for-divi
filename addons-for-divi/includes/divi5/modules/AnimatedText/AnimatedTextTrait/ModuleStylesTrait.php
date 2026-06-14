<?php
/**
 * Animated Text: Module styles trait.
 *
 * @package DiviTorqueLite\Modules\AnimatedText
 * @since   4.4.0
 */

namespace DiviTorqueLite\Modules\AnimatedText\AnimatedTextTrait;

if (!defined('ABSPATH')) {
    exit;
}

use ET\Builder\FrontEnd\Module\Style;
use ET\Builder\Packages\Module\Options\Css\CssStyle;

trait ModuleStylesTrait
{
    /**
     * Build the flat custom-style declarations that mirror the D4 apply_css()
     * calls NOT covered by the named decoration groups (text alignment, inline
     * flex layout, text-stroke on prefix/animated/suffix, the typed cursor, and
     * the slide-animation rule). The prefix/animated/suffix padding, margin,
     * background, and border-radius are emitted by the named decoration groups
     * (elements->style below). Keep in lockstep with the JS twin in
     * src/divi5/modules/animated-text/styles.jsx.
     *
     * @param array  $attrs       Module attributes.
     * @param string $order_class Module order class selector.
     * @return array
     */
    public static function build_custom_styles($attrs, $order_class)
    {
        $advanced = $attrs['module']['advanced'] ?? [];

        $val = function ($key, $fallback) use ($advanced) {
            return $advanced[$key]['desktop']['value'] ?? $fallback;
        };

        $styles = [];
        $push   = function ($selector, $declaration, $at_rule = false) use (&$styles) {
            $styles[] = [
                'atRules'     => $at_rule,
                'selector'    => $selector,
                'declaration' => $declaration,
            ];
        };

        $layout          = $val('layout', 'inline');
        $text_alignment  = $val('textAlignment', 'left');
        $animation_type  = $val('animationType', 'typed');
        $show_cursor     = $val('showCursor', 'on');
        $slide_animation = $val('slideAnimation', 'flipInX');

        // Text alignment on the wrapper.
        $push($order_class, sprintf('text-align: %1$s;', $text_alignment));

        // Inline layout: flex the head and align via justify-content.
        if ('inline' === $layout) {
            $push($order_class . ' .dtq-animated-text-head', 'display: flex; align-items: center;');
            $push($order_class . ' .dtq-animated-text-head', sprintf('justify-content: %1$s;', $text_alignment));
        }

        // Text stroke (webkit-only; not expressible via decoration groups).
        $stroke_blocks = [
            ['prefixStroke',   'prefixStrokeColor',   $order_class . ' .dtq-animated-text-prefix span'],
            ['animatedStroke', 'animatedStrokeColor', $order_class . ' .dtq-animated-text-main'],
            ['suffixStroke',   'suffixStrokeColor',   $order_class . ' .dtq-animated-text-suffix span'],
        ];
        foreach ($stroke_blocks as $block) {
            list($width_key, $color_key, $selector) = $block;
            $width = $val($width_key, '');
            if ('' !== $width && '0px' !== $width && '0' !== $width) {
                $push(
                    $selector,
                    sprintf(
                        '-webkit-text-stroke-width: %1$s; -webkit-text-stroke-color: %2$s;',
                        $width,
                        $val($color_key, '')
                    )
                );
            }
        }

        // Typed cursor (mirrors D4 .dtq-text-animation:after when show_cursor on).
        if ('typed' === $animation_type && 'on' === $show_cursor) {
            $push(
                $order_class . ' .dtq-text-animation:after',
                sprintf(
                    'display: block; right: -%1$s; width: %2$s; background: %3$s; height: %4$s;',
                    $val('cursorGap', '8px'),
                    $val('cursorWidth', '3px'),
                    $val('cursorColor', '#333333'),
                    $val('cursorHeight', '100%')
                )
            );
        }

        // Slide animation (mirrors D4: applied to the active slide li).
        $push(
            $order_class . ' .dtq-animated-text-slide li.text-in',
            sprintf('animation: %1$s 700ms;', $slide_animation)
        );

        return $styles;
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

            // Prefix / Animated / Suffix font + background + border + spacing.
            $elements->style(['attrName' => 'prefix']),
            $elements->style(['attrName' => 'animated']),
            $elements->style(['attrName' => 'suffix']),
        ];

        // Flat custom declarations ported from D4 apply_css().
        $custom_styles = self::build_custom_styles($attrs, $order_class);
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
