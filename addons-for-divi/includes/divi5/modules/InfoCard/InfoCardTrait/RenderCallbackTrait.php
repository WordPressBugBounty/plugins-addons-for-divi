<?php
/**
 * InfoCard: Render callback trait.
 *
 * @package DiviTorqueLite\Modules\InfoCard
 * @since   4.4.0
 */

namespace DiviTorqueLite\Modules\InfoCard\InfoCardTrait;

if (!defined('ABSPATH')) {
    exit;
}

use ET\Builder\Packages\Module\Module;
use ET\Builder\Packages\Module\Layout\Components\ModuleElements\ModuleElements;
use WP_Block;

trait RenderCallbackTrait
{
    /**
     * Server-side render for the InfoCard module.
     *
     * Mirrors the D4 markup:
     *   <div class="dtq-card dtq-swapped-img-selector dtq-hover--{anim} use-icon-{onoff}">
     *     [figure | icon] [content (title, desc, button)]
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

        $use_icon    = ($advanced['useIcon']['desktop']['value'] ?? 'off') === 'on';
        $use_badge   = ($advanced['useBadge']['desktop']['value'] ?? 'off') === 'on';
        $use_button  = ($advanced['useButton']['desktop']['value'] ?? 'off') === 'on';
        $overlay_on  = ($advanced['overlayOnHover']['desktop']['value'] ?? 'on') === 'on';
        $hover_anim  = $advanced['imageHoverAnimation']['desktop']['value'] ?? 'none';
        $use_light   = ($advanced['useLightbox']['desktop']['value'] ?? 'off') === 'on';

        // The D5 upload field stores the image as an object (`{ src, ... }`);
        // accept both that and a plain URL string.
        $photo_val = $attrs['photo']['innerContent']['desktop']['value'] ?? '';
        $photo_src = is_array($photo_val) ? ($photo_val['src'] ?? '') : $photo_val;
        $photo_alt = $attrs['photo']['advanced']['alt']['desktop']['value'] ?? '';

        // Badge.
        $badge_html = '';
        if ($use_badge) {
            $badge_html = $elements->render(['attrName' => 'badge']);
        }

        // Figure (only when icon is OFF and an image is set).
        $figure_html = '';
        if (!$use_icon && !empty($photo_src)) {
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
                }
            }

            $lightbox_class = $use_light ? ' dtq-lightbox' : '';

            $figure_html = sprintf(
                '<div class="dtq-figure dtq-card-figure">%1$s%2$s<img class="dtq-img-cover dtq-card-figure-img%5$s" data-mfp-src="%3$s" src="%3$s" alt="%4$s"/></div>',
                $badge_html,
                $overlay_html,
                esc_url($photo_src),
                esc_attr($photo_alt),
                esc_attr($lightbox_class)
            );
        }

        // Icon (only when icon is ON).
        $icon_html = '';
        if ($use_icon) {
            $icon_value = $advanced['icon']['desktop']['value'] ?? '';
            if (is_array($icon_value)) {
                $icon_uni  = $icon_value['unicode'] ?? '';
                $icon_type = $icon_value['type'] ?? 'divi';
                $icon_wt   = $icon_value['weight'] ?? '400';
            } else {
                $icon_parts = explode('||', (string) $icon_value);
                $icon_uni   = $icon_parts[0] ?? '';
                $icon_type  = $icon_parts[1] ?? 'divi';
                $icon_wt    = $icon_parts[2] ?? '400';
            }
            $icon_font = 'fa' === $icon_type ? 'FontAwesome' : 'ETmodules';
            dtq_inject_fa_icons($icon_uni . '||' . $icon_type . '||' . $icon_wt);
            $icon_html = sprintf(
                '<div class="dtq-card-icon-wrap">%1$s<div class="dtq-card-icon"><i class="dtq-et-icon" style="font-family:\'%2$s\';font-weight:%3$s;">%4$s</i></div></div>',
                $badge_html,
                esc_attr($icon_font),
                esc_attr($icon_wt),
                dtq_resolve_icon_unicode($icon_uni)
            );
        }

        // Content (title, description, button).
        $title_html = $elements->render(['attrName' => 'title']);
        $desc_html  = $elements->render(['attrName' => 'description']);

        $button_html = '';
        if ($use_button) {
            $button_html = sprintf(
                '<div class="dtq-btn-card-wrap">%1$s</div>',
                $elements->render(['attrName' => 'button'])
            );
        }

        $content_html = sprintf(
            '<div class="dtq-card-content">%1$s%2$s%3$s</div>',
            $title_html,
            $desc_html,
            $button_html
        );

        $card_classes = sprintf(
            'dtq-card dtq-swapped-img-selector dtq-hover--%1$s use-icon-%2$s',
            esc_attr($hover_anim),
            $use_icon ? 'on' : 'off'
        );

        $children = sprintf(
            '<div class="%1$s">%2$s%3$s%4$s</div>',
            esc_attr($card_classes),
            $figure_html,
            $icon_html,
            $content_html
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
