<?php
/**
 * IconBox: Render callback trait.
 *
 * @package DiviTorqueLite\Modules\IconBox
 * @since   4.4.0
 */

namespace DiviTorqueLite\Modules\IconBox\IconBoxTrait;

if (!defined('ABSPATH')) {
    exit;
}

use ET\Builder\Packages\Module\Module;
use ET\Builder\Packages\Module\Layout\Components\ModuleElements\ModuleElements;
use WP_Block;

trait RenderCallbackTrait
{
    /**
     * Render the icon (or image) markup.
     *
     * Mirrors the D4 `render_icon()` output.
     *
     * @param array $advanced The `module.advanced` attrs array.
     * @return string
     */
    public static function render_icon($advanced)
    {
        $use_image  = ($advanced['useImage']['desktop']['value'] ?? 'off') === 'on';
        $icon_value = $advanced['icon']['desktop']['value'] ?? '';
        $icon_image = $advanced['iconImage']['desktop']['value'] ?? '';
        $image_alt  = $advanced['imageAlt']['desktop']['value'] ?? '';

        $inner = '';

        if (!$use_image) {
            if (!empty($icon_value)) {
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
                if (!empty($uni)) {
                    $font  = 'fa' === $type ? 'FontAwesome' : 'ETmodules';
                    dtq_inject_fa_icons($uni . '||' . $type . '||' . $wt);
                    $inner = sprintf(
                        '<i class="dtq-icon dtq-et-icon" style="font-family:\'%1$s\';font-weight:%2$s">%3$s</i>',
                        $font,
                        esc_attr($wt),
                        dtq_resolve_icon_unicode($uni)
                    );
                }
            }
        } elseif (!empty($icon_image)) {
            $inner = sprintf(
                '<img class="dtq-icon-image" src="%1$s" alt="%2$s" />',
                esc_url($icon_image),
                esc_attr($image_alt)
            );
        }

        if (!empty($icon_value) || !empty($icon_image)) {
            return sprintf(
                '<div class="dtq-iconbox__icon-wrap"><div class="dtq-iconbox__icon">%1$s</div></div>',
                $inner
            );
        }

        return '';
    }

    /**
     * Render the badge markup.
     *
     * @param array $advanced The `module.advanced` attrs array.
     * @return string
     */
    public static function render_badge($advanced)
    {
        $badge_text = $advanced['badgeText']['desktop']['value'] ?? '';

        if (empty($badge_text)) {
            return '';
        }

        return sprintf(
            '<div class="dtq-iconbox__badge">%1$s</div>',
            et_core_esc_previously($badge_text)
        );
    }

    /**
     * Server-side render for the IconBox module.
     *
     * Mirrors the D4 markup and the VB edit component:
     *   <div class="dtq-module dtq-iconbox">
     *     [badge] [absolute icon]
     *     <div class="dtq-iconbox-inner dtq-bg-support">
     *       [normal icon] [title] [description]
     *     </div>
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
        $advanced  = $attrs['module']['advanced'] ?? [];
        $placement = $advanced['iconPlacement']['desktop']['value'] ?? 'normal';

        $badge       = self::render_badge($advanced);
        $icon        = self::render_icon($advanced);
        $title       = $elements->render(['attrName' => 'title']);
        $description = $elements->render(['attrName' => 'description']);

        $children = sprintf(
            '<div class="dtq-module dtq-iconbox">%1$s%2$s<div class="dtq-iconbox-inner dtq-bg-support">%3$s%4$s%5$s</div></div>',
            $badge,
            'absolute' === $placement ? $icon : '',
            'absolute' !== $placement ? $icon : '',
            $title,
            $description
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
