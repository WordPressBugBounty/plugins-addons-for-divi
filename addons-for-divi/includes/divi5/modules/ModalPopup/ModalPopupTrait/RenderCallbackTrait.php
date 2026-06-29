<?php
/**
 * ModalPopup: Render callback trait.
 *
 * @package DiviTorqueLite\Modules\ModalPopup
 * @since   4.7.0
 */

namespace DiviTorqueLite\Modules\ModalPopup\ModalPopupTrait;

if (!defined('ABSPATH')) {
    exit;
}

use ET\Builder\Packages\Module\Module;

trait RenderCallbackTrait
{
    /**
     * Server-side render for the Modal Popup.
     *
     * Mirrors the VB edit component (minus the builder-inline class):
     *   <div class="dtq-module dtq-modalpopup dtq-modalpopup--size-X dtq-modalpopup--pos-X dtq-modalpopup--anim-X">
     *     [trigger button]
     *     <div class="dtq-modalpopup__overlay" data-dtq-close-overlay="on|off">
     *       <div class="dtq-modalpopup__box">[close][title][content]</div>
     *     </div>
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
        $advanced = $attrs['module']['advanced'] ?? [];

        // Mirrors src/divi5/modules/modal-popup/wrapper-class.js (builder=false).
        $size           = $advanced['size']['desktop']['value'] ?? 'medium';
        $position       = $advanced['position']['desktop']['value'] ?? 'center';
        $animation      = $advanced['animation']['desktop']['value'] ?? 'fade';
        $trigger_align  = $advanced['triggerAlign']['desktop']['value'] ?? 'left';
        $close_shape    = $advanced['closeShape']['desktop']['value'] ?? 'plain';
        $close_position = $advanced['closePosition']['desktop']['value'] ?? 'inside';

        $wrapper_class = implode(
            ' ',
            [
                'dtq-module',
                'dtq-modalpopup',
                'dtq-modalpopup--size-' . $size,
                'dtq-modalpopup--pos-' . $position,
                'dtq-modalpopup--anim-' . $animation,
                'dtq-modalpopup--trig-' . $trigger_align,
                'dtq-modalpopup--close-' . $close_shape,
                'dtq-modalpopup--close-' . $close_position,
            ]
        );

        $show_close       = ($advanced['showClose']['desktop']['value'] ?? 'on') === 'on';
        $close_on_overlay = ($advanced['closeOnOverlay']['desktop']['value'] ?? 'on') === 'on' ? 'on' : 'off';
        $close_on_esc     = ($advanced['closeOnEsc']['desktop']['value'] ?? 'on') === 'on' ? 'on' : 'off';
        $open_on          = $advanced['openOn']['desktop']['value'] ?? 'click';
        $load_delay       = $advanced['loadDelay']['desktop']['value'] ?? '';

        $content_type = $advanced['contentType']['desktop']['value'] ?? 'text';

        // Plain, non-navigating trigger button (no link — opening is handled by
        // initModals' delegated click). Avoids the Divi button element's <a href>.
        $trigger_label = $attrs['triggerText']['innerContent']['desktop']['value'] ?? 'Open Popup';
        $trigger_html  = sprintf(
            '<button type="button" class="dtq-modalpopup__trigger">%1$s</button>',
            esc_html($trigger_label)
        );
        $title_html   = $elements->render(['attrName' => 'title', 'tagName' => 'h3']);

        if ('layout' === $content_type) {
            $layout_id    = (int) ($advanced['layoutId']['desktop']['value'] ?? 0);
            $content_html = sprintf(
                '<div class="dtq-modalpopup__content dtq-modalpopup__content--layout">%1$s</div>',
                self::render_saved_layout($layout_id)
            );
        } else {
            $content_html = $elements->render(['attrName' => 'content']);
        }

        $close_html = $show_close
            ? '<button class="dtq-modalpopup__close" type="button" aria-label="Close"><span aria-hidden="true">&times;</span></button>'
            : '';

        $box_html = sprintf(
            '<div class="dtq-modalpopup__box" role="dialog" aria-modal="true">%1$s%2$s%3$s</div>',
            $close_html,
            $title_html,
            $content_html
        );

        $overlay_html = sprintf(
            '<div class="dtq-modalpopup__overlay" data-dtq-close-overlay="%1$s" data-dtq-close-esc="%2$s" aria-hidden="true">%3$s</div>',
            esc_attr($close_on_overlay),
            esc_attr($close_on_esc),
            $box_html
        );

        $modal_html = sprintf(
            '<div class="%1$s" data-dtq-open-on="%2$s" data-dtq-load-delay="%3$s"><div class="dtq-modalpopup__trigger-wrap">%4$s</div>%5$s</div>',
            esc_attr($wrapper_class),
            esc_attr($open_on),
            esc_attr($load_delay),
            $trigger_html,
            $overlay_html
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
                    $modal_html,
                ],
            ]
        );
    }

    /**
     * Render a saved Divi Library layout (or a page/post) by ID for the popup.
     *
     * Restricted to published `et_pb_layout`, `page` or `post` content to avoid
     * exposing private/arbitrary posts. Runs the content through both the D4
     * shortcode and D5 block parsers so a layout built in either renders.
     *
     * @param int $id The layout/post ID.
     *
     * @return string
     */
    private static function render_saved_layout($id)
    {
        if ($id <= 0) {
            return '';
        }

        $post = get_post($id);
        if (!$post || 'publish' !== $post->post_status) {
            return '';
        }
        if (!in_array($post->post_type, ['et_pb_layout', 'page', 'post'], true)) {
            return '';
        }

        return do_blocks(do_shortcode($post->post_content));
    }
}
