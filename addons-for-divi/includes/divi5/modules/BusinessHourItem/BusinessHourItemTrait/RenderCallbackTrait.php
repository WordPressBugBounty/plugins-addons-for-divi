<?php
/**
 * BusinessHourItem: Render callback trait.
 *
 * @package DiviTorqueLite\Modules\BusinessHourItem
 * @since   4.4.0
 */

namespace DiviTorqueLite\Modules\BusinessHourItem\BusinessHourItemTrait;

if (!defined('ABSPATH')) {
    exit;
}

use ET\Builder\Packages\Module\Module;

trait RenderCallbackTrait
{
    /**
     * Server-side render for a business hour item.
     *
     * Mirrors the D4 markup and the VB edit component:
     *   <div class="dtq-module-child dtq-business-hour-child">
     *     <div class="dtq-business-hour-day">[day]</div>
     *     <div class="dtq-business-hour-separator"></div>
     *     <div class="dtq-business-hour-time">[time]</div>
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
        // D4 renders day/time only when non-empty.
        $day_value  = $attrs['day']['innerContent']['desktop']['value'] ?? '';
        $time_value = $attrs['time']['innerContent']['desktop']['value'] ?? '';

        $day_html  = '' !== $day_value ? $elements->render(['attrName' => 'day']) : '';
        $time_html = '' !== $time_value ? $elements->render(['attrName' => 'time']) : '';

        $item_html = sprintf(
            '<div class="dtq-module-child dtq-business-hour-child">%1$s<div class="dtq-business-hour-separator"></div>%2$s</div>',
            $day_html,
            $time_html
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
