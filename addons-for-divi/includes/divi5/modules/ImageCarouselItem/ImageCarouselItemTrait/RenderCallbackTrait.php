<?php
/**
 * ImageCarouselItem: Render callback trait.
 *
 * @package DiviTorqueLite\Modules\ImageCarouselItem
 * @since   4.5.0
 */

namespace DiviTorqueLite\Modules\ImageCarouselItem\ImageCarouselItemTrait;

if (!defined('ABSPATH')) {
    exit;
}

use ET\Builder\Packages\Module\Module;

trait RenderCallbackTrait
{
    /**
     * Render a Divi 5 icon-picker value as inline <i> markup.
     *
     * @param mixed  $icon      Icon value: array { unicode, type, weight } or "unicode||type||weight".
     * @param string $css_class Wrapper class.
     *
     * @return string
     */
    public static function render_icon($icon, $css_class)
    {
        if (empty($icon)) {
            return '';
        }

        if (is_array($icon)) {
            $uni  = $icon['unicode'] ?? '';
            $type = $icon['type'] ?? 'divi';
            $wt   = $icon['weight'] ?? '400';
        } else {
            $parts = explode('||', (string) $icon);
            $uni   = $parts[0] ?? '';
            $type  = $parts[1] ?? 'divi';
            $wt    = $parts[2] ?? '400';
        }

        if ('' === $uni) {
            return '';
        }

        $font = 'fa' === $type ? 'FontAwesome' : 'ETmodules';

        if (function_exists('dtq_inject_fa_icons')) {
            dtq_inject_fa_icons($uni . '||' . $type . '||' . $wt);
        }

        $glyph = function_exists('dtq_resolve_icon_unicode') ? dtq_resolve_icon_unicode($uni) : $uni;

        return sprintf(
            '<i class="%1$s" style="font-family:\'%2$s\';font-weight:%3$s">%4$s</i>',
            esc_attr($css_class),
            esc_attr($font),
            esc_attr($wt),
            $glyph
        );
    }

    /**
     * Server-side render for the Image Carousel slide.
     *
     * @param array  $attrs    Block attributes.
     * @param string $content  Block content (unused).
     * @param object $block    Parsed block.
     * @param object $elements Module elements helper.
     *
     * @return string
     */
    public static function render_callback($attrs, $content, $block, $elements)
    {
        $advanced = $attrs['module']['advanced'] ?? [];

        $card_preset        = $advanced['cardPreset']['desktop']['value'] ?? 'custom';
        $content_type       = $advanced['contentType']['desktop']['value'] ?? 'normal';
        $content_position   = $advanced['contentPosition']['desktop']['value'] ?? 'bottom';
        $content_alignment  = $advanced['contentAlignment']['desktop']['value'] ?? 'left';
        $image_hover_anim   = $advanced['imageHoverAnimation']['desktop']['value'] ?? 'none';
        $overlay_on_hover   = $advanced['overlayOnHover']['desktop']['value'] ?? 'off';
        $use_button         = $advanced['useButton']['desktop']['value'] ?? 'off';

        // Photo. The D5 upload field stores the image as an object
        // (`{ src, ... }`); accept both that and a plain URL string.
        $photo_val = $attrs['photo']['innerContent']['desktop']['value'] ?? '';
        $photo_src = is_array($photo_val) ? ($photo_val['src'] ?? '') : $photo_val;
        $photo_alt = $attrs['photo']['advanced']['alt']['desktop']['value'] ?? '';
        $img       = '';
        if (!empty($photo_src)) {
            $img = sprintf(
                '<img class="dtq-swapped-img" src="%1$s" alt="%2$s"/>',
                esc_url($photo_src),
                esc_attr($photo_alt)
            );
        }

        // Overlay.
        $overlay = '';
        if ('on' === $overlay_on_hover) {
            $overlay_icon  = $advanced['overlayIcon']['desktop']['value'] ?? '';
            $overlay_glyph = self::render_icon($overlay_icon, 'dtq-overlay-icon dtq-et-icon');
            $overlay       = sprintf('<div class="dtq-overlay">%1$s</div>', $overlay_glyph);
        }

        $figure = sprintf('<figure class="dtq-figure">%1$s%2$s</figure>', $overlay, $img);

        // Texts.
        $title      = $elements->render(['attrName' => 'title']);
        $sub_title  = $elements->render(['attrName' => 'subTitle']);
        $title_text = $attrs['title']['innerContent']['desktop']['value'] ?? '';
        $sub_text   = $attrs['subTitle']['innerContent']['desktop']['value'] ?? '';

        // Button.
        $button = '';
        if ('on' === $use_button) {
            $button_text   = $advanced['buttonText']['desktop']['value'] ?? 'Click Here';
            $button_link   = $advanced['buttonLink']['desktop']['value'] ?? '';
            $button_target = ($advanced['buttonTarget']['desktop']['value'] ?? 'off') === 'on' ? ' target="_blank"' : '';
            $button_icon   = $advanced['buttonIcon']['desktop']['value'] ?? '';
            $button_glyph  = self::render_icon($button_icon, 'dtq-btn-icon');

            $button = sprintf(
                '<div class="dtq-btn-wrap"><a class="et_pb_button dtq-btn-default dtq-btn-img-carousel" href="%1$s"%2$s>%3$s%4$s</a></div>',
                esc_url($button_link ?: '#'),
                $button_target,
                $button_glyph,
                esc_html($button_text)
            );
        }

        // Content block — only when a title or subtitle exists (mirrors D4).
        $content_block = '';
        if ('' !== $title_text || '' !== $sub_text) {
            $content_block = sprintf(
                '<div class="content content--%1$s content--%2$s"><div class="content-inner">%3$s%4$s%5$s</div></div>',
                esc_attr($content_alignment),
                esc_attr($content_type),
                $title,
                $sub_title,
                $button
            );
        }

        // Order: content above figure when normal + top, else below.
        $is_bottom = !('normal' === $content_type && 'top' === $content_position);

        $item_html = sprintf(
            '<div class="dtq-carousel-item dtq-image-carousel-item dtq-swapped-img-selector dtq-card--%5$s dtq-hover--%1$s">%2$s%3$s%4$s</div>',
            esc_attr($image_hover_anim),
            $is_bottom ? '' : $content_block,
            $figure,
            $is_bottom ? $content_block : '',
            esc_attr($card_preset)
        );

        return Module::render(
            [
                'orderIndex'          => $block->parsed_block['orderIndex'] ?? 0,
                'storeInstance'       => $block->parsed_block['storeInstance'] ?? null,
                'id'                  => $block->parsed_block['id'],
                'name'                => $block->block_type->name,
                'moduleCategory'      => $block->block_type->category,
                'attrs'               => $attrs,
                'elements'            => $elements,
                'classnamesFunction'  => [self::class, 'module_classnames'],
                'stylesComponent'     => [self::class, 'module_styles'],
                'scriptDataComponent' => [self::class, 'module_script_data'],
                'children'            => [
                    $elements->style_components(['attrName' => 'module']),
                    $item_html,
                ],
            ]
        );
    }
}
