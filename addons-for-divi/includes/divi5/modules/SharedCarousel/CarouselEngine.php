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
        // Divi saves lengths and durations with their unit ("0ms", "0px"), so
        // a zero has to be recognised with a unit too. Matching only a bare "0"
        // turned Autoplay Delay 0 into 2000ms and Gap Between Slides 0 into
        // 10px on the front end, while the builder (config.js) kept the 0.
        $is_zero = 1 === preg_match('/^\s*0+(?:\.0*)?\s*[a-z%]*\s*$/i', (string) $val);
        return ($n > 0 || $is_zero) ? $n : $fallback;
    }

    /**
     * Parse a leading signed integer ("-20px" is -20), mirroring config.js
     * num(). For values that may be negative, where to_int() would fall back.
     *
     * @param mixed $val      Raw value.
     * @param int   $fallback Fallback integer.
     *
     * @return int
     */
    public static function to_signed_int($val, $fallback)
    {
        if (null === $val || '' === $val) {
            return $fallback;
        }
        return 1 === preg_match('/^\s*([+-]?\d+)/', (string) $val, $m) ? (int) $m[1] : $fallback;
    }

    /**
     * The carousel's transition effect, or 'slide' for anything unknown.
     * Mirrors carouselEffect() in config.js.
     *
     * @param array $advanced The module advanced attrs.
     *
     * @return string
     */
    public static function effect($advanced)
    {
        $effect = $advanced['effect']['desktop']['value'] ?? 'slide';
        return in_array($effect, ['slide', 'fade', 'coverflow', 'cards', 'flip', 'cube'], true) ? $effect : 'slide';
    }

    /**
     * Does this carousel run Continuous Scroll? Needs autoplay and the plain
     * slide effect. Mirrors isContinuous() in config.js.
     *
     * @param array $advanced The module advanced attrs.
     *
     * @return bool
     */
    public static function is_continuous($advanced)
    {
        return 'on' === ($advanced['isAutoplay']['desktop']['value'] ?? 'on')
            && 'continuous' === ($advanced['autoplayMode']['desktop']['value'] ?? 'step')
            && 'slide' === self::effect($advanced)
            && self::grid_rows($advanced) <= 1;
    }

    /**
     * Rows of slides (Swiper grid), 1 to 4. Only a horizontal, fixed-width
     * carousel with the slide effect can stack rows; anything else gets 1.
     * Mirrors gridRows() in config.js.
     *
     * @param array $advanced The module advanced attrs.
     *
     * @return int
     */
    public static function grid_rows($advanced)
    {
        if ('slide' !== self::effect($advanced)
            || 'on' === ($advanced['isVertical']['desktop']['value'] ?? 'off')
            || 'on' === ($advanced['isVariableWidth']['desktop']['value'] ?? 'off')
        ) {
            return 1;
        }
        return min(4, max(1, self::to_int($advanced['gridRows']['desktop']['value'] ?? '1', 1)));
    }

    /**
     * The parent carousel's `module.advanced` attrs, for a slide's render.
     * Pinch to Zoom and Parallax Captions change the slide markup, so the
     * slide needs to know how its carousel is set. Defaults and presets are
     * folded in through Divi's own get_all_attrs().
     *
     * @param object $block The slide's parsed block.
     *
     * @return array
     */
    public static function parent_advanced($block)
    {
        $store = '\ET\Builder\FrontEnd\BlockParser\BlockParserStore';
        if (!is_object($block) || empty($block->parsed_block['id']) || !class_exists($store) || !method_exists($store, 'get_parent')) {
            return [];
        }
        $parent = $store::get_parent($block->parsed_block['id'], $block->parsed_block['storeInstance'] ?? null);
        if (!$parent) {
            return [];
        }
        $utils = '\ET\Builder\Packages\ModuleUtils\ModuleUtils';
        $attrs = (class_exists($utils) && method_exists($utils, 'get_all_attrs')) ? $utils::get_all_attrs($parent) : ($parent->attrs ?? []);
        $advanced = $attrs['module']['advanced'] ?? [];
        return is_array($advanced) ? $advanced : [];
    }

    /**
     * Slide markup options that come from the parent carousel.
     *
     * @param array $parent The parent's `module.advanced` attrs.
     *
     * @return array ['zoom' => bool, 'parallax' => int|null] Parallax is the caption offset in px, or null.
     */
    public static function slide_options($parent)
    {
        $continuous = self::is_continuous($parent);
        $zoom       = !$continuous && 'on' === ($parent['enableZoom']['desktop']['value'] ?? 'off');
        $parallax   = (!$continuous && 'on' === ($parent['parallaxCaptions']['desktop']['value'] ?? 'off'))
            ? max(0, self::to_int($parent['parallaxAmount']['desktop']['value'] ?? '60px', 60))
            : null;
        return ['zoom' => $zoom, 'parallax' => $parallax];
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
        // A layout migrated from Divi 4 carries the legacy `%%24%%` icon form,
        // which html_entity_decode leaves untouched, so the arrow renders as
        // literal text. Resolve it to a real glyph first.
        if ('' !== $unicode && function_exists('dtq_resolve_icon_unicode')) {
            $unicode = dtq_resolve_icon_unicode($unicode);
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
        // On-demand: every front-end carousel render funnels through here, so
        // this is the single point that pulls in the Swiper library. Registered
        // (not enqueued) in Modules.php, so pages without a carousel never load
        // it. Guarded for non-WP/REST contexts.
        if (function_exists('wp_enqueue_script')) {
            wp_enqueue_style('divi-torque-lite-swiper');
            wp_enqueue_script('divi-torque-lite-swiper');
        }

        $val    = function ($key, $fallback) use ($advanced) {
            return $advanced[$key]['desktop']['value'] ?? $fallback;
        };
        $bp_int = function ($key, $bp, $fallback) use ($advanced) {
            $raw = $advanced[$key][$bp]['value'] ?? null;
            return (null === $raw || '' === $raw) ? $fallback : self::to_int($raw, $fallback);
        };

        // A count of 0 (possible from converted/imported layouts; the field's
        // own minimum is 1) makes Swiper size slides as Infinity and they vanish.
        $slide_count   = max(1, self::to_int($val('slideCount', '3'), 3));
        $tablet_count  = max(1, $bp_int('slideCount', 'tablet', $slide_count));
        $phone_count   = max(1, $bp_int('slideCount', 'phone', $tablet_count));
        $slide_scroll  = self::to_int($val('slideToScroll', '1'), 1);
        $space         = self::to_int($val('slideSpacing', '10px'), 10);
        $is_infinite   = 'on' === $val('isInfinite', 'on');
        $is_swipe      = 'on' === $val('isSwipe', 'on');
        $is_center     = 'on' === $val('isCenter', 'off');
        $is_vertical   = 'on' === $val('isVertical', 'off');
        $is_variable   = 'on' === $val('isVariableWidth', 'off');
        $is_autoplay   = 'on' === $val('isAutoplay', 'on');
        // Continuous Scroll: the runtime moves the track at a steady speed
        // itself (runtime.js), so Swiper's loop, autoplay, arrows, dots and
        // keyboard paging are all off. Swiper still lays the slides out and
        // handles dragging. It needs the plain slide effect.
        $effect        = self::effect($advanced);
        // Fade, Cards, Flip and Cube stack or turn single slides.
        $single_view   = in_array($effect, ['fade', 'cards', 'flip', 'cube'], true);
        $continuous    = self::is_continuous($advanced);
        $reverse       = 'on' === $val('reverseDirection', 'off');
        $pause_hover   = 'off' !== $val('pauseOnHover', 'on');
        $stop_interact = 'on' === $val('stopOnInteraction', 'off');
        $end_behavior  = $val('endBehavior', 'restart');
        $show_nav      = !$continuous && 'off' !== $val('useNav', 'on');
        $show_pagi     = !$continuous && 'on' === $val('usePagi', 'off');

        $config = [
            'speed'          => self::to_int($val('animationSpeed', '700ms'), 700),
            'loop'           => $is_infinite && !$continuous,
            'grabCursor'     => true,
            'simulateTouch'  => $is_swipe,
            'allowTouchMove' => $is_swipe,
            'observer'       => true,
            'observeParents' => true,
            'spaceBetween'   => $space,
            // With fewer slides than fit the viewport there is nothing to page
            // through, so Swiper disables its controls and dragging instead of
            // rendering dead arrows and a single bullet.
            'watchOverflow'  => true,
            // Swiper's a11y module labels the nav buttons, exposes the slide
            // roles and announces slide changes in a live region. It was never
            // enabled, so carousels shipped with unlabelled arrows and silent
            // slide transitions. Keep in lockstep with the JS twin in
            // src/divi5/shared/carousel/config.js.
            'a11y'           => [
                'enabled'                 => true,
                'prevSlideMessage'        => esc_html__('Previous slide', 'addons-for-divi'),
                'nextSlideMessage'        => esc_html__('Next slide', 'addons-for-divi'),
                'firstSlideMessage'       => esc_html__('This is the first slide', 'addons-for-divi'),
                'lastSlideMessage'        => esc_html__('This is the last slide', 'addons-for-divi'),
                'paginationBulletMessage' => esc_html__('Go to slide {{index}}', 'addons-for-divi'),
            ],
            // Arrow-key control when the carousel has focus.
            'keyboard'       => ['enabled' => true, 'onlyInViewport' => true],
            // Whole-pixel slide sizes and positions. Fractional widths left
            // images a half pixel off their grid, which reads as a soft edge
            // and a shimmer while the track moves.
            'roundLengths'        => true,
            // Per-slide progress and visibility classes (parallax captions,
            // the thumbnail strip, the a11y module's visible set).
            'watchSlidesProgress' => true,
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

        if ($slide_scroll > 1 && !$is_vertical && !$is_variable && !$continuous) {
            $config['slidesPerGroup'] = $slide_scroll;
        }

        if ($continuous) {
            // Arrow keys would page a track the runtime is moving; the pause
            // button and focus pause are the keyboard controls here.
            $config['keyboard'] = ['enabled' => false];
            // A film strip is thrown, not paged: a drag glides to a stop with
            // momentum instead of snapping to the nearest slide, and the strip
            // picks up again from there.
            $config['freeMode'] = [
                'enabled'        => true,
                'momentum'       => true,
                'momentumRatio'  => 0.6,
                'momentumBounce' => false,
                'sticky'         => false,
            ];
        }

        if ($is_center && !$is_vertical && !$continuous) {
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
            $pagi_type = $val('pagiType', 'dot');
            if ('progressbar' === $pagi_type) {
                $config['pagination'] = ['el' => '.swiper-pagination', 'type' => 'progressbar'];
            } else {
                $config['pagination'] = ['el' => '.swiper-pagination', 'clickable' => true];
                if ('number' === $pagi_type) {
                    $config['dtqPagiType'] = 'number';
                } elseif ('dynamic' === $pagi_type) {
                    // A long row of dots shrinks to the few around the active one.
                    $config['pagination']['dynamicBullets']     = true;
                    $config['pagination']['dynamicMainBullets'] = 1;
                }
            }
        }

        if (!$continuous && !$is_vertical && 'on' === $val('showScrollbar', 'off')) {
            $config['scrollbar'] = ['el' => '.swiper-scrollbar', 'draggable' => true, 'snapOnRelease' => true, 'hide' => false];
        }

        // Transition effects.
        if ('slide' !== $effect) {
            $config['effect'] = $effect;
            $slide_shadows    = 'off' !== $val('slideShadows', 'on');
            if ($single_view) {
                $config['slidesPerView'] = 1;
                $config['spaceBetween']  = 0;
                unset($config['breakpoints'], $config['slidesPerGroup'], $config['centeredSlides'], $config['slidesOffsetBefore'], $config['slidesOffsetAfter']);
            }
            if ('fade' === $effect) {
                $config['fadeEffect'] = ['crossFade' => true];
            } elseif ('cards' === $effect) {
                $config['cardsEffect'] = ['slideShadows' => $slide_shadows, 'rotate' => true, 'perSlideOffset' => 8, 'perSlideRotate' => 2];
            } elseif ('flip' === $effect) {
                $config['flipEffect'] = ['slideShadows' => $slide_shadows, 'limitRotation' => true];
            } elseif ('cube' === $effect) {
                $config['cubeEffect'] = ['slideShadows' => $slide_shadows, 'shadow' => $slide_shadows, 'shadowOffset' => 20, 'shadowScale' => 0.94];
            } elseif ('coverflow' === $effect) {
                // Coverflow is built around a centered active slide.
                $config['centeredSlides'] = true;
                unset($config['slidesOffsetBefore'], $config['slidesOffsetAfter']);
                $config['coverflowEffect'] = [
                    'rotate'       => self::to_signed_int($val('coverflowRotate', '50deg'), 50),
                    'depth'        => self::to_signed_int($val('coverflowDepth', '100px'), 100),
                    'stretch'      => self::to_signed_int($val('coverflowStretch', '0px'), 0),
                    'modifier'     => 1,
                    'slideShadows' => $slide_shadows,
                ];
            }
        }

        // Gallery options.
        $rows = self::grid_rows($advanced);
        if ($rows > 1) {
            // Row fill keeps the slides' natural height (column fill needs a
            // fixed carousel height). Swiper's loop cannot run on a
            // row-filled grid, and a grid has no single slide to center.
            $config['grid'] = ['rows' => $rows, 'fill' => 'row'];
            $config['loop'] = false;
            unset($config['centeredSlides'], $config['slidesOffsetBefore'], $config['slidesOffsetAfter']);
        }
        if (!$continuous && 'on' === $val('enableZoom', 'off')) {
            $config['zoom'] = ['maxRatio' => 3, 'toggle' => true];
        }
        if (!$continuous && 'on' === $val('parallaxCaptions', 'off')) {
            $config['parallax'] = true;
        }
        if (!$continuous && 'on' === $val('showThumbs', 'off')) {
            // Built by the runtime from the slides' own images (runtime.js).
            $thumbs        = max(1, self::to_int($val('thumbCount', '6'), 6));
            $thumbs_tablet = max(1, $bp_int('thumbCount', 'tablet', $thumbs));
            $config['dtqThumbs'] = [
                'count'  => $thumbs,
                'tablet' => $thumbs_tablet,
                'phone'  => max(1, $bp_int('thumbCount', 'phone', $thumbs_tablet)),
                'gap'    => max(0, self::to_int($val('thumbGap', '8px'), 8)),
            ];
        }

        // Free Scroll and Mouse Wheel (Slide by Slide; Continuous Scroll
        // always glides and leaves the wheel to the page). Free scroll moves
        // a track, so it needs the plain slide effect.
        if (!$continuous && 'slide' === $effect && 'on' === $val('freeScroll', 'off')) {
            $config['freeMode'] = [
                'enabled'        => true,
                'momentum'       => true,
                'momentumRatio'  => 1,
                'momentumBounce' => true,
                'sticky'         => 'on' === $val('freeScrollSnap', 'off'),
            ];
        }
        if (!$continuous && 'on' === $val('mousewheel', 'off')) {
            // forceToAxis: a horizontal carousel only takes horizontal wheel
            // and trackpad movement, so scrolling down the page is never
            // captured. releaseOnEdges hands the wheel back at either end.
            $config['mousewheel'] = ['enabled' => true, 'forceToAxis' => true, 'releaseOnEdges' => true, 'sensitivity' => 1];
            if ($is_vertical) {
                // A looping vertical carousel has no end to release at.
                $config['loop'] = false;
            }
        }

        // At the last slide, when the loop is off. "restart" is Swiper's
        // default: autoplay returns to the first slide, the arrows stop.
        if (!$is_infinite && !$continuous && 'rewind' === $end_behavior) {
            $config['rewind'] = true;
        }

        if ($is_autoplay && !$continuous) {
            $config['autoplay'] = [
                'delay'                => self::to_int($val('autoplaySpeed', '2000ms'), 2000),
                // Hover, keyboard focus, off-screen and the pause button are
                // all held by the shared runtime (runtime.js), which owns
                // pausing. Swiper's own hover pause cannot see those other
                // reasons and would restart autoplay underneath them.
                'pauseOnMouseEnter'    => false,
                // Stop After Interaction is a runtime hold, so the pause
                // button can start autoplay again.
                'disableOnInteraction' => false,
                'reverseDirection'     => $reverse,
                'stopOnLastSlide'      => !$is_infinite && 'stop' === $end_behavior,
            ];
        }

        // Settings the runtime reads (stripped before Swiper sees the config).
        $config['dtqMotion'] = $continuous
            ? [
                'mode'              => 'continuous',
                'pauseOnHover'      => $pause_hover,
                // Pixels per second. A floor of 10 keeps a stored 0 from
                // stopping the strip (and from a zero-length loop).
                'pxPerSec'          => max(10, self::to_int($val('scrollSpeed', '60px'), 60)),
                'reverse'           => $reverse,
                'stopOnInteraction' => $stop_interact,
            ]
            : [
                'mode'              => 'step',
                // Moving content that cannot be paused is a WCAG 2.2.2
                // problem; hovering pauses it by default.
                'pauseOnHover'      => $pause_hover,
                'stopOnInteraction' => $stop_interact,
            ];

        return ['config' => $config, 'show_nav' => $show_nav, 'show_pagi' => $show_pagi];
    }

    /**
     * Inline custom properties that lay the slides out before Swiper starts.
     *
     * Read from the built config, so the counts are the ones Swiper will use.
     * Returns an empty string for layouts the pre-init row cannot predict
     * (vertical, variable width, centered, effects other than slide, rows).
     * The rules live in src/divi5/shared/carousel/carousel.scss.
     *
     * @param array $config A config from build_swiper_config().
     *
     * @return string A leading-space ` style="..."` attribute, or ''.
     */
    public static function pre_init_style($config)
    {
        if (!is_array($config)
            || !isset($config['slidesPerView'])
            || !is_int($config['slidesPerView'])
            || isset($config['direction'])
            || !empty($config['centeredSlides'])
            || (isset($config['effect']) && 'slide' !== $config['effect'])
            || isset($config['grid'])
        ) {
            return '';
        }

        $phone   = max(1, (int) $config['slidesPerView']);
        $tablet  = max(1, (int) ($config['breakpoints'][768]['slidesPerView'] ?? $phone));
        $desktop = max(1, (int) ($config['breakpoints'][981]['slidesPerView'] ?? $tablet));
        $gap     = max(0, (int) ($config['spaceBetween'] ?? 0));

        return sprintf(
            ' style="%s"',
            esc_attr(sprintf('--dtq-slides:%1$d;--dtq-slides-tablet:%2$d;--dtq-slides-phone:%3$d;--dtq-gap:%4$dpx', $desktop, $tablet, $phone, $gap))
        );
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
     * Markup for the optional controls: the pause button and autoplay progress
     * bar sit inside `.swiper` (over the slides), the scrollbar after it.
     * Mirrors renderCarouselControls() in renderArrows.jsx; the runtime
     * (runtime.js) wires them up.
     *
     * @param array $advanced The module advanced attrs.
     *
     * @return array ['inner' => string, 'after' => string]
     */
    public static function render_controls($advanced)
    {
        $val        = function ($key, $fallback) use ($advanced) {
            return $advanced[$key]['desktop']['value'] ?? $fallback;
        };
        $autoplay   = 'on' === $val('isAutoplay', 'on');
        $continuous = self::is_continuous($advanced);
        $vertical   = 'on' === $val('isVertical', 'off');

        $inner = '';
        if ($autoplay && 'on' === $val('showPauseButton', 'off')) {
            $position = $val('pausePosition', 'top-right');
            if (!in_array($position, ['top-right', 'top-left', 'bottom-right', 'bottom-left'], true)) {
                $position = 'top-right';
            }
            $pause = esc_html__('Pause the carousel', 'addons-for-divi');
            $inner .= sprintf(
                '<button type="button" class="dtq-carousel-pause dtq-carousel-pause--%1$s" aria-pressed="false" aria-label="%2$s" data-label-pause="%2$s" data-label-play="%3$s"><span class="dtq-carousel-pause__icon" aria-hidden="true"></span></button>',
                esc_attr($position),
                esc_attr($pause),
                esc_attr(esc_html__('Play the carousel', 'addons-for-divi'))
            );
        }
        if ($autoplay && !$continuous && 'on' === $val('showAutoplayProgress', 'off')) {
            $inner .= '<div class="dtq-carousel-progress" aria-hidden="true"><span class="dtq-carousel-progress__bar"></span></div>';
        }

        $after = (!$continuous && !$vertical && 'on' === $val('showScrollbar', 'off')) ? '<div class="swiper-scrollbar"></div>' : '';
        if (!$continuous && 'on' === $val('showThumbs', 'off')) {
            // Filled by the runtime from the slides' images.
            $after .= '<div class="swiper dtq-carousel-thumbs"><div class="swiper-wrapper"></div></div>';
        }

        return ['inner' => $inner, 'after' => $after];
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
        $classes    = ['dtq-swiper-carousel', $type_class, 'dtq-lightbox-off'];
        $continuous = self::is_continuous($advanced);
        $effect     = self::effect($advanced);
        if ($continuous) {
            $classes[] = 'dtq-autoplay--continuous';
        }
        if ('slide' !== $effect) {
            $classes[] = 'dtq-effect--' . $effect;
        }
        if (self::grid_rows($advanced) > 1) {
            $classes[] = 'dtq-grid';
        }
        if ('on' === ($advanced['isCenter']['desktop']['value'] ?? 'off') && !$continuous && 'slide' === $effect && self::grid_rows($advanced) <= 1) {
            $classes[] = 'dtq-centered';
            $center_type = $advanced['centerModeType']['desktop']['value'] ?? 'classic';
            $classes[]   = 'dtq-centered--' . (in_array($center_type, ['classic', 'highlighted'], true) ? $center_type : 'classic');
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

        // Every value below is author-controlled and is interpolated straight into
        // a CSS declaration, so each is sanitized by type first. Unsanitized, a
        // colour of `red;}…` would escape its declaration and rewrite the page's
        // styling; `navPos`/`navPosHz`/`navBorderStyle` are worse still because
        // they land where a CSS *property name* is expected. Values that fail
        // their check fall back to the module default instead of being emitted.
        // Colours are resolved first so Divi global colours (`$variable(...)$`)
        // become `var(--gcid-…)` rather than leaking the raw token into the CSS.
        $val   = function ($key, $fallback) use ($advanced) {
            return $advanced[$key]['desktop']['value'] ?? $fallback;
        };
        $color = function ($key, $fallback) use ($advanced) {
            return dtq_css_color(dtq_resolve_css_value($advanced[$key]['desktop']['value'] ?? ''), $fallback);
        };
        $len   = function ($key, $fallback) use ($advanced) {
            return dtq_css_length(dtq_resolve_css_value($advanced[$key]['desktop']['value'] ?? ''), $fallback);
        };
        $enum  = function ($key, $fallback, array $allowed) use ($advanced) {
            $value = $advanced[$key]['desktop']['value'] ?? $fallback;
            return in_array($value, $allowed, true) ? $value : $fallback;
        };
        $hover = function ($key) use ($advanced) {
            $raw = $advanced[$key]['desktop']['hover'] ?? null;
            if (null === $raw || '' === $raw) {
                return null;
            }
            return dtq_css_color(dtq_resolve_css_value($raw), 'inherit');
        };

        $styles = [];
        $push   = function ($selector, $declaration) use (&$styles) {
            $styles[] = ['atRules' => false, 'selector' => $selector, 'declaration' => $declaration];
        };

        $dtq = $order_class . ' .dtq-swiper-carousel';

        // Navigation arrows.
        $nav_color        = $color('navColor', '#333333');
        $nav_bg           = $color('navBg', '#dddddd');
        $nav_height       = $len('navHeight', '40px');
        $nav_width        = $len('navWidth', '40px');
        $nav_icon_size    = $len('navIconSize', '30px');
        $nav_radius       = $len('navRadius', '40px');
        $nav_border_width = $len('navBorderWidth', '0px');
        $nav_border_style = $enum('navBorderStyle', 'solid', ['none', 'hidden', 'solid', 'dashed', 'dotted', 'double', 'groove', 'ridge', 'inset', 'outset']);
        $nav_border_color = $color('navBorderColor', '#333333');
        $nav_skew         = $len('navSkew', '0deg');
        $nav_pos_x        = $len('navPosX', '-15px');
        $nav_pos_y        = $len('navPosY', '50%');
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
        $pagi_bg        = $color('pagiBg', '#dddddd');
        $pagi_bg_active = $color('pagiBgActive', '#333333');
        $pagi_height    = $len('pagiHeight', '10px');
        $pagi_width     = $len('pagiWidth', '10px');
        $pagi_radius    = $len('pagiRadius', '10px');
        $pagi_spacing   = $len('pagiSpacing', '10px');
        $pagi_pos_y     = $len('pagiPosY', '10px');
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
            $active_decl .= sprintf(' width: %1$s;', $len('pagiWidthActive', 'auto'));
        }
        $push($dtq . ' .swiper-pagination-bullet-active', $active_decl);

        // Responsive spacing values are interpolated the same way the desktop ones
        // are, so they need the same length check. Returns null when unset or not
        // a plain length, so the callers' `?:` fallbacks to the desktop value
        // still work. Never fall back to 0px: a phone Slide Width of calc()/var()
        // became `width: 0px` and collapsed every slide on phones.
        $bp_raw  = function ($key, $bp) use ($advanced) {
            $raw = $advanced[$key][$bp]['value'] ?? null;
            if (null === $raw || '' === $raw) {
                return null;
            }
            $length = dtq_css_length(dtq_resolve_css_value($raw), '');
            return '' === $length ? null : $length;
        };
        $push_at = function ($at_rule, $selector, $declaration) use (&$styles) {
            $styles[] = ['atRules' => $at_rule, 'selector' => $selector, 'declaration' => $declaration];
        };
        $tablet = '@media only screen and (max-width: 980px)';
        $phone  = '@media only screen and (max-width: 767px)';

        // Number pagination.
        if ('number' === $val('pagiType', 'dot')) {
            $pagi_color        = $color('pagiColor', '#333333');
            $pagi_color_hover  = $hover('pagiColor');
            $pagi_text         = $len('pagiText', '16px');
            $pagi_text_active  = $color('pagiTextActive', $pagi_bg_active);
            $push($dtq . ' .swiper-pagination-bullet', sprintf('background: transparent; width: auto; height: auto; border-radius: 0; font-size: %1$s; line-height: 1; color: %2$s;', $pagi_text, $pagi_color));
            $push($dtq . ' .swiper-pagination-bullet-active', sprintf('background: transparent; color: %1$s;', $pagi_text_active));
            if ($pagi_color_hover) $push($dtq . ' .swiper-pagination-bullet:hover', sprintf('color: %1$s;', $pagi_color_hover));
        }

        // Progress bar and dynamic dots pagination.
        $pagi_type = $val('pagiType', 'dot');
        if ('progressbar' === $pagi_type) {
            $push(
                $dtq . ' > .swiper-pagination.swiper-pagination-progressbar',
                sprintf('display: block; height: %1$s; background: %2$s; border-radius: %3$s; overflow: hidden;', $pagi_height, $pagi_bg, $pagi_radius)
            );
            $push($dtq . ' > .swiper-pagination .swiper-pagination-progressbar-fill', sprintf('background: %1$s; border-radius: %2$s;', $pagi_bg_active, $pagi_radius));
        } elseif ('dynamic' === $pagi_type) {
            // Swiper sizes and offsets dynamic bullets from their outer width,
            // so the spacing has to be margin (not the flex gap) and the row a
            // plain line.
            $push(
                $dtq . ' > .swiper-pagination.swiper-pagination-bullets-dynamic',
                'display: block; left: 50%; transform: translateX(-50%); white-space: nowrap; overflow: hidden; font-size: 0;'
            );
            $push(
                $dtq . ' > .swiper-pagination-bullets-dynamic .swiper-pagination-bullet',
                sprintf('display: inline-block; position: relative; margin: 0 calc(%1$s / 2);', $pagi_spacing)
            );
        }

        // Draggable scrollbar.
        if ('on' === $val('showScrollbar', 'off')) {
            $push(
                $dtq . ' > .swiper-scrollbar',
                sprintf('position: relative; left: auto; right: auto; top: auto; bottom: auto; width: 100%%; height: %1$s; margin-top: %2$s; background: %3$s; border-radius: %4$s;', $pagi_height, $pagi_pos_y, $pagi_bg, $pagi_radius)
            );
            $push($dtq . ' > .swiper-scrollbar .swiper-scrollbar-drag', sprintf('background: %1$s; border-radius: %2$s;', $pagi_bg_active, $pagi_radius));
        }

        // Autoplay progress bar.
        if ('on' === $val('showAutoplayProgress', 'off')) {
            $push($dtq . ' .dtq-carousel-progress', sprintf('background: %1$s;', $pagi_bg));
            $push($dtq . ' .dtq-carousel-progress__bar', sprintf('background: %1$s;', $pagi_bg_active));
        }

        // Pause button (takes the arrow style).
        if ('on' === $val('showPauseButton', 'off')) {
            $push(
                $dtq . ' .dtq-carousel-pause',
                sprintf(
                    'width: %1$s; height: %2$s; background: %3$s; color: %4$s; border: %5$s %6$s %7$s; border-radius: %8$s; font-size: calc(%9$s / 2);',
                    $nav_width,
                    $nav_height,
                    $nav_bg,
                    $nav_color,
                    $nav_border_width,
                    $nav_border_style,
                    $nav_border_color,
                    $nav_radius,
                    $nav_icon_size
                )
            );
            if ($hover('navColor')) $push($dtq . ' .dtq-carousel-pause:hover', sprintf('color: %1$s;', $hover('navColor')));
            if ($hover('navBg')) $push($dtq . ' .dtq-carousel-pause:hover', sprintf('background: %1$s;', $hover('navBg')));
            if ($hover('navBorderColor')) $push($dtq . ' .dtq-carousel-pause:hover', sprintf('border-color: %1$s;', $hover('navBorderColor')));
        }

        // Alongside navigation (CSS-positioned).
        if ('alongside' === $val('navType', 'overlay')) {
            // These two are interpolated as CSS *property names*, so they must be
            // an exact match from the allowed set — never a passed-through value.
            $nav_pos    = $enum('navPos', 'bottom', ['top', 'bottom']);
            $nav_pos_hz = $enum('navPosHz', 'right', ['left', 'right']);
            $nav_x_ctr  = 'on' === $val('navXCenter', 'off');
            $nav_w      = (int) $nav_width;
            $nav_gap    = $len('navGap', '10px');
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

        // Variable slide width. With `slidesPerView: 'auto'` Swiper takes each
        // slide's width from CSS, so the "Slide Width" option only has an effect
        // once it is emitted here — without this the field rendered in the panel
        // and did nothing.
        if ('on' === $val('isVariableWidth', 'off')) {
            $slide_width  = $len('slideWidth', '');
            $image_height = $len('imageHeight', '');
            // 0 counts as unset: a 0px image height would hide the whole carousel.
            if ((float) $image_height > 0) {
                // One height for every image, and each slide as wide as its
                // image, so portrait and landscape images share a row. The text
                // block is held to the image width so a long title cannot
                // stretch its slide. Keep in lockstep with styles.js.
                $push($dtq . ' .swiper-slide', 'width: auto;');
                $push($dtq . ' .swiper-slide img', sprintf('height: %s; width: auto; max-width: none;', $image_height));
                $push($dtq . ' .swiper-slide .content', 'width: 0; min-width: 100%;');
                foreach (['tablet' => $tablet, 'phone' => $phone] as $bp => $at_rule) {
                    $bp_height = $bp_raw('imageHeight', $bp);
                    if ((float) $bp_height > 0) {
                        $push_at($at_rule, $dtq . ' .swiper-slide img', sprintf('height: %s;', $bp_height));
                    }
                }
            } elseif ('' !== $slide_width) {
                $push($dtq . ' .swiper-slide', sprintf('width: %s;', $slide_width));
                foreach (['tablet' => $tablet, 'phone' => $phone] as $bp => $at_rule) {
                    $bp_width = $bp_raw('slideWidth', $bp);
                    if (null !== $bp_width) {
                        $push_at($at_rule, $dtq . ' .swiper-slide', sprintf('width: %s;', $bp_width));
                    }
                }
            }
        }

        // Image Height without Variable Slide Width: every image is cropped to
        // one height, so slides of different shapes (and effects, which show
        // whole slides) line up evenly. Keep in lockstep with styles.js.
        if ('on' !== $val('isVariableWidth', 'off')) {
            $effect_image_height = $len('imageHeight', '');
            if ((float) $effect_image_height > 0) {
                $push($dtq . ' .swiper-slide .dtq-figure img', sprintf('height: %s; width: 100%%; max-width: 100%%; object-fit: cover;', $effect_image_height));
                foreach (['tablet' => $tablet, 'phone' => $phone] as $bp => $at_rule) {
                    $bp_height = $bp_raw('imageHeight', $bp);
                    if ((float) $bp_height > 0) {
                        $push_at($at_rule, $dtq . ' .swiper-slide .dtq-figure img', sprintf('height: %s;', $bp_height));
                    }
                }
            }
        }

        // Thumbnail strip. Prefixed with the wrapper's child combinator so the
        // slide rules above (variable width, image height) never reach the
        // thumbnails. Keep in lockstep with styles.js.
        if ('on' === $val('showThumbs', 'off') && !self::is_continuous($advanced)) {
            $thumbs  = $dtq . ' > .dtq-carousel-thumbs';
            $opacity = min(1, max(0.1, self::to_int($val('thumbOpacity', '50%'), 50) / 100));
            $push($thumbs, sprintf('height: %1$s; margin-top: %2$s;', $len('thumbHeight', '80px'), $len('thumbSpacingTop', '10px')));
            foreach (['tablet' => $tablet, 'phone' => $phone] as $bp => $at_rule) {
                $bp_height = $bp_raw('thumbHeight', $bp);
                if ((float) $bp_height > 0) {
                    $push_at($at_rule, $thumbs, sprintf('height: %s;', $bp_height));
                }
            }
            $push(
                $thumbs . ' .swiper-slide',
                sprintf(
                    'height: 100%%; margin-bottom: 0; overflow: hidden; cursor: pointer; box-sizing: border-box; opacity: %1$s; border: %2$s solid transparent; border-radius: %3$s; transition: opacity 0.2s, border-color 0.2s;',
                    rtrim(rtrim(number_format($opacity, 2, '.', ''), '0'), '.'),
                    $len('thumbActiveBorderWidth', '2px'),
                    $len('thumbRadius', '4px')
                )
            );
            $push($thumbs . ' .swiper-slide-thumb-active', sprintf('opacity: 1; border-color: %1$s;', $color('thumbActiveBorderColor', '#2ea3f2')));
            $push($thumbs . ' .swiper-slide img', 'display: block; width: 100%; height: 100%; max-width: none; object-fit: cover;');
        }

        // Carousel spacing top/bottom (pad the viewport).
        $spacing_top    = $len('carouselSpacingTop', '0px');
        $spacing_bottom = $len('carouselSpacingBottom', '0px');
        if ((int) $spacing_top || (int) $spacing_bottom) {
            $push($dtq . ' > .swiper', sprintf('padding-top: %1$s; padding-bottom: %2$s;', $spacing_top, $spacing_bottom));
            if ($bp_raw('carouselSpacingTop', 'tablet') || $bp_raw('carouselSpacingBottom', 'tablet')) {
                $push_at($tablet, $dtq . ' > .swiper', sprintf('padding-top: %1$s; padding-bottom: %2$s;', $bp_raw('carouselSpacingTop', 'tablet') ?: $spacing_top, $bp_raw('carouselSpacingBottom', 'tablet') ?: $spacing_bottom));
            }
            if ($bp_raw('carouselSpacingTop', 'phone') || $bp_raw('carouselSpacingBottom', 'phone')) {
                $push_at($phone, $dtq . ' > .swiper', sprintf('padding-top: %1$s; padding-bottom: %2$s;', $bp_raw('carouselSpacingTop', 'phone') ?: $spacing_top, $bp_raw('carouselSpacingBottom', 'phone') ?: $spacing_bottom));
            }
        }

        // Custom transition easing. Only a real timing function is allowed through
        // — this lands inside a declaration, so an arbitrary string could close it.
        // Not in Continuous Scroll: there the only wrapper transition is the
        // glide after a drag, which needs an ease-out (carousel.scss), and an
        // ease-in-out made it speed up before it slowed down.
        $continuous     = self::is_continuous($advanced);
        $css_transition = trim((string) $val('cssTransition', ''));
        if (!$continuous
            && '' !== $css_transition
            && preg_match('/^(?:linear|ease|ease-in|ease-out|ease-in-out|step-start|step-end|cubic-bezier\(\s*[0-9.,\s-]+\)|steps\(\s*[0-9,a-z\s-]+\))$/i', $css_transition)
        ) {
            $push($dtq . ' .swiper-wrapper', sprintf('transition-timing-function: %1$s !important;', $css_transition));
        }

        // Custom cursor.
        if ('on' === $val('customCursor', 'off')) {
            $cursor_name = $val('cursorName', 'css_grab');
            $parts       = explode('_', $cursor_name);
            $c_type      = $parts[0] ?? '';
            $c_icon      = $parts[1] ?? '';
            $uris        = self::cursor_data_uris();
            if ('css' === $c_type && preg_match('/^[a-z-]+$/', $c_icon)) {
                // CSS cursor keywords are lowercase letters and hyphens only;
                // anything else would break out of the declaration.
                $push($dtq, sprintf('cursor: %1$s !important;', $c_icon));
            } elseif ('custom' === $c_type && isset($uris[$c_icon])) {
                $push($dtq, sprintf("cursor: url('%1\$s'), auto !important;", $uris[$c_icon]));
            }
        }

        // Center highlighted (scale the active slide).
        if ('on' === $val('isCenter', 'off') && 'highlighted' === $val('centerModeType', 'classic')) {
            $animation_speed = $len('animationSpeed', '700ms');
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
