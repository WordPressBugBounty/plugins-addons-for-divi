<?php
/**
 * Number Counter: Render callback trait.
 *
 * @package DiviTorqueLite\Modules\NumberCounter
 * @since   4.4.0
 */

namespace DiviTorqueLite\Modules\NumberCounter\NumberCounterTrait;

if (!defined('ABSPATH')) {
    exit;
}

use ET\Builder\Packages\Module\Module;
use ET\Builder\Packages\Module\Layout\Components\ModuleElements\ModuleElements;
use WP_Block;

trait RenderCallbackTrait
{
    /**
     * Server-side render for the Number Counter module.
     *
     * Mirrors the D4 render() markup (and the VB edit component) exactly:
     *
     *   <div class="dtq-module dtq-number [dtq-counter]">
     *     <div class="dtq-number-wrap">
     *       <div class="dtq-number-text">{number}</div>
     *     </div>
     *     <div class="dtq-number-title"><h3>{title}</h3></div>
     *   </div>
     *
     * Keep byte-for-byte identical with
     * src/divi5/modules/number-counter/edit.jsx.
     *
     * Like D4, the counter-up library is enqueued only when use_counter is
     * on (the handle is registered by Assets::vendor_enqueue_scripts());
     * src/divi5/frontend.js initCounters() runs the count-up on load.
     *
     * @param array          $attrs    Block attributes.
     * @param string         $content  Block content (unused).
     * @param WP_Block       $block    Parsed block.
     * @param ModuleElements $elements Module elements helper.
     *
     * @return string
     */
    public static function render_callback($attrs, $content, $block, $elements)
    {
        $advanced = $attrs['module']['advanced'] ?? [];

        $use_counter = $advanced['useCounter']['desktop']['value'] ?? 'off';

        $number_val = $attrs['number']['innerContent']['desktop']['value'] ?? '';
        $title_val  = $attrs['title']['innerContent']['desktop']['value'] ?? '';

        // D4 parity: the counter library loads only when the counter is used.
        if ('on' === $use_counter && function_exists('wp_enqueue_script')) {
            wp_enqueue_script('divi-torque-lite-counter-up');
        }

        // Number (mirrors D4 render_number(): only when non-empty).
        $number_html = '';
        if ('' !== $number_val) {
            $number_html = sprintf(
                '<div class="dtq-number-wrap">%1$s</div>',
                $elements->render(['attrName' => 'number'])
            );
        }

        // Title (mirrors D4 render_title(): only when non-empty).
        $title_html = '';
        if ('' !== $title_val) {
            $title_html = sprintf(
                '<div class="dtq-number-title">%1$s</div>',
                $elements->render(['attrName' => 'title'])
            );
        }

        $children = sprintf(
            '<div class="dtq-module dtq-number%1$s">%2$s%3$s</div>',
            ('on' === $use_counter) ? ' dtq-counter' : '',
            $number_html,
            $title_html
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
                'children'            => $children,
            ]
        );
    }
}
