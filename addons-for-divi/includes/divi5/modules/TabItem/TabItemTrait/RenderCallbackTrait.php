<?php
/**
 * TabItem: Render callback trait.
 *
 * @package DiviTorqueLite\Modules\TabItem
 * @since   4.7.0
 */

namespace DiviTorqueLite\Modules\TabItem\TabItemTrait;

if (!defined('ABSPATH')) {
    exit;
}

use ET\Builder\Packages\Module\Module;

trait RenderCallbackTrait
{
    /**
     * Server-side render for a tab panel.
     *
     * Renders the panel content only — the tab label is read by the parent Tabs
     * module to build the nav row. The order-class div is the panel (the
     * `dtq-tabs__panel` class comes from module_classnames):
     *   <div class="dtq_tab_item dtq-tabs__panel">[content]</div>
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
        $content_html = $elements->render(['attrName' => 'content']);

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
                    $content_html,
                ],
            ]
        );
    }
}
