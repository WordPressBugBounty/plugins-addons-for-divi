<?php
/**
 * ImageAccordionItem: Render callback trait.
 *
 * @package DiviTorqueLite\Modules\ImageAccordionItem
 * @since   4.9.0
 */

namespace DiviTorqueLite\Modules\ImageAccordionItem\ImageAccordionItemTrait;

if (!defined('ABSPATH')) {
    exit;
}

use ET\Builder\Packages\Module\Module;
use DiviTorqueLite\Modules\Shared\ButtonElement;
use ET\Builder\Packages\Module\Layout\Components\ModuleElements\ModuleElements;
use WP_Block;

trait RenderCallbackTrait
{
    /**
     * Server-side render for one Image Accordion panel.
     *
     * Mirrors src/divi5/modules/image-accordion-item/edit.jsx. The order-class
     * div this returns into IS the panel; ModuleClassnamesTrait adds the panel
     * classes, so there is no extra wrapper.
     *
     * @param array          $attrs    Block attributes.
     * @param string         $content  Block content (unused; no grandchildren).
     * @param WP_Block       $block    Parsed block.
     * @param ModuleElements $elements Module elements helper.
     *
     * @return string
     */
    public static function render_callback($attrs, $content, $block, $elements)
    {
        $advanced = $attrs['module']['advanced'] ?? [];

        // Divi 5 stores an upload as an object; Divi 4 stored a bare URL.
        $image_raw = $advanced['image']['desktop']['value'] ?? '';
        $src       = is_array($image_raw) ? ($image_raw['src'] ?? '') : (string) $image_raw;
        $alt       = (string) ($advanced['imageAlt']['desktop']['value'] ?? '');

        $media_html = sprintf(
            '<div class="dtq-image-accordion__media">%s</div>',
            '' !== $src
                ? sprintf(
                    '<img src="%1$s" alt="%2$s" loading="lazy" decoding="async" />',
                    esc_url($src),
                    esc_attr($alt)
                )
                : ''
        );

        // No toggle button is rendered here. Whether panels are click-activated
        // is the parent's setting, and a child module cannot see its parent's
        // attributes server-side — no child in this plugin does. The parent
        // emits data-dtq-activator on its wrapper and frontend.js adds the
        // button where it is needed, the same way the accordion's boot pass
        // stamps role and aria-expanded onto its titles.
        //
        // That also keeps hover mode free of a full-panel button that would sit
        // over the panel's own links and swallow them.

        // Hand-rendered for the same reason as every other button in this
        // release: on Divi 5.9.0 elements->render for a nested button element
        // returns an empty string.
        $button = ButtonElement::render(
            $attrs['button'] ?? [],
            'dtq-btn-default dtq-image-accordion__btn'
        );
        $button_html = '' !== $button
            ? sprintf('<div class="dtq-image-accordion__btn-wrap">%s</div>', $button)
            : '';

        $children = sprintf(
            '%1$s<span class="dtq-image-accordion__overlay" aria-hidden="true"></span>'
                . '<div class="dtq-image-accordion__body">%2$s%3$s%4$s</div>',
            $media_html,
            $elements->render(['attrName' => 'title']),
            $elements->render(['attrName' => 'content']),
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
                'children'            => [
                    $elements->style_components(['attrName' => 'module']),
                    $children,
                ],
            ]
        );
    }
}
