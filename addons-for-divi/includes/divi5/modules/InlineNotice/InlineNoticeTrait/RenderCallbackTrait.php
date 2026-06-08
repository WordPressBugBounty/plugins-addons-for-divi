<?php
/**
 * InlineNotice: Render callback trait.
 *
 * @package DiviTorqueLite\Modules\InlineNotice
 * @since   4.4.0
 */

namespace DiviTorqueLite\Modules\InlineNotice\InlineNoticeTrait;

if (!defined('ABSPATH')) {
    exit;
}

use ET\Builder\Packages\Module\Module;
use ET\Builder\Packages\Module\Layout\Components\ModuleElements\ModuleElements;
use WP_Block;

trait RenderCallbackTrait
{
    /**
     * Render the figure (icon or image) markup.
     *
     * Mirrors D4 `render_figure()` / `render_icon()`.
     *
     * @param array $attrs Block attributes.
     *
     * @return string
     */
    public static function render_figure($attrs)
    {
        $advanced = $attrs['module']['advanced'] ?? [];
        $use_icon = ($advanced['useIcon']['desktop']['value'] ?? 'on') === 'on';

        if ($use_icon) {
            $icon = $advanced['icon']['desktop']['value'] ?? '';

            if (is_array($icon)) {
                $uni  = $icon['unicode'] ?? '';
                $type = $icon['type'] ?? 'divi';
                $wt   = $icon['weight'] ?? '400';
            } else {
                $p    = explode('||', (string) $icon);
                $uni  = $p[0] ?? '';
                $type = $p[1] ?? 'divi';
                $wt   = $p[2] ?? '400';
            }

            if ('' !== $uni) {
                $font = 'fa' === $type ? 'FontAwesome' : 'ETmodules';
                dtq_inject_fa_icons($uni . '||' . $type . '||' . $wt);

                return sprintf(
                    '<div class="dtq-alert-icon"><i class="dtq-et-icon dtq-alert-icon-inner" style="font-family:\'%1$s\';font-weight:%2$s">%3$s</i></div>',
                    esc_attr($font),
                    esc_attr($wt),
                    dtq_resolve_icon_unicode($uni)
                );
            }

            return '';
        }

        $image     = $advanced['image']['desktop']['value'] ?? '';
        $image_alt = $advanced['imageAlt']['desktop']['value'] ?? '';

        if ($image) {
            return sprintf(
                '<div class="dtq-alert-icon"><img class="dtq-alert-icon-inner" src="%1$s" alt="%2$s"></div>',
                esc_url($image),
                esc_attr($image_alt)
            );
        }

        return '';
    }

    /**
     * Render the dismiss markup. Mirrors D4 `render_dismiss()`.
     *
     * @param array $attrs Block attributes.
     *
     * @return string
     */
    public static function render_dismiss($attrs)
    {
        $advanced     = $attrs['module']['advanced'] ?? [];
        $show_dismiss = ($advanced['showDismiss']['desktop']['value'] ?? 'on') === 'on';

        if ($show_dismiss) {
            return '<div class="dtq-alert-dismiss">✕</div>';
        }

        return '';
    }

    /**
     * Server-side render for the InlineNotice module.
     *
     * Mirrors the D4 markup:
     *   <div class="dtq-module dtq-alert dtq-alert-{type}">
     *     [figure] <div class="dtq-alert-content">[title][desc]</div> [dismiss]
     *   </div>
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
        $advanced   = $attrs['module']['advanced'] ?? [];
        $alert_type = $advanced['alertType']['desktop']['value'] ?? 'danger';

        $figure  = self::render_figure($attrs);
        $title   = $elements->render(['attrName' => 'title']);
        $desc    = $elements->render(['attrName' => 'content']);
        $dismiss = self::render_dismiss($attrs);

        $children = sprintf(
            '<div class="dtq-module dtq-alert dtq-alert-%1$s">%2$s<div class="dtq-alert-content">%3$s%4$s</div>%5$s</div>',
            esc_attr($alert_type),
            $figure,
            $title,
            $desc,
            $dismiss
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
