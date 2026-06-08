<?php
/**
 * DualButton: Render callback trait.
 *
 * @package DiviTorqueLite\Modules\DualButton
 * @since   4.4.0
 */

namespace DiviTorqueLite\Modules\DualButton\DualButtonTrait;

if (!defined('ABSPATH')) {
    exit;
}

use ET\Builder\Packages\Module\Module;

trait RenderCallbackTrait
{
    /**
     * Render a single button anchor.
     *
     * @param string $text     Button text.
     * @param string $link     Button URL.
     * @param string $target   Link target (`_self`|`_blank`).
     * @param string $modifier Button modifier class (`primary`|`secondary`).
     *
     * @return string
     */
    public static function render_button($text, $link, $target, $modifier)
    {
        return sprintf(
            '<a class="et_pb_button btn-el btn-el--%1$s" href="%2$s" target="%3$s">%4$s</a>',
            esc_attr($modifier),
            esc_url($link),
            esc_attr($target),
            et_core_esc_previously($text)
        );
    }

    /**
     * Render the optional connector between the two buttons.
     *
     * @param array $advanced The `module.advanced` attrs array.
     *
     * @return string
     */
    public static function render_connector($advanced)
    {
        $connector_type = $advanced['connectorType']['desktop']['value'] ?? 'empty';

        if ('empty' === $connector_type) {
            return '';
        }

        $inner = '';

        if ('text' === $connector_type) {
            $inner = esc_html($advanced['connectorText']['desktop']['value'] ?? '');
        } elseif ('icon' === $connector_type) {
            $icon_raw = $advanced['connectorIcon']['desktop']['value'] ?? '';

            if (is_array($icon_raw)) {
                $uni    = $icon_raw['unicode'] ?? '';
                $type   = $icon_raw['type'] ?? 'divi';
                $weight = $icon_raw['weight'] ?? '400';
            } else {
                $parts  = explode('||', (string) $icon_raw);
                $uni    = $parts[0] ?? '';
                $type   = $parts[1] ?? 'divi';
                $weight = $parts[2] ?? '400';
            }

            if ('' !== $uni) {
                $font = 'fa' === $type ? 'FontAwesome' : 'ETmodules';

                if (function_exists('dtq_inject_fa_icons')) {
                    // Reconstruct the legacy string form so the FA detector
                    // (which expects "unicode||type||weight") works for both shapes.
                    dtq_inject_fa_icons($uni . '||' . $type . '||' . $weight);
                }

                // $uni is already an HTML entity; output raw (do not esc_html).
                $inner = sprintf(
                    '<i class="dtq-et-icon" style="font-family:\'%1$s\';font-weight:%2$s">%3$s</i>',
                    esc_attr($font),
                    esc_attr($weight),
                    dtq_resolve_icon_unicode($uni)
                );
            }
        }

        return sprintf(
            '<div class="dtq-btn__connector dtq-btn__connector--%1$s">%2$s</div>',
            esc_attr($connector_type),
            $inner
        );
    }

    /**
     * Emit gap, alignment, and connector CSS as an inline <style> block.
     *
     * @param array  $advanced    The `module.advanced` attrs array.
     * @param string $selector    The module order-class selector (e.g. ".dtq_dual_button_0").
     *
     * @return string
     */
    public static function render_inline_styles($advanced, $selector)
    {
        $button_gap    = $advanced['buttonGap']['desktop']['value']    ?? '40px';
        $alignment     = $advanced['btnAlignment']['desktop']['value'] ?? 'left';
        $justify_map   = ['center' => 'center', 'right' => 'flex-end', 'left' => 'flex-start'];
        $justify       = $justify_map[$alignment] ?? 'flex-start';
        $connector_type = $advanced['connectorType']['desktop']['value'] ?? 'empty';

        $rules = [
            "$selector .btn-el--primary{margin-right:calc($button_gap / 2)}",
            "$selector .btn-el--secondary{margin-left:calc($button_gap / 2)}",
            "$selector .dtq-dual-btn{justify-content:$justify}",
        ];

        if ('empty' !== $connector_type) {
            $size         = $advanced['connectorSize']['desktop']['value']        ?? '30px';
            $bg           = $advanced['connectorBg']['desktop']['value']          ?? 'transparent';
            $text_color   = $advanced['connectorTextColor']['desktop']['value']   ?? '#333';
            $text_size    = $advanced['connectorTextSize']['desktop']['value']    ?? '14px';
            $radius       = $advanced['connectorRadius']['desktop']['value']      ?? '0px';
            $border_width = $advanced['connectorBorderWidth']['desktop']['value'] ?? '0px';
            $border_color = $advanced['connectorBorderColor']['desktop']['value'] ?? 'transparent';

            $rules[] = "$selector .dtq-btn__connector{"
                . "width:$size;height:$size;background:$bg;color:$text_color;"
                . "font-size:$text_size;border-radius:$radius;"
                . "box-shadow:0 0 0 $border_width $border_color}";
        }

        return '<style>' . implode('', $rules) . '</style>';
    }

    /**
     * Server-side render for the DualButton module.
     *
     * Mirrors the D4 markup:
     *     <div class="dtq-module dtq-dual-btn">
     *       <div class="dtq-btn-wrap">[primary] [connector]</div>
     *       <div class="dtq-btn-wrap">[secondary]</div>
     *     </div>
     *
     * @param array          $attrs    Block attributes.
     * @param string         $content  Block content (unused).
     * @param \WP_Block      $block    Parsed block.
     * @param object         $elements Module elements helper.
     *
     * @return string
     */
    public static function render_callback($attrs, $content, $block, $elements)
    {
        $advanced = $attrs['module']['advanced'] ?? [];

        $btn_a_text   = $advanced['btnAText']['desktop']['value'] ?? 'Button 1';
        $btn_a_link   = $advanced['btnALink']['desktop']['value'] ?? '#';
        $btn_a_target = $advanced['btnALinkTarget']['desktop']['value'] ?? '_self';

        $btn_b_text   = $advanced['btnBText']['desktop']['value'] ?? 'Button 2';
        $btn_b_link   = $advanced['btnBLink']['desktop']['value'] ?? '#';
        $btn_b_target = $advanced['btnBLinkTarget']['desktop']['value'] ?? '_self';

        // Gap and alignment CSS is emitted inline because Style::add() only processes
        // items returned by $elements->style() — raw array pushes are silently dropped.
        $order_class = $block->parsed_block['orderIndex'] !== null
            ? sprintf('dtq_dual_button_%d', $block->parsed_block['orderIndex'])
            : '';
        $custom_css  = '';
        if (!empty($order_class)) {
            $custom_css = self::render_inline_styles($advanced, '.' . $order_class);
        }

        $children = $custom_css . sprintf(
            '<div class="dtq-module dtq-dual-btn">' .
                '<div class="dtq-btn-wrap">%1$s%2$s</div>' .
                '<div class="dtq-btn-wrap">%3$s</div>' .
            '</div>',
            self::render_button($btn_a_text, $btn_a_link, $btn_a_target, 'primary'),
            self::render_connector($advanced),
            self::render_button($btn_b_text, $btn_b_link, $btn_b_target, 'secondary')
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
