<?php
/**
 * Post List: server-side render callback.
 *
 * @package DiviTorqueLite\Modules\PostList
 * @since   4.6.0
 */

namespace DiviTorqueLite\Modules\PostList\PostListTrait;

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
     * Resolve the list icon glyph from the icon-picker value (object or legacy
     * "glyph||font||weight" string), injecting the FA font when needed.
     *
     * @param mixed $icon_value Icon attr value.
     *
     * @return string Rendered glyph (entity-decoded), or ''.
     */
    protected static function list_icon_glyph($icon_value)
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
     * Build the post <li> items from a WP_Query. Mirrors the D4
     * templates/list-content.php markup.
     *
     * @param array $attrs Module attributes.
     *
     * @return string
     */
    protected static function render_posts($attrs)
    {
        $post_type      = self::get_attr($attrs, 'module.advanced.postType', 'post');
        $categories     = self::get_attr($attrs, 'module.advanced.includeCategories', '');
        $order_by       = self::get_attr($attrs, 'module.advanced.orderBy', 'date');
        $order          = self::get_attr($attrs, 'module.advanced.order', 'ASC');
        $posts_number   = (int) self::get_attr($attrs, 'module.advanced.postsNumber', '6');
        $offset         = (int) self::get_attr($attrs, 'module.advanced.postOffset', '0');
        $exclude        = self::get_attr($attrs, 'module.advanced.excludePosts', '');
        $only_image     = self::get_attr($attrs, 'module.advanced.postsOnlyWithImage', 'on');
        $show_thumb     = self::get_attr($attrs, 'module.advanced.showThumb', 'on');
        $show_icon      = self::get_attr($attrs, 'module.advanced.showIcon', 'on');
        $show_excerpt   = self::get_attr($attrs, 'module.advanced.showExcerpt', 'on');
        $excerpt_length = (int) self::get_attr($attrs, 'module.advanced.excerptLength', '150');
        $show_author    = self::get_attr($attrs, 'module.advanced.showAuthor', 'off');
        $show_date      = self::get_attr($attrs, 'module.advanced.showDate', 'off');
        $date_format    = self::get_attr($attrs, 'module.advanced.dateFormat', 'M d, Y');
        $glyph          = self::list_icon_glyph(self::get_attr($attrs, 'module.advanced.listIcon', ''));

        $query_args = [
            'posts_per_page' => $posts_number > 0 ? $posts_number : 6,
            'post_type'      => sanitize_text_field($post_type),
            'post_status'    => 'publish',
            'orderby'        => sanitize_text_field($order_by),
            'order'          => sanitize_text_field($order),
            'offset'         => $offset,
        ];

        if ('on' === $only_image) {
            $query_args['meta_key'] = '_thumbnail_id';
        }

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

                // Figure (thumbnail or list icon).
                $figure = '';
                if ('on' === $show_thumb) {
                    if (has_post_thumbnail()) {
                        $figure = sprintf(
                            '<div class="dtq-post-list-thumb"><img class="dtq-img-cover" src="%1$s" alt="" /></div>',
                            esc_url(get_the_post_thumbnail_url())
                        );
                    }
                } elseif ('on' === $show_icon && '' !== $glyph) {
                    $figure = sprintf('<div class="dtq-post-list-icon"><i class="dtq-et-font-icon">%1$s</i></div>', $glyph);
                }

                // Meta (author + date).
                $author = ('on' === $show_author)
                    ? sprintf('<div class="dtq-post-list-author">By %1$s</div>', esc_html(get_the_author()))
                    : '';
                $date = ('on' === $show_date)
                    ? sprintf('<div class="dtq-post-list-date">%1$s</div>', esc_html(get_the_time($date_format)))
                    : '';
                $separator = ('on' === $show_author && 'on' === $show_date) ? '<span class="dtq-separator">|</span>' : '';
                $meta = ('on' === $show_author || 'on' === $show_date)
                    ? sprintf('<div class="dtq-post-list-meta">%1$s %2$s %3$s</div>', $author, $separator, $date)
                    : '';

                // Excerpt.
                $excerpt = ('on' === $show_excerpt)
                    ? sprintf('<p class="dtq-post-list-excerpt">%1$s</p>', esc_html(mb_strimwidth(get_the_excerpt(), 0, $excerpt_length > 0 ? $excerpt_length : 150, '...')))
                    : '';

                $output .= sprintf(
                    '<li class="dtq-post-list-child"><a class="dtq-post-list-child-inner" href="%1$s">%5$s<div class="dtq-post-list-content"><h3 class="dtq-post-list-title">%2$s</h3>%3$s%4$s</div></a></li>',
                    esc_url(get_the_permalink()),
                    esc_html(get_the_title()),
                    $meta,
                    $excerpt,
                    $figure
                );
            }
        }

        wp_reset_postdata();

        if ('' === $output) {
            $output = '<li class="dtq-post-list-child">' . esc_html__('No posts found.', 'addons-for-divi') . '</li>';
        }

        return $output;
    }

    /**
     * Render the Post List module.
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
        $list_type = self::get_attr($attrs, 'module.advanced.listType', 'list');

        $children = sprintf(
            '<div class="dtq-module dtq-post-list type-%1$s"><ul class="dtq-post-list-parent">%2$s</ul></div>',
            esc_attr($list_type),
            self::render_posts($attrs)
        );

        return Module::render(
            [
                'orderIndex'          => $block->parsed_block['orderIndex'] ?? 0,
                'storeInstance'       => $block->parsed_block['storeInstance'] ?? null,
                'attrs'               => $attrs,
                'elements'            => $elements,
                'id'                  => $block->parsed_block['id'] ?? '',
                'name'                => $block->block_type->name ?? '',
                'moduleClassName'     => 'dtq_post_list',
                'classnamesFunction'  => [self::class, 'module_classnames'],
                'stylesComponent'     => [self::class, 'module_styles'],
                'scriptDataComponent' => [self::class, 'module_script_data'],
                'children'            => $children,
            ]
        );
    }
}
