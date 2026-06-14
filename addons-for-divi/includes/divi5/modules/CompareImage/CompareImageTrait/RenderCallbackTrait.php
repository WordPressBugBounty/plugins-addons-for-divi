<?php
/**
 * Compare Image: Render callback trait.
 *
 * @package DiviTorqueLite\Modules\CompareImage
 * @since   4.4.0
 */

namespace DiviTorqueLite\Modules\CompareImage\CompareImageTrait;

if (!defined('ABSPATH')) {
    exit;
}

use ET\Builder\Packages\Module\Module;
use ET\Builder\Packages\Module\Layout\Components\ModuleElements\ModuleElements;
use WP_Block;

trait RenderCallbackTrait
{
    /**
     * Server-side render for the Before & After Slider module.
     *
     * Mirrors the D4 render() markup exactly (the D4 computed `__compare`
     * field was a VB-only mechanism and is dropped — the before/after images
     * render directly from attrs):
     *
     *   <div class="dtq-image-compare {handle_style}" data-orientation ...>
     *     <div class="dtq-image-compare-container">
     *       <img class="dtq-before-img" style="position: absolute;" .../>
     *       <img class="dtq-after-img" .../>
     *     </div>
     *   </div>
     *
     * The data attributes are the exact set the ported D4 frontend init
     * (initCompareImages in src/divi5/frontend.js) reads before calling
     * jQuery .twentytwenty(). Like D4, the slider markup is only emitted
     * when a before image is set.
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

        $before_label  = $advanced['beforeLabel']['desktop']['value'] ?? 'Before';
        $after_label   = $advanced['afterLabel']['desktop']['value'] ?? 'After';
        $orientation   = $advanced['orientation']['desktop']['value'] ?? 'horizontal';
        $offset_pct    = $advanced['offsetPct']['desktop']['value'] ?? '0.5';
        $move_on_hover = $advanced['moveOnHover']['desktop']['value'] ?? 'off';
        $overlay       = $advanced['overlay']['desktop']['value'] ?? 'on';
        $handle_style  = $advanced['handleStyle']['desktop']['value'] ?? 'handle-1';

        // The D5 upload field stores the image as an object (`{ src, ... }`);
        // accept both that and a plain URL string.
        $before_val = $attrs['beforeImage']['innerContent']['desktop']['value'] ?? '';
        $before_img = is_array($before_val) ? ($before_val['src'] ?? '') : $before_val;
        $after_val  = $attrs['afterImage']['innerContent']['desktop']['value'] ?? '';
        $after_img  = is_array($after_val) ? ($after_val['src'] ?? '') : $after_val;

        $children = '';

        if (!empty($before_img)) {
            // The compare slider needs the jQuery twentytwenty plugin (and
            // its jquery.event.move dependency) on the front end only — the
            // VB renders a static preview instead.
            if (function_exists('wp_enqueue_script')) {
                wp_enqueue_script('divi-torque-lite-event-move');
                wp_enqueue_script('divi-torque-lite-twentytwenty');
            }

            $images = sprintf(
                '<img class="dtq-before-img" style="position: absolute;" src="%1$s" alt="%3$s"/><img class="dtq-after-img" src="%2$s" alt="%4$s"/>',
                esc_url($before_img),
                esc_url($after_img),
                esc_attr($before_label),
                esc_attr($after_label)
            );

            $children = sprintf(
                '<div class="dtq-image-compare %2$s" data-orientation="%3$s" data-moveonhover="%4$s" data-beforelabel="%5$s" data-afterlabel="%6$s" data-offsetpct="%7$s" data-overlay="%8$s"><div class="dtq-image-compare-container">%1$s</div></div>',
                $images,
                esc_attr($handle_style),
                esc_attr($orientation),
                esc_attr($move_on_hover),
                esc_attr($before_label),
                esc_attr($after_label),
                esc_attr($offset_pct),
                esc_attr($overlay)
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
                'children'            => $children,
            ]
        );
    }
}
