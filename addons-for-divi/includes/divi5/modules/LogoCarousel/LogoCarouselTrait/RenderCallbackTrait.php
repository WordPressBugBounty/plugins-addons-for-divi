<?php
/**
 * LogoCarousel: Render callback trait (Swiper) — thin over CarouselEngine.
 *
 * @package DiviTorqueLite\Modules\LogoCarousel
 * @since   4.5.0
 */

namespace DiviTorqueLite\Modules\LogoCarousel\LogoCarouselTrait;

if (!defined('ABSPATH')) {
    exit;
}

use ET\Builder\Packages\Module\Module;
use DiviTorqueLite\Modules\SharedCarousel\CarouselEngine;

trait RenderCallbackTrait
{
    /**
     * Server-side render for the Logo Carousel parent.
     *
     * @param array  $attrs    Block attributes.
     * @param string $content  Rendered child slides (each is a `.swiper-slide`).
     * @param object $block    Parsed block.
     * @param object $elements Module elements helper.
     *
     * @return string
     */
    public static function render_callback($attrs, $content, $block, $elements)
    {
        $advanced = $attrs['module']['advanced'] ?? [];

        $built     = CarouselEngine::build_swiper_config($advanced);
        $config    = $built['config'];
        $show_nav  = $built['show_nav'];
        $show_pagi = $built['show_pagi'];

        $classes   = CarouselEngine::base_wrapper_classes($advanced, 'dtq-logo-carousel');
        // Logo-specific: the hover-effect class (zoom/fade/grayscale).
        $classes[] = $advanced['logoHover']['desktop']['value'] ?? 'zoom_in';

        $nav_html  = $show_nav ? (CarouselEngine::render_arrow($advanced, 'prev') . CarouselEngine::render_arrow($advanced, 'next')) : '';
        $pagi_html = $show_pagi ? '<div class="swiper-pagination"></div>' : '';

        $carousel = sprintf(
            '<div class="%1$s"><div class="swiper" data-swiper-config="%2$s"><div class="swiper-wrapper">%3$s</div></div>%4$s%5$s</div>',
            esc_attr(implode(' ', $classes)),
            esc_attr(wp_json_encode($config)),
            $content,
            $nav_html,
            $pagi_html
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
                    $carousel,
                ],
            ]
        );
    }
}
