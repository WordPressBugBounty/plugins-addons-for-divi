<?php
/**
 * News Ticker: module styles trait.
 *
 * @package DiviTorqueLite\Modules\NewsTicker
 * @since   4.6.0
 */

namespace DiviTorqueLite\Modules\NewsTicker\NewsTickerTrait;

if (!defined('ABSPATH')) {
    exit;
}

use ET\Builder\FrontEnd\Module\Style;
use ET\Builder\Packages\Module\Options\Css\CssStyle;

trait ModuleStylesTrait
{
    /**
     * Read a desktop attr value with a fallback.
     *
     * @param array  $attr     The attr array (module.advanced).
     * @param string $key      Sub-key.
     * @param mixed  $fallback Fallback value.
     *
     * @return mixed
     */
    protected static function style_value($attr, $key, $fallback)
    {
        $value = $attr[$key]['desktop']['value'] ?? null;
        return (null === $value || '' === $value) ? $fallback : $value;
    }

    /**
     * Append a CSS unit when the value is a bare number.
     *
     * @param mixed  $value Raw value.
     * @param string $unit  Unit to append.
     *
     * @return string
     */
    protected static function with_unit($value, $unit)
    {
        if ('' === $value || null === $value) {
            return (string) $value;
        }
        $str = trim((string) $value);
        if (preg_match('/[a-z%]$/i', $str) || 0 === strpos($str, 'calc(') || 0 === strpos($str, 'var(')) {
            return $str;
        }
        return $str . $unit;
    }

    /**
     * Convert a spacing value into a CSS shorthand, accepting both the legacy
     * D4 pipe-string and the D5 spacing object.
     *
     * @param mixed $val Spacing value.
     *
     * @return string
     */
    protected static function spacing_shorthand($val)
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
     * Build the custom style array ported from the D4 render_css() calls.
     *
     * @param string $order_class Module order class.
     * @param array  $advanced    The `module.advanced` attrs.
     *
     * @return array
     */
    public static function build_news_ticker_styles($order_class, $advanced)
    {
        $styles = [];
        $add    = function ($selector, $declaration) use (&$styles) {
            if (!empty($declaration)) {
                $styles[] = [
                    'atRules'     => false,
                    'selector'    => $selector,
                    'declaration' => $declaration,
                ];
            }
        };

        $a             = is_array($advanced) ? $advanced : [];
        $speed         = self::with_unit(self::style_value($a, 'speed', '30000ms'), 'ms');
        $slide_dir     = 'right' === self::style_value($a, 'slideDirection', 'left') ? 'reverse' : 'normal';
        $item_spacing  = self::with_unit(self::style_value($a, 'itemSpacing', '20px'), 'px');
        $pause_on_hover = self::style_value($a, 'pauseOnHover', 'on');
        $use_bullet    = self::style_value($a, 'useBullet', 'off');
        $bullet_color  = self::style_value($a, 'bulletColor', '#8a8585');
        $title_pos     = self::style_value($a, 'titlePosition', 'left');
        $title_padding = self::spacing_shorthand(self::style_value($a, 'titlePadding', '20px|20px|20px|20px'));

        $ticker = $order_class . ' .dtq-news-tricker';
        $title  = $order_class . ' .dtq-news-title';

        $add($order_class . ' .dtq-news-wrap', sprintf('animation: %1$s linear 0s infinite %2$s none running news-move;', $speed, $slide_dir));
        $add($order_class . ' .dtq-news-wrap li', sprintf('padding: 0 %1$s;', $item_spacing));

        if ('on' === $pause_on_hover) {
            $add($order_class . ':hover .dtq-news-wrap', '-webkit-animation-play-state: paused!important; animation-play-state: paused!important;');
        }

        if ('on' === $use_bullet) {
            $add($order_class . ' .dtq-news-wrap li a', 'display: inline-block; position: relative;');
            $add(
                $order_class . ' .dtq-news-wrap li a:before',
                sprintf('content: ""; position: absolute; height: 6px; width: 6px; background: %1$s; top: 50%%; left: -15px; transform: translateY(-50%%); border-radius: 50%%;', $bullet_color)
            );
        }

        if ('' !== $title_padding) {
            $add($title, sprintf('padding: %1$s;', $title_padding));
        }

        if ('right' === $title_pos) {
            $add($ticker, 'flex-direction: row-reverse;');
            $add($title, 'margin-left: 10px;');
        } else {
            $add($title, 'margin-right: 10px;');
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
        $custom_styles = self::build_news_ticker_styles($order_class, $advanced);

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
            $elements->style(['attrName' => 'text']),
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
