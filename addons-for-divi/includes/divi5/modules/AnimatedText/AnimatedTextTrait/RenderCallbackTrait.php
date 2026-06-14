<?php
/**
 * Animated Text: Render callback trait.
 *
 * @package DiviTorqueLite\Modules\AnimatedText
 * @since   4.4.0
 */

namespace DiviTorqueLite\Modules\AnimatedText\AnimatedTextTrait;

if (!defined('ABSPATH')) {
    exit;
}

use ET\Builder\Packages\Module\Module;
use WP_Block;

trait RenderCallbackTrait
{
    /**
     * Decode the animated strings attribute into an array of phrases.
     *
     * The D5 attribute stores ONE PHRASE PER LINE (divi/textarea). But the
     * D4 -> D5 converter has no custom decode hook for the old `options_list`
     * value (it only exposes a fixed set of expansion fns), so the
     * conversion-outline maps the raw D4 attribute straight into the attr.
     * That raw value is a JSON array of {value:"…"} objects.
     *
     * We therefore accept BOTH shapes and normalise to an array of strings:
     *   - migrated users: a JSON array `[{"value":"First"},{"value":"…"}]`
     *     (possibly still carrying the shortcode-encoded brackets/quotes).
     *   - new users:      newline-separated plain text.
     *
     * Keep in lockstep with the JS twin `decodeAnimatedStrings()` in
     * src/divi5/modules/animated-text/strings.js.
     *
     * @param mixed $raw Stored attribute value.
     * @return array<int,string>
     */
    public static function decode_animated_strings($raw)
    {
        if (null === $raw || '' === $raw) {
            return [];
        }

        $text = (string) $raw;

        // Migrated D4 values may still carry the encoded brackets/quotes from
        // the shortcode attribute (the converter copies the raw string verbatim).
        $text = str_replace(
            ['&#91;', '&#93;', '%91', '%93', '%22'],
            ['[', ']', '[', ']', '"'],
            $text
        );

        $trimmed = trim($text);

        // D4 JSON array shape: [{"value":"…"}, …]
        if ('[' === substr($trimmed, 0, 1)) {
            $parsed = json_decode($trimmed, true);
            if (is_array($parsed)) {
                $out = [];
                foreach ($parsed as $item) {
                    if (is_array($item)) {
                        $val = isset($item['value']) ? (string) $item['value'] : '';
                    } else {
                        $val = (string) $item;
                    }
                    if ('' !== $val) {
                        $out[] = $val;
                    }
                }
                return $out;
            }
        }

        // New-user shape: one phrase per line.
        $lines = preg_split('/\r\n|\r|\n/', $text);
        $out   = [];
        foreach ((array) $lines as $line) {
            $line = trim($line);
            if ('' !== $line) {
                $out[] = $line;
            }
        }
        return $out;
    }

    /**
     * Server-side render for the Animated Text module.
     *
     * Mirrors the D4 markup (and the VB edit component) exactly:
     *   <div id="dtq-animated-text-{order}" class="dtq-module dtq-animated-text dtq-front" data-settings=".." data-type="{type}">
     *     <{tag} class="dtq-animated-text-head">
     *       [prefix]<div class="dtq-animated-text-prefix"><span>{prefix}</span>[&nbsp;]</div>
     *       [animation html — per type]
     *       [suffix]<div class="dtq-animated-text-suffix">[&nbsp;]<span>{suffix}</span></div>
     *     </{tag}>
     *   </div>
     *
     * Conditionally enqueues the matching library: typed.js for the typed
     * engine, textillate (jQuery) for tilt; slide needs no library. The
     * animate.css keyframes used by tilt/slide ship in the D5 frontend
     * bundle.css (ported into module.scss).
     *
     * @param array          $attrs    Block attributes.
     * @param string         $content  Block content (unused).
     * @param WP_Block       $block    Parsed block.
     * @param object         $elements Module elements helper.
     *
     * @return string
     */
    public static function render_callback($attrs, $content, $block, $elements)
    {
        $advanced = $attrs['module']['advanced'] ?? [];

        $val = function ($key, $fallback) use ($advanced) {
            return $advanced[$key]['desktop']['value'] ?? $fallback;
        };

        $animation_type = $val('animationType', 'typed');
        $layout         = $val('layout', 'inline');

        $prefix = $attrs['prefix']['innerContent']['desktop']['value'] ?? '';
        $suffix = $attrs['suffix']['innerContent']['desktop']['value'] ?? '';
        $strings = self::decode_animated_strings(
            $attrs['animatedText']['innerContent']['desktop']['value'] ?? ''
        );

        // Heading tag from the Animated font group (D4 default h3).
        $heading_level = $attrs['animated']['decoration']['font']['font']['desktop']['value']['headingLevel'] ?? 'h3';
        $heading_level = preg_match('/^h[1-6]$/', (string) $heading_level) ? $heading_level : 'h3';

        $order_number = $block->parsed_block['orderIndex'] ?? 0;

        // Build per-type settings + conditionally enqueue the engine library.
        $settings = [];
        if ('typed' === $animation_type) {
            // The typed engine is bundled in dist/divi5/frontend.js (vanilla,
            // src/divi5/modules/animated-text/typing.js) — no external library
            // to enqueue.
            $settings['strings']    = $strings;
            $settings['typeSpeed']  = intval($val('animationSpeed', '100ms'));
            $settings['startDelay'] = intval($val('startDelay', '300ms'));
            $settings['backSpeed']  = intval($val('backSpeed', '50ms'));
            $settings['backDelay']  = intval($val('backDelay', '500ms'));
            $settings['loop']       = 'on' === $val('useLoop', 'on');
            $settings['showCursor'] = 'on' === $val('showCursor', 'on');
        } elseif ('tilt' === $animation_type) {
            if (function_exists('wp_enqueue_script')) {
                wp_enqueue_script('divi-torque-lite-text-animation');
            }
            $tilt_sync    = explode('|', (string) $val('tiltSync', 'off|off'));
            $tilt_reverse = explode('|', (string) $val('tiltReverse', 'off|off'));
            $tilt_shuffle = explode('|', (string) $val('tiltShuffle', 'off|off'));
            $tilt_delay   = intval($val('tiltDelay', '50ms'));

            $settings['loop']           = true;
            $settings['in']['effect']     = $val('tiltIn', 'flip');
            $settings['in']['delayScale'] = 1.5;
            $settings['in']['delay']      = $tilt_delay;
            $settings['in']['sync']       = 'on' === ($tilt_sync[0] ?? 'off');
            $settings['in']['reverse']    = 'on' === ($tilt_reverse[0] ?? 'off');
            $settings['in']['shuffle']    = 'on' === ($tilt_shuffle[0] ?? 'off');
            $settings['out']['effect']     = $val('tiltOut', 'rotateOutDownLeft');
            $settings['out']['delayScale'] = 1.5;
            $settings['out']['delay']      = $tilt_delay;
            $settings['out']['sync']       = 'on' === ($tilt_sync[1] ?? 'off');
            $settings['out']['reverse']    = 'on' === ($tilt_reverse[1] ?? 'off');
            $settings['out']['shuffle']    = 'on' === ($tilt_shuffle[1] ?? 'off');
        } else { // slide
            $settings['slide_gap'] = intval($val('slideGap', '1500ms'));
        }

        $data_settings = sprintf(
            'data-settings="%1$s"',
            htmlspecialchars(wp_json_encode($settings), ENT_QUOTES, 'UTF-8')
        );

        $nbsp = 'inline' === $layout ? '&nbsp;' : '';

        // Prefix / suffix.
        $prefix_html = '';
        if ('' !== $prefix) {
            $prefix_html = sprintf(
                '<div class="dtq-animated-text-prefix"><span>%1$s</span>%2$s</div>',
                esc_html($prefix),
                $nbsp
            );
        }
        $suffix_html = '';
        if ('' !== $suffix) {
            $suffix_html = sprintf(
                '<div class="dtq-animated-text-suffix">%2$s<span>%1$s</span></div>',
                esc_html($suffix),
                $nbsp
            );
        }

        // Animation html.
        $animation_html = '';
        if ('typed' === $animation_type) {
            $animation_html = '<div class="dtq-text-animation dtq-animated-text-main dtq-typed-text"></div>';
        } elseif ('tilt' === $animation_type) {
            $items = '';
            foreach ($strings as $s) {
                $items .= '<li>' . esc_html($s) . '</li>';
            }
            $animation_html = sprintf(
                '<div class="dtq-animated-text-tilt"><ul class="texts dtq-animated-text-main">%1$s</ul></div>',
                $items
            );
        } else { // slide
            $items = '';
            foreach ($strings as $s) {
                $items .= '<li>' . esc_html($s) . '</li>';
            }
            $animation_html = sprintf(
                '<ul class="dtq-animated-text-slide dtq-animated-text-main">%1$s</ul>',
                $items
            );
        }

        $children = sprintf(
            '<div id="dtq-animated-text-%6$s" class="dtq-module dtq-animated-text dtq-front" %1$s data-type="%2$s"><%3$s class="dtq-animated-text-head">%4$s%5$s%7$s</%3$s></div>',
            $data_settings,
            esc_attr($animation_type),
            esc_attr($heading_level),
            $prefix_html,
            $animation_html,
            esc_attr($order_number),
            $suffix_html
        );

        return Module::render(
            [
                'attrs'               => $attrs,
                'elements'            => $elements,
                'id'                  => $block->parsed_block['id'],
                'name'                => $block->block_type->name,
                'moduleCategory'      => $block->block_type->category,
                'classnamesFunction'  => [self::class, 'module_classnames'],
                'stylesComponent'     => [self::class, 'module_styles'],
                'scriptDataComponent' => [self::class, 'module_script_data'],
                'orderIndex'          => $block->parsed_block['orderIndex'] ?? 0,
                'storeInstance'       => $block->parsed_block['storeInstance'] ?? null,
                'children'            => $children,
            ]
        );
    }
}
