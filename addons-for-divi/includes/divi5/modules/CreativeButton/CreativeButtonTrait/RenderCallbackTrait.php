<?php
/**
 * CreativeButton: Render callback trait.
 *
 * @package DiviTorqueLite\Modules\CreativeButton
 * @since   4.9.0
 */

namespace DiviTorqueLite\Modules\CreativeButton\CreativeButtonTrait;

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
     * Hover effects this module ships.
     *
     * The chosen value lands in a class name, so it is matched against this
     * list rather than trusted.
     */
    const EFFECTS = [
        'none',
        'fill-up',
        'fill-left',
        'fill-diagonal',
        'sweep-in',
        'shutter-h',
        'shutter-v',
        'radial-out',
        'border-draw',
        'shine',
        'text-slide-up',
    ];

    const ALIGNMENTS = ['left', 'center', 'right'];

    /**
     * Build the wrapper class list.
     *
     * PHP twin of src/divi5/modules/creative-button/wrapper-class.js.
     *
     * @param array $advanced The `module.advanced` attrs array.
     *
     * @return string
     */
    public static function wrapper_class($advanced)
    {
        $effect    = $advanced['effect']['desktop']['value'] ?? 'fill-up';
        $alignment = $advanced['alignment']['desktop']['value'] ?? 'center';

        if (!in_array($effect, self::EFFECTS, true)) {
            $effect = 'fill-up';
        }
        if (!in_array($alignment, self::ALIGNMENTS, true)) {
            $alignment = 'center';
        }

        $classes = [
            'dtq-module',
            'dtq-creative-btn',
            // Marks this as Divi 5 markup so the site-wide Divi 4 frontend.js
            // leaves it alone.
            'dtq-d5',
            'dtq-creative-btn--fx-' . $effect,
            'dtq-creative-btn--align-' . $alignment,
        ];

        if (($advanced['fullWidth']['desktop']['value'] ?? 'off') === 'on') {
            $classes[] = 'dtq-creative-btn--full';
        }

        return implode(' ', $classes);
    }

    /**
     * Server-side render for the Creative Button module.
     *
     * Mirrors src/divi5/modules/creative-button/edit.jsx.
     *
     * The anchor is built here rather than through
     * $elements->render(['attrName' => 'button']): on Divi 5.9.0 that path emits
     * nothing for a button declared as a nested element, which is reproducible
     * with Divi's own pricing-tables-item and so is not specific to our
     * metadata. The attribute keeps its divi/button decoration group, so the
     * Design tab still styles this anchor through the selector in module.json.
     *
     * @param array          $attrs    Block attributes.
     * @param string         $content  Block content (unused; no children).
     * @param WP_Block       $block    Parsed block.
     * @param ModuleElements $elements Module elements helper.
     *
     * @return string
     */
    public static function render_callback($attrs, $content, $block, $elements)
    {
        $advanced = $attrs['module']['advanced'] ?? [];

        $button_html = ButtonElement::render(
            $attrs['button'] ?? [],
            'dtq-btn-default dtq-creative-btn__link',
            [
                // The label lives in its own span so the text-slide-up effect can
                // move it independently of the anchor.
                'children' => sprintf(
                    '<span class="dtq-creative-btn__label">%s</span>',
                    et_core_esc_previously((string) ($attrs['button']['innerContent']['desktop']['value']['text'] ?? ''))
                ),
            ]
        );

        // Only the Slide Up Text effect reads this, and it lands in CSS through
        // attr() — esc_attr keeps it inside the attribute.
        $hover_text = (string) ($advanced['hoverText']['desktop']['value'] ?? '');
        $hover_attr = '' !== $hover_text
            ? sprintf(' data-dtq-hover-text="%s"', esc_attr($hover_text))
            : '';

        $children = sprintf(
            '<div class="%1$s"><span class="dtq-creative-btn__slot"%2$s>%3$s</span></div>',
            esc_attr(self::wrapper_class($advanced)),
            $hover_attr,
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
