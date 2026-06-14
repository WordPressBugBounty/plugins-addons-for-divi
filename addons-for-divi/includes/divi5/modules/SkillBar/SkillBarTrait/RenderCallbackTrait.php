<?php
/**
 * SkillBar: Render callback trait.
 *
 * @package DiviTorqueLite\Modules\SkillBar
 * @since   4.4.0
 */

namespace DiviTorqueLite\Modules\SkillBar\SkillBarTrait;

if (!defined('ABSPATH')) {
    exit;
}

use ET\Builder\Packages\Module\Module;

trait RenderCallbackTrait
{
    /**
     * Server-side render for the Skill Bar parent.
     *
     * Mirrors the D4 markup and the VB edit component:
     *   <div class="dtq-module dtq-parent dtq-skill">
     *     [title] [children]
     *   </div>
     *
     * @param array  $attrs    Block attributes.
     * @param string $content  Rendered child bars.
     * @param object $block    Parsed block.
     * @param object $elements Module elements helper.
     *
     * @return string
     */
    public static function render_callback($attrs, $content, $block, $elements)
    {
        $title = $elements->render(['attrName' => 'title']);

        $skill_html = sprintf(
            '<div class="dtq-module dtq-parent dtq-skill">%1$s%2$s</div>',
            $title,
            $content
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
                    $skill_html,
                ],
            ]
        );
    }
}
