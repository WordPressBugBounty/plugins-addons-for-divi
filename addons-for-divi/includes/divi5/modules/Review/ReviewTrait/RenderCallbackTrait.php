<?php
/**
 * Review: Render callback trait.
 *
 * @package DiviTorqueLite\Modules\Review
 * @since   4.4.0
 */

namespace DiviTorqueLite\Modules\Review\ReviewTrait;

if (!defined('ABSPATH')) {
    exit;
}

use ET\Builder\Packages\Module\Module;
use ET\Builder\Packages\Module\Layout\Components\ModuleElements\ModuleElements;
use WP_Block;

trait RenderCallbackTrait
{
    /**
     * Build the star spans for one row (inactive or active). Mirrors the D4
     * `render_stars()` output exactly: `<span class="dtq-star">{glyph}</span>`
     * repeated `scale` times, using the same unicode glyphs (☆ / ★).
     *
     * @param int    $count Star count (the rating scale).
     * @param string $glyph Star glyph.
     *
     * @return string
     */
    public static function render_stars($count, $glyph)
    {
        $stars = '';
        for ($i = 1; $i <= $count; $i++) {
            $stars .= '<span class="dtq-star">' . $glyph . '</span>';
        }
        return $stars;
    }

    /**
     * Server-side render for the Review module.
     *
     * Mirrors the D4 markup (and the VB edit component) exactly:
     *   <div class="dtq-module dtq-review dtq-swapped-img-selector dtq-hover--{anim}">
     *     [figure (badge, overlay, img)]
     *     <div class="dtq-review-content">
     *       [title]
     *       <div class="dtq-ratings dtq-flex">
     *         <div class="dtq-stars-wrap">
     *           <div class="dtq-stars-inact">☆…</div>
     *           <div class="dtq-stars-act">★…</div>
     *         </div>
     *         [(rating/scale)]
     *       </div>
     *       [description]
     *       [button wrap]
     *     </div>
     *   </div>
     *
     * The star fill width on `.dtq-stars-act` is emitted by ModuleStylesTrait
     * (D4 used an inline `--active-width` custom property instead).
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

        $scale_raw   = $advanced['scale']['desktop']['value'] ?? '5';
        $rating      = $advanced['rating']['desktop']['value'] ?? '5';
        $scale       = (int) $scale_raw;
        $show_number = ($advanced['showNumber']['desktop']['value'] ?? 'off') === 'on';
        $use_light   = ($advanced['useLightbox']['desktop']['value'] ?? 'off') === 'on';
        $use_badge   = ($advanced['useBadge']['desktop']['value'] ?? 'off') === 'on';
        $use_button  = ($advanced['useButton']['desktop']['value'] ?? 'off') === 'on';
        $overlay_on  = ($advanced['overlayOnHover']['desktop']['value'] ?? 'on') === 'on';
        $hover_anim  = $advanced['imgAnim']['desktop']['value'] ?? 'none';

        // The D5 upload field stores the image as an object (`{ src, ... }`);
        // accept both that and a plain URL string.
        $image_val = $attrs['image']['innerContent']['desktop']['value'] ?? '';
        $image_src = is_array($image_val) ? ($image_val['src'] ?? '') : $image_val;
        $image_alt = $attrs['image']['advanced']['alt']['desktop']['value'] ?? '';

        // Badge.
        $badge_html = '';
        if ($use_badge) {
            $badge_html = $elements->render(['attrName' => 'badge']);
        }

        // Figure (only when an image is set). Mirrors D4 `_render_image()`.
        $figure_html = '';
        if (!empty($image_src)) {
            $overlay_html = '';
            if ($overlay_on) {
                $overlay_icon = $advanced['overlayIcon']['desktop']['value'] ?? '';
                if (is_array($overlay_icon)) {
                    $overlay_uni  = $overlay_icon['unicode'] ?? '';
                    $overlay_type = $overlay_icon['type'] ?? 'divi';
                    $overlay_wt   = $overlay_icon['weight'] ?? '400';
                } else {
                    $overlay_parts = explode('||', (string) $overlay_icon);
                    $overlay_uni   = $overlay_parts[0] ?? '';
                    $overlay_type  = $overlay_parts[1] ?? 'divi';
                    $overlay_wt    = $overlay_parts[2] ?? '400';
                }

                if ('' !== $overlay_uni) {
                    $overlay_font = 'fa' === $overlay_type ? 'FontAwesome' : 'ETmodules';
                    dtq_inject_fa_icons($overlay_uni . '||' . $overlay_type . '||' . $overlay_wt);
                    $overlay_html = sprintf(
                        '<div class="dtq-overlay"><i class="dtq-overlay-icon" style="font-family:\'%1$s\';font-weight:%2$s;">%3$s</i></div>',
                        esc_attr($overlay_font),
                        esc_attr($overlay_wt),
                        dtq_resolve_icon_unicode($overlay_uni)
                    );
                } else {
                    // D4 renders the overlay with an empty icon element.
                    $overlay_html = '<div class="dtq-overlay"><i class="dtq-overlay-icon"></i></div>';
                }
            }

            // Lightbox: keep the exact D4 class/data hooks (`.dtq-lightbox`
            // + `data-mfp-src`) and conditionally load magnific popup on the
            // front end only (the VB renders the image plain, no popup).
            $lightbox_class = '';
            if ($use_light) {
                $lightbox_class = ' dtq-lightbox';
                if (function_exists('wp_enqueue_script')) {
                    wp_enqueue_script('divi-torque-lite-magnific-popup');
                    wp_enqueue_style('divi-torque-lite-magnific-popup');
                }
            }

            $figure_html = sprintf(
                '<div class="dtq-rating-figure">%1$s%2$s<img class="dtq-img-cover dtq-review-img%5$s" data-mfp-src="%3$s" src="%3$s" alt="%4$s"/></div>',
                $badge_html,
                $overlay_html,
                esc_url($image_src),
                esc_attr($image_alt),
                esc_attr($lightbox_class)
            );
        }

        // Rating number (mirrors D4 `render_rarings_number()`).
        $number_html = '';
        if ($show_number) {
            $number_html = '<div class="dtq-ratings-number">(' . esc_html($rating) . '/' . esc_html($scale_raw) . ')</div>';
        }

        // Title + description.
        $title_html = $elements->render(['attrName' => 'title']);
        $desc_html  = $elements->render(['attrName' => 'description']);

        // Button.
        $button_html = '';
        if ($use_button) {
            $button_html = sprintf(
                '<div class="dtq-rating-btn-wrap">%1$s</div>',
                $elements->render(['attrName' => 'button'])
            );
        }

        $ratings_html = sprintf(
            '<div class="dtq-ratings dtq-flex"><div class="dtq-stars-wrap"><div class="dtq-stars-inact">%1$s</div><div class="dtq-stars-act">%2$s</div></div>%3$s</div>',
            self::render_stars($scale, '☆'),
            self::render_stars($scale, '★'),
            $number_html
        );

        $children = sprintf(
            '<div class="dtq-module dtq-review dtq-swapped-img-selector dtq-hover--%1$s">%2$s<div class="dtq-review-content">%3$s%4$s%5$s%6$s</div></div>',
            esc_attr($hover_anim),
            $figure_html,
            $title_html,
            $ratings_html,
            $desc_html,
            $button_html
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
