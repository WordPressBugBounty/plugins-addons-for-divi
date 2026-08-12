<?php
/**
 * CtaBox: Render callback trait.
 *
 * @package DiviTorqueLite\Modules\CtaBox
 * @since   4.9.0
 */

namespace DiviTorqueLite\Modules\CtaBox\CtaBoxTrait;

if (!defined('ABSPATH')) {
    exit;
}

use ET\Builder\Packages\Module\Module;
use DiviTorqueLite\Modules\Shared\ButtonElement;
use ET\Builder\Packages\Module\Layout\Components\ModuleElements\ModuleElements;
use WP_Block;

trait RenderCallbackTrait
{
    /**
     * Build the wrapper class list.
     *
     * PHP twin of src/divi5/modules/cta-box/wrapper-class.js. The builder
     * preview and the published page must produce the same classes, or the
     * layout is right while editing and wrong on the site. Change one, change
     * the other.
     *
     * @param array $advanced The `module.advanced` attrs array.
     *
     * @return string
     */
    public static function wrapper_class($advanced)
    {
        $layout    = $advanced['layout']['desktop']['value'] ?? 'stacked';
        $alignment = $advanced['alignment']['desktop']['value'] ?? 'center';
        $overlay   = ($advanced['useOverlay']['desktop']['value'] ?? 'off') === 'on';

        // Both land in a class name, so neither may be taken on trust.
        $layouts    = ['stacked', 'inline', 'inline-reverse'];
        $alignments = ['left', 'center', 'right'];

        if (!in_array($layout, $layouts, true)) {
            $layout = 'stacked';
        }
        if (!in_array($alignment, $alignments, true)) {
            $alignment = 'center';
        }

        $classes = [
            'dtq-module',
            'dtq-cta-box',
            // Marks this as Divi 5 markup so the site-wide Divi 4 frontend.js
            // leaves it alone.
            'dtq-d5',
            'dtq-cta-box--layout-' . $layout,
            'dtq-cta-box--align-' . $alignment,
        ];

        if ($overlay) {
            $classes[] = 'dtq-cta-box--overlay';
        }

        return implode(' ', $classes);
    }

    /**
     * Server-side render for the Call To Action module.
     *
     * Mirrors src/divi5/modules/cta-box/edit.jsx:
     *
     *   <div class="dtq-module dtq-cta-box dtq-d5 …">
     *     <span class="dtq-cta-box__overlay" aria-hidden="true"></span>
     *     <div class="dtq-cta-box__text">[eyebrow][headline][body]</div>
     *     <div class="dtq-cta-box__actions">[btnA][btnB]</div>
     *   </div>
     *
     * @param array          $attrs    Block attributes.
     * @param string         $content  Block content (unused; this module has no children).
     * @param WP_Block       $block    Parsed block.
     * @param ModuleElements $elements Module elements helper.
     *
     * @return string
     */
    public static function render_callback($attrs, $content, $block, $elements)
    {
        $advanced = $attrs['module']['advanced'] ?? [];

        $use_overlay      = ($advanced['useOverlay']['desktop']['value'] ?? 'off') === 'on';
        $use_second_button = ($advanced['useSecondButton']['desktop']['value'] ?? 'off') === 'on';

        $overlay_html = $use_overlay
            ? '<span class="dtq-cta-box__overlay" aria-hidden="true"></span>'
            : '';

        $text_html = sprintf(
            '<div class="dtq-cta-box__text">%1$s%2$s%3$s</div>',
            $elements->render(['attrName' => 'subtitle']),
            $elements->render(['attrName' => 'title']),
            $elements->render(['attrName' => 'description'])
        );

        // Buttons are left out of the markup entirely rather than hidden with
        // CSS. A display:none anchor keeps its place in the tab order, so a
        // keyboard user would land on a control nobody can see. ButtonElement
        // returns an empty string when the label is blank, which is how an
        // author switches the primary button off.
        $buttons_html = ButtonElement::render($attrs['btnA'] ?? [], 'dtq-btn-default dtq-cta-box__btn--a');

        if ($use_second_button) {
            $buttons_html .= ButtonElement::render($attrs['btnB'] ?? [], 'dtq-btn-default dtq-cta-box__btn--b');
        }

        $actions_html = '' !== $buttons_html
            ? sprintf('<div class="dtq-cta-box__actions">%1$s</div>', $buttons_html)
            : '';

        $children = sprintf(
            '<div class="%1$s">%2$s%3$s%4$s</div>',
            esc_attr(self::wrapper_class($advanced)),
            $overlay_html,
            $text_html,
            $actions_html
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
