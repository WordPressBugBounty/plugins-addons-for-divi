<?php
/**
 * SkillBar: Module styles trait.
 *
 * @package DiviTorqueLite\Modules\SkillBar
 * @since   4.4.0
 */

namespace DiviTorqueLite\Modules\SkillBar\SkillBarTrait;

if (!defined('ABSPATH')) {
    exit;
}

use ET\Builder\FrontEnd\Module\Style;
use ET\Builder\Packages\Module\Options\Css\CssStyle;

trait ModuleStylesTrait
{
    /**
     * Build the Skill Bar parent declarations, mirroring styles.jsx.
     *
     * Ports the D4 render() get_responsive_styles calls:
     * - nameSpacing        -> margin-left on .dtq-skillbar__name
     * - levelSpacing       -> margin-right on .dtq-skillbar__level
     * - titleSpacingBottom -> margin-bottom on .dtq-skill__title (+responsive)
     * - barSpacingBottom   -> margin-bottom !important on each child (+responsive)
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

        $push($order_class . ' .dtq-skillbar__name', sprintf('margin-left: %1$s;', $val('nameSpacing', '15px')));
        $push($order_class . ' .dtq-skillbar__level', sprintf('margin-right: %1$s;', $val('levelSpacing', '15px')));
        $push($order_class . ' .dtq-skill__title', sprintf('margin-bottom: %1$s;', $val('titleSpacingBottom', '10px')));
        $push($order_class . ' .dtq_skill_bar_item', sprintf('margin-bottom: %1$s !important;', $val('barSpacingBottom', '20px')));

        // Additive responsive output for the two mobile-enabled fields.
        $responsive = [
            ['titleSpacingBottom', $order_class . ' .dtq-skill__title', 'margin-bottom', ''],
            ['barSpacingBottom', $order_class . ' .dtq_skill_bar_item', 'margin-bottom', ' !important'],
        ];
        foreach ($responsive as $r) {
            list($key, $selector, $prop, $suffix) = $r;
            $tablet = $advanced[$key]['tablet']['value'] ?? null;
            $phone  = $advanced[$key]['phone']['value'] ?? null;
            if (null !== $tablet) {
                $push($selector, sprintf('%1$s: %2$s%3$s;', $prop, $tablet, $suffix), $tablet_at);
            }
            if (null !== $phone) {
                $push($selector, sprintf('%1$s: %2$s%3$s;', $prop, $phone, $suffix), $phone_at);
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
            $elements->style(['attrName' => 'name']),
            $elements->style(['attrName' => 'level']),
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
