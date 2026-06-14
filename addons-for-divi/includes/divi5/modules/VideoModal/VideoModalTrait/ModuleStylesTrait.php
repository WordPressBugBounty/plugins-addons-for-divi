<?php
/**
 * Video Modal: Module styles trait.
 *
 * @package DiviTorqueLite\Modules\VideoModal
 * @since   4.4.0
 */

namespace DiviTorqueLite\Modules\VideoModal\VideoModalTrait;

if (!defined('ABSPATH')) {
    exit;
}

use ET\Builder\FrontEnd\Module\Style;
use ET\Builder\Packages\Module\Options\Css\CssStyle;

trait ModuleStylesTrait
{
    /**
     * Build the flat custom-style declarations that mirror the D4
     * `apply_css()` + popup `render()` `ET_Builder_Element::set_style()`
     * calls. Keep this in lockstep with the JS twin in
     * src/divi5/modules/video-modal/styles.jsx.
     *
     * Note on the popup rules: the magnific popup DOM is appended to <body>,
     * outside the module wrapper, so the popup background / close icon color
     * are emitted as explicit `body.dtq-video-popup-{order}`-prefixed
     * selectors (the front-end init toggles that body class per module via
     * the trigger's data-order).
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

        $hover = function ($key) use ($advanced) {
            return $advanced[$key]['desktop']['hover'] ?? '';
        };

        $trigger_element  = $val('triggerElement', 'icon');
        $use_overlay      = $val('useOverlay', 'on');
        $use_text_box     = $val('useTextBox', 'off');
        $use_animation    = $val('useAnimation', 'off');
        $wave_bg          = $val('waveBg', '#ffffff');
        $icon_color       = $val('iconColor', '#5b2cff');
        $icon_opacity     = $val('iconOpacity', '1');
        $icon_bg          = $val('iconBg', '');
        $icon_radius      = $val('iconRadius', '0px');
        $popup_bg         = $val('popupBg', 'rgba(0,0,0,.8)');
        $close_icon_color = $val('closeIconColor', '#ffffff');
        $text_box_bg      = $val('textBoxBg', '#5b2cff');
        $text_box_radius  = $val('textBoxRadius', '0px');

        // Per-module order suffix — must match the data-order the markup
        // carries. The order class is `.dtq_video_modal_{orderIndex}`, and
        // RenderCallbackTrait emits the same orderIndex as data-order.
        $order_number = substr(strrchr($order_class, '_'), 1);

        $styles = [];

        $push = function ($selector, $declaration, $at_rule = false) use (&$styles) {
            $styles[] = [
                'atRules'     => $at_rule,
                'selector'    => $selector,
                'declaration' => $declaration,
            ];
        };

        // Responsive helpers (same semantics as the review/compare-image
        // ports): emit a breakpoint rule only when the value exists and
        // differs from the next-larger breakpoint.
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

        $responsive = function ($key, $selector, $property, $fallback) use ($push, $val, $bp_val, $bp_changed, $at_rules) {
            $push($selector, sprintf('%1$s: %2$s;', $property, $val($key, $fallback)));
            foreach (['tablet', 'phone'] as $breakpoint) {
                if ($bp_changed($key, $breakpoint)) {
                    $push(
                        $selector,
                        sprintf('%1$s: %2$s;', $property, $bp_val($key, $breakpoint, $fallback)),
                        $at_rules[$breakpoint]
                    );
                }
            }
        };

        // Popup styles (mirrors the two set_style() calls in D4 render()).
        // The popup DOM lives in <body>; the front-end magnific callbacks add
        // `dtq-video-popup-{order}` on body while a yt/vm popup is open.
        $push(
            "body.dtq-video-popup-{$order_number} .mfp-bg",
            sprintf('opacity:1!important;background: %1$s!important;', $popup_bg)
        );
        $push(
            "body.dtq-video-popup-{$order_number} .mfp-iframe-holder .mfp-close",
            sprintf('color: %1$s!important;', $close_icon_color)
        );

        // Icon/Text alignment (mirrors D4 get_responsive_styles icon_alignment).
        $responsive('iconAlignment', $order_class . ' .dtq-video-popup-icon', 'justify-content', 'center');

        if ('text' !== $trigger_element) {
            $push($order_class . ' .dtq-video-popup svg', sprintf('fill: %1$s;', $icon_color));
            if ('' !== $hover('iconColor')) {
                $push($order_class . ':hover .dtq-video-popup svg', sprintf('fill: %1$s;', $hover('iconColor')));
            }

            $responsive('iconHeight', $order_class . ' .dtq-video-popup .dtq-video-popup-icon', 'height', 'initial');
            $responsive('iconWidth', $order_class . ' .dtq-video-popup .dtq-video-popup-icon', 'width', 'initial');

            $push($order_class . ' .dtq-video-popup .dtq-video-popup-icon', sprintf('border-radius:%1$s;', $icon_radius));

            $responsive('iconSize', $order_class . ' .dtq-video-popup-icon svg', 'width', '60px');
            if ('' !== $hover('iconSize')) {
                $push($order_class . ':hover .dtq-video-popup-icon svg', sprintf('width:%1$s;', $hover('iconSize')));
            }

            $push($order_class . ' .dtq-video-popup-icon svg', sprintf('opacity:%1$s;', $icon_opacity));
            if ('' !== $hover('iconOpacity')) {
                $push($order_class . ':hover .dtq-video-popup-icon svg', sprintf('opacity:%1$s;', $hover('iconOpacity')));
            }

            if (!empty($icon_bg)) {
                $push($order_class . ' .dtq-video-popup-icon', sprintf('background: %1$s;', $icon_bg));
            }
            if ('' !== $hover('iconBg')) {
                $push($order_class . ':hover .dtq-video-popup-icon', sprintf('background: %1$s;', $hover('iconBg')));
            }
        }

        if ('icon' !== $trigger_element && 'on' === $use_text_box) {
            $push($order_class . ' .dtq-video-popup .dtq-video-popup-text', sprintf('border-radius:%1$s;', $text_box_radius));
            $responsive('textBoxHeight', $order_class . ' .dtq-video-popup .dtq-video-popup-text', 'height', '80px');
            $responsive('textBoxWidth', $order_class . ' .dtq-video-popup .dtq-video-popup-text', 'width', '80px');
            if (!empty($text_box_bg)) {
                $push($order_class . ' .dtq-video-popup .dtq-video-popup-text', sprintf('background: %1$s;', $text_box_bg));
            }
            if ('' !== $hover('textBoxBg')) {
                $push($order_class . ':hover .dtq-video-popup .dtq-video-popup-text', sprintf('background: %1$s;', $hover('textBoxBg')));
            }
        }

        if ('icon_text' === $trigger_element) {
            $responsive('iconSpacing', $order_class . ' .dtq-video-popup-icon', 'margin-right', '20px');
        }

        if ('on' === $use_overlay) {
            $responsive('imgHeight', $order_class . ' .dtq-video-popup-figure', 'height', 'auto');
            // D4 emits this absolute-positioning rule both in the
            // (trigger != icon && use_overlay) and the (use_overlay)
            // branches — the second subsumes the first, so it is emitted
            // once here.
            $push($order_class . ' .dtq-video-popup-trigger', 'justify-content: center; position: absolute; left: 0; top: 0;');
        }

        // Wave animation (mirrors the D4 use_animation branch).
        if ('on' === $use_animation) {
            $wave_selector = $order_class . ' .dtq-video-popup a:after';
            if ('icon_text' === $trigger_element) {
                $wave_selector = $order_class . ' .dtq-video-popup .dtq-video-popup-icon:after';
            }

            if ('icon' !== $trigger_element) {
                $push($wave_selector, sprintf('border-radius: %1$s;', $text_box_radius));
            }
            if ('text' !== $trigger_element) {
                $push($wave_selector, sprintf('border-radius: %1$s;', $icon_radius));
            }

            $push(
                $wave_selector,
                sprintf(
                    'content: ""; -webkit-box-shadow: 0 0 0 15px %1$s, 0 0 0 30px %1$s, 0 0 0 45px %1$s; box-shadow: 0 0 0 15px %1$s, 0 0 0 30px %1$s, 0 0 0 45px %1$s;',
                    $wave_bg
                )
            );
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

            // Image overlay background (D4 custom image_bg fields).
            $elements->style(['attrName' => 'overlay']),

            // Trigger text font.
            $elements->style(['attrName' => 'text']),
        ];

        // Flat custom declarations ported from D4 apply_css() + popup styles.
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
