<?php
/**
 * LogoGridItem: Render callback trait.
 *
 * @package DiviTorqueLite\Modules\LogoGridItem
 * @since   4.4.0
 */

namespace DiviTorqueLite\Modules\LogoGridItem\LogoGridItemTrait;

if (!defined('ABSPATH')) {
    exit;
}

use ET\Builder\Packages\Module\Module;

trait RenderCallbackTrait
{
    /**
     * Server-side render for a logo grid item.
     *
     * Mirrors the D4 markup and the VB edit component:
     *   <div class="dtq-module dtq-child dtq-logo-grid__item">
     *     <div class="dtq-logo-grid__item__inner [dtq-tooltip]" [data-tippy-*]>
     *       <img src alt/>
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

        $use_tooltip       = ($advanced['useTooltip']['desktop']['value'] ?? 'off') === 'on';
        $tooltip_text      = $advanced['tooltipText']['desktop']['value'] ?? 'Tooltip!';
        $tooltip_position  = $advanced['tooltipPosition']['desktop']['value'] ?? 'top';
        $tooltip_animation = $advanced['tooltipAnimation']['desktop']['value'] ?? 'scale';
        $tooltip_theme     = $advanced['tooltipTheme']['desktop']['value'] ?? 'dark';

        // Logo image. The D5 upload field stores an object (`{ src, ... }`).
        $logo_val = $attrs['logo']['innerContent']['desktop']['value'] ?? '';
        $logo_src = is_array($logo_val) ? ($logo_val['src'] ?? '') : $logo_val;
        $logo_alt = $attrs['logo']['advanced']['alt']['desktop']['value'] ?? '';

        $img = '';
        if (!empty($logo_src)) {
            $img = sprintf(
                '<img src="%1$s" alt="%2$s"/>',
                esc_url($logo_src),
                esc_attr($logo_alt)
            );
        }

        // Tooltip: same data-tippy-* attributes as D4. The libraries are
        // enqueued only when a tooltip is actually used (handles registered
        // in includes/assets.php).
        $tippy_attrs = '';
        if ($use_tooltip) {
            wp_enqueue_script('divi-torque-lite-popper');
            wp_enqueue_script('divi-torque-lite-tippy');
            wp_enqueue_style('divi-torque-lite-tippy');

            $tippy_attrs = sprintf(
                ' data-tippy-content="%1$s" data-tippy-placement="%2$s" data-tippy-animation="%3$s" data-tippy-theme="%4$s"',
                esc_attr($tooltip_text),
                esc_attr($tooltip_position),
                esc_attr($tooltip_animation),
                esc_attr($tooltip_theme)
            );
        }

        $item_html = sprintf(
            '<div class="dtq-module dtq-child dtq-logo-grid__item"><div class="dtq-logo-grid__item__inner%1$s"%2$s>%3$s</div></div>',
            $use_tooltip ? ' dtq-tooltip' : '',
            $tippy_attrs,
            $img
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
                    $item_html,
                ],
            ]
        );
    }
}
