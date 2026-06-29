<?php
/**
 * FancyText: Render callback trait.
 *
 * @package DiviTorqueLite\Modules\FancyText
 * @since   4.7.0
 */

namespace DiviTorqueLite\Modules\FancyText\FancyTextTrait;

if (!defined('ABSPATH')) {
    exit;
}

use ET\Builder\Packages\Module\Module;

trait RenderCallbackTrait
{
    /**
     * Server-side render for Fancy Text.
     *
     *   <tag class="dtq-fancy dtq-fancy--effect-X[ --hl-Y][ --anim-Z]" data-dtq-*>
     *     <span class="dtq-fancy__before">before </span>
     *     <span class="dtq-fancy__highlight">fancy</span>
     *     <span class="dtq-fancy__after"> after</span>
     *   </tag>
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
        $val      = function ($key, $fallback = '') use ($advanced) {
            $v = $advanced[$key]['desktop']['value'] ?? '';
            return '' !== $v ? $v : $fallback;
        };

        $effect  = $val('effect', 'highlight');
        $before  = $val('beforeText', '');
        $after   = $val('afterText', '');

        // Rotating words.
        $strings = array_values(array_filter(array_map(
            'trim',
            preg_split('/\r\n|\r|\n/', (string) $val('rotateStrings', ''))
        ), function ($s) {
            return '' !== $s;
        }));

        $fancy = 'rotate' === $effect ? ($strings[0] ?? 'Fancy') : $val('fancyText', 'Fancy');

        // Tag (whitelisted).
        $tag = $val('tag', 'h2');
        if (!in_array($tag, ['h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'p', 'span'], true)) {
            $tag = 'h2';
        }

        // Classes (mirrors wrapper-class.js).
        $classes = ['dtq-fancy', 'dtq-fancy--effect-' . $effect];
        if ('highlight' === $effect) {
            $classes[] = 'dtq-fancy--hl-' . $val('highlightStyle', 'marker');
        }
        if ('rotate' === $effect) {
            $classes[] = 'dtq-fancy--anim-' . $val('rotateAnimation', 'fade-up');
        }

        $before_html = '' !== $before ? sprintf('<span class="dtq-fancy__before">%1$s </span>', esc_html($before)) : '';
        $after_html  = '' !== $after ? sprintf('<span class="dtq-fancy__after"> %1$s</span>', esc_html($after)) : '';

        $fancy_html = sprintf(
            '<%1$s class="%2$s" data-dtq-effect="%3$s" data-dtq-anim="%4$s" data-dtq-speed="%5$s" data-dtq-strings="%6$s">%7$s<span class="dtq-fancy__highlight">%8$s</span>%9$s</%1$s>',
            $tag,
            esc_attr(implode(' ', $classes)),
            esc_attr($effect),
            esc_attr($val('rotateAnimation', 'fade-up')),
            esc_attr($val('rotateSpeed', '2000')),
            esc_attr(wp_json_encode($strings)),
            $before_html,
            esc_html($fancy),
            $after_html
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
                    $fancy_html,
                ],
            ]
        );
    }
}
