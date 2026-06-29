<?php
/**
 * Accordion: Module styles trait.
 *
 * @package DiviTorqueLite\Modules\Accordion
 * @since   4.7.0
 */

namespace DiviTorqueLite\Modules\Accordion\AccordionTrait;

if (!defined('ABSPATH')) {
    exit;
}

use ET\Builder\FrontEnd\Module\Style;
use ET\Builder\Packages\Module\Options\Css\CssStyle;

trait ModuleStylesTrait
{
    /**
     * Build the Accordion parent custom declarations, mirroring styles.jsx.
     *
     * - itemSpacing -> margin-bottom on each `.dtq-accordion__item` (+responsive).
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

        $selector = $order_class . ' .dtq-accordion__item';
        $push($selector, sprintf('margin-bottom: %1$s;', $val('itemSpacing', '10px')));

        $tablet = $advanced['itemSpacing']['tablet']['value'] ?? null;
        $phone  = $advanced['itemSpacing']['phone']['value'] ?? null;
        if (null !== $tablet) {
            $push($selector, sprintf('margin-bottom: %1$s;', $tablet), $tablet_at);
        }
        if (null !== $phone) {
            $push($selector, sprintf('margin-bottom: %1$s;', $phone), $phone_at);
        }

        // Icon color + size are CSS variables (the icon CSS falls back to its
        // own per-type defaults when unset).
        $icon_color = $advanced['iconColor']['desktop']['value'] ?? null;
        if (null !== $icon_color && '' !== $icon_color) {
            $push($order_class, sprintf('--dtq-acc-icon-color: %1$s;', $icon_color));
        }
        $icon_size = $advanced['iconSize']['desktop']['value'] ?? null;
        if (null !== $icon_size && '' !== $icon_size) {
            $push($order_class, sprintf('--dtq-acc-icon-size: %1$s;', $icon_size));
        }

        // Header / active colors emitted as targeted rules only when set, so an
        // unset value never overrides the title font color or a preset default.
        $title_bg = $advanced['titleBg']['desktop']['value'] ?? null;
        if (null !== $title_bg && '' !== $title_bg) {
            $push($order_class . ' .dtq-accordion__title', sprintf('background-color: %1$s;', $title_bg));
        }
        $active_bg = $advanced['activeBg']['desktop']['value'] ?? null;
        if (null !== $active_bg && '' !== $active_bg) {
            $push($order_class . ' .dtq-accordion__item--open > .dtq-accordion__title', sprintf('background-color: %1$s;', $active_bg));
        }
        $active_color = $advanced['activeColor']['desktop']['value'] ?? null;
        if (null !== $active_color && '' !== $active_color) {
            $push($order_class . ' .dtq-accordion__item--open > .dtq-accordion__title', sprintf('color: %1$s;', $active_color));
        }

        // Phase 3 variables (consumed by module.scss).
        $icon_bg = $advanced['iconBgColor']['desktop']['value'] ?? null;
        if (null !== $icon_bg && '' !== $icon_bg) {
            $push($order_class, sprintf('--dtq-acc-icon-bg: %1$s;', $icon_bg));
        }
        $fixed_h = $advanced['fixedHeight']['desktop']['value'] ?? null;
        if (null !== $fixed_h && '' !== $fixed_h) {
            $push($order_class, sprintf('--dtq-acc-fixed-h: %1$s;', $fixed_h));
        }
        $anim_dur = $advanced['animDuration']['desktop']['value'] ?? null;
        if (null !== $anim_dur && '' !== $anim_dur) {
            $push($order_class, sprintf('--dtq-acc-anim-dur: %1$s;', $anim_dur));
        }

        // Custom toggle icons → CSS `content` escape + font (also loads the font).
        if (($advanced['iconSource']['desktop']['value'] ?? 'preset') === 'custom') {
            $closed = self::icon_to_css($advanced['closedIcon']['desktop']['value'] ?? '');
            $open   = self::icon_to_css($advanced['openIcon']['desktop']['value'] ?? '');
            if ('' !== $closed['css']) {
                $push($order_class, sprintf('--dtq-acc-icon-closed: "%1$s";', $closed['css']));
                $push($order_class, sprintf("--dtq-acc-icon-font: '%1\$s';", $closed['font']));
            }
            if ('' !== $open['css']) {
                $push($order_class, sprintf('--dtq-acc-icon-open: "%1$s";', $open['css']));
            }
        }

        return $styles;
    }

    /**
     * Convert a Divi icon-picker value into a CSS `content` escape + font name,
     * and ensure the icon font is loaded on the page.
     *
     * @param mixed $icon_value Icon attr value (object or "uni||type||weight").
     *
     * @return array { css: string, font: string }
     */
    public static function icon_to_css($icon_value)
    {
        if (is_array($icon_value)) {
            $uni  = $icon_value['unicode'] ?? '';
            $type = $icon_value['type'] ?? 'divi';
            $wt   = $icon_value['weight'] ?? '400';
        } else {
            $parts = explode('||', (string) $icon_value);
            $uni   = $parts[0] ?? '';
            $type  = $parts[1] ?? 'divi';
            $wt    = $parts[2] ?? '400';
        }

        $font = 'fa' === $type ? 'FontAwesome' : 'ETmodules';
        if ('' === $uni) {
            return ['css' => '', 'font' => $font];
        }

        if (function_exists('dtq_inject_fa_icons')) {
            dtq_inject_fa_icons($uni . '||' . $type . '||' . $wt);
        }
        $resolved = function_exists('dtq_resolve_icon_unicode') ? dtq_resolve_icon_unicode($uni) : $uni;

        $css = '';
        if (preg_match('/&#x([0-9a-fA-F]+);/', $resolved, $m)) {
            $css = '\\' . strtolower($m[1]);
        } elseif (preg_match('/&#(\d+);/', $resolved, $m)) {
            $css = '\\' . dechex((int) $m[1]);
        }

        return ['css' => $css, 'font' => $font];
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
            $elements->style(['attrName' => 'subtitle']),
            $elements->style(['attrName' => 'content']),
            $elements->style(['attrName' => 'item']),
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
