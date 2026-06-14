<?php
/**
 * SkillBarItem: Render callback trait.
 *
 * @package DiviTorqueLite\Modules\SkillBarItem
 * @since   4.4.0
 */

namespace DiviTorqueLite\Modules\SkillBarItem\SkillBarItemTrait;

if (!defined('ABSPATH')) {
    exit;
}

use ET\Builder\Packages\Module\Module;

trait RenderCallbackTrait
{
    /**
     * Server-side render for a skill bar item.
     *
     * Mirrors the D4 markup and the VB edit component:
     *   <div class="dtq-module dtq-child dtq-skillbar">
     *     <div class="dtq-skillbar__wrapper">
     *       <div class="dtq-skillbar__inner">
     *         <div class="dtq-skillbar__inner__text">[name] [level]</div>
     *       </div>
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
        $advanced = $attrs['module']['advanced'] ?? [];

        $use_name   = ($advanced['useName']['desktop']['value'] ?? 'on') === 'on';
        $hide_level = ($advanced['isHideLevel']['desktop']['value'] ?? 'off') === 'on';
        $level      = $advanced['level']['desktop']['value'] ?? '30%';

        $name_html  = $use_name ? $elements->render(['attrName' => 'name']) : '';
        $level_html = !$hide_level
            ? sprintf('<span class="dtq-skillbar__level">%1$s</span>', esc_html($level))
            : '';

        // D4: inner text renders when name is used OR level text isn't hidden.
        $inner_text = '';
        if ($use_name || !$hide_level) {
            $inner_text = sprintf(
                '<div class="dtq-skillbar__inner__text">%1$s %2$s</div>',
                $name_html,
                $level_html
            );
        }

        $item_html = sprintf(
            '<div class="dtq-module dtq-child dtq-skillbar"><div class="dtq-skillbar__wrapper"><div class="dtq-skillbar__inner">%1$s</div></div></div>',
            $inner_text
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
