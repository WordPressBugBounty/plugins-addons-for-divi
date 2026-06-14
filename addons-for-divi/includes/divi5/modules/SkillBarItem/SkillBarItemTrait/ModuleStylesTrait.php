<?php
/**
 * SkillBarItem: Module styles trait.
 *
 * @package DiviTorqueLite\Modules\SkillBarItem
 * @since   4.4.0
 */

namespace DiviTorqueLite\Modules\SkillBarItem\SkillBarItemTrait;

if (!defined('ABSPATH')) {
    exit;
}

use ET\Builder\FrontEnd\Module\Style;
use ET\Builder\Packages\Module\Options\Css\CssStyle;

trait ModuleStylesTrait
{
    /**
     * Build the Skill Bar Item declarations, mirroring styles.jsx.
     *
     * Ports the D4 render_css():
     * - useName             -> justify-content on the inner text
     * - level + barHeight   -> width/height of the fill (.dtq-skillbar__inner)
     * - barRadius           -> border-radius of the track (.dtq-skillbar__wrapper)
     * - nameSpacing         -> margin-left on the name (+responsive)
     * - levelSpacing        -> margin-right on the level (+responsive)
     * - textPlacement       -> in: full size text; out: absolute above the bar
     * - levelBgColor/barBgColor -> fill/track background (+hover)
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

        $val   = function ($key, $fallback) use ($advanced) {
            return $advanced[$key]['desktop']['value'] ?? $fallback;
        };
        $hover = function ($key) use ($advanced) {
            return $advanced[$key]['desktop']['hover'] ?? null;
        };

        $styles = [];
        $push   = function ($selector, $declaration, $at_rules = false) use (&$styles) {
            $styles[] = ['atRules' => $at_rules, 'selector' => $selector, 'declaration' => $declaration];
        };

        $tablet_at = '@media only screen and (max-width: 980px)';
        $phone_at  = '@media only screen and (max-width: 767px)';

        $use_name       = $val('useName', 'on');
        $level          = $val('level', '30%');
        $bar_height     = $val('barHeight', '30px');
        $text_placement = $val('textPlacement', 'in');

        // Justify the inner text (level only -> right edge; name + level -> spread).
        $push(
            $order_class . ' .dtq-skillbar__inner__text',
            'off' === $use_name ? 'justify-content: flex-end;' : 'justify-content: space-between;'
        );

        // Bar fill: width is the skill level, height is the bar height.
        $push($order_class . ' .dtq-skillbar__inner', sprintf('width: %1$s; height: %2$s;', $level, $bar_height));
        $bar_height_tablet = $advanced['barHeight']['tablet']['value'] ?? null;
        $bar_height_phone  = $advanced['barHeight']['phone']['value'] ?? null;
        if (null !== $bar_height_tablet) {
            $push($order_class . ' .dtq-skillbar__inner', sprintf('height: %1$s;', $bar_height_tablet), $tablet_at);
        }
        if (null !== $bar_height_phone) {
            $push($order_class . ' .dtq-skillbar__inner', sprintf('height: %1$s;', $bar_height_phone), $phone_at);
        }

        // Track radius.
        $push($order_class . ' .dtq-skillbar__wrapper', sprintf('border-radius: %1$s;', $val('barRadius', '40px')));

        // Name / level edge spacing (responsive, only when set — mirrors D4).
        $spacings = [
            ['nameSpacing', '.dtq-skill ' . $order_class . ' .dtq-skillbar__name', 'margin-left'],
            ['levelSpacing', '.dtq-skill ' . $order_class . ' .dtq-skillbar__level', 'margin-right'],
        ];
        foreach ($spacings as $s) {
            list($key, $selector, $prop) = $s;
            $desktop = $advanced[$key]['desktop']['value'] ?? null;
            if (null === $desktop || '' === $desktop) {
                continue;
            }
            $push($selector, sprintf('%1$s: %2$s;', $prop, $desktop));
            $tablet = $advanced[$key]['tablet']['value'] ?? null;
            $phone  = $advanced[$key]['phone']['value'] ?? null;
            if (null !== $tablet) {
                $push($selector, sprintf('%1$s: %2$s;', $prop, $tablet), $tablet_at);
            }
            if (null !== $phone) {
                $push($selector, sprintf('%1$s: %2$s;', $prop, $phone), $phone_at);
            }
        }

        // Text placement.
        if ('out' === $text_placement) {
            $push(
                $order_class . ' .dtq-skillbar__inner__text',
                sprintf(
                    'position: absolute; top: -%1$s; width: %2$s; height: auto; transform: translateY(-100%%);',
                    $val('textSpacing', '12px'),
                    $level
                )
            );
        } elseif ('in' === $text_placement) {
            $push($order_class . ' .dtq-skillbar__inner__text', 'width: 100%; height: 100%;');
        }

        // Fill / track background colors (+hover).
        $push($order_class . ' .dtq-skillbar__inner', sprintf('background-color: %1$s;', $val('levelBgColor', '#0e40ff')));
        if ($hover('levelBgColor')) {
            $push($order_class . ':hover .dtq-skillbar__inner', sprintf('background-color: %1$s;', $hover('levelBgColor')));
        }
        $push($order_class . ' .dtq-skillbar__wrapper', sprintf('background-color: %1$s;', $val('barBgColor', '#b2bad1')));
        if ($hover('barBgColor')) {
            $push($order_class . ':hover .dtq-skillbar__wrapper', sprintf('background-color: %1$s;', $hover('barBgColor')));
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
            $elements->style(['attrName' => 'name']),
            $elements->style(['attrName' => 'level']),
            $elements->style(['attrName' => 'bar']),
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
