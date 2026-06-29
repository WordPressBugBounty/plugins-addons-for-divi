<?php
/**
 * Accordion: Render callback trait.
 *
 * @package DiviTorqueLite\Modules\Accordion
 * @since   4.7.0
 */

namespace DiviTorqueLite\Modules\Accordion\AccordionTrait;

if (!defined('ABSPATH')) {
    exit;
}

use ET\Builder\Packages\Module\Module;

trait RenderCallbackTrait
{
    /**
     * Server-side render for the Accordion parent.
     *
     * Mirrors the VB edit component:
     *   <div class="dtq-module dtq-accordion[ dtq-accordion--no-icon]"
     *        data-dtq-close-others="on|off">
     *     [accordion-item children]
     *   </div>
     *
     * @param array  $attrs    Block attributes.
     * @param string $content  Rendered child items.
     * @param object $block    Parsed block.
     * @param object $elements Module elements helper.
     *
     * @return string
     */
    public static function render_callback($attrs, $content, $block, $elements)
    {
        $advanced = $attrs['module']['advanced'] ?? [];

        $close_others = ($advanced['closeOthers']['desktop']['value'] ?? 'on') === 'on' ? 'on' : 'off';
        $show_icon    = ($advanced['showIcon']['desktop']['value'] ?? 'on') === 'on';

        $activator      = $advanced['activator']['desktop']['value'] ?? 'click';
        $initial        = $advanced['initialDisplay']['desktop']['value'] ?? 'first';
        $autoplay_speed = $advanced['autoplaySpeed']['desktop']['value'] ?? '4000';
        $scroll_top     = ($advanced['scrollToTop']['desktop']['value'] ?? 'off') === 'on' ? 'on' : 'off';
        $scroll_load    = ($advanced['scrollOnLoad']['desktop']['value'] ?? 'off') === 'on' ? 'on' : 'off';

        // Mirrors src/divi5/modules/accordion/wrapper-class.js — keep in sync.
        $style         = $advanced['accordionStyle']['desktop']['value'] ?? 'boxed';
        $icon_style    = $advanced['iconStyle']['desktop']['value'] ?? 'chevron';
        $icon_source   = $advanced['iconSource']['desktop']['value'] ?? 'preset';
        $icon_shape    = $advanced['iconShape']['desktop']['value'] ?? 'none';
        $icon_position = $advanced['iconPosition']['desktop']['value'] ?? 'right';
        $content_height = $advanced['contentHeight']['desktop']['value'] ?? 'auto';
        $content_anim   = $advanced['contentAnimation']['desktop']['value'] ?? 'none';

        $classes = [
            'dtq-module',
            'dtq-accordion',
            'dtq-accordion--style-' . $style,
            'dtq-accordion--icon-' . $icon_style,
        ];
        if ('custom' === $icon_source) {
            $classes[] = 'dtq-accordion--icon-custom';
        }
        if ('none' !== $icon_shape) {
            $classes[] = 'dtq-accordion--icon-shape-' . $icon_shape;
        }
        if ('left' === $icon_position) {
            $classes[] = 'dtq-accordion--icon-left';
        }
        if (!$show_icon) {
            $classes[] = 'dtq-accordion--no-icon';
        }
        if (($advanced['contentCloseButton']['desktop']['value'] ?? 'off') === 'on') {
            $classes[] = 'dtq-accordion--close-btn';
        }
        if ('fixed' === $content_height) {
            $classes[] = 'dtq-accordion--height-fixed';
        }
        if ('none' !== $content_anim) {
            $classes[] = 'dtq-accordion--anim-' . $content_anim;
        }
        $wrapper_class = implode(' ', $classes);

        $accordion_html = sprintf(
            '<div class="%1$s" data-dtq-close-others="%2$s" data-dtq-activator="%3$s" data-dtq-initial="%4$s" data-dtq-autoplay-speed="%5$s" data-dtq-scroll-top="%6$s" data-dtq-scroll-load="%7$s">%8$s</div>',
            esc_attr($wrapper_class),
            esc_attr($close_others),
            esc_attr($activator),
            esc_attr($initial),
            esc_attr($autoplay_speed),
            esc_attr($scroll_top),
            esc_attr($scroll_load),
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
                    $accordion_html,
                ],
            ]
        );
    }
}
