<?php
/**
 * LogoGrid: Module styles trait.
 *
 * @package DiviTorqueLite\Modules\LogoGrid
 * @since   4.4.0
 */

namespace DiviTorqueLite\Modules\LogoGrid\LogoGridTrait;

if (!defined('ABSPATH')) {
    exit;
}

use ET\Builder\FrontEnd\Module\Style;
use ET\Builder\Packages\Module\Options\Css\CssStyle;

trait ModuleStylesTrait
{
    /**
     * Build the Logo Grid parent declarations, mirroring styles.jsx.
     *
     * Ports the D4 render_css():
     * - logoOverflow -> overflow on .dtq-logo-grid__item
     * - gridHeight   -> height on each child wrapper (+responsive)
     * - logoSize     -> width on the logo img (+responsive), 100% when unset
     * - gridGap      -> negative outer margin on .dtq-logo-grid (+responsive)
     * - columnCount  -> flex basis + gap padding on each child wrapper per breakpoint
     *
     * D4 emitted the logoSize rules without the order class (a global-selector
     * bug); D5 scopes them to the module. D4 also emitted tablet/phone column
     * rules unconditionally with possibly-empty values (invalid CSS that
     * browsers dropped); D5 emits them only when a tablet/phone value exists,
     * falling back to the nearest larger breakpoint.
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
        $bp = function ($key, $device) use ($advanced) {
            return $advanced[$key][$device]['value'] ?? null;
        };

        $styles = [];
        $push   = function ($selector, $declaration, $at_rules = false) use (&$styles) {
            $styles[] = ['atRules' => $at_rules, 'selector' => $selector, 'declaration' => $declaration];
        };

        $tablet_at = '@media only screen and (max-width: 980px)';
        $phone_at  = '@media only screen and (max-width: 767px)';

        $column_count = $val('columnCount', '4');
        $grid_gap     = $val('gridGap', '5px');
        $grid_height  = $val('gridHeight', '');
        $logo_size    = $val('logoSize', '');

        $push($order_class . ' .dtq-logo-grid__item', sprintf('overflow: %1$s;', $val('logoOverflow', 'visible')));

        // Column height (only when set — mirrors D4).
        if (!empty($grid_height)) {
            $push($order_class . ' .dtq_logo_grid_item', sprintf('height: %1$s;', $grid_height));
            if (null !== $bp('gridHeight', 'tablet')) {
                $push($order_class . ' .dtq_logo_grid_item', sprintf('height: %1$s;', $bp('gridHeight', 'tablet')), $tablet_at);
            }
            if (null !== $bp('gridHeight', 'phone')) {
                $push($order_class . ' .dtq_logo_grid_item', sprintf('height: %1$s;', $bp('gridHeight', 'phone')), $phone_at);
            }
        }

        // Static logo width, or fluid when unset.
        if (!empty($logo_size)) {
            $push($order_class . ' .dtq-logo-grid__item img', sprintf('width: %1$s;', $logo_size));
            if (null !== $bp('logoSize', 'tablet')) {
                $push($order_class . ' .dtq-logo-grid__item img', sprintf('width: %1$s;', $bp('logoSize', 'tablet')), $tablet_at);
            }
            if (null !== $bp('logoSize', 'phone')) {
                $push($order_class . ' .dtq-logo-grid__item img', sprintf('width: %1$s;', $bp('logoSize', 'phone')), $phone_at);
            }
        } else {
            $push($order_class . ' .dtq-logo-grid__item img', 'width: 100%;');
        }

        // Grid gap (negative outer margin compensates the per-item padding).
        $push($order_class . ' .dtq-logo-grid', sprintf('margin: -%1$s;', $grid_gap));
        if (null !== $bp('gridGap', 'tablet')) {
            $push($order_class . ' .dtq-logo-grid', sprintf('margin: -%1$s;', $bp('gridGap', 'tablet')), $tablet_at);
        }
        if (null !== $bp('gridGap', 'phone')) {
            $push($order_class . ' .dtq-logo-grid', sprintf('margin: -%1$s;', $bp('gridGap', 'phone')), $phone_at);
        }

        // Columns + per-item gap padding, per breakpoint.
        $push($order_class . ' .dtq_logo_grid_item', sprintf('flex: 0 0 calc(100%%/%1$s);padding:%2$s;', $column_count, $grid_gap));
        $cols_tablet = $bp('columnCount', 'tablet');
        $gap_tablet  = $bp('gridGap', 'tablet');
        if (null !== $cols_tablet || null !== $gap_tablet) {
            $push(
                $order_class . ' .dtq_logo_grid_item',
                sprintf('flex: 0 0 calc(100%%/%1$s);padding:%2$s;', $cols_tablet ?? $column_count, $gap_tablet ?? $grid_gap),
                $tablet_at
            );
        }
        $cols_phone = $bp('columnCount', 'phone');
        $gap_phone  = $bp('gridGap', 'phone');
        if (null !== $cols_phone || null !== $gap_phone) {
            $push(
                $order_class . ' .dtq_logo_grid_item',
                sprintf('flex: 0 0 calc(100%%/%1$s);padding:%2$s;', $cols_phone ?? $cols_tablet ?? $column_count, $gap_phone ?? $gap_tablet ?? $grid_gap),
                $phone_at
            );
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
