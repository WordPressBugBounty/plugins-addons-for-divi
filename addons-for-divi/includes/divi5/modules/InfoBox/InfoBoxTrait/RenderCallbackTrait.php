<?php
/**
 * InfoBox: Render callback trait.
 *
 * @package DiviTorqueLite\Modules\InfoBox
 * @since   4.4.0
 */

namespace DiviTorqueLite\Modules\InfoBox\InfoBoxTrait;

if (!defined('ABSPATH')) {
    exit;
}

use ET\Builder\Packages\Module\Module;
use ET\Builder\Packages\Module\Layout\Components\ModuleElements\ModuleElements;
use WP_Block;

trait RenderCallbackTrait
{
    /**
     * Server-side render for the InfoBox module.
     *
     * Mirrors the D4 markup:
     *   <div class="dtq-info-box dtq-swapped-img-selector dtq-hover--{imgAnim}">
     *     <div class="dtq-info-box-figure"> {figure} </div>
     *     <div class="dtq-info-box-content">{title}{content}{button}</div>
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

        $main_figure = $advanced['mainFigure']['desktop']['value'] ?? 'image';
        $img_anim    = $advanced['imgAnim']['desktop']['value'] ?? 'none';
        $use_button  = ($advanced['useButton']['desktop']['value'] ?? 'off') === 'on';

        // Media figure.
        $figure_html = self::render_figure($advanced, $main_figure);

        $media_html = '';
        if (!empty($figure_html)) {
            $media_html = sprintf('<div class="dtq-info-box-figure"> %1$s</div>', $figure_html);
        }

        // Title + content elements.
        $title_html   = $elements->render(['attrName' => 'title']);
        $content_html = $elements->render(['attrName' => 'content']);

        // Button.
        $button_html = '';
        if ($use_button) {
            $button = $elements->render(['attrName' => 'button']);
            if (!empty($button)) {
                $button_html = sprintf('<div class="dtq-info-box-btn">%1$s</div>', $button);
            }
        }

        $children = sprintf(
            '<div class="dtq-info-box dtq-swapped-img-selector dtq-hover--%1$s">
                %2$s
                <div class="dtq-info-box-content">
                    %3$s %4$s %5$s
                </div>
            </div>',
            esc_attr($img_anim),
            $media_html,
            $title_html,
            $content_html,
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

    /**
     * Render the media figure (image, icon or video) markup.
     *
     * @param array  $advanced    The `module.advanced` attrs array.
     * @param string $main_figure The selected media type.
     *
     * @return string
     */
    public static function render_figure($advanced, $main_figure)
    {
        if ('image' === $main_figure) {
            $photo = $advanced['photo']['desktop']['value'] ?? '';
            if (empty($photo)) {
                return '';
            }

            return sprintf(
                '<img class="dtq-info-box-img dtq-swapped-img" src="%1$s" alt=""/>',
                esc_url($photo)
            );
        }

        if ('icon' === $main_figure) {
            $icon = $advanced['icon']['desktop']['value'] ?? '';
            if (empty($icon)) {
                return '';
            }

            if (is_array($icon)) {
                $uni  = $icon['unicode'] ?? '';
                $type = $icon['type'] ?? 'divi';
                $wt   = $icon['weight'] ?? '400';
            } else {
                $p    = explode('||', (string) $icon);
                $uni  = $p[0] ?? '';
                $type = $p[1] ?? 'divi';
                $wt   = $p[2] ?? '400';
            }

            if (empty($uni)) {
                return '';
            }

            if ('fa' === $type && function_exists('dtq_inject_fa_icons')) {
                dtq_inject_fa_icons($uni);
            }

            $font = 'fa' === $type ? 'FontAwesome' : 'ETmodules';

            return sprintf(
                '<span class="dtq-info-box-icon">
                    <i class="dtq-et-icon" style="font-family:\'%1$s\';font-weight:%2$s">%3$s</i>
                </span>',
                esc_attr($font),
                esc_attr($wt),
                dtq_resolve_icon_unicode($uni)
            );
        }

        // Video.
        $video = $advanced['video']['desktop']['value'] ?? '';
        if (empty($video)) {
            return '';
        }

        $overlay_src = $advanced['voSrc']['desktop']['value'] ?? '';

        if (function_exists('et_pb_check_oembed_provider') && false !== et_pb_check_oembed_provider(esc_url($video))) {
            $video_src = wp_oembed_get(esc_url($video));
        } else {
            $video_src = sprintf('<video controls><source type="video/mp4" src="%1$s"></video>', esc_url($video));
        }

        $overlay_html = '';
        if (!empty($overlay_src)) {
            $overlay_html = sprintf(
                '<div style="background-image: url(%1$s)" class="et_pb_video_overlay">
                    <div class="et_pb_video_overlay_hover">
                        <a href="#" class="et_pb_video_play"></a>
                    </div>
                </div>',
                esc_url($overlay_src)
            );
        }

        return sprintf(
            '<div class="dtq-content-video et_pb_video">
                <div class="et_pb_video_box dtq-content-video-wrap">
                    %1$s
                </div>
                %2$s
            </div>',
            $video_src,
            $overlay_html
        );
    }
}
