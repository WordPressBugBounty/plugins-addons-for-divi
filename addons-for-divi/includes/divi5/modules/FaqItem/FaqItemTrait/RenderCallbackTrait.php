<?php
/**
 * FaqItem: Render callback trait.
 *
 * @package DiviTorqueLite\Modules\FaqItem
 * @since   4.7.0
 */

namespace DiviTorqueLite\Modules\FaqItem\FaqItemTrait;

if (!defined('ABSPATH')) {
    exit;
}

use ET\Builder\Packages\Module\Module;

trait RenderCallbackTrait
{
    /**
     * Server-side render for an FAQ item (accordion-structured).
     *   <div class="dtq_faq_item dtq-accordion__item">
     *     <div class="dtq-accordion__title"><div class="dtq-accordion__heading">
     *       <h3 class="dtq-accordion__title-text">Question</h3>
     *     </div></div>
     *     <div class="dtq-accordion__content">
     *       <div class="dtq-accordion__content-inner">Answer</div>
     *     </div>
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
        $question = $elements->render(['attrName' => 'question', 'tagName' => 'h3']);
        $answer   = $elements->render(['attrName' => 'answer']);

        // Same control semantics as the Accordion Item (this module reuses the
        // `.dtq-accordion__*` markup and the shared frontend.js handler): without
        // role/tabindex/aria-expanded the question was a plain <div> that only
        // answered to a mouse click, so FAQs were keyboard-inoperable and silent
        // to screen readers.
        $panel_id = function_exists('wp_unique_id')
            ? wp_unique_id('dtq-faq-panel-')
            : 'dtq-faq-panel-' . (int) ($block->parsed_block['orderIndex'] ?? 0);

        $keep_open       = ($attrs['module']['advanced']['keepOpen']['desktop']['value'] ?? 'off') === 'on';
        $open_by_default = ($attrs['module']['advanced']['openByDefault']['desktop']['value'] ?? 'off') === 'on';
        $is_open         = $open_by_default || $keep_open;

        // An unnamed role="region" is announced as bare "region" and still
        // counts as a landmark -- one per question. Name it after the question.
        $header_id = $panel_id . '-header';

        $header_html = sprintf(
            '<div class="dtq-accordion__title" id="%4$s" role="button" tabindex="0" aria-expanded="%2$s" aria-controls="%3$s"><div class="dtq-accordion__heading">%1$s</div></div>',
            $question,
            $is_open ? 'true' : 'false',
            esc_attr($panel_id),
            esc_attr($header_id)
        );
        $content_html = sprintf(
            '<div class="dtq-accordion__content" id="%2$s" role="region" aria-labelledby="%3$s">%1$s</div>',
            $answer,
            esc_attr($panel_id),
            esc_attr($header_id)
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
                    $header_html,
                    $content_html,
                ],
            ]
        );
    }
}
