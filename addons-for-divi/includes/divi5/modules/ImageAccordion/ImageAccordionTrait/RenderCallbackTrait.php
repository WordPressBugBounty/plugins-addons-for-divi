<?php
/**
 * ImageAccordion: Render callback trait.
 *
 * @package DiviTorqueLite\Modules\ImageAccordion
 * @since   4.9.0
 */

namespace DiviTorqueLite\Modules\ImageAccordion\ImageAccordionTrait;

if (!defined('ABSPATH')) {
    exit;
}

use ET\Builder\Packages\Module\Module;
use ET\Builder\Packages\Module\Layout\Components\ModuleElements\ModuleElements;
use WP_Block;

trait RenderCallbackTrait
{
    const DIRECTIONS = ['horizontal', 'vertical'];
    const ACTIVATORS = ['hover', 'click'];
    const STACK_ON   = ['none', 'tablet', 'phone'];
    const REVEALS    = ['always', 'active'];
    const POSITIONS  = ['top', 'center', 'bottom'];

    /**
     * Build the wrapper class list.
     *
     * PHP twin of src/divi5/modules/image-accordion/wrapper-class.js. Every one
     * of these values lands in a class name, so each is matched against its
     * allowlist rather than trusted.
     *
     * @param array $advanced The `module.advanced` attrs array.
     *
     * @return string
     */
    public static function wrapper_class($advanced)
    {
        $pick = function ($key, array $allowed, $fallback) use ($advanced) {
            $value = $advanced[$key]['desktop']['value'] ?? $fallback;
            return in_array($value, $allowed, true) ? $value : $fallback;
        };

        $direction = $pick('direction', self::DIRECTIONS, 'horizontal');
        $activator = $pick('activator', self::ACTIVATORS, 'hover');
        $reveal    = $pick('contentReveal', self::REVEALS, 'active');
        $position  = $pick('contentPosition', self::POSITIONS, 'bottom');
        $stack_on  = $pick('stackOn', self::STACK_ON, 'phone');

        $classes = [
            'dtq-module',
            'dtq-image-accordion',
            // Marks this as Divi 5 markup so the site-wide Divi 4 frontend.js
            // leaves it alone.
            'dtq-d5',
            'dtq-image-accordion--dir-' . $direction,
            'dtq-image-accordion--act-' . $activator,
            'dtq-image-accordion--reveal-' . $reveal,
            'dtq-image-accordion--content-' . $position,
        ];

        if ('none' !== $stack_on) {
            $classes[] = 'dtq-image-accordion--stack-' . $stack_on;
        }

        return implode(' ', $classes);
    }

    /**
     * Server-side render for the Image Accordion parent.
     *
     * The panels come from the child module and arrive pre-rendered in
     * $content.
     *
     * @param array          $attrs    Block attributes.
     * @param string         $content  Pre-rendered child panels.
     * @param WP_Block       $block    Parsed block.
     * @param ModuleElements $elements Module elements helper.
     *
     * @return string
     */
    public static function render_callback($attrs, $content, $block, $elements)
    {
        $advanced  = $attrs['module']['advanced'] ?? [];
        $activator = $advanced['activator']['desktop']['value'] ?? 'hover';

        if (!in_array($activator, self::ACTIVATORS, true)) {
            $activator = 'hover';
        }

        $children = sprintf(
            '<div class="%1$s" data-dtq-activator="%2$s">%3$s</div>',
            esc_attr(self::wrapper_class($advanced)),
            esc_attr($activator),
            $content
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
                    $elements->style_components(['attrName' => 'module']),
                    $children,
                ],
            ]
        );
    }
}
