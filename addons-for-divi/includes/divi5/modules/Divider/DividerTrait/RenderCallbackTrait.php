<?php
/**
 * Divider: Render callback trait.
 *
 * @package DiviTorqueLite\Modules\Divider
 * @since   4.4.0
 */

namespace DiviTorqueLite\Modules\Divider\DividerTrait;

if (!defined('ABSPATH')) {
    exit;
}

use ET\Builder\Packages\Module\Module;
use ET\Builder\Packages\Module\Layout\Components\ModuleElements\ModuleElements;
use WP_Block;

trait RenderCallbackTrait
{
    use ShapesTrait;

    /**
     * Server-side render for the Divider module.
     *
     * Mirrors the D4 markup:
     *   <div class="dtq-module dtq-divider">
     *     [left border]  [element]  [right border]  [shape]
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
        $advanced = $attrs['module']['advanced'] ?? [];

        $active_element = $advanced['activeElement']['desktop']['value'] ?? 'icon';
        $use_shape      = $advanced['useShape']['desktop']['value'] ?? 'off';

        $left_border  = self::render_border('start', $use_shape);
        $element      = self::render_element($attrs, $active_element);
        $right_border = self::render_border('end', $use_shape);
        $shape        = self::render_shape($advanced, $use_shape);

        $children = sprintf(
            '<div class="dtq-module dtq-divider">%1$s%2$s%3$s%4$s</div>',
            $left_border,
            $element,
            $right_border,
            $shape
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

    /**
     * Render the divider content element (text / icon / image).
     *
     * @param array  $attrs          Block attributes.
     * @param string $active_element The selected element type.
     *
     * @return string
     */
    public static function render_element($attrs, $active_element)
    {
        $advanced = $attrs['module']['advanced'] ?? [];

        if ('text' === $active_element) {
            $title = $advanced['title']['desktop']['value'] ?? '';
            if (empty($title)) {
                return '';
            }

            $tag = $attrs['title']['decoration']['font']['font']['desktop']['value']['headingLevel'] ?? 'h2';
            if (!in_array($tag, ['h1', 'h2', 'h3', 'h4', 'h5', 'h6'], true)) {
                $tag = 'h2';
            }

            return sprintf(
                '<%1$s class="dtq-divider__element dtq-divider__text"><span>%2$s</span></%1$s>',
                esc_attr($tag),
                et_core_esc_previously($title)
            );
        }

        if ('icon' === $active_element) {
            $icon_raw = $advanced['icon']['desktop']['value'] ?? '';

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

            if (empty($uni)) {
                return '';
            }

            $font = 'fa' === $type ? 'FontAwesome' : 'ETmodules';

            if (function_exists('dtq_inject_fa_icons')) {
                // Reconstruct the legacy string form so the FA detector (which
                // expects "unicode||type||weight") works for both value shapes.
                dtq_inject_fa_icons($uni . '||' . $type . '||' . $weight);
            }

            return sprintf(
                '<div class="dtq-divider__icon dtq-divider__element"><i class="dtq-icon dtq-et-icon" style="font-family:\'%1$s\';font-weight:%2$s">%3$s</i></div>',
                esc_attr($font),
                esc_attr($weight),
                dtq_resolve_icon_unicode($uni)
            );
        }

        if ('image' === $active_element) {
            $img_url = $advanced['imgUrl']['desktop']['value'] ?? '';
            if (empty($img_url)) {
                return '';
            }

            return sprintf(
                '<div class="dtq-divider__image dtq-divider__element"><img src="%1$s" alt="" /></div>',
                esc_url($img_url)
            );
        }

        return '';
    }

    /**
     * Render a border (start / end). Only output when shapes are off.
     *
     * @param string $side      Either 'start' or 'end'.
     * @param string $use_shape The use-shape toggle value.
     *
     * @return string
     */
    public static function render_border($side, $use_shape)
    {
        if ('off' !== $use_shape) {
            return '';
        }

        return sprintf(
            '<div class="dtq-divider__border dtq-divider__border-%1$s"></div>',
            esc_attr($side)
        );
    }

    /**
     * Render the bottom shape SVG. Only output when shapes are on.
     *
     * @param array  $advanced  The `module.advanced` attrs.
     * @param string $use_shape The use-shape toggle value.
     *
     * @return string
     */
    public static function render_shape($advanced, $use_shape)
    {
        if ('on' !== $use_shape) {
            return '';
        }

        $shape  = $advanced['shape']['desktop']['value'] ?? 'shape_1';
        $shapes = self::get_shapes();
        $svg    = $shapes[$shape] ?? '';

        if (empty($svg)) {
            return '';
        }

        return '<div class="dtq-divider__shape">' . $svg . '</div>';
    }
}
