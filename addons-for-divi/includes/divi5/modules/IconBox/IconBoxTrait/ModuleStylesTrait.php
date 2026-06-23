<?php
/**
 * IconBox: Module styles trait.
 *
 * @package DiviTorqueLite\Modules\IconBox
 * @since   4.4.0
 */

namespace DiviTorqueLite\Modules\IconBox\IconBoxTrait;

if (!defined('ABSPATH')) {
    exit;
}

use ET\Builder\FrontEnd\Module\Style;
use ET\Builder\Packages\Module\Options\Css\CssStyle;

trait ModuleStylesTrait
{
    /**
     * Normalize a spacing value to a CSS shorthand.
     *
     * After a D4→D5 migration, convertSpacing fields become D5 spacing objects
     * `{top,right,bottom,left}` instead of the legacy `"a|b|c|d"` pipe string.
     * Accept both formats and emit identical CSS for the string case.
     *
     * @param mixed $val The spacing value (object array or pipe string).
     * @return string CSS shorthand, or '' when empty.
     */
    public static function dtq_spacing($val)
    {
        if (empty($val)) {
            return '';
        }
        if (is_array($val)) {
            return sprintf('%s %s %s %s', $val['top'] ?? '0px', $val['right'] ?? '0px', $val['bottom'] ?? '0px', $val['left'] ?? '0px');
        }
        $p = explode('|', (string) $val);
        return 4 === count($p) ? implode(' ', $p) : (string) $val;
    }

    /**
     * Build absolute-placement declarations for an element (icon or badge).
     *
     * Ports the D4 `get_absolute_element_styles()` logic: positions the element
     * via the chosen corner, honours the horizontal/vertical "center" toggles,
     * and applies the matching translate transform.
     *
     * @param array  $advanced The module.advanced attrs.
     * @param string $prefix   Field prefix ('icon' or 'badge').
     * @param string $selector CSS selector for the element.
     * @return array
     */
    public static function build_absolute_styles($advanced, $prefix, $selector)
    {
        $position   = $advanced[$prefix . 'Position']['desktop']['value'] ?? 'left_top';
        $is_center_x = ($advanced[$prefix . 'IsCenterX']['desktop']['value'] ?? 'off') === 'on';
        $is_center_y = ($advanced[$prefix . 'IsCenterY']['desktop']['value'] ?? 'off') === 'on';
        $offset_x   = $advanced[$prefix . 'OffsetX']['desktop']['value'] ?? '';
        $offset_y   = $advanced[$prefix . 'OffsetY']['desktop']['value'] ?? '';

        $parts = explode('_', (string) $position);
        $side  = $parts[0] ?? 'left';
        $vert  = $parts[1] ?? 'top';

        $out = [];

        $out[] = [
            'atRules'     => false,
            'selector'    => $selector,
            'declaration' => 'position: absolute; z-index: 999;',
        ];

        $opp_side = 'left' === $side ? 'right' : 'left';
        $out[] = [
            'atRules'     => false,
            'selector'    => $selector,
            'declaration' => $is_center_x
                ? sprintf('%1$s: 50%%; %2$s: auto;', $side, $opp_side)
                : sprintf('%1$s: %2$s; %3$s: auto;', $side, '' !== $offset_x ? $offset_x : '50%', $opp_side),
        ];

        $opp_vert = 'top' === $vert ? 'bottom' : 'top';
        $out[] = [
            'atRules'     => false,
            'selector'    => $selector,
            'declaration' => $is_center_y
                ? sprintf('%1$s: 50%%; %2$s: auto;', $vert, $opp_vert)
                : sprintf('%1$s: %2$s; %3$s: auto;', $vert, '' !== $offset_y ? $offset_y : '50%', $opp_vert),
        ];

        $val_x = '0';
        $val_y = '0';
        if ($is_center_x) {
            $val_x = ('right_top' === $position || 'right_bottom' === $position) ? '50%' : '-50%';
        }
        if ($is_center_y) {
            $val_y = ('right_top' === $position || 'left_top' === $position) ? '-50%' : '50%';
        }

        $out[] = [
            'atRules'     => false,
            'selector'    => $selector,
            'declaration' => sprintf('transform: translateX(%1$s) translateY(%2$s);', $val_x, $val_y),
        ];

        return $out;
    }

    /**
     * Build the custom IconBox declarations from the flat `module.advanced` attrs.
     *
     * Ports the D4 `render_css()` set_style calls into the custom-style array
     * shape: `['atRules' => false, 'selector' => ..., 'declaration' => ...]`.
     *
     * @param array  $advanced    The `module.advanced` attrs array.
     * @param string $order_class The module order class.
     * @return array
     */
    public static function build_iconbox_styles($advanced, $order_class)
    {
        if (!is_array($advanced)) {
            return [];
        }

        $styles = [];

        $use_image     = ($advanced['useImage']['desktop']['value'] ?? 'off') === 'on';
        $placement     = $advanced['iconPlacement']['desktop']['value'] ?? 'normal';
        $alignment     = $advanced['contentAlignment']['desktop']['value'] ?? 'center';
        $icon_color    = $advanced['iconColor']['desktop']['value'] ?? '#333';
        $icon_bg       = $advanced['iconBg']['desktop']['value'] ?? '';
        $badge_bg      = $advanced['badgeBg']['desktop']['value'] ?? '';
        $icon_rotate   = $advanced['iconBgRotate']['desktop']['value'] ?? '0deg';
        $icon_width    = $advanced['iconWidth']['desktop']['value'] ?? 'auto';
        $icon_height   = $advanced['iconHeight']['desktop']['value'] ?? 'auto';
        $icon_size     = $advanced['iconSize']['desktop']['value'] ?? '60px';
        $icon_spacing  = $advanced['iconSpacing']['desktop']['value'] ?? '10px';
        $icon_padding  = $advanced['iconPadding']['desktop']['value'] ?? '';
        $title_spacing = $advanced['titleSpacing']['desktop']['value'] ?? '10px';

        $is_negative = '-' === substr((string) $icon_rotate, 0, 1);
        $rotate_deg  = absint($icon_rotate);

        // Alignment.
        $styles[] = [
            'atRules'     => false,
            'selector'    => $order_class . ' .dtq-iconbox',
            'declaration' => sprintf('text-align: %1$s;', $alignment),
        ];

        if ('right' === $alignment) {
            $styles[] = [
                'atRules'     => false,
                'selector'    => $order_class . ' .dtq-iconbox__icon-wrap',
                'declaration' => 'justify-content: flex-end;',
            ];
        } elseif ('center' === $alignment) {
            $styles[] = [
                'atRules'     => false,
                'selector'    => $order_class . ' .dtq-iconbox__icon-wrap',
                'declaration' => 'justify-content: center;',
            ];
        }

        // Icon bottom spacing (normal placement only).
        if ('normal' === $placement) {
            $styles[] = [
                'atRules'     => false,
                'selector'    => $order_class . ' .dtq-iconbox__icon-wrap',
                'declaration' => sprintf('margin-bottom: %1$s;', $icon_spacing),
            ];
        }

        // Icon box rotate.
        $styles[] = [
            'atRules'     => false,
            'selector'    => $order_class . ' .dtq-iconbox__icon',
            'declaration' => sprintf('transform: rotate(%1$s);', $icon_rotate),
        ];
        $styles[] = [
            'atRules'     => false,
            'selector'    => $order_class . ' .dtq-iconbox__icon i, ' . $order_class . ' .dtq-iconbox__icon img',
            'declaration' => sprintf('transform: rotate(%1$s%2$sdeg);', $is_negative ? '' : '-', $rotate_deg),
        ];

        // Icon width / height.
        if ('auto' !== $icon_width) {
            $styles[] = [
                'atRules'     => false,
                'selector'    => $order_class . ' .dtq-iconbox__icon',
                'declaration' => sprintf('width: %1$s;', $icon_width),
            ];
        }
        if ('auto' !== $icon_height) {
            $styles[] = [
                'atRules'     => false,
                'selector'    => $order_class . ' .dtq-iconbox__icon',
                'declaration' => sprintf('height: %1$s;', $icon_height),
            ];
        }

        // Icon background.
        if (!empty($icon_bg)) {
            $styles[] = [
                'atRules'     => false,
                'selector'    => $order_class . ' .dtq-iconbox__icon',
                'declaration' => sprintf('background-color: %1$s;', $icon_bg),
            ];
        }

        if (!$use_image) {
            // Icon font.
            $icon_attr   = $advanced['icon']['desktop']['value'] ?? [];
            $icon_type   = is_array($icon_attr) ? ($icon_attr['type'] ?? '') : '';
            $font_family = 'fa' === $icon_type ? 'FontAwesome' : 'ETmodules';

            $styles[] = [
                'atRules'     => false,
                'selector'    => $order_class . ' .dtq-iconbox__icon i',
                'declaration' => sprintf('font-family: "%1$s"; font-size: %2$s; color: %3$s;', $font_family, $icon_size, $icon_color),
            ];
        } else {
            // Image icon.
            $icon_pad_css = self::dtq_spacing($icon_padding);
            if ('' !== $icon_pad_css) {
                $styles[] = [
                    'atRules'     => false,
                    'selector'    => $order_class . ' .dtq-iconbox__icon',
                    'declaration' => sprintf('padding: %1$s;', $icon_pad_css),
                ];
            }
            $styles[] = [
                'atRules'     => false,
                'selector'    => $order_class . ' .dtq-iconbox__icon img',
                'declaration' => sprintf('width: %1$s;', $icon_size),
            ];
        }

        // Badge background.
        if (!empty($badge_bg)) {
            $styles[] = [
                'atRules'     => false,
                'selector'    => $order_class . ' .dtq-iconbox__badge',
                'declaration' => sprintf('background-color: %1$s;', $badge_bg),
            ];
        }

        // Title bottom spacing.
        $styles[] = [
            'atRules'     => false,
            'selector'    => $order_class . ' .dtq-iconbox__title',
            'declaration' => sprintf('padding-bottom: %1$s;', $title_spacing),
        ];

        // Absolute icon placement (only when iconPlacement === 'absolute').
        if ('absolute' === $placement) {
            foreach (self::build_absolute_styles($advanced, 'icon', $order_class . ' .dtq-iconbox__icon') as $s) {
                $styles[] = $s;
            }
        }

        // Badge placement (badge is always absolutely positioned) + padding.
        foreach (self::build_absolute_styles($advanced, 'badge', $order_class . ' .dtq-iconbox__badge') as $s) {
            $styles[] = $s;
        }

        $badge_padding = $advanced['badgePadding']['desktop']['value'] ?? '';
        $badge_pad_css = self::dtq_spacing($badge_padding);
        if ('' !== $badge_pad_css) {
            $styles[] = [
                'atRules'     => false,
                'selector'    => $order_class . ' .dtq-iconbox__badge',
                'declaration' => sprintf('padding: %1$s;', $badge_pad_css),
            ];
        }

        // ---------------------------------------------------------------
        // Additive responsive (tablet/phone) output. Desktop entries above
        // are untouched; the loop below only APPENDS @media entries for
        // responsive fields whose breakpoint value exists and differs from
        // the next-larger breakpoint.
        // ---------------------------------------------------------------
        $breakpoints = [
            'tablet' => '@media only screen and (max-width: 980px)',
            'phone'  => '@media only screen and (max-width: 767px)',
        ];

        foreach ($breakpoints as $breakpoint => $at_rule) {
            $larger = 'phone' === $breakpoint ? 'tablet' : 'desktop';

            // Resolve a field's value at this breakpoint, only when it exists
            // and differs from the next-larger breakpoint's resolved value.
            $bp = function ($key) use ($advanced, $breakpoint, $larger) {
                $val = $advanced[$key][$breakpoint]['value'] ?? null;
                if (null === $val) {
                    return null;
                }
                $larger_val = $advanced[$key][$larger]['value']
                    ?? ($advanced[$key]['desktop']['value'] ?? null);
                if ($val === $larger_val) {
                    return null;
                }
                return $val;
            };

            // Icon bottom spacing (normal placement only) — iconSpacing.
            if ('normal' === $placement) {
                $val = $bp('iconSpacing');
                if (null !== $val) {
                    $styles[] = [
                        'atRules'     => $at_rule,
                        'selector'    => $order_class . ' .dtq-iconbox__icon-wrap',
                        'declaration' => sprintf('margin-bottom: %1$s;', $val),
                    ];
                }
            }

            // Icon width — iconWidth (skip when desktop resolved to auto-only
            // is irrelevant; emit whenever a non-auto responsive value exists).
            $val = $bp('iconWidth');
            if (null !== $val && 'auto' !== $val) {
                $styles[] = [
                    'atRules'     => $at_rule,
                    'selector'    => $order_class . ' .dtq-iconbox__icon',
                    'declaration' => sprintf('width: %1$s;', $val),
                ];
            }

            // Icon height — iconHeight.
            $val = $bp('iconHeight');
            if (null !== $val && 'auto' !== $val) {
                $styles[] = [
                    'atRules'     => $at_rule,
                    'selector'    => $order_class . ' .dtq-iconbox__icon',
                    'declaration' => sprintf('height: %1$s;', $val),
                ];
            }

            if (!$use_image) {
                // Icon font size — iconSize (font icon branch).
                $val = $bp('iconSize');
                if (null !== $val) {
                    $styles[] = [
                        'atRules'     => $at_rule,
                        'selector'    => $order_class . ' .dtq-iconbox__icon i',
                        'declaration' => sprintf('font-size: %1$s;', $val),
                    ];
                }
            } else {
                // Image icon padding — iconPadding (image branch).
                $val = $bp('iconPadding');
                if (null !== $val) {
                    $pad_css = self::dtq_spacing($val);
                    if ('' !== $pad_css) {
                        $styles[] = [
                            'atRules'     => $at_rule,
                            'selector'    => $order_class . ' .dtq-iconbox__icon',
                            'declaration' => sprintf('padding: %1$s;', $pad_css),
                        ];
                    }
                }
                // Image icon size — iconSize (image width branch).
                $val = $bp('iconSize');
                if (null !== $val) {
                    $styles[] = [
                        'atRules'     => $at_rule,
                        'selector'    => $order_class . ' .dtq-iconbox__icon img',
                        'declaration' => sprintf('width: %1$s;', $val),
                    ];
                }
            }

            // Title bottom spacing — titleSpacing.
            $val = $bp('titleSpacing');
            if (null !== $val) {
                $styles[] = [
                    'atRules'     => $at_rule,
                    'selector'    => $order_class . ' .dtq-iconbox__title',
                    'declaration' => sprintf('padding-bottom: %1$s;', $val),
                ];
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

        $advanced      = $attrs['module']['advanced'] ?? [];
        $custom_styles = self::build_iconbox_styles($advanced, $order_class);

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
            $elements->style(['attrName' => 'title']),

            // Description font styles.
            $elements->style(['attrName' => 'description']),

            // Badge font + border styles.
            $elements->style(['attrName' => 'badge']),

            // Icon border + box-shadow styles.
            $elements->style(['attrName' => 'icon']),
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
