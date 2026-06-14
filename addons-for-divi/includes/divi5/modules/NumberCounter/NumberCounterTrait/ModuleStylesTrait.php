<?php
/**
 * Number Counter: Module styles trait.
 *
 * @package DiviTorqueLite\Modules\NumberCounter
 * @since   4.4.0
 */

namespace DiviTorqueLite\Modules\NumberCounter\NumberCounterTrait;

if (!defined('ABSPATH')) {
    exit;
}

use ET\Builder\FrontEnd\Module\Style;
use ET\Builder\Packages\Module\Options\Css\CssStyle;

trait ModuleStylesTrait
{
    /**
     * Shared absolute-element declaration builder (mirrors D4
     * get_absolute_element_styles(); same as the Testimonial twin). D4 stores
     * the offsets as SEPARATE fields (position select + is_center_x/y toggles
     * + offset_x/y ranges) — nothing is packed.
     *
     * @param string $position    left_top|left_bottom|right_top|right_bottom.
     * @param string $is_center_x 'on'|'off'.
     * @param string $is_center_y 'on'|'off'.
     * @param string $offset_x    Horizontal offset.
     * @param string $offset_y    Vertical offset.
     * @param string $z_index     z-index value.
     * @return string
     */
    public static function absolute_decl($position, $is_center_x, $is_center_y, $offset_x, $offset_y, $z_index)
    {
        $pos_parts = explode('_', (string) $position);
        $pos_x     = $pos_parts[0] ?? 'right';
        $pos_y     = $pos_parts[1] ?? 'top';
        $x_val     = ('on' === $is_center_x) ? '50%' : $offset_x;
        $y_val     = ('on' === $is_center_y) ? '50%' : $offset_y;
        $tx        = '0';
        $ty        = '0';
        if ('on' === $is_center_x) {
            $tx = ('right' === $pos_x) ? '50%' : '-50%';
        }
        if ('on' === $is_center_y) {
            $ty = ('top' === $pos_y) ? '-50%' : '50%';
        }
        return sprintf(
            'position: absolute; z-index: %1$s; %2$s: %3$s; %4$s: %5$s; transform: translateX(%6$s) translateY(%7$s);',
            $z_index,
            $pos_x,
            $x_val,
            $pos_y,
            $y_val,
            $tx,
            $ty
        );
    }

    /**
     * Build the flat custom-style declarations that mirror the D4 render_css()
     * `ET_Builder_Element::set_style()` calls. Keep this in lockstep with the
     * JS twin in src/divi5/modules/number-counter/styles.jsx.
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

        // D4 parity (dt_if_not_migrated()): legacy installs (the `ba_version`
        // option exists) shipped 'center' as the D4 default number_alignment.
        // module.json carries the NEW-USER default ('left'); when the attr is
        // absent on a legacy install we fall back to the legacy default so
        // migrated layouts render unchanged.
        $is_legacy = function_exists('get_option') && get_option('ba_version');

        $use_box          = $val('useBox', 'on');
        $number_rotate    = $val('numberRotate', '0deg');
        $number_alignment = $advanced['numberAlignment']['desktop']['value'] ?? ($is_legacy ? 'center' : 'left');
        $number_placement = $val('numberPlacement', '_default');
        $number_height    = $val('numberHeight', '100px');
        $number_width     = $val('numberWidth', '100px');
        $title_spacing    = $val('titleSpacing', '10px');

        $styles = [];

        $push = function ($selector, $declaration, $at_rule = false) use (&$styles) {
            $styles[] = [
                'atRules'     => $at_rule,
                'selector'    => $selector,
                'declaration' => $declaration,
            ];
        };

        // Number box: size + flex centering + rotation (D4: use_box === 'on'
        // only; the number background/border/box-shadow are emitted via the
        // numberBox element styles, also gated on use_box).
        if ('on' === $use_box) {
            $push($order_class . ' .dtq-number-wrap', sprintf('height: %s;', $number_height));
            $push($order_class . ' .dtq-number-wrap', sprintf('width: %s;', $number_width));
            $push(
                $order_class . ' .dtq-number-wrap',
                sprintf(
                    'display: flex; justify-content: center; align-items: center; border-style: solid; transform: rotate(%s);',
                    $number_rotate
                )
            );
        }

        // Number alignment.
        $push($order_class . ' .dtq-number-wrap', sprintf('text-align: %s;', $number_alignment));
        if ('center' === $number_alignment) {
            $push($order_class . ' .dtq-number-wrap', 'margin-right: auto;margin-left: auto;');
        } elseif ('right' === $number_alignment) {
            $push($order_class . ' .dtq-number-wrap', 'margin-left: auto;');
        }

        // Title spacing (default number placement only, like D4).
        if ('_default' === $number_placement) {
            $push($order_class . ' .dtq-number-title', sprintf('margin-top: %s;', $title_spacing));
        }

        // Absolute number placement.
        if ('absolute' === $number_placement) {
            $push($order_class . ' .dtq-number-wrap', 'z-index: 9!important;');
            $push($order_class . ' .dtq-number-title', 'z-index: 999!important;position: relative;');
            $push(
                $order_class . ' .dtq-number-wrap',
                self::absolute_decl(
                    $val('numberPosition', 'left_top'),
                    $val('numberIsCenterX', 'off'),
                    $val('numberIsCenterY', 'off'),
                    $val('numberOffsetX', '50px'),
                    $val('numberOffsetY', '50px'),
                    '999'
                )
            );
        }

        // ------------------------------------------------------------------
        // Responsive (tablet/phone) output for the D4 mobile-enabled fields:
        // number_height, number_width, title_spacing, number_offset_x/y.
        // Purely additive: desktop declarations are untouched.
        // ------------------------------------------------------------------

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
                return $node['tablet']['value'] !== ($node['desktop']['value'] ?? null);
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

        foreach (['tablet', 'phone'] as $breakpoint) {
            $at_rule = $at_rules[$breakpoint];

            // Number box size.
            if ('on' === $use_box) {
                if ($bp_changed('numberHeight', $breakpoint)) {
                    $push($order_class . ' .dtq-number-wrap', sprintf('height: %s;', $bp_val('numberHeight', $breakpoint, $number_height)), $at_rule);
                }
                if ($bp_changed('numberWidth', $breakpoint)) {
                    $push($order_class . ' .dtq-number-wrap', sprintf('width: %s;', $bp_val('numberWidth', $breakpoint, $number_width)), $at_rule);
                }
            }

            // Title spacing.
            if ('_default' === $number_placement && $bp_changed('titleSpacing', $breakpoint)) {
                $push($order_class . ' .dtq-number-title', sprintf('margin-top: %s;', $bp_val('titleSpacing', $breakpoint, $title_spacing)), $at_rule);
            }

            // Absolute number offsets.
            if ('absolute' === $number_placement
                && ($bp_changed('numberOffsetX', $breakpoint) || $bp_changed('numberOffsetY', $breakpoint))
            ) {
                $push(
                    $order_class . ' .dtq-number-wrap',
                    self::absolute_decl(
                        $val('numberPosition', 'left_top'),
                        $val('numberIsCenterX', 'off'),
                        $val('numberIsCenterY', 'off'),
                        $bp_val('numberOffsetX', $breakpoint, $val('numberOffsetX', '50px')),
                        $bp_val('numberOffsetY', $breakpoint, $val('numberOffsetY', '50px')),
                        '999'
                    ),
                    $at_rule
                );
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

        $use_box = $attrs['module']['advanced']['useBox']['desktop']['value'] ?? 'on';

        $all_styles = [
            // Module wrapper styles (D4 borders.main / box_shadow.main target
            // %%order_class%% directly, so no styleProps redirection needed).
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

            // Number font.
            $elements->style(['attrName' => 'number']),

            // Title font.
            $elements->style(['attrName' => 'title']),
        ];

        // Number box background/border/box-shadow. Gated on use_box like D4:
        // the bg is only applied with the box (get_custom_bg_style inside the
        // use_box branch) and the number border/shadow fields are
        // use_box-scoped in the D4 UI (depends_show_if / show_if).
        if ('on' === $use_box) {
            $all_styles[] = $elements->style(['attrName' => 'numberBox']);
        }

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
