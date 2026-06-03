<?php
/**
 * GradientHeading: Render callback trait.
 *
 * @package DiviTorqueLite\Modules\GradientHeading
 * @since   4.3.0
 */

namespace DiviTorqueLite\Modules\GradientHeading\GradientHeadingTrait;

if (!defined('ABSPATH')) {
    exit;
}

use ET\Builder\Packages\Module\Module;
use ET\Builder\Packages\Module\Layout\Components\ModuleElements\ModuleElements;
use WP_Block;

trait RenderCallbackTrait
{
    /**
     * Server-side render for the GradientHeading module.
     *
     * Mirrors the D4 markup:
     *     <h{1..6} class="dtq-gradient-heading">[optional <a>]Title[/a]</h>
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
        $title_value = $attrs['title']['innerContent']['desktop']['value'] ?? '';

        $tag = $attrs['title']['decoration']['font']['font']['desktop']['value']['headingLevel'] ?? '';
        if (empty($tag)) {
            $tag = $attrs['module']['advanced']['htmlTag']['desktop']['value'] ?? 'h1';
        }
        $allowed_tags = ['h1', 'h2', 'h3', 'h4', 'h5', 'h6'];
        if (!in_array($tag, $allowed_tags, true)) {
            $tag = 'h1';
        }

        $use_link = ($attrs['module']['advanced']['useLink']['desktop']['value'] ?? 'off') === 'on';
        $link_url = $attrs['module']['advanced']['linkUrl']['desktop']['value'] ?? '';

        $link_options    = $attrs['module']['advanced']['linkOptions']['desktop']['value'] ?? [];
        $link_target_new = false;
        $link_nofollow   = false;
        if (is_array($link_options)) {
            $link_target_new = in_array('linkTarget', $link_options, true);
            $link_nofollow   = in_array('linkRel', $link_options, true);
        }

        $inner_html = $title_value;

        if ($use_link && !empty($link_url)) {
            $inner_html = sprintf(
                '<a href="%1$s" target="%2$s"%3$s>%4$s</a>',
                esc_url($link_url),
                $link_target_new ? '_blank' : '_self',
                $link_nofollow ? ' rel="nofollow"' : '',
                $title_value
            );
        }

        $children = sprintf(
            '<%1$s class="dtq-gradient-heading">%2$s</%1$s>',
            esc_attr($tag),
            $inner_html
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
