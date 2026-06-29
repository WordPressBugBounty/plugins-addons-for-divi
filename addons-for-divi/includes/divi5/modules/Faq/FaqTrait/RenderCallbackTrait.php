<?php
/**
 * Faq: Render callback trait.
 *
 * @package DiviTorqueLite\Modules\Faq
 * @since   4.7.0
 */

namespace DiviTorqueLite\Modules\Faq\FaqTrait;

if (!defined('ABSPATH')) {
    exit;
}

use ET\Builder\Packages\Module\Module;

trait RenderCallbackTrait
{
    /**
     * Server-side render for the FAQ parent.
     *
     *   <div class="dtq-accordion dtq-faq dtq-accordion--style-X dtq-accordion--icon-chevron"
     *        data-dtq-close-others="on|off" data-dtq-initial="first">
     *     [faq-item children]
     *   </div>
     *   <script type="application/ld+json">FAQPage…</script>
     *
     * @param array  $attrs    Block attributes.
     * @param string $content  Rendered child items.
     * @param object $block    Parsed block.
     * @param object $elements Module elements helper.
     *
     * @return string
     */
    public static function render_callback($attrs, $content, $block, $elements)
    {
        $advanced = $attrs['module']['advanced'] ?? [];

        $style        = $advanced['faqStyle']['desktop']['value'] ?? 'boxed';
        $close_others = ($advanced['closeOthers']['desktop']['value'] ?? 'off') === 'on' ? 'on' : 'off';
        $schema_on    = ($advanced['showSchema']['desktop']['value'] ?? 'on') === 'on';

        $wrapper_class = sprintf(
            'dtq-accordion dtq-faq dtq-accordion--style-%1$s dtq-accordion--icon-chevron',
            $style
        );

        $faq_html = sprintf(
            '<div class="%1$s" data-dtq-close-others="%2$s" data-dtq-initial="first">%3$s</div>',
            esc_attr($wrapper_class),
            esc_attr($close_others),
            $content
        );

        $schema_html = $schema_on ? self::schema_json($block) : '';

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
                    $faq_html . $schema_html,
                ],
            ]
        );
    }

    /**
     * Build the FAQPage JSON-LD from the child question/answer pairs.
     *
     * @param object $block Parsed block (with innerBlocks).
     *
     * @return string
     */
    public static function schema_json($block)
    {
        $inner_blocks = $block->parsed_block['innerBlocks'] ?? [];
        $entities     = [];

        foreach ($inner_blocks as $inner_block) {
            $a        = $inner_block['attrs'] ?? [];
            $question = trim((string) ($a['question']['innerContent']['desktop']['value'] ?? ''));
            $answer   = trim((string) ($a['answer']['innerContent']['desktop']['value'] ?? ''));
            if ('' === $question || '' === $answer) {
                continue;
            }
            $entities[] = [
                '@type'          => 'Question',
                'name'           => wp_strip_all_tags($question),
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text'  => wp_kses_post($answer),
                ],
            ];
        }

        if (empty($entities)) {
            return '';
        }

        $data = [
            '@context'   => 'https://schema.org',
            '@type'      => 'FAQPage',
            'mainEntity' => $entities,
        ];

        return sprintf(
            '<script type="application/ld+json">%1$s</script>',
            wp_json_encode($data, JSON_UNESCAPED_SLASHES)
        );
    }
}
