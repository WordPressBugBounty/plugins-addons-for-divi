<?php
/**
 * Contact Form 7: server-side render callback.
 *
 * @package DiviTorqueLite\Modules\ContactForm7
 * @since   4.6.0
 */

namespace DiviTorqueLite\Modules\ContactForm7\ContactForm7Trait;

if (!defined('ABSPATH')) {
    exit;
}

use ET\Builder\Packages\Module\Module;

trait RenderCallbackTrait
{
    /**
     * Read a desktop attr value with a device fallback (empty = unset).
     *
     * @param array  $attrs   Module attributes.
     * @param string $path    Dot path.
     * @param mixed  $default Fallback.
     *
     * @return mixed
     */
    protected static function get_attr($attrs, $path, $default = '')
    {
        $value = $attrs;
        foreach (explode('.', $path) as $key) {
            if (!is_array($value) || !isset($value[$key])) {
                return $default;
            }
            $value = $value[$key];
        }

        if (is_array($value)) {
            if (isset($value['desktop']['value']) && '' !== $value['desktop']['value']) {
                return $value['desktop']['value'];
            }
            return $default;
        }

        return ('' !== $value && null !== $value) ? $value : $default;
    }

    /**
     * Resolve the header icon glyph from an icon-picker value.
     *
     * @param mixed $icon_value Icon attr value.
     *
     * @return string
     */
    protected static function header_icon_glyph($icon_value)
    {
        if (empty($icon_value)) {
            return '';
        }
        if (is_array($icon_value)) {
            $uni  = $icon_value['unicode'] ?? '';
            $type = $icon_value['type'] ?? 'divi';
            $wt   = $icon_value['weight'] ?? '400';
        } else {
            $parts = explode('||', (string) $icon_value);
            $uni   = $parts[0] ?? '';
            $type  = $parts[1] ?? 'divi';
            $wt    = $parts[2] ?? '400';
        }
        if (empty($uni)) {
            return '';
        }
        if (function_exists('dtq_inject_fa_icons')) {
            dtq_inject_fa_icons($uni . '||' . $type . '||' . $wt);
        }
        return function_exists('dtq_resolve_icon_unicode') ? dtq_resolve_icon_unicode($uni) : $uni;
    }

    /**
     * Render the Contact Form 7 module.
     *
     * @param array     $attrs    Module attributes.
     * @param string    $content  Inner content.
     * @param \WP_Block $block    Block instance.
     * @param object    $elements Elements helper.
     *
     * @return string
     */
    public static function render_callback($attrs, $content, $block, $elements)
    {
        $form_id     = self::get_attr($attrs, 'module.advanced.formId', '');
        $use_header  = self::get_attr($attrs, 'module.advanced.useFormHeader', 'off');
        $use_icon    = self::get_attr($attrs, 'module.advanced.useIcon', 'off');
        $header_img  = self::get_attr($attrs, 'module.advanced.headerImg', '');
        $title       = self::get_attr($attrs, 'module.advanced.formHeaderTitle', '');
        $text        = self::get_attr($attrs, 'module.advanced.formHeaderText', '');
        $cr_styles   = self::get_attr($attrs, 'module.advanced.crCustomStyles', 'off');
        $fullwidth   = self::get_attr($attrs, 'module.advanced.useFormButtonFullwidth', 'off');
        $btn_align   = 'on' === $fullwidth ? 'fullwidth' : self::get_attr($attrs, 'module.advanced.buttonAlignment', 'left');

        // Form header.
        $form_header = '';
        if ('on' === $use_header && ('' !== $title || '' !== $text)) {
            if ('on' === $use_icon) {
                $glyph      = self::header_icon_glyph(self::get_attr($attrs, 'module.advanced.headerIcon', ''));
                $icon_image = sprintf('<div class="dtq-form-header-icon"><span class="et-pb-icon">%1$s</span></div>', $glyph);
            } else {
                $icon_image = '' !== $header_img
                    ? sprintf('<div class="dtq-form-header-image"><img src="%1$s" alt="" /></div>', esc_url($header_img))
                    : '';
            }

            $title_html = '' !== $title ? sprintf('<h2 class="dtq-form-header-title">%1$s</h2>', esc_html($title)) : '';
            $text_html  = '' !== $text ? sprintf('<div class="dtq-form-header-text">%1$s</div>', esc_html($text)) : '';

            $form_header = sprintf(
                '<div class="dtq-form-header-container"><div class="dtq-form-header">%1$s<div class="dtq-form-header-info">%2$s%3$s</div></div></div>',
                $icon_image,
                $title_html,
                $text_html
            );
        }

        // CF7 form.
        if ('' === $form_id || '0' === (string) $form_id) {
            $form = esc_html__('Please select a Contact Form 7.', 'addons-for-divi');
        } else {
            $form = do_shortcode(sprintf('[contact-form-7 id="%1$s"]', esc_attr($form_id)));
        }

        $cr_class = 'on' === $cr_styles ? 'dtq-cf7-cr' : '';

        $children = sprintf(
            '<div class="dtq-module dtq-cf7 dtq-cf7-container dtq-cf7-styler-button-%1$s">%2$s<div class="dtq-cf7-styler %3$s">%4$s</div></div>',
            esc_attr($btn_align),
            $form_header,
            esc_attr(trim($cr_class)),
            $form
        );

        return Module::render(
            [
                'orderIndex'          => $block->parsed_block['orderIndex'] ?? 0,
                'storeInstance'       => $block->parsed_block['storeInstance'] ?? null,
                'attrs'               => $attrs,
                'elements'            => $elements,
                'id'                  => $block->parsed_block['id'] ?? '',
                'name'                => $block->block_type->name ?? '',
                'moduleClassName'     => 'dtq_cf7_styler',
                'classnamesFunction'  => [self::class, 'module_classnames'],
                'stylesComponent'     => [self::class, 'module_styles'],
                'scriptDataComponent' => [self::class, 'module_script_data'],
                'children'            => $children,
            ]
        );
    }
}
