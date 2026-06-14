<?php
/**
 * BusinessHour: Render callback trait.
 *
 * @package DiviTorqueLite\Modules\BusinessHour
 * @since   4.4.0
 */

namespace DiviTorqueLite\Modules\BusinessHour\BusinessHourTrait;

if (!defined('ABSPATH')) {
    exit;
}

use ET\Builder\Packages\Module\Module;

trait RenderCallbackTrait
{
    /**
     * Server-side render for the Business Hours parent.
     *
     * Mirrors the D4 markup and the VB edit component:
     *   <div class="dtq-module dtq-business-hour">
     *     <div class="dtq-business-hour-title"><h2>[title]</h2></div>
     *     <div class="dtq-business-hour-content">[children]</div>
     *   </div>
     *
     * @param array  $attrs    Block attributes.
     * @param string $content  Rendered child rows.
     * @param object $block    Parsed block.
     * @param object $elements Module elements helper.
     *
     * @return string
     */
    public static function render_callback($attrs, $content, $block, $elements)
    {
        $show_title = ($attrs['module']['advanced']['showTitle']['desktop']['value'] ?? 'on') === 'on';

        $title_html = '';
        if ($show_title) {
            $title_html = sprintf(
                '<div class="dtq-business-hour-title">%1$s</div>',
                $elements->render(['attrName' => 'title'])
            );
        }

        $hours_html = sprintf(
            '<div class="dtq-module dtq-business-hour">%1$s<div class="dtq-business-hour-content">%2$s</div></div>',
            $title_html,
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
                    $hours_html,
                ],
            ]
        );
    }
}
