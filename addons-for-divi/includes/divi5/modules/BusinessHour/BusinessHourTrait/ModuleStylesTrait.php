<?php
/**
 * BusinessHour: Module styles trait.
 *
 * @package DiviTorqueLite\Modules\BusinessHour
 * @since   4.4.0
 */

namespace DiviTorqueLite\Modules\BusinessHour\BusinessHourTrait;

if (!defined('ABSPATH')) {
    exit;
}

use ET\Builder\FrontEnd\Module\Style;
use ET\Builder\Packages\Module\Options\Css\CssStyle;

trait ModuleStylesTrait
{
    /**
     * Convert a padding value to a CSS shorthand.
     *
     * Accepts BOTH the legacy pipe-string ("a|b|c|d") and the D5 spacing
     * object ({top,right,bottom,left}) that D4->D5 migration produces for
     * convertSpacing fields (item_padding, title_padding).
     *
     * @param mixed $val Padding value.
     *
     * @return string
     */
    public static function pad_value($val)
    {
        if (empty($val)) {
            return $val;
        }
        if (is_array($val)) {
            return sprintf(
                '%1$s %2$s %3$s %4$s',
                !empty($val['top']) ? $val['top'] : '0px',
                !empty($val['right']) ? $val['right'] : '0px',
                !empty($val['bottom']) ? $val['bottom'] : '0px',
                !empty($val['left']) ? $val['left'] : '0px'
            );
        }
        $parts = explode('|', (string) $val);
        return count($parts) === 4 ? implode(' ', $parts) : $val;
    }

    /**
     * Build the Business Hours parent declarations, mirroring styles.jsx.
     *
     * Ports the D4 render_css():
     * - showSeparator off  -> hide .dtq-business-hour-separator
     * - day/timeTextWidth  -> flex on day/time (when not "auto", +responsive)
     * - titleSpacing       -> margin-bottom !important on the title (+responsive)
     * - itemPadding        -> padding !important on each item row (+responsive)
     * - titlePadding       -> padding !important on the title (+responsive)
     * - itemSpacing        -> margin-bottom !important on each child module
     * - showDivider        -> border-bottom OR SVG pattern :after on each child
     * - separator*         -> border-top OR SVG pattern on the separator
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

        $tablet_at = '@media only screen and (max-width: 980px)';
        $phone_at  = '@media only screen and (max-width: 767px)';

        // Emit tablet/phone declarations for a key when set.
        $responsive = function ($key, $selector, $build) use ($advanced, $push, $tablet_at, $phone_at) {
            $tablet = $advanced[$key]['tablet']['value'] ?? null;
            $phone  = $advanced[$key]['phone']['value'] ?? null;
            if (null !== $tablet) {
                $push($selector, $build($tablet), $tablet_at);
            }
            if (null !== $phone) {
                $push($selector, $build($phone), $phone_at);
            }
        };

        // Separator visibility.
        if ('off' === $val('showSeparator', 'on')) {
            $push($order_class . ' .dtq-business-hour-separator', 'display: none!important;');
        }

        // Day / time text widths (D4: only when not "auto").
        $widths = [
            ['timeTextWidth', $order_class . ' .dtq-business-hour-time'],
            ['dayTextWidth', $order_class . ' .dtq-business-hour-day'],
        ];
        foreach ($widths as $w) {
            list($key, $selector) = $w;
            if ('auto' !== $val($key, 'auto')) {
                $push($selector, sprintf('flex: %1$s;', $val($key, 'auto')));
                $responsive($key, $selector, function ($v) {
                    return sprintf('flex: %1$s;', $v);
                });
            }
        }

        // Title spacing bottom (+responsive).
        $push($order_class . ' .dtq-business-hour-title', sprintf('margin-bottom: %1$s!important;', $val('titleSpacing', '25px')));
        $responsive('titleSpacing', $order_class . ' .dtq-business-hour-title', function ($v) {
            return sprintf('margin-bottom: %1$s!important;', $v);
        });

        // Item padding (+responsive). D4 selector: %%order_class%% .ba_business_hour_child .dtq-business-hour-child.
        $item_pad_sel = $order_class . ' .dtq_business_hour_item .dtq-business-hour-child';
        $push($item_pad_sel, sprintf('padding: %1$s!important;', self::pad_value($val('itemPadding', '0px|0px|0px|0px'))));
        $responsive('itemPadding', $item_pad_sel, function ($v) {
            return sprintf('padding: %1$s!important;', self::pad_value($v));
        });

        // Title padding (+responsive).
        $push($order_class . ' .dtq-business-hour-title', sprintf('padding: %1$s!important;', self::pad_value($val('titlePadding', '0px|0px|0px|0px'))));
        $responsive('titlePadding', $order_class . ' .dtq-business-hour-title', function ($v) {
            return sprintf('padding: %1$s!important;', self::pad_value($v));
        });

        // Item spacing bottom (D4 emits the desktop value only).
        $push($order_class . ' .dtq_business_hour_item', sprintf('margin-bottom: %1$s!important;', $val('itemSpacing', '25px')));

        // Item divider.
        if ('on' === $val('showDivider', 'off')) {
            $divider_color  = $val('dividerColor', '#dddddd');
            $divider_weight = $val('dividerWeight', '1px');
            $divider_height = $val('dividerHeight', '10px');

            $push($order_class . ' .dtq_business_hour_item', sprintf('padding-bottom: %1$s!important;', $val('itemSpacing', '25px')));

            if ('#' === substr((string) $divider_color, 0, 1)) {
                $divider_color = self::hex_to_rgb($divider_color);
            }

            $divider_type = explode('_', (string) $val('dividerType', 'solid_border'));

            if ('border' === ($divider_type[1] ?? '')) {
                $push(
                    $order_class . ' .dtq_business_hour_item',
                    sprintf('border-bottom: %1$s %2$s %3$s;', $divider_weight, $divider_type[0], $divider_color)
                );
            } else {
                $pattern_bg = '';
                if ('curved' === $divider_type[0] || 'zigzag' === $divider_type[0]) {
                    $pattern_bg = self::get_pattern($divider_type[0], $divider_color, $divider_weight);
                }

                $push(
                    $order_class . ' .dtq_business_hour_item:after',
                    sprintf(
                        'content: ""; position: absolute; background-image: url("%1$s"); height: %2$s; background-size: %2$s 100%%; bottom: calc(-%2$s / 2);',
                        $pattern_bg,
                        $divider_height
                    )
                );
            }
        }

        // Separator (between day and time).
        $separator_gap = $val('separatorGap', '15px');
        if ('' !== $separator_gap && null !== $separator_gap) {
            $push(
                $order_class . ' .dtq-business-hour-separator',
                sprintf('margin-right: %1$s; margin-left: %1$s;', $separator_gap)
            );
        }

        $separator_color  = $val('separatorColor', '#dddddd');
        $separator_weight = $val('separatorWeight', '1px');
        $separator_height = $val('separatorHeight', '10px');
        $type             = $val('separatorType', 'solid_border');

        if ('none_all' !== $type) {
            if ('#' === substr((string) $separator_color, 0, 1)) {
                $separator_color = self::hex_to_rgb($separator_color);
            }

            $sep_type = explode('_', (string) $type);

            if ('border' === ($sep_type[1] ?? '')) {
                $push(
                    $order_class . ' .dtq-business-hour-separator',
                    sprintf('border-top: %1$s %2$s %3$s; height: %1$s;', $separator_weight, $sep_type[0], $separator_color)
                );
            } else {
                $pattern_bg = '';
                if ('curved' === $sep_type[0] || 'zigzag' === $sep_type[0]) {
                    $pattern_bg = self::get_pattern($sep_type[0], $separator_color, $separator_weight);
                }

                $push(
                    $order_class . ' .dtq-business-hour-separator',
                    sprintf(
                        'background-image: url("%1$s"); height: %2$s; background-size: %2$s 100%%;',
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
            $elements->style(['attrName' => 'title']),
            $elements->style(['attrName' => 'titleBox']),
            $elements->style(['attrName' => 'item']),
            $elements->style(['attrName' => 'day']),
            $elements->style(['attrName' => 'time']),
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
