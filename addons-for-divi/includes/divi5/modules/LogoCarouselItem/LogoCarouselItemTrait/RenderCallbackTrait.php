<?php
/**
 * LogoCarouselItem: Render callback trait.
 *
 * @package DiviTorqueLite\Modules\LogoCarouselItem
 * @since   4.5.0
 */

namespace DiviTorqueLite\Modules\LogoCarouselItem\LogoCarouselItemTrait;

if (!defined('ABSPATH')) {
    exit;
}

use ET\Builder\Packages\Module\Module;

trait RenderCallbackTrait
{
    /**
     * Render a Divi 5 icon-picker value as inline <i> markup.
     *
     * @param mixed  $icon      Icon value.
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
     * Server-side render for a logo slide.
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

        $is_link    = ($advanced['isLink']['desktop']['value'] ?? 'off') === 'on';
        $overlay_on = ($advanced['overlayOnHover']['desktop']['value'] ?? 'off') === 'on';

        // Logo image. The D5 upload field stores an object (`{ src, ... }`).
        $logo_val = $attrs['logo']['innerContent']['desktop']['value'] ?? '';
        $logo_src = is_array($logo_val) ? ($logo_val['src'] ?? '') : $logo_val;
        $logo_alt = $attrs['logo']['advanced']['alt']['desktop']['value'] ?? '';

        $img = '';
        if (!empty($logo_src)) {
            $img = sprintf(
                '<img class="dtq-swapped-img" src="%1$s" alt="%2$s"/>',
                esc_url($logo_src),
                esc_attr($logo_alt)
            );
        }

        // Optional link wrapper. D4 stored target/nofollow in a single
        // pipe-string `link_options` ("target|rel"); honour it as a fallback
        // when the migrated D5 toggles aren't explicitly set.
        if ($is_link) {
            $legacy        = explode('|', (string) ($advanced['linkOptions']['desktop']['value'] ?? ''));
            $legacy_target = ($legacy[0] ?? 'off') === 'on';
            $legacy_nf     = ($legacy[1] ?? 'off') === 'on';
            $url           = $advanced['linkUrl']['desktop']['value'] ?? '';
            $target        = (($advanced['linkTarget']['desktop']['value'] ?? 'off') === 'on' || $legacy_target) ? ' target="_blank"' : '';
            $nofollow      = (($advanced['linkNofollow']['desktop']['value'] ?? 'off') === 'on' || $legacy_nf) ? ' rel="nofollow"' : '';
            $logo     = sprintf('<a href="%1$s"%2$s%3$s>%4$s</a>', esc_url($url ?: '#'), $target, $nofollow, $img);
        } else {
            $logo = sprintf('<div class="dtq-lightbox-ctrl">%1$s</div>', $img);
        }

        // Overlay.
        $overlay = '';
        if ($overlay_on) {
            $overlay_icon  = $advanced['overlayIcon']['desktop']['value'] ?? '';
            $overlay_glyph = self::render_icon($overlay_icon, 'dtq-overlay-icon dtq-et-icon');
            $overlay       = sprintf('<div class="dtq-overlay">%1$s</div>', $overlay_glyph);
        }

        $item_html = sprintf(
            '<div class="dtq-carousel-item dtq-logo-carousel-item dtq-swapped-img-selector">%1$s%2$s</div>',
            $overlay,
            $logo
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
