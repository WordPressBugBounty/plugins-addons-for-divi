<?php
/**
 * Video Modal: Render callback trait.
 *
 * @package DiviTorqueLite\Modules\VideoModal
 * @since   4.4.0
 */

namespace DiviTorqueLite\Modules\VideoModal\VideoModalTrait;

if (!defined('ABSPATH')) {
    exit;
}

use ET\Builder\Packages\Module\Module;
use ET\Builder\Packages\Module\Layout\Components\ModuleElements\ModuleElements;
use WP_Block;

trait RenderCallbackTrait
{
    /**
     * Embedded SVG play icons for the trigger. Copied verbatim from the D4
     * `render_trigger()` icon map (includes/modules/divi-4/VideoModal/
     * VideoModal.php). Keep this in lockstep with the JS twin in
     * src/divi5/modules/video-modal/play-icons.js — the markup must match
     * byte-for-byte so VB and front end render identically.
     *
     * @return array
     */
    public static function play_icons()
    {
        return [
            '1' => '<svg xmlns="http://www.w3.org/2000/svg" x="0px" y="0px" viewBox="0 0 511.999 511.999"><g><path d="M443.86,196.919L141.46,10.514C119.582-2.955,93.131-3.515,70.702,9.016c-22.429,12.529-35.819,35.35-35.819,61.041  v371.112c0,38.846,31.3,70.619,69.77,70.829c0.105,0,0.21,0.001,0.313,0.001c12.022-0.001,24.55-3.769,36.251-10.909 c9.413-5.743,12.388-18.029,6.645-27.441c-5.743-9.414-18.031-12.388-27.441-6.645c-5.473,3.338-10.818,5.065-15.553,5.064 c-14.515-0.079-30.056-12.513-30.056-30.898V70.058c0-11.021,5.744-20.808,15.364-26.183c9.621-5.375,20.966-5.135,30.339,0.636 l302.401,186.405c9.089,5.596,14.29,14.927,14.268,25.601c-0.022,10.673-5.261,19.983-14.4,25.56L204.147,415.945 c-9.404,5.758-12.36,18.049-6.602,27.452c5.757,9.404,18.048,12.36,27.452,6.602l218.611-133.852  c20.931-12.769,33.457-35.029,33.507-59.55C477.165,232.079,464.729,209.767,443.86,196.919z"/></g></svg>',

            '2' => '<svg xmlns="http://www.w3.org/2000/svg" x="0px" y="0px" viewBox="0 0 494.148 494.148"><g><g><path d="M405.284,201.188L130.804,13.28C118.128,4.596,105.356,0,94.74,0C74.216,0,61.52,16.472,61.52,44.044v406.124 c0,27.54,12.68,43.98,33.156,43.98c10.632,0,23.2-4.6,35.904-13.308l274.608-187.904c17.66-12.104,27.44-28.392,27.44-45.884 C432.632,229.572,422.964,213.288,405.284,201.188z"/> </g></g></svg>',

            '3' => '<svg viewBox="0 0 494.942 494.942" xmlns="http://www.w3.org/2000/svg"><path d="m35.353 0 424.236 247.471-424.236 247.471z"/></svg>',

            '4' => '<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 60 60"><path d="M30,0C13.458,0,0,13.458,0,30s13.458,30,30,30s30-13.458,30-30S46.542,0,30,0z M45.563,30.826l-22,15 C23.394,45.941,23.197,46,23,46c-0.16,0-0.321-0.038-0.467-0.116C22.205,45.711,22,45.371,22,45V15c0-0.371,0.205-0.711,0.533-0.884 c0.328-0.174,0.724-0.15,1.031,0.058l22,15C45.836,29.36,46,29.669,46,30S45.836,30.64,45.563,30.826z"/> <g></g></svg>',

            '5' => '<svg xmlns="http://www.w3.org/2000/svg" x="0px" y="0px" viewBox="0 0 485 485"><g><path d="M413.974,71.026C368.171,25.225,307.274,0,242.5,0S116.829,25.225,71.026,71.026C25.225,116.829,0,177.726,0,242.5 s25.225,125.671,71.026,171.474C116.829,459.775,177.726,485,242.5,485s125.671-25.225,171.474-71.026 C459.775,368.171,485,307.274,485,242.5S459.775,116.829,413.974,71.026z M242.5,455C125.327,455,30,359.673,30,242.5 S125.327,30,242.5,30S455,125.327,455,242.5S359.673,455,242.5,455z"/><polygon points="181.062,336.575 343.938,242.5 181.062,148.425"/></g></svg>',

            '6' => '<svg xmlns="http://www.w3.org/2000/svg" x="0px" y="0px" viewBox="0 0 310 310"><g><path d="M297.917,64.645c-11.19-13.302-31.85-18.728-71.306-18.728H83.386c-40.359,0-61.369,5.776-72.517,19.938 C0,79.663,0,100.008,0,128.166v53.669c0,54.551,12.896,82.248,83.386,82.248h143.226c34.216,0,53.176-4.788,65.442-16.527 C304.633,235.518,310,215.863,310,181.835v-53.669C310,98.471,309.159,78.006,297.917,64.645z M199.021,162.41l-65.038,33.991 c-1.454,0.76-3.044,1.137-4.632,1.137c-1.798,0-3.592-0.484-5.181-1.446c-2.992-1.813-4.819-5.056-4.819-8.554v-67.764 c0-3.492,1.822-6.732,4.808-8.546c2.987-1.814,6.702-1.938,9.801-0.328l65.038,33.772c3.309,1.718,5.387,5.134,5.392,8.861 C204.394,157.263,202.325,160.684,199.021,162.41z"/></g></svg>',
        ];
    }

    /**
     * Normalize a D5 upload field value: migrated D4 values are plain URL
     * strings, native D5 values may be objects ({ src, ... }).
     *
     * @param mixed $val Upload field value.
     *
     * @return string
     */
    public static function upload_src($val)
    {
        if (is_array($val)) {
            return $val['src'] ?? '';
        }
        return (string) $val;
    }

    /**
     * Server-side render for the Video Modal module.
     *
     * Mirrors the D4 markup (and the VB edit component) exactly:
     *   <div class="dtq-module dtq-video-popup">
     *     [inline modal (self-hosted only, mfp-hide)]
     *     <div class="dtq-video-popup-wrap">
     *       <a class="dtq-video-popup-trigger dtq-popup-{type}" data-order=".."
     *          data-type="{type}" href="{video_link}" [data-mfp-src="#dtq-video-popup-.."]>
     *         [icon span][text span]
     *       </a>
     *     </div>
     *     [overlay figure]
     *   </div>
     *
     * The per-instance order number comes from the block orderIndex — the
     * same number that forms the order class (.dtq_video_modal_{n}) — so the
     * trigger's data-order matches the body.dtq-video-popup-{n} popup
     * selectors that ModuleStylesTrait emits (the popup DOM is appended to
     * <body>, outside the module wrapper; the front-end magnific callbacks
     * toggle that body class per module, exactly like D4).
     *
     * @param array          $attrs    Block attributes.
     * @param string         $content  Block content (unused).
     * @param WP_Block       $block    Parsed block.
     * @param ModuleElements $elements Module elements helper.
     *
     * @return string
     */
    public static function render_callback($attrs, $content, $block, $elements)
    {
        $advanced = $attrs['module']['advanced'] ?? [];

        $use_overlay     = $advanced['useOverlay']['desktop']['value'] ?? 'on';
        $trigger_element = $advanced['triggerElement']['desktop']['value'] ?? 'icon';
        $icon            = $advanced['icon']['desktop']['value'] ?? '1';
        $type            = $advanced['type']['desktop']['value'] ?? 'yt';
        $video_link      = $advanced['videoLink']['desktop']['value'] ?? '';
        $video           = self::upload_src($advanced['video']['desktop']['value'] ?? '');
        $custom_icon     = self::upload_src($advanced['customPlayIcon']['desktop']['value'] ?? '');

        $image_src = self::upload_src($attrs['image']['innerContent']['desktop']['value'] ?? '');
        $image_alt = $attrs['image']['advanced']['alt']['desktop']['value'] ?? '';

        // Per-instance order number (matches the order class suffix).
        $order_number = $block->parsed_block['orderIndex'] ?? 0;

        // Magnific popup is always needed on the front end (both source
        // paths) — the library handles are registered in includes/assets.php.
        if (function_exists('wp_enqueue_script')) {
            wp_enqueue_script('divi-torque-lite-magnific-popup');
            wp_enqueue_style('divi-torque-lite-magnific-popup');
        }

        // Trigger inner parts (mirrors D4 render_trigger()).
        $icon_span = '';
        if ('text' !== $trigger_element) {
            $icons     = self::play_icons();
            $icon_html = 'custom' === $icon
                ? sprintf('<img src="%1$s" alt="custom-icon"/>', esc_url($custom_icon))
                : ($icons[$icon] ?? $icons['1']);
            $icon_span = sprintf('<span class="dtq-video-popup-icon">%1$s</span>', $icon_html);
        }

        $text_span = '';
        if ('icon' !== $trigger_element) {
            $text_span = $elements->render(['attrName' => 'text']);
        }

        // Self-hosted inline modal (type=video only). Hidden via mfp-hide;
        // magnific clones it into the popup on the front end.
        $inline_modal = '';
        $data_modal   = '';
        if ('video' === $type) {
            $inline_modal = sprintf(
                '<div class="mfp-hide dtq-modal" id="dtq-video-popup-%1$s" data-order="%1$s"><div class="dtq-video-wrap"><video controls><source type="video/mp4" src="%2$s"></video></div></div>',
                esc_attr($order_number),
                esc_url($video)
            );
            $data_modal = sprintf(' data-mfp-src="#dtq-video-popup-%1$s"', esc_attr($order_number));
        }

        // Overlay image figure (mirrors D4: rendered whenever use_overlay is on).
        $img_overlay = '';
        if ('on' === $use_overlay) {
            $img_overlay = sprintf(
                '<div class="dtq-video-popup-figure"><img src="%1$s" alt="%2$s"/></div>',
                esc_url($image_src),
                esc_attr($image_alt)
            );
        }

        // Mirrors D4: youtu.be short links are rewritten to the full URL so
        // the magnific youtube pattern matches.
        if (false !== strpos($video_link, 'youtu.be')) {
            $video_link = str_replace('youtu.be/', 'youtube.com/watch?v=', $video_link);
        }

        $children = sprintf(
            '<div class="dtq-module dtq-video-popup">%1$s<div class="dtq-video-popup-wrap"><a class="dtq-video-popup-trigger dtq-popup-%2$s" data-order="%3$s" data-type="%2$s" href="%4$s"%5$s>%6$s%7$s</a></div>%8$s</div>',
            $inline_modal,
            esc_attr($type),
            esc_attr($order_number),
            esc_url($video_link),
            $data_modal,
            $icon_span,
            $text_span,
            $img_overlay
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
