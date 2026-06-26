<?php
/**
 * News Ticker: server-side render callback.
 *
 * @package DiviTorqueLite\Modules\NewsTicker
 * @since   4.6.0
 */

namespace DiviTorqueLite\Modules\NewsTicker\NewsTickerTrait;

if (!defined('ABSPATH')) {
    exit;
}

use ET\Builder\Packages\Module\Module;

trait RenderCallbackTrait
{
    /**
     * Read a desktop attr value with a device fallback. Treats empty strings
     * as unset so module.json defaults win.
     *
     * @param array  $attrs   Module attributes.
     * @param string $path    Dot path under attrs (e.g. module.advanced.speed).
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
     * Build the news <li> items from a WP_Query.
     *
     * @param array $attrs Module attributes.
     *
     * @return string
     */
    protected static function render_news($attrs)
    {
        $post_type  = self::get_attr($attrs, 'module.advanced.postType', 'post');
        $categories = self::get_attr($attrs, 'module.advanced.includeCategories', '');
        $order_by   = self::get_attr($attrs, 'module.advanced.orderBy', 'date');
        $order      = self::get_attr($attrs, 'module.advanced.order', 'ASC');
        $news_count = (int) self::get_attr($attrs, 'module.advanced.newsCount', '5');
        $offset     = (int) self::get_attr($attrs, 'module.advanced.postOffset', '0');
        $exclude    = self::get_attr($attrs, 'module.advanced.excludePosts', '');

        $query_args = [
            'posts_per_page' => $news_count > 0 ? $news_count : 5,
            'post_type'      => sanitize_text_field($post_type),
            'post_status'    => 'publish',
            'orderby'        => sanitize_text_field($order_by),
            'order'          => sanitize_text_field($order),
            'offset'         => $offset,
        ];

        if ('post' === $post_type && '' !== $categories) {
            $query_args['cat'] = sanitize_text_field($categories);
        }

        if ('' !== $exclude) {
            $ids = array_filter(array_map('absint', array_map('trim', explode(',', $exclude))));
            if (!empty($ids)) {
                $query_args['post__not_in'] = $ids;
            }
        }

        $query  = new \WP_Query($query_args);
        $output = '';

        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $output .= sprintf(
                    '<li><a href="%1$s">%2$s</a></li>',
                    esc_url(get_the_permalink()),
                    esc_html(get_the_title())
                );
            }
        }

        wp_reset_postdata();

        if ('' === $output) {
            $output = '<li>' . esc_html__('No News Found', 'addons-for-divi') . '</li>';
        }

        return $output;
    }

    /**
     * Render the News Ticker module.
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
        $use_title  = self::get_attr($attrs, 'module.advanced.useTitle', 'on');
        $title_text = self::get_attr($attrs, 'module.advanced.titleText', '');

        $title_html = '';
        if ('on' === $use_title) {
            $title_html = sprintf('<div class="dtq-news-title">%1$s</div>', esc_html($title_text));
        }

        $children = sprintf(
            '<div class="dtq-module dtq-news-tricker">%1$s<div class="dtq-news-container"><ul class="dtq-news-wrap">%2$s</ul></div></div>',
            $title_html,
            self::render_news($attrs)
        );

        return Module::render(
            [
                'orderIndex'          => $block->parsed_block['orderIndex'] ?? 0,
                'storeInstance'       => $block->parsed_block['storeInstance'] ?? null,
                'attrs'               => $attrs,
                'elements'            => $elements,
                'id'                  => $block->parsed_block['id'] ?? '',
                'name'                => $block->block_type->name ?? '',
                'moduleClassName'     => 'dtq_news_ticker',
                'classnamesFunction'  => [self::class, 'module_classnames'],
                'stylesComponent'     => [self::class, 'module_styles'],
                'scriptDataComponent' => [self::class, 'module_script_data'],
                'children'            => $children,
            ]
        );
    }
}
