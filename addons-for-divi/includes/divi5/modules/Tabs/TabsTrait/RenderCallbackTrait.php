<?php
/**
 * Tabs: Render callback trait.
 *
 * @package DiviTorqueLite\Modules\Tabs
 * @since   4.7.0
 */

namespace DiviTorqueLite\Modules\Tabs\TabsTrait;

if (!defined('ABSPATH')) {
    exit;
}

use ET\Builder\Packages\Module\Module;

trait RenderCallbackTrait
{
    /**
     * Server-side render for the Tabs parent.
     *
     * Pre-builds the nav row from the child tab titles (SEO-friendly, no layout
     * shift) and wraps the rendered children (panels):
     *   <div class="dtq-module dtq-tabs dtq-tabs--style-X dtq-tabs--align-Y">
     *     <ul class="dtq-tabs__nav"><li class="dtq-tabs__nav-item[ --active]">Title</li>…</ul>
     *     <div class="dtq-tabs__panels">[child panels]</div>
     *   </div>
     *
     * @param array  $attrs    Block attributes.
     * @param string $content  Rendered child panels.
     * @param object $block    Parsed block.
     * @param object $elements Module elements helper.
     *
     * @return string
     */
    public static function render_callback($attrs, $content, $block, $elements)
    {
        $advanced = $attrs['module']['advanced'] ?? [];

        $style = $advanced['tabStyle']['desktop']['value'] ?? 'line';
        $align = $advanced['tabAlign']['desktop']['value'] ?? 'left';

        $wrapper_class = implode(
            ' ',
            [
                'dtq-module',
                'dtq-tabs',
                'dtq-tabs--style-' . $style,
                'dtq-tabs--align-' . $align,
            ]
        );

        // Build the nav from the child tab titles.
        $inner_blocks = $block->parsed_block['innerBlocks'] ?? [];
        $nav_items    = '';
        $index        = 0;
        foreach ($inner_blocks as $inner_block) {
            $title = $inner_block['attrs']['title']['innerContent']['desktop']['value'] ?? '';
            if ('' === $title) {
                $title = sprintf('Tab %d', $index + 1);
            }
            $active = 0 === $index ? ' dtq-tabs__nav-item--active' : '';
            $nav_items .= sprintf(
                '<li class="dtq-tabs__nav-item%1$s" role="tab" tabindex="0" data-dtq-index="%2$d">%3$s</li>',
                $active,
                (int) $index,
                esc_html(wp_strip_all_tags($title))
            );
            $index++;
        }

        $nav_html = sprintf('<ul class="dtq-tabs__nav" role="tablist">%1$s</ul>', $nav_items);
        $panels_html = sprintf('<div class="dtq-tabs__panels">%1$s</div>', $content);

        $tabs_html = sprintf(
            '<div class="%1$s">%2$s%3$s</div>',
            esc_attr($wrapper_class),
            $nav_html,
            $panels_html
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
                    $tabs_html,
                ],
            ]
        );
    }
}
