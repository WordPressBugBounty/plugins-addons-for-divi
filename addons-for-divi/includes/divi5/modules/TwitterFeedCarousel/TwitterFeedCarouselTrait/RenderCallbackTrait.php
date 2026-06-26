<?php
/**
 * Twitter Carousel: server-side render callback (Swiper).
 *
 * @package DiviTorqueLite\Modules\TwitterFeedCarousel
 * @since   4.6.0
 */

namespace DiviTorqueLite\Modules\TwitterFeedCarousel\TwitterFeedCarouselTrait;

if (!defined('ABSPATH')) {
    exit;
}

use ET\Builder\Packages\Module\Module;
use DiviTorqueLite\Modules\Shared\TwitterApi;
use DiviTorqueLite\Modules\SharedCarousel\CarouselEngine;

trait RenderCallbackTrait
{
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

    public static function render_callback($attrs, $content, $block, $elements)
    {
        $advanced = $attrs['module']['advanced'] ?? [];
        $user     = self::get_attr($attrs, 'module.advanced.userName', '');
        $key      = self::get_attr($attrs, 'module.advanced.consumerKey', '');
        $secret   = self::get_attr($attrs, 'module.advanced.consumerSecret', '');
        $sort_by  = self::get_attr($attrs, 'module.advanced.sortBy', 'recent-posts');
        $limit    = (int) self::get_attr($attrs, 'module.advanced.tweetsLimit', '8');
        $align    = self::get_attr($attrs, 'module.advanced.alignment', 'left');

        if ('' === trim($user) || '' === trim($key) || '' === trim($secret)) {
            $inner = sprintf(
                '<div class="dtq-tweet-error-message">%1$s</div>',
                esc_html__('Add your X (Twitter) handle and paid API credentials to display the feed.', 'addons-for-divi')
            );
        } else {
            $items = TwitterApi::get_tweets($key, $secret, $user, $limit);
            $items = TwitterApi::sort_and_limit($items, $sort_by, $limit);

            if (empty($items)) {
                $inner = sprintf(
                    '<div class="dtq-tweet-error-message">%1$s</div>',
                    esc_html__('No tweets found. Check the username and that your X API plan allows reading timelines.', 'addons-for-divi')
                );
            } else {
                $built     = CarouselEngine::build_swiper_config($advanced);
                $config    = $built['config'];
                $show_nav  = $built['show_nav'];
                $show_pagi = $built['show_pagi'];

                $classes   = CarouselEngine::base_wrapper_classes($advanced, 'dtq-twitter-feed-carousel');
                $nav_html  = $show_nav ? (CarouselEngine::render_arrow($advanced, 'prev') . CarouselEngine::render_arrow($advanced, 'next')) : '';
                $pagi_html = $show_pagi ? '<div class="swiper-pagination"></div>' : '';

                $inner = sprintf(
                    '<div class="%1$s"><div class="swiper" data-swiper-config="%2$s"><div class="swiper-wrapper">%3$s</div></div>%4$s%5$s</div>',
                    esc_attr(implode(' ', $classes)),
                    esc_attr(wp_json_encode($config)),
                    TwitterApi::build_items_html($advanced, $items, true),
                    $nav_html,
                    $pagi_html
                );
            }
        }

        $children = sprintf('<div class="dtq-module dtq-twitter-feed dtq-twitter-%1$s">%2$s</div>', esc_attr($align), $inner);

        return Module::render(
            [
                'orderIndex'          => $block->parsed_block['orderIndex'] ?? 0,
                'storeInstance'       => $block->parsed_block['storeInstance'] ?? null,
                'attrs'               => $attrs,
                'elements'            => $elements,
                'id'                  => $block->parsed_block['id'] ?? '',
                'name'                => $block->block_type->name ?? '',
                'moduleClassName'     => 'dtq_twitter_feed_carousel',
                'classnamesFunction'  => [self::class, 'module_classnames'],
                'stylesComponent'     => [self::class, 'module_styles'],
                'scriptDataComponent' => [self::class, 'module_script_data'],
                'children'            => $children,
            ]
        );
    }
}
