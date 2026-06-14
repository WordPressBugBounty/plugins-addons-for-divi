<?php
/**
 * Shared, extensible Swiper carousel engine (PHP side).
 *
 * Static helper used by every carousel module's RenderCallbackTrait /
 * ModuleStylesTrait so the Swiper config, arrow rendering, nav/pagination CSS,
 * and wrapper classes live in ONE place. Mirrors the JS shared layer
 * (src/divi5/shared/carousel/*). Autoloads via the existing PSR-4 rule
 * `DiviTorqueLite\Modules\ => includes/divi5/modules/`; it is NOT a module and
 * must not be registered in the dependency tree.
 *
 * Static class (not a trait) on purpose: modules already `use` several traits;
 * a shared trait sharing method names would cause PHP trait-collision fatals.
 *
 * @package DiviTorqueLite\Modules\SharedCarousel
 * @since   4.5.0
 */

namespace DiviTorqueLite\Modules\SharedCarousel;

if (!defined('ABSPATH')) {
    exit;
}

class CarouselEngine
{
    /**
     * Parse the leading integer out of a value like "3" or "700ms".
     *
     * @param mixed $val      Raw value.
     * @param int   $fallback Fallback integer.
     *
     * @return int
     */
    public static function to_int($val, $fallback)
    {
        if (null === $val || '' === $val) {
            return $fallback;
        }
        $n = (int) $val;
        return ($n > 0 || '0' === (string) $val) ? $n : $fallback;
    }

    /**
     * Resolve a Divi icon value to its glyph char + font.
     *
     * @param mixed  $icon     Icon value (object or "unicode||type||weight").
     * @param string $fallback Fallback glyph char.
     *
     * @return array ['char' => string, 'font' => string, 'weight' => string]
     */
    public static function resolve_arrow($icon, $fallback)
    {
        $unicode = '';
        $type    = 'divi';
        $weight  = '400';
        if (is_array($icon)) {
            $unicode = $icon['unicode'] ?? '';
            $type    = $icon['type'] ?? 'divi';
            $weight  = $icon['weight'] ?? '400';
        } elseif (is_string($icon) && '' !== $icon) {
            $parts   = explode('||', $icon);
            $unicode = $parts[0] ?? '';
            $type    = $parts[1] ?? 'divi';
            $weight  = $parts[2] ?? '400';
        }
        $char = '' !== $unicode ? html_entity_decode($unicode, ENT_QUOTES, 'UTF-8') : $fallback;
        return [
            'char'   => $char,
            'font'   => 'fa' === $type ? 'FontAwesome' : 'ETmodules',
            'weight' => $weight ?: '400',
        ];
    }

    /**
     * Build the Swiper config array, mirroring the JS `buildSwiperConfig()`.
     *
     * @param array $advanced The `module.advanced` attrs array.
     *
     * @return array ['config' => array, 'show_nav' => bool, 'show_pagi' => bool]
     */
    public static function build_swiper_config($advanced)
    {
        $val    = function ($key, $fallback) use ($advanced) {
            return $advanced[$key]['desktop']['value'] ?? $fallback;
        };
        $bp_int = function ($key, $bp, $fallback) use ($advanced) {
            $raw = $advanced[$key][$bp]['value'] ?? null;
            return (null === $raw || '' === $raw) ? $fallback : self::to_int($raw, $fallback);
        };

        $slide_count   = self::to_int($val('slideCount', '3'), 3);
        $tablet_count  = $bp_int('slideCount', 'tablet', $slide_count);
        $phone_count   = $bp_int('slideCount', 'phone', $tablet_count);
        $slide_scroll  = self::to_int($val('slideToScroll', '1'), 1);
        $space         = self::to_int($val('slideSpacing', '10px'), 10);
        $is_infinite   = 'on' === $val('isInfinite', 'on');
        $is_swipe      = 'on' === $val('isSwipe', 'on');
        $is_center     = 'on' === $val('isCenter', 'off');
        $is_vertical   = 'on' === $val('isVertical', 'off');
        $is_variable   = 'on' === $val('isVariableWidth', 'off');
        $is_autoplay   = 'on' === $val('isAutoplay', 'on');
        $show_nav      = 'off' !== $val('useNav', 'on');
        $show_pagi     = 'on' === $val('usePagi', 'off');

        $config = [
            'speed'          => self::to_int($val('animationSpeed', '700ms'), 700),
            'loop'           => $is_infinite,
            'grabCursor'     => true,
            'simulateTouch'  => $is_swipe,
            'allowTouchMove' => $is_swipe,
            'observer'       => true,
            'observeParents' => true,
            'spaceBetween'   => $space,
        ];

        if ($is_vertical) {
            $config['direction']     = 'vertical';
            $config['slidesPerView'] = 1;
        } elseif ($is_variable) {
            $config['slidesPerView'] = 'auto';
        } else {
            $config['slidesPerView'] = $phone_count;
            $config['breakpoints']   = [
                768 => ['slidesPerView' => $tablet_count, 'spaceBetween' => $space],
                981 => ['slidesPerView' => $slide_count, 'spaceBetween' => $space],
            ];
        }

        if ($slide_scroll > 1 && !$is_vertical && !$is_variable) {
            $config['slidesPerGroup'] = $slide_scroll;
        }

        if ($is_center && !$is_vertical) {
            $config['centeredSlides'] = true;
            $center_mode_type = $val('centerModeType', 'classic');
            $center_padding   = self::to_int($val('centerPadding', '0px'), 0);
            if ('classic' === $center_mode_type && !$is_variable && $center_padding > 0) {
                $config['slidesOffsetBefore'] = $center_padding;
                $config['slidesOffsetAfter']  = $center_padding;
            }
        }

        if ($show_nav) {
            $config['navigation'] = ['nextEl' => '.swiper-button-next', 'prevEl' => '.swiper-button-prev'];
        }

        if ($show_pagi) {
            $config['pagination'] = ['el' => '.swiper-pagination', 'clickable' => true];
            if ('number' === $val('pagiType', 'dot')) {
                $config['dtqPagiType'] = 'number';
            }
        }

        if ($is_autoplay) {
            $config['autoplay'] = [
                'delay'                => self::to_int($val('autoplaySpeed', '2000ms'), 2000),
                'disableOnInteraction' => false,
            ];
        }

        return ['config' => $config, 'show_nav' => $show_nav, 'show_pagi' => $show_pagi];
    }

    /**
     * Render an arrow button with its icon glyph.
     *
     * @param array  $advanced The module advanced attrs.
     * @param string $which    'prev' or 'next'.
     *
     * @return string
     */
    public static function render_arrow($advanced, $which)
    {
        $is_prev = 'prev' === $which;
        $icon    = $advanced[$is_prev ? 'iconLeft' : 'iconRight']['desktop']['value'] ?? '';
        $glyph   = self::resolve_arrow($icon, $is_prev ? '4' : '5');

        if (function_exists('dtq_inject_fa_icons') && !empty($icon)) {
            $uni = is_array($icon) ? ($icon['unicode'] ?? '') : explode('||', (string) $icon)[0];
            dtq_inject_fa_icons($uni . '||' . $glyph['font'] . '||' . $glyph['weight']);
        }

        return sprintf(
            '<div class="swiper-button-%1$s"><i class="dtq-arrow-glyph" style="font-family:\'%2$s\';font-weight:%3$s">%4$s</i></div>',
            esc_attr($which),
            esc_attr($glyph['font']),
            esc_attr($glyph['weight']),
            esc_html($glyph['char'])
        );
    }

    /**
     * Base wrapper classes for a carousel module. Modules append their own
     * (e.g. logo carousel appends its logoHover class).
     *
     * @param array  $advanced   The module advanced attrs.
     * @param string $type_class The per-type class (e.g. 'dtq-image-carousel').
     *
     * @return array
     */
    public static function base_wrapper_classes($advanced, $type_class)
    {
        $classes = ['dtq-swiper-carousel', $type_class, 'dtq-lightbox-off'];
        if ('on' === ($advanced['isCenter']['desktop']['value'] ?? 'off')) {
            $classes[] = 'dtq-centered';
            $classes[] = 'dtq-centered--' . ($advanced['centerModeType']['desktop']['value'] ?? 'classic');
        }
        if ('on' === ($advanced['isVertical']['desktop']['value'] ?? 'off')) {
            $classes[] = 'dtq-vertical';
        }
        return $classes;
    }

    /**
     * Build navigation / pagination styles for the Swiper carousel, mirroring
     * the JS `buildCarouselStyles()`. Slide spacing/sizing/layout are handled by
     * Swiper itself.
     *
     * @param string $order_class The module order class selector.
     * @param array  $advanced    The `module.advanced` attrs array.
     *
     * @return array
     */
    public static function build_carousel_styles($order_class, $advanced)
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
        $push   = function ($selector, $declaration) use (&$styles) {
            $styles[] = ['atRules' => false, 'selector' => $selector, 'declaration' => $declaration];
        };

        $dtq = $order_class . ' .dtq-swiper-carousel';

        // Navigation arrows.
        $nav_color        = $val('navColor', '#333333');
        $nav_bg           = $val('navBg', '#dddddd');
        $nav_height       = $val('navHeight', '40px');
        $nav_width        = $val('navWidth', '40px');
        $nav_icon_size    = $val('navIconSize', '30px');
        $nav_radius       = $val('navRadius', '40px');
        $nav_border_width = $val('navBorderWidth', '0px');
        $nav_border_style = $val('navBorderStyle', 'solid');
        $nav_border_color = $val('navBorderColor', '#333333');
        $nav_skew         = $val('navSkew', '0deg');
        $nav_pos_x        = $val('navPosX', '-15px');
        $nav_pos_y        = $val('navPosY', '50%');
        $nav_height_int   = (int) $nav_height;
        $skew_int         = (int) $nav_skew;
        $skew_inner       = $skew_int < 0 ? abs($skew_int) : -abs($skew_int);

        $push(
            $dtq . ' > .swiper-button-prev, ' . $dtq . ' > .swiper-button-next',
            sprintf(
                'height: %1$s; width: %2$s; background: %3$s; color: %4$s; border: %5$s %6$s %7$s; border-radius: %8$s; transform: skew(%9$s); top: %10$s; margin-top: -%11$spx;',
                $nav_height,
                $nav_width,
                $nav_bg,
                $nav_color,
                $nav_border_width,
                $nav_border_style,
                $nav_border_color,
                $nav_radius,
                $nav_skew,
                $nav_pos_y,
                $nav_height_int / 2
            )
        );
        $push($dtq . ' > .swiper-button-prev::after, ' . $dtq . ' > .swiper-button-next::after', 'display: none;');
        $push(
            $dtq . ' > .swiper-button-prev .dtq-arrow-glyph, ' . $dtq . ' > .swiper-button-next .dtq-arrow-glyph',
            sprintf('font-size: %1$s; line-height: 1; transform: skew(%2$sdeg); display: inline-block;', $nav_icon_size, $skew_inner)
        );
        $push($dtq . ' > .swiper-button-prev', sprintf('left: %1$s; right: auto;', $nav_pos_x));
        $push($dtq . ' > .swiper-button-next', sprintf('right: %1$s; left: auto;', $nav_pos_x));
        if ($hover('navColor')) $push($dtq . ' > .swiper-button-prev:hover, ' . $dtq . ' > .swiper-button-next:hover', sprintf('color: %1$s;', $hover('navColor')));
        if ($hover('navBg')) $push($dtq . ' > .swiper-button-prev:hover, ' . $dtq . ' > .swiper-button-next:hover', sprintf('background: %1$s;', $hover('navBg')));
        if ($hover('navBorderColor')) $push($dtq . ' > .swiper-button-prev:hover, ' . $dtq . ' > .swiper-button-next:hover', sprintf('border-color: %1$s;', $hover('navBorderColor')));

        // Pagination dots.
        $pagi_bg        = $val('pagiBg', '#dddddd');
        $pagi_bg_active = $val('pagiBgActive', '#333333');
        $pagi_height    = $val('pagiHeight', '10px');
        $pagi_width     = $val('pagiWidth', '10px');
        $pagi_radius    = $val('pagiRadius', '10px');
        $pagi_spacing   = $val('pagiSpacing', '10px');
        $pagi_pos_y     = $val('pagiPosY', '10px');
        $pagi_alignment = $val('pagiAlignment', 'center');
        $justify        = 'left' === $pagi_alignment ? 'flex-start' : ('right' === $pagi_alignment ? 'flex-end' : 'center');

        $push(
            $dtq . ' > .swiper-pagination',
            sprintf('position: relative; display: flex; justify-content: %1$s; align-items: center; gap: %2$s; margin-top: %3$s; width: 100%%; bottom: auto; left: auto; transform: none;', $justify, $pagi_spacing, $pagi_pos_y)
        );
        $push(
            $dtq . ' .swiper-pagination-bullet',
            sprintf('width: %1$s; height: %2$s; background: %3$s; border-radius: %4$s; opacity: 1; margin: 0;', $pagi_width, $pagi_height, $pagi_bg, $pagi_radius)
        );
        $active_decl = sprintf('background: %1$s;', $pagi_bg_active);
        if ($val('pagiWidthActive', '')) {
            $active_decl .= sprintf(' width: %1$s;', $val('pagiWidthActive', ''));
        }
        $push($dtq . ' .swiper-pagination-bullet-active', $active_decl);

        $bp_raw  = function ($key, $bp) use ($advanced) {
            return $advanced[$key][$bp]['value'] ?? null;
        };
        $push_at = function ($at_rule, $selector, $declaration) use (&$styles) {
            $styles[] = ['atRules' => $at_rule, 'selector' => $selector, 'declaration' => $declaration];
        };
        $tablet = '@media only screen and (max-width: 980px)';
        $phone  = '@media only screen and (max-width: 767px)';

        // Number pagination.
        if ('number' === $val('pagiType', 'dot')) {
            $pagi_color        = $val('pagiColor', '#333333');
            $pagi_color_hover  = $advanced['pagiColor']['desktop']['hover'] ?? null;
            $pagi_text         = $val('pagiText', '16px');
            $pagi_text_active  = $val('pagiTextActive', $pagi_bg_active);
            $push($dtq . ' .swiper-pagination-bullet', sprintf('background: transparent; width: auto; height: auto; border-radius: 0; font-size: %1$s; line-height: 1; color: %2$s;', $pagi_text, $pagi_color));
            $push($dtq . ' .swiper-pagination-bullet-active', sprintf('background: transparent; color: %1$s;', $pagi_text_active));
            if ($pagi_color_hover) $push($dtq . ' .swiper-pagination-bullet:hover', sprintf('color: %1$s;', $pagi_color_hover));
        }

        // Alongside navigation (CSS-positioned).
        if ('alongside' === $val('navType', 'overlay')) {
            $nav_pos    = $val('navPos', 'bottom');
            $nav_pos_hz = $val('navPosHz', 'right');
            $nav_x_ctr  = 'on' === $val('navXCenter', 'off');
            $nav_w      = (int) $nav_width;
            $nav_gap    = $val('navGap', '10px');
            $nav_gap_i  = (int) $nav_gap;
            $push($dtq . ' > .swiper-button-prev, ' . $dtq . ' > .swiper-button-next', sprintf('top: auto; margin-top: 0; %1$s: %2$s;', $nav_pos, $nav_pos_y));
            if ($nav_x_ctr) {
                $half = $nav_w + $nav_gap_i / 2;
                $push($dtq . ' > .swiper-button-next', sprintf('left: auto; right: calc(50%% - %1$spx);', $half));
                $push($dtq . ' > .swiper-button-prev', sprintf('right: auto; left: calc(50%% - %1$spx);', $half));
            } else {
                $push($dtq . ' > .swiper-button-next', sprintf('left: auto; right: auto; %1$s: %2$s;', $nav_pos_hz, $nav_pos_x));
                $push($dtq . ' > .swiper-button-prev', sprintf('left: auto; right: auto; %1$s: %2$s; margin-%1$s: calc(%3$s + %4$s);', $nav_pos_hz, $nav_pos_x, $nav_width, $nav_gap));
            }
        }

        // Carousel spacing top/bottom (pad the viewport).
        $spacing_top    = $val('carouselSpacingTop', '0px');
        $spacing_bottom = $val('carouselSpacingBottom', '0px');
        if ((int) $spacing_top || (int) $spacing_bottom) {
            $push($dtq . ' > .swiper', sprintf('padding-top: %1$s; padding-bottom: %2$s;', $spacing_top, $spacing_bottom));
            if ($bp_raw('carouselSpacingTop', 'tablet') || $bp_raw('carouselSpacingBottom', 'tablet')) {
                $push_at($tablet, $dtq . ' > .swiper', sprintf('padding-top: %1$s; padding-bottom: %2$s;', $bp_raw('carouselSpacingTop', 'tablet') ?: $spacing_top, $bp_raw('carouselSpacingBottom', 'tablet') ?: $spacing_bottom));
            }
            if ($bp_raw('carouselSpacingTop', 'phone') || $bp_raw('carouselSpacingBottom', 'phone')) {
                $push_at($phone, $dtq . ' > .swiper', sprintf('padding-top: %1$s; padding-bottom: %2$s;', $bp_raw('carouselSpacingTop', 'phone') ?: $spacing_top, $bp_raw('carouselSpacingBottom', 'phone') ?: $spacing_bottom));
            }
        }

        // Custom transition easing.
        $css_transition = $val('cssTransition', '');
        if ($css_transition) {
            $push($dtq . ' .swiper-wrapper', sprintf('transition-timing-function: %1$s !important;', $css_transition));
        }

        // Custom cursor.
        if ('on' === $val('customCursor', 'off')) {
            $cursor_name = $val('cursorName', 'css_grab');
            $parts       = explode('_', $cursor_name);
            $c_type      = $parts[0] ?? '';
            $c_icon      = $parts[1] ?? '';
            $uris        = self::cursor_data_uris();
            if ('css' === $c_type) {
                $push($dtq, sprintf('cursor: %1$s !important;', $c_icon));
            } elseif ('custom' === $c_type && isset($uris[$c_icon])) {
                $push($dtq, sprintf("cursor: url('%1\$s'), auto !important;", $uris[$c_icon]));
            }
        }

        // Center highlighted (scale the active slide).
        if ('on' === $val('isCenter', 'off') && 'highlighted' === $val('centerModeType', 'classic')) {
            $animation_speed = $val('animationSpeed', '700ms');
            $push($dtq . '.dtq-centered--highlighted .swiper-slide', sprintf('transform: scale(0.8); transition: transform %1$s;', $animation_speed));
            $push($dtq . '.dtq-centered--highlighted .swiper-slide-active', 'transform: scale(1);');
        }

        return $styles;
    }

    /**
     * Custom cursor image data-URIs (kept in sync with config.js CURSOR_DATA_URIS).
     *
     * @return array
     */
    public static function cursor_data_uris()
    {
        return [
            'pizza'  => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAACAAAAAgBAMAAACBVGfHAAAABGdBTUEAALGPC/xhBQAAACBjSFJNAAB6JgAAgIQAAPoAAACA6AAAdTAAAOpgAAA6mAAAF3CculE8AAAAIVBMVEUAAAAAAAD/////zGb/mTOZAAAAzAD/zDP/AAD/Zmb/mZm5WRymAAAAAXRSTlMAQObYZgAAAAFiS0dEAmYLfGQAAAAJcEhZcwAAAMgAAADIAGP6560AAAAHdElNRQfkBRkTCRh4PlpnAAAA8ElEQVQoz12QsbnDIAyExQaWQ0ySztngfW8BMgIFA3gEVardEXfu3KbzmJEgTrCvEfo5wQEgABjMakDVNgZP/1l/GbX9p81ISHuvgJL23tfgIUAs5VCxWgWyjKoQ8GRlpO2lwXOM3TkG9IACAkoTg7pKKsc6hWKzGg1ZhVKohHVMGUkhr8Bw57Kjc5RfA0626Jmk2A9g7m7LNDL6AsRNlynNNJQezOL4ktK4TQDcRr5OMtJswMxMaXXkv2BkJ3kJvkIamAb7A441mv8Bo+9AqIR8fdoadJjWpgaGltnvwfqCnXB/hH6kPwCCg+wRbJe+ATasSMvHEwtpAAAAJXRFWHRkYXRlOmNyZWF0ZQAyMDIwLTA1LTI1VDE5OjA5OjIzKzAwOjAwCTF7LQAAACV0RVh0ZGF0ZTptb2RpZnkAMjAyMC0wNS0yNVQxOTowOTowNiswMDowMGhx60sAAAAASUVORK5CYII=',
            'burger' => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAB8AAAAfBAMAAADtgAsKAAAABGdBTUEAALGPC/xhBQAAACBjSFJNAAB6JgAAgIQAAPoAAACA6AAAdTAAAOpgAAA6mAAAF3CculE8AAAALVBMVEUAAAAAAAD////MZgBmMwCZMwD/zDP/mQD/zAB4eHhGRkbc3NygoKDIyMhmAAAKaD9VAAAAAXRSTlMAQObYZgAAAAFiS0dEAmYLfGQAAAAJcEhZcwAAAMgAAADIAGP6560AAAAHdElNRQfkBRkTGhFgDaNRAAAAEGNhTnYAAAAgAAAAIAAAAAAAAAAAYrnu+gAAATdJREFUKM9lkbFOwzAQhl2lM4qrLLW68AYgG2VtlIu6sVTxCzCwA5XzACCydmyUpWNNl630CVLyBkxImXkG7i6oSOFXlv+7/85nR4RiIDkEk2FkcvkPUCSA9DyDvMlyDVc9CKmca9Q5FMAS7c2dNr/ELNmiDHcFmdaWPms1R0zOVa1j98gRwCp1xKt7R4A60FlnnXugnnGK3pFs8doDi92OAiVPHWd4JvmyLDWBEa0Vk33Bydd4l4j3cCs63ExDIbfAq7OSdwQ7GQHkdD0jpwqBqrYSSImsP99CMVLN2kvS7ORnByEupG/aTdWu1/VOHSMEX1Ltvd+3zWnzcQAEt1IqX3tfHb2MDIKuS3iEwtkLTIhvMMAkguJpLiiSaj52UZTAb9oBb+ncM8z7Vx4DvwnA3+/jlr78AzvMazraOl3vAAAAJXRFWHRkYXRlOmNyZWF0ZQAyMDIwLTA1LTI1VDE5OjI2OjE2KzAwOjAwfOGxJQAAACV0RVh0ZGF0ZTptb2RpZnkAMjAyMC0wNS0yNVQxOToyNjoxNiswMDowMA28CZkAAAAASUVORK5CYII=',
        ];
    }
}
