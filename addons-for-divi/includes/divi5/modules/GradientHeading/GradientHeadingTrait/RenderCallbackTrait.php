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

use DiviTorqueLite\Modules\Shared\DynamicValue;
use ET\Builder\Packages\Module\Module;
use ET\Builder\Packages\Module\Layout\Components\ModuleElements\ModuleElements;
use WP_Block;

trait RenderCallbackTrait
{
    /**
     * Read the Link Options value in every shape it is stored in.
     *
     * The Divi 5 field (divi/checkboxes) stores an array of the ticked values,
     * `['linkTarget', 'linkRel']`. Divi 4's multiple_checkboxes stored a pipe
     * string in option order, `link_target|link_rel` as on/off ("on|off" is
     * new window without nofollow), and the conversion outline copies that
     * string as it is. Reading the array only dropped every migrated choice
     * (#79). JS twin: linkOptions() in
     * src/divi5/modules/gradient-heading/link-options.js.
     *
     * @param mixed $value Raw linkOptions attribute value.
     *
     * @return array{new_window: bool, nofollow: bool}
     */
    public static function link_options($value)
    {
        $tokens = [];
        if (is_array($value)) {
            $tokens = array_values(array_filter($value, 'is_string'));
        } elseif (is_string($value) && '' !== trim($value)) {
            $tokens = array_map('trim', preg_split('/[|,]/', $value));
        }

        // Named values: the Divi 5 array, or the same names in a string.
        if (in_array('linkTarget', $tokens, true) || in_array('linkRel', $tokens, true)) {
            return [
                'new_window' => in_array('linkTarget', $tokens, true),
                'nofollow'   => in_array('linkRel', $tokens, true),
            ];
        }

        // Divi 4 positional on/off flags, only from a string.
        if (is_string($value)) {
            return [
                'new_window' => 'on' === strtolower($tokens[0] ?? ''),
                'nofollow'   => 'on' === strtolower($tokens[1] ?? ''),
            ];
        }

        return ['new_window' => false, 'nofollow' => false];
    }

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
        // Printed below, so resolve a dynamic-content binding first (#86).
        $title_value = DynamicValue::resolve($attrs['title']['innerContent']['desktop']['value'] ?? '');

        $tag = $attrs['title']['decoration']['font']['font']['desktop']['value']['headingLevel'] ?? '';
        if (empty($tag)) {
            $tag = $attrs['module']['advanced']['htmlTag']['desktop']['value'] ?? 'h1';
        }
        $allowed_tags = ['h1', 'h2', 'h3', 'h4', 'h5', 'h6'];
        if (!in_array($tag, $allowed_tags, true)) {
            $tag = 'h1';
        }

        $use_link = ($attrs['module']['advanced']['useLink']['desktop']['value'] ?? 'off') === 'on';
        $link_url = DynamicValue::resolve($attrs['module']['advanced']['linkUrl']['desktop']['value'] ?? '');

        $link_options    = self::link_options($attrs['module']['advanced']['linkOptions']['desktop']['value'] ?? []);
        $link_target_new = $link_options['new_window'];
        $link_nofollow   = $link_options['nofollow'];

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
                'children'            => [
                    // Background pattern/mask and the box-shadow overlay are DOM,
                    // not CSS. Without this call Divi renders them in the builder
                    // and nowhere else, so the options silently do nothing on the
                    // front end.
                    $elements->style_components(['attrName' => 'module']),
                    $children,
                ],
            ]
        );
    }
}
