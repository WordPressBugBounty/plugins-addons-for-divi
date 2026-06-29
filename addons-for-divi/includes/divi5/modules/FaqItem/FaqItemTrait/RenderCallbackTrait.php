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

        $header_html = sprintf(
            '<div class="dtq-accordion__title"><div class="dtq-accordion__heading">%1$s</div></div>',
            $question
        );
        $content_html = sprintf('<div class="dtq-accordion__content">%1$s</div>', $answer);

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
