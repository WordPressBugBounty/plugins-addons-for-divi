<?php
/**
 * StickyVideo: Render callback trait.
 *
 * @package DiviTorqueLite\Modules\StickyVideo
 * @since   4.9.0
 */

namespace DiviTorqueLite\Modules\StickyVideo\StickyVideoTrait;

if (!defined('ABSPATH')) {
    exit;
}

use ET\Builder\Packages\Module\Module;
use ET\Builder\Packages\Module\Layout\Components\ModuleElements\ModuleElements;
use WP_Block;

trait RenderCallbackTrait
{
    const SOURCES    = ['youtube', 'vimeo', 'self'];
    const POSITIONS  = ['bottom-right', 'bottom-left', 'top-right', 'top-left'];
    const ANIMATIONS = ['slide', 'fade', 'none'];
    const RATIOS     = ['16:9', '4:3', '1:1', '21:9'];
    const DISABLE_ON = ['none', 'phone', 'tablet-phone'];
    const STICK_ON   = ['played', 'always'];
    const CLOSE_ACTIONS = ['unstick', 'stop'];

    /**
     * Normalise an upload value.
     *
     * Divi 5 stores an object, Divi 4 stored a bare URL. Same helper as
     * VideoModalTrait::upload_src().
     *
     * @param mixed $value Raw attribute value.
     *
     * @return string
     */
    public static function upload_src($value)
    {
        if (is_array($value)) {
            return (string) ($value['src'] ?? '');
        }

        return (string) $value;
    }

    /**
     * Extract a YouTube video id.
     *
     * PHP twin of youtubeId() in src/divi5/modules/sticky-video/embed-url.js.
     *
     * @param string $url Author-supplied URL.
     *
     * @return string Empty when none was found.
     */
    public static function youtube_id($url)
    {
        $url = trim((string) $url);

        if ('' === $url) {
            return '';
        }

        $patterns = [
            '#(?:youtu\.be/)([\w-]{6,})#i',
            '#[?&]v=([\w-]{6,})#i',
            '#(?:youtube(?:-nocookie)?\.com/(?:embed|v|shorts)/)([\w-]{6,})#i',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $url, $matches)) {
                return $matches[1];
            }
        }

        return '';
    }

    /**
     * Extract a Vimeo video id.
     *
     * PHP twin of vimeoId() in src/divi5/modules/sticky-video/embed-url.js.
     *
     * @param string $url Author-supplied URL.
     *
     * @return string Empty when none was found.
     */
    public static function vimeo_id($url)
    {
        $url = trim((string) $url);

        if ('' === $url) {
            return '';
        }

        $patterns = [
            '#player\.vimeo\.com/video/(\d+)#i',
            '#vimeo\.com/(?:channels/[\w-]+/|groups/[^/]+/videos/)?(\d+)#i',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $url, $matches)) {
                return $matches[1];
            }
        }

        return '';
    }

    /**
     * Build the embed URL.
     *
     * PHP twin of embedUrl() in src/divi5/modules/sticky-video/embed-url.js.
     * The URL is assembled from an extracted id and known parameters rather
     * than passing the author's URL through, so nothing author-supplied reaches
     * an iframe src except a video id matched by the patterns above.
     *
     * @param string $source   youtube|vimeo.
     * @param string $url      Author-supplied URL.
     * @param array  $options  autoplay|muted|loop|controls|noCookie booleans.
     *
     * @return string Empty when the id could not be read.
     */
    public static function embed_url($source, $url, $options = [])
    {
        $autoplay  = !empty($options['autoplay']);
        $muted     = !empty($options['muted']);
        $loop      = !empty($options['loop']);
        $controls  = !isset($options['controls']) || $options['controls'];
        $no_cookie = !empty($options['noCookie']);

        if ('youtube' === $source) {
            $id = self::youtube_id($url);
            if ('' === $id) {
                return '';
            }

            $host   = $no_cookie ? 'www.youtube-nocookie.com' : 'www.youtube.com';
            $params = [
                'autoplay'    => $autoplay ? 1 : 0,
                'mute'        => $muted ? 1 : 0,
                'controls'    => $controls ? 1 : 0,
                'playsinline' => 1,
                'rel'         => 0,
            ];
            if ($loop) {
                // YouTube needs the playlist parameter for a single video to loop.
                $params['loop']     = 1;
                $params['playlist'] = $id;
            }

            return 'https://' . $host . '/embed/' . rawurlencode($id) . '?' . http_build_query($params);
        }

        if ('vimeo' === $source) {
            $id = self::vimeo_id($url);
            if ('' === $id) {
                return '';
            }

            $params = [
                'autoplay'    => $autoplay ? 1 : 0,
                'muted'       => $muted ? 1 : 0,
                'loop'        => $loop ? 1 : 0,
                'playsinline' => 1,
            ];
            if (!$controls) {
                $params['controls'] = 0;
            }

            return 'https://player.vimeo.com/video/' . rawurlencode($id) . '?' . http_build_query($params);
        }

        return '';
    }

    /**
     * Build the wrapper class list.
     *
     * PHP twin of src/divi5/modules/sticky-video/wrapper-class.js.
     *
     * @param array $advanced The `module.advanced` attrs array.
     *
     * @return string
     */
    public static function wrapper_class($advanced)
    {
        $pick = function ($key, array $allowed, $fallback) use ($advanced) {
            $value = $advanced[$key]['desktop']['value'] ?? $fallback;
            return in_array($value, $allowed, true) ? $value : $fallback;
        };

        $ratio = $pick('aspectRatio', self::RATIOS, '16:9');

        $classes = [
            'dtq-module',
            'dtq-sticky-video',
            'dtq-d5',
            'dtq-sticky-video--pos-' . $pick('stickPosition', self::POSITIONS, 'bottom-right'),
            'dtq-sticky-video--anim-' . $pick('stickAnimation', self::ANIMATIONS, 'slide'),
            'dtq-sticky-video--ratio-' . str_replace(':', '-', $ratio),
        ];

        $disable_on = $pick('disableOn', self::DISABLE_ON, 'phone');
        if ('none' !== $disable_on) {
            $classes[] = 'dtq-sticky-video--off-' . $disable_on;
        }

        return implode(' ', $classes);
    }

    /**
     * Server-side render for the Sticky Video module.
     *
     * @param array          $attrs    Block attributes.
     * @param string         $content  Block content (unused; no children).
     * @param WP_Block       $block    Parsed block.
     * @param ModuleElements $elements Module elements helper.
     *
     * @return string
     */
    public static function render_callback($attrs, $content, $block, $elements)
    {
        $advanced = $attrs['module']['advanced'] ?? [];

        $pick = function ($key, array $allowed, $fallback) use ($advanced) {
            $value = $advanced[$key]['desktop']['value'] ?? $fallback;
            return in_array($value, $allowed, true) ? $value : $fallback;
        };
        $on = function ($key, $fallback) use ($advanced) {
            return ($advanced[$key]['desktop']['value'] ?? $fallback) === 'on';
        };

        $source = $pick('source', self::SOURCES, 'youtube');

        // Autoplay only works muted in every current browser, so a muted-off
        // autoplay would simply not start. Forcing it is kinder than shipping a
        // setting that silently does nothing.
        $autoplay = $on('autoplay', 'off');
        $muted    = $autoplay ? true : ($advanced['muted']['desktop']['value'] ?? 'on') !== 'off';

        $media_html = '';

        if ('self' === $source) {
            $file = self::upload_src($advanced['videoFile']['desktop']['value'] ?? '');
            if ('' !== $file) {
                $media_html = sprintf(
                    '<video class="dtq-sticky-video__media" src="%1$s" preload="none" playsinline%2$s%3$s%4$s></video>',
                    esc_url($file),
                    ($advanced['controls']['desktop']['value'] ?? 'on') !== 'off' ? ' controls' : '',
                    $muted ? ' muted' : '',
                    $on('loop', 'off') ? ' loop' : ''
                );
            }
        } else {
            $embed = self::embed_url($source, $advanced['videoUrl']['desktop']['value'] ?? '', [
                'autoplay' => $autoplay,
                'muted'    => $muted,
                'loop'     => $on('loop', 'off'),
                'controls' => ($advanced['controls']['desktop']['value'] ?? 'on') !== 'off',
                'noCookie' => ($advanced['noCookie']['desktop']['value'] ?? 'on') !== 'off',
            ]);

            if ('' !== $embed) {
                // No src yet. The iframe is created by frontend.js when the
                // visitor presses play — which keeps the video host out of the
                // page until then, and is what makes the one-time relocation in
                // frontend.js safe (reparenting an iframe reloads it).
                $media_html = sprintf(
                    '<div class="dtq-sticky-video__embed" data-dtq-src="%1$s" data-dtq-title="%2$s"></div>',
                    esc_url($embed),
                    esc_attr($advanced['videoTitle']['desktop']['value'] ?? __('Embedded video', 'addons-for-divi'))
                );
            }
        }

        if ('' === $media_html) {
            $children = sprintf(
                '<div class="%1$s"><p class="dtq-sticky-video__placeholder">%2$s</p></div>',
                esc_attr(self::wrapper_class($advanced)),
                esc_html__('Add a video URL, or choose a file for a self-hosted video.', 'addons-for-divi')
            );
        } else {
            $poster = self::upload_src($advanced['poster']['desktop']['value'] ?? '');

            $facade = sprintf(
                '<button type="button" class="dtq-sticky-video__facade" aria-label="%1$s">%2$s'
                    . '<span class="dtq-sticky-video__play" aria-hidden="true"></span></button>',
                esc_attr__('Play video', 'addons-for-divi'),
                '' !== $poster
                    ? sprintf('<img src="%s" alt="" loading="lazy" decoding="async" />', esc_url($poster))
                    : ''
            );

            $close = $on('showClose', 'on')
                ? sprintf(
                    '<button type="button" class="dtq-sticky-video__close" aria-label="%s"><span aria-hidden="true">&#10005;</span></button>',
                    esc_attr__('Close video', 'addons-for-divi')
                )
                : '';

            $children = sprintf(
                '<div class="%1$s" data-dtq-sv-stick="%2$s" data-dtq-sv-close-action="%3$s"'
                    . ' data-dtq-sv-stick-position="%4$s" data-dtq-sv-stick-animation="%5$s" data-dtq-sv-disable-on="%6$s">'
                    . '<div class="dtq-sticky-video__slot"><div class="dtq-sticky-video__player">%7$s%8$s%9$s</div></div>'
                    . '</div>',
                esc_attr(self::wrapper_class($advanced)),
                esc_attr($pick('stickOn', self::STICK_ON, 'played')),
                esc_attr($pick('closeAction', self::CLOSE_ACTIONS, 'unstick')),
                esc_attr($pick('stickPosition', self::POSITIONS, 'bottom-right')),
                esc_attr($pick('stickAnimation', self::ANIMATIONS, 'slide')),
                esc_attr($pick('disableOn', self::DISABLE_ON, 'phone')),
                $media_html,
                $facade,
                $close
            );
        }

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
                'children'            => [
                    $elements->style_components(['attrName' => 'module']),
                    $children,
                ],
            ]
        );
    }
}
