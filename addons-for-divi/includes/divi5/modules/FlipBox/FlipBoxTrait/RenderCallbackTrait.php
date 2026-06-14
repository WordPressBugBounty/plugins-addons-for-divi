<?php
/**
 * FlipBox: Render callback trait.
 *
 * @package DiviTorqueLite\Modules\FlipBox
 * @since   4.4.0
 */

namespace DiviTorqueLite\Modules\FlipBox\FlipBoxTrait;

if (!defined('ABSPATH')) {
    exit;
}

use ET\Builder\Packages\Module\Module;
use ET\Builder\Packages\Module\Layout\Components\ModuleElements\ModuleElements;
use WP_Block;

trait RenderCallbackTrait
{
    /**
     * Build the flip animation classes for the wrapper. Mirrors the D4
     * render() class logic exactly — keep in lockstep with the JS twin in
     * src/divi5/modules/flip-box/edit.jsx.
     *
     * @param array $advanced The `module.advanced` attrs array.
     * @return string[]
     */
    public static function build_animation_classes($advanced)
    {
        $val = function ($key, $fallback) use ($advanced) {
            return $advanced[$key]['desktop']['value'] ?? $fallback;
        };

        $animation_type     = $val('animationType', 'flip');
        $animation_3d       = $val('animation3d', 'off');
        $direction          = $val('direction', 'right');
        $direction_alt      = $val('directionAlt', 'h');
        $direction_diagonal = $val('directionDiagonal', 'right');

        $classes = ['dtq-flipbox--' . $animation_type];

        if ('on' === $animation_3d) {
            $classes[] = 'dtq-flipbox-3d';
        }
        if (in_array($animation_type, ['flip', 'slide', 'push'], true)) {
            $classes[] = "dtq-{$animation_type}-{$direction}";
        }
        if ('diagonal' === $animation_type) {
            $classes[] = "dtq-{$animation_type}-{$direction_diagonal}";
        }
        if ('rotate_3d' === $animation_type) {
            $classes[] = "dtq-{$animation_type}-{$direction_alt}";
        }

        return $classes;
    }

    /**
     * Render the media (icon or image) for one side. Mirrors the D4
     * render_media_front()/render_media_back() output and the VB edit
     * component exactly:
     *   icon:  <div class="dtq-flipbox-figure-{side}">
     *            <div class="dtq-flipbox-icon dtq-flipbox-icon-{side}"><i class="dtq-et-icon">…</i></div>
     *          </div>
     *   image: <div class="dtq-flipbox-figure-{side}">
     *            <div class="dtq-flipbox-img-{side}"><img src alt /></div>
     *          </div>
     *
     * @param string $side  'front' or 'back'.
     * @param array  $attrs Block attributes.
     * @return string
     */
    public static function render_media($side, $attrs)
    {
        $advanced   = $attrs['module']['advanced'] ?? [];
        $media_type = $advanced[('front' === $side ? 'frontMediaType' : 'backMediaType')]['desktop']['value'] ?? 'icon';

        if ('none' === $media_type) {
            return '';
        }

        if ('icon' === $media_type) {
            $icon_value = $advanced[('front' === $side ? 'frontIcon' : 'backIcon')]['desktop']['value'] ?? '';

            // The D5 icon picker stores an object; accept the legacy D4
            // "unicode||type||weight" string too (pre-expansion values).
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

            if ('' === $uni) {
                return '';
            }

            $font = 'fa' === $type ? 'FontAwesome' : 'ETmodules';
            dtq_inject_fa_icons($uni . '||' . $type . '||' . $wt);

            return sprintf(
                '<div class="dtq-flipbox-figure-%1$s"><div class="dtq-flipbox-icon dtq-flipbox-icon-%1$s"><i class="dtq-et-icon" style="font-family:\'%2$s\';font-weight:%3$s">%4$s</i></div></div>',
                esc_attr($side),
                esc_attr($font),
                esc_attr($wt),
                dtq_resolve_icon_unicode($uni)
            );
        }

        if ('image' === $media_type) {
            $image_attr = 'front' === $side ? ($attrs['frontImage'] ?? []) : ($attrs['backImage'] ?? []);
            $image_val  = $image_attr['innerContent']['desktop']['value'] ?? '';
            $image_src  = is_array($image_val) ? ($image_val['src'] ?? '') : $image_val;
            $image_alt  = $image_attr['advanced']['alt']['desktop']['value'] ?? '';

            if (empty($image_src)) {
                return '';
            }

            return sprintf(
                '<div class="dtq-flipbox-figure-%1$s"><div class="dtq-flipbox-img-%1$s"><img src="%2$s" alt="%3$s"/></div></div>',
                esc_attr($side),
                esc_url($image_src),
                esc_attr($image_alt)
            );
        }

        return '';
    }

    /**
     * Server-side render for the FlipBox module.
     *
     * Mirrors the D4 markup (and the VB edit component) exactly:
     *   <div class="dtq-module dtq-flipbox dtq-flipbox--{type} ...">
     *     <div class="dtq-flipbox-inner">
     *       <div class="dtq-flipbox-card-container">
     *         <div class="dtq-flipbox-front-card dtq-flipbox-card">
     *           <div class="dtq-flipbox-card-inner">
     *             <div class="dtq-flipbox-front-content dtq-flipbox-content">
     *               [figure]
     *               <div class="dtq-flipbox-content-wrap">[title][subtitle][desc]</div>
     *             </div>
     *           </div>
     *         </div>
     *         <div class="dtq-flipbox-back-card dtq-flipbox-card">
     *           ... back side (+ optional button wrap) ...
     *         </div>
     *         <div class="dtq-flank"></div>
     *       </div>
     *     </div>
     *   </div>
     *
     * The flip itself is pure CSS (module.scss) keyed off the same
     * wrapper/animation classes D4 used — no frontend JS.
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
        $advanced = $attrs['module']['advanced'] ?? [];

        $use_button = ($advanced['useButton']['desktop']['value'] ?? 'off') === 'on';

        $classes = self::build_animation_classes($advanced);

        $front_media = self::render_media('front', $attrs);
        $back_media  = self::render_media('back', $attrs);

        $front_title    = $elements->render(['attrName' => 'frontTitle']);
        $front_subtitle = $elements->render(['attrName' => 'frontSubtitle']);
        $front_desc     = $elements->render(['attrName' => 'frontDescription']);
        $back_title     = $elements->render(['attrName' => 'backTitle']);
        $back_subtitle  = $elements->render(['attrName' => 'backSubtitle']);
        $back_desc      = $elements->render(['attrName' => 'backDescription']);

        $button_html = '';
        if ($use_button) {
            $button_html = sprintf(
                '<div class="dtq-flipbox-btn-wrap">%1$s</div>',
                $elements->render(['attrName' => 'button'])
            );
        }

        $front_card = sprintf(
            '<div class="dtq-flipbox-front-card dtq-flipbox-card"><div class="dtq-flipbox-card-inner"><div class="dtq-flipbox-front-content dtq-flipbox-content">%1$s<div class="dtq-flipbox-content-wrap">%2$s%3$s%4$s</div></div></div></div>',
            $front_media,
            $front_title,
            $front_subtitle,
            $front_desc
        );

        $back_card = sprintf(
            '<div class="dtq-flipbox-back-card dtq-flipbox-card"><div class="dtq-flipbox-card-inner"><div class="dtq-flipbox-back-content dtq-flipbox-content">%1$s<div class="dtq-flipbox-content-wrap">%2$s%3$s%4$s%5$s</div></div></div></div>',
            $back_media,
            $back_title,
            $back_subtitle,
            $back_desc,
            $button_html
        );

        $children = sprintf(
            '<div class="dtq-module dtq-flipbox %1$s"><div class="dtq-flipbox-inner"><div class="dtq-flipbox-card-container">%2$s%3$s<div class="dtq-flank"></div></div></div></div>',
            esc_attr(implode(' ', $classes)),
            $front_card,
            $back_card
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
