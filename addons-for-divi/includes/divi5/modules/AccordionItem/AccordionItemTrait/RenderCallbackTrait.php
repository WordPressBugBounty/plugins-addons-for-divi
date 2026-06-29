<?php
/**
 * AccordionItem: Render callback trait.
 *
 * @package DiviTorqueLite\Modules\AccordionItem
 * @since   4.7.0
 */

namespace DiviTorqueLite\Modules\AccordionItem\AccordionItemTrait;

if (!defined('ABSPATH')) {
    exit;
}

use ET\Builder\Packages\Module\Module;

trait RenderCallbackTrait
{
    /**
     * Server-side render for an accordion item.
     *
     * Title + content render as direct children of the order-class div so the
     * CSS `.dtq-accordion__item--open > .dtq-accordion__content` selector
     * resolves the same way the builder renders it:
     *   <div class="dtq_accordion_item dtq-accordion__item[ ...--open]">
     *     <h3 class="dtq-accordion__title">…</h3>
     *     <div class="dtq-accordion__content">…</div>
     *   </div>
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

        // Disabled items are omitted from the front end entirely.
        if (($advanced['disableItem']['desktop']['value'] ?? 'off') === 'on') {
            return '';
        }

        // Optional anchor target for deep-linking (#anchor opens + scrolls here).
        $anchor_id   = trim((string) ($advanced['anchorId']['desktop']['value'] ?? ''));
        $anchor_html = '';
        if ('' !== $anchor_id) {
            $anchor_html = sprintf(
                '<span class="dtq-accordion__anchor" id="%1$s" aria-hidden="true"></span>',
                esc_attr(sanitize_title($anchor_id))
            );
        }

        // Title (optionally wrapped in a link).
        $title_el   = $elements->render(['attrName' => 'title', 'tagName' => 'h3']);
        $title_link = trim((string) ($advanced['titleLink']['desktop']['value'] ?? ''));
        if ('' !== $title_link) {
            $title_el = sprintf(
                '<a class="dtq-accordion__title-anchor" href="%1$s">%2$s</a>',
                esc_url($title_link),
                $title_el
            );
        }

        // Optional subtitle.
        $subtitle_val  = trim((string) ($attrs['subtitle']['innerContent']['desktop']['value'] ?? ''));
        $subtitle_html = '' !== $subtitle_val ? $elements->render(['attrName' => 'subtitle']) : '';

        // Optional header media (icon or image with focal point + flip).
        $media_html = '';
        $media_type = $advanced['headerMediaType']['desktop']['value'] ?? 'none';
        if ('icon' === $media_type) {
            $icon_val = $advanced['headerIcon']['desktop']['value'] ?? '';
            if (is_array($icon_val)) {
                $uni  = $icon_val['unicode'] ?? '';
                $type = $icon_val['type'] ?? 'divi';
                $wt   = $icon_val['weight'] ?? '400';
            } else {
                $parts = explode('||', (string) $icon_val);
                $uni   = $parts[0] ?? '';
                $type  = $parts[1] ?? 'divi';
                $wt    = $parts[2] ?? '400';
            }
            if ('' !== $uni) {
                $font = 'fa' === $type ? 'FontAwesome' : 'ETmodules';
                if (function_exists('dtq_inject_fa_icons')) {
                    dtq_inject_fa_icons($uni . '||' . $type . '||' . $wt);
                }
                $glyph = function_exists('dtq_resolve_icon_unicode') ? dtq_resolve_icon_unicode($uni) : $uni;
                $media_html = sprintf(
                    '<div class="dtq-accordion__media dtq-accordion__media--icon"><i class="dtq-icon" style="font-family:\'%1$s\';font-weight:%2$s">%3$s</i></div>',
                    esc_attr($font),
                    esc_attr($wt),
                    $glyph
                );
            }
        } elseif ('image' === $media_type) {
            $img     = $advanced['headerImage']['desktop']['value'] ?? '';
            $img_url = is_array($img) ? ($img['url'] ?? '') : $img;
            if ('' !== $img_url) {
                $focal_x = $advanced['focalX']['desktop']['value'] ?? '50%';
                $focal_y = $advanced['focalY']['desktop']['value'] ?? '50%';
                $size    = $advanced['headerImageSize']['desktop']['value'] ?? '48px';
                $flip_h  = ($advanced['flipH']['desktop']['value'] ?? 'off') === 'on';
                $flip_v  = ($advanced['flipV']['desktop']['value'] ?? 'off') === 'on';
                $transform = ($flip_h || $flip_v)
                    ? sprintf('transform:scale(%1$d,%2$d);', $flip_h ? -1 : 1, $flip_v ? -1 : 1)
                    : '';
                $style = sprintf(
                    'width:%1$s;height:%1$s;object-fit:cover;object-position:%2$s %3$s;%4$s',
                    esc_attr($size),
                    esc_attr($focal_x),
                    esc_attr($focal_y),
                    $transform
                );
                $media_html = sprintf(
                    '<div class="dtq-accordion__media"><img src="%1$s" alt="" loading="lazy" style="%2$s" /></div>',
                    esc_url($img_url),
                    esc_attr($style)
                );
            }
        }

        $header_html = sprintf(
            '<div class="dtq-accordion__title">%1$s<div class="dtq-accordion__heading">%2$s%3$s</div></div>',
            $media_html,
            $title_el,
            $subtitle_html
        );

        // Optional read-more button (rendered only when it has text).
        $readmore_text = trim((string) ($attrs['readMore']['innerContent']['desktop']['value']['text'] ?? ''));
        $readmore_html = '';
        if ('' !== $readmore_text) {
            $readmore_html = sprintf(
                '<div class="dtq-accordion__readmore-wrap">%1$s</div>',
                $elements->render(['attrName' => 'readMore'])
            );
        }

        // Content + read-more + always-rendered close button (parent class controls its display).
        $close_html = sprintf(
            '<button type="button" class="dtq-accordion__close">%1$s</button>',
            esc_html__('Close', 'divi-torque-lite')
        );
        $content_html = sprintf(
            '<div class="dtq-accordion__content">%1$s%2$s%3$s</div>',
            $elements->render(['attrName' => 'content']),
            $readmore_html,
            $close_html
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
                    $anchor_html,
                    $header_html,
                    $content_html,
                ],
            ]
        );
    }
}
