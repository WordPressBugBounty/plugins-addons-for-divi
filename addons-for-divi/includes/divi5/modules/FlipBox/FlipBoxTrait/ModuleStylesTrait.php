<?php
/**
 * FlipBox: Module styles trait.
 *
 * @package DiviTorqueLite\Modules\FlipBox
 * @since   4.4.0
 */

namespace DiviTorqueLite\Modules\FlipBox\FlipBoxTrait;

if (!defined('ABSPATH')) {
    exit;
}

use ET\Builder\FrontEnd\Module\Style;
use ET\Builder\Packages\Module\Options\Css\CssStyle;

trait ModuleStylesTrait
{
    /**
     * The sides a padding value sets, as side => CSS value. Sides left empty
     * are absent, so the caller can fill them from a larger breakpoint or the
     * default instead of forcing 0px.
     *
     * Accepts the D5 spacing object {top,right,bottom,left}, the D4 string
     * "top|right|bottom|left", and the raw field edit {side, value} that the
     * builder saved before the padding fields got Divi's spacing handler
     * (pages edited on 2.4.1 and earlier hold that shape).
     *
     * @param string|array $val Padding value.
     * @return array
     */
    public static function dtq_pad_sides($val)
    {
        $names = ['top', 'right', 'bottom', 'left'];

        if (is_array($val)) {
            $parts = [];
            foreach ($names as $name) {
                $parts[] = $val[$name] ?? '';
            }
            // Raw field edit {side, value}: use it unless that side is set too.
            $side  = $val['side'] ?? null;
            $index = is_string($side) ? array_search($side, $names, true) : false;
            if (false !== $index && '' === $parts[$index] && is_scalar($val['value'] ?? null)) {
                $parts[$index] = $val['value'];
            }
        } else {
            $parts = array_pad(explode('|', (string) $val), 4, '');
        }

        $sides = [];
        foreach ($names as $i => $name) {
            $part = is_scalar($parts[$i]) ? trim((string) $parts[$i]) : '';
            if ('' !== $part) {
                $sides[$name] = $part;
            }
        }
        return $sides;
    }

    /**
     * CSS padding shorthand for one breakpoint. Each side comes from the
     * nearest breakpoint that sets it (phone, then tablet, then desktop),
     * then from the default, the way Divi's own spacing inherits.
     *
     * @param array  $advanced   The `module.advanced` attrs array.
     * @param string $key        Attribute key, e.g. "frontPadding".
     * @param string $breakpoint desktop, tablet or phone.
     * @param string $default    Default value, "top|right|bottom|left".
     * @return string
     */
    public static function dtq_pad_at($advanced, $key, $breakpoint, $default)
    {
        $sides = self::dtq_pad_sides($default);
        foreach (['desktop', 'tablet', 'phone'] as $bp) {
            $sides = array_merge($sides, self::dtq_pad_sides($advanced[$key][$bp]['value'] ?? ''));
            if ($bp === $breakpoint) {
                break;
            }
        }
        return sprintf(
            '%1$s %2$s %3$s %4$s',
            $sides['top'] ?? '0px',
            $sides['right'] ?? '0px',
            $sides['bottom'] ?? '0px',
            $sides['left'] ?? '0px'
        );
    }

    /**
     * Build the flat custom-style declarations that mirror the D4 render_css()
     * `ET_Builder_Element::set_style()` / `get_responsive_styles()` calls.
     * Keep this in lockstep with the JS twin in
     * src/divi5/modules/flip-box/styles.jsx.
     *
     * @param array  $attrs       Module attributes.
     * @param string $order_class Module order class selector.
     * @return array Array of ['atRules'=>...,'selector'=>..,'declaration'=>..].
     */
    public static function build_custom_styles($attrs, $order_class)
    {
        $advanced = $attrs['module']['advanced'] ?? [];

        $val = function ($key, $fallback) use ($advanced) {
            return $advanced[$key]['desktop']['value'] ?? $fallback;
        };

        $styles = [];

        $push = function ($selector, $declaration, $at_rule = false) use (&$styles) {
            $styles[] = [
                'atRules'     => $at_rule,
                'selector'    => $selector,
                'declaration' => $declaration,
            ];
        };

        // ------------------------------------------------------------------
        // Static (desktop-only) declarations — mirror the plain D4
        // set_style() calls, which only ever used the desktop prop value.
        // ------------------------------------------------------------------

        $animation_type = $val('animationType', 'flip');
        $direction_alt  = $val('directionAlt', 'h');
        $duration       = $val('duration', '600ms');
        $main_height    = $val('mainHeight', '300px');

        if ('rotate_3d' === $animation_type) {
            $push($order_class . ' .dtq-flipbox-inner .dtq-flank', sprintf('background: %s;', $val('flankColor', '#dddddd')));
            if ('v' === $direction_alt) {
                $push(
                    $order_class . ' .dtq-flipbox-inner .dtq-flank',
                    sprintf('transform: rotateX(-90deg) translateZ(calc(%s - 100px))!important;', $main_height)
                );
            }
        }

        // Flip duration on both cards + container (D4: "transition: all {d} ease;").
        $push(
            sprintf(
                '%1$s .dtq-flipbox-front-card, %1$s .dtq-flipbox-back-card, %1$s .dtq-flipbox-card-container',
                $order_class
            ),
            sprintf('transition: all %s ease;', $duration)
        );

        // Vertical alignment + media-position layout for each side. The two
        // sides intentionally share the same branch logic D4 used (back side
        // position values are flex-start/center/flex-end, exactly like D4).
        $sides = [
            'front' => ['alignKey' => 'frontAlignItems', 'posKey' => 'frontImgPosition', 'iconColorKey' => 'frontIconColor'],
            'back'  => ['alignKey' => 'backAlignItems', 'posKey' => 'backImgPosition', 'iconColorKey' => 'backIconColor'],
        ];
        foreach ($sides as $side => $keys) {
            $align_items = $val($keys['alignKey'], 'center');
            $img_pos     = $val($keys['posKey'], 'center');
            $icon_color  = $val($keys['iconColorKey'], '');

            $push($order_class . " .dtq-flipbox-{$side}-card", sprintf('align-items: %s;', $align_items));

            if ('center' !== $img_pos) {
                $push($order_class . " .dtq-flipbox-{$side}-content", sprintf('align-items: %s;', $align_items));
                $push($order_class . " .dtq-flipbox-{$side}-content", 'display: flex;');
            }
            if ('right' === $img_pos) {
                $push($order_class . " .dtq-flipbox-{$side}-content", 'flex-direction: row-reverse;');
            }
            if (!empty($icon_color)) {
                $push($order_class . " .dtq-flipbox-icon-{$side}", sprintf('color: %s;', $icon_color));
            }
        }

        // ------------------------------------------------------------------
        // Responsive declarations — mirror the D4 get_responsive_styles()
        // calls. Desktop always emits (with the D4 default as fallback);
        // tablet/phone emit only when the breakpoint value exists and
        // differs from the next-larger breakpoint (review precedent).
        // ------------------------------------------------------------------

        // Padding resolves side by side across breakpoints (dtq_pad_at), so a
        // padding entry reads its own value instead of the plain fallback.
        $pad_at = function ($key, $def) use ($advanced) {
            return function ($breakpoint) use ($advanced, $key, $def) {
                return self::dtq_pad_at($advanced, $key, $breakpoint, $def);
            };
        };

        $entries = [];

        $entries[] = [
            'key'   => 'mainHeight',
            'def'   => '300px',
            'when'  => true,
            'rules' => function ($v) use ($order_class) {
                return [[$order_class . ' .dtq-flipbox-inner', sprintf('height: %s;', $v)]];
            },
        ];

        foreach (['front', 'back'] as $side) {
            $entries[] = [
                'key'   => $side . 'Alignment',
                'def'   => 'center',
                'when'  => true,
                'rules' => function ($v) use ($order_class, $side) {
                    return [[$order_class . " .dtq-flipbox-{$side}-card", sprintf('text-align: %s;', $v)]];
                },
            ];
            $entries[] = [
                'key'     => $side . 'Padding',
                'def'     => '30px|30px|30px|30px',
                'when'    => true,
                'resolve' => $pad_at($side . 'Padding', '30px|30px|30px|30px'),
                'rules'   => function ($v) use ($order_class, $side) {
                    return [[$order_class . " .dtq-flipbox-{$side}-card", sprintf('padding: %s;', $v)]];
                },
            ];
            $entries[] = [
                'key'     => $side . 'CtPadding',
                'def'     => '0px|0px|0px|0px',
                'when'    => true,
                'resolve' => $pad_at($side . 'CtPadding', '0px|0px|0px|0px'),
                'rules'   => function ($v) use ($order_class, $side) {
                    return [[$order_class . " .dtq-flipbox-{$side}-card .dtq-flipbox-content-wrap", sprintf('padding: %s;', $v)]];
                },
            ];
            $entries[] = [
                'key'   => $side . 'IconSize',
                'def'   => '60px',
                'when'  => true,
                'rules' => function ($v) use ($order_class, $side) {
                    return [[$order_class . " .dtq-flipbox-icon-{$side}", sprintf('font-size: %s;', $v)]];
                },
            ];
            $entries[] = [
                'key'   => $side . 'ImgWidth',
                'def'   => '',
                'when'  => !empty($val($side . 'ImgWidth', '')),
                'rules' => function ($v) use ($order_class, $side) {
                    return [[$order_class . " .dtq-flipbox-figure-{$side}", sprintf('width: %1$s; max-width: %1$s; flex: 0 0 %1$s;', $v)]];
                },
            ];
            $entries[] = [
                'key'   => $side . 'ImgHeight',
                'def'   => '',
                'when'  => !empty($val($side . 'ImgHeight', '')),
                'rules' => function ($v) use ($order_class, $side) {
                    return [
                        [$order_class . " .dtq-flipbox-figure-{$side}", sprintf('height: %s;', $v)],
                        [$order_class . " .dtq-flipbox-figure-{$side} img", sprintf('height: %s;', $v)],
                    ];
                },
            ];
            $entries[] = [
                'key'     => $side . 'ImgPadding',
                'def'     => '0px|0px|0px|0px',
                'when'    => true,
                'resolve' => $pad_at($side . 'ImgPadding', '0px|0px|0px|0px'),
                'rules'   => function ($v) use ($order_class, $side) {
                    return [[$order_class . " .dtq-flipbox-figure-{$side} img", sprintf('padding: %s;', $v)]];
                },
            ];
            $entries[] = [
                'key'   => $side . 'SubtitleSpacing',
                'def'   => '0px',
                'when'  => true,
                'rules' => function ($v) use ($order_class, $side) {
                    return [[$order_class . " .dtq-flipbox-subtitle-{$side}", sprintf('margin-top: %s;', $v)]];
                },
            ];
            $entries[] = [
                'key'   => $side . 'DescSpacing',
                'def'   => '0px',
                'when'  => true,
                'rules' => function ($v) use ($order_class, $side) {
                    return [[$order_class . " .dtq-flipbox-desc-{$side}", sprintf('margin-top: %s;', $v)]];
                },
            ];
        }

        $entries[] = [
            'key'   => 'btnSpacing',
            'def'   => '15px',
            'when'  => true,
            'rules' => function ($v) use ($order_class) {
                return [[$order_class . ' .dtq-flipbox-btn-wrap', sprintf('margin-top: %s;', $v)]];
            },
        ];

        $bp_val = function ($key, $breakpoint, $fallback) use ($advanced) {
            $node = $advanced[$key] ?? [];
            if ('phone' === $breakpoint) {
                return $node['phone']['value']
                    ?? $node['tablet']['value']
                    ?? $node['desktop']['value']
                    ?? $fallback;
            }
            if ('tablet' === $breakpoint) {
                return $node['tablet']['value']
                    ?? $node['desktop']['value']
                    ?? $fallback;
            }
            return $node['desktop']['value'] ?? $fallback;
        };

        $bp_changed = function ($key, $breakpoint) use ($advanced) {
            $node = $advanced[$key] ?? [];
            if ('tablet' === $breakpoint) {
                if (!isset($node['tablet']['value'])) {
                    return false;
                }
                $desktop = $node['desktop']['value'] ?? null;
                return $node['tablet']['value'] !== $desktop;
            }
            if ('phone' === $breakpoint) {
                if (!isset($node['phone']['value'])) {
                    return false;
                }
                $larger = $node['tablet']['value'] ?? ($node['desktop']['value'] ?? null);
                return $node['phone']['value'] !== $larger;
            }
            return false;
        };

        $at_rules = [
            'tablet' => '@media only screen and (max-width: 980px)',
            'phone'  => '@media only screen and (max-width: 767px)',
        ];

        $value_at = function ($entry, $breakpoint) use ($bp_val) {
            return isset($entry['resolve'])
                ? $entry['resolve']($breakpoint)
                : $bp_val($entry['key'], $breakpoint, $entry['def']);
        };

        foreach ($entries as $entry) {
            if (!$entry['when']) {
                continue;
            }
            foreach ($entry['rules']($value_at($entry, 'desktop')) as $rule) {
                $push($rule[0], $rule[1]);
            }
            foreach (['tablet', 'phone'] as $breakpoint) {
                if (!$bp_changed($entry['key'], $breakpoint)) {
                    continue;
                }
                foreach ($entry['rules']($value_at($entry, $breakpoint)) as $rule) {
                    $push($rule[0], $rule[1], $at_rules[$breakpoint]);
                }
            }
        }

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

            // Front/back card backgrounds.
            $elements->style(['attrName' => 'front']),
            $elements->style(['attrName' => 'back']),

            // Front/back media background + border.
            $elements->style(['attrName' => 'frontMedia']),
            $elements->style(['attrName' => 'backMedia']),

            // Card border + box shadow (both sides).
            $elements->style(['attrName' => 'card']),

            // Text fonts.
            $elements->style(['attrName' => 'frontTitle']),
            $elements->style(['attrName' => 'frontSubtitle']),
            $elements->style(['attrName' => 'frontDescription']),
            $elements->style(['attrName' => 'backTitle']),
            $elements->style(['attrName' => 'backSubtitle']),
            $elements->style(['attrName' => 'backDescription']),

            // Button styles.
            $elements->style(['attrName' => 'button']),
        ];

        // Flat custom declarations ported from D4 render_css().
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
