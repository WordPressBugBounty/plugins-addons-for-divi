<?php
/**
 * ScrollImage: Render callback trait.
 *
 * @package DiviTorqueLite\Modules\ScrollImage
 * @since   4.4.0
 */

namespace DiviTorqueLite\Modules\ScrollImage\ScrollImageTrait;

if (!defined('ABSPATH')) {
    exit;
}

use ET\Builder\Packages\Module\Module;
use ET\Builder\Packages\Module\Layout\Components\ModuleElements\ModuleElements;
use WP_Block;

trait RenderCallbackTrait
{
    /**
     * Render the direction icon (or icon image) markup.
     *
     * Mirrors the D4 `render_icon()` output (the font icon is rendered via an
     * inner `<i>` carrying the icon font inline, like IconBox).
     *
     * @param array $advanced The `module.advanced` attrs array.
     * @return string
     */
    public static function render_icon($advanced)
    {
        $show_icon  = ($advanced['showIcon']['desktop']['value'] ?? 'off') === 'on';
        $use_image  = ($advanced['useImage']['desktop']['value'] ?? 'off') === 'on';
        $icon_value = $advanced['icon']['desktop']['value'] ?? '';
        $icon_image = $advanced['iconImage']['desktop']['value'] ?? '';

        if (!$show_icon) {
            return '';
        }

        if ($use_image) {
            $img = '';
            if (!empty($icon_image)) {
                $img = sprintf('<img src="%1$s" alt="" />', esc_url($icon_image));
            }
            return sprintf(
                '<div class="dtq-scroll-image-icon"><div class="dtq-scroll-image-icon-el">%1$s</div></div>',
                $img
            );
        }

        $inner = '';
        if (!empty($icon_value)) {
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
            if (!empty($uni)) {
                $font = 'fa' === $type ? 'FontAwesome' : 'ETmodules';
                dtq_inject_fa_icons($uni . '||' . $type . '||' . $wt);
                $inner = sprintf(
                    '<i class="dtq-icon dtq-et-icon" style="font-family:\'%1$s\';font-weight:%2$s">%3$s</i>',
                    $font,
                    esc_attr($wt),
                    dtq_resolve_icon_unicode($uni)
                );
            }
        }

        return sprintf(
            '<div class="dtq-scroll-image-icon dtq-et-font-icon"><div class="dtq-scroll-image-icon-el">%1$s</div></div>',
            $inner
        );
    }

    /**
     * Render the overlay markup.
     *
     * @param array $advanced The `module.advanced` attrs array.
     * @return string
     */
    public static function render_overlay($advanced)
    {
        $use_overlay = ($advanced['useOverlay']['desktop']['value'] ?? 'off') === 'on';

        if (!$use_overlay) {
            return '';
        }

        return '<div class="dtq-scroll-image-overlay"></div>';
    }

    /**
     * Server-side render for the ScrollImage module.
     *
     * Mirrors the D4 markup and the VB edit component:
     *   <div class="dtq-module dtq-scroll-image" data-dir-hover data-dir-scroll>
     *     [icon]
     *     <div class="scroll-figure-wrap">
     *       [overlay]
     *       <img class="dtq-scroll-image-el" src alt />
     *     </div>
     *   </div>
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

        $scroll_type       = $advanced['scrollType']['desktop']['value'] ?? 'on_hover';
        $scroll_dir_hover  = $advanced['scrollDirHover']['desktop']['value'] ?? 'Y_btt';
        $scroll_dir_scroll = $advanced['scrollDirScroll']['desktop']['value'] ?? 'vertical';

        // Migrated uploads come through as D5 image objects (`{ src, ... }`);
        // accept both that and a plain URL string.
        $image_val = $attrs['image']['innerContent']['desktop']['value'] ?? '';
        $image_src = is_array($image_val) ? ($image_val['src'] ?? '') : $image_val;
        $image_alt = $attrs['image']['advanced']['alt']['desktop']['value'] ?? '';

        if (!empty($image_src)) {
            $children = sprintf(
                '<div class="dtq-module dtq-scroll-image" data-dir-hover="%5$s" data-dir-scroll="%6$s">%1$s<div class="scroll-figure-wrap">%2$s<img class="dtq-scroll-image-el" src="%3$s" alt="%4$s" /></div></div>',
                self::render_icon($advanced),
                self::render_overlay($advanced),
                esc_url($image_src),
                esc_attr($image_alt),
                esc_attr('on_hover' === $scroll_type ? $scroll_dir_hover : 'none'),
                esc_attr('on_scroll' === $scroll_type ? $scroll_dir_scroll : 'none')
            );
        } else {
            $children = '<div class="dtq-module dtq-scroll-image"></div>';
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
