<?php
/**
 * Breadcrumbs: Render callback trait.
 *
 * @package DiviTorqueLite\Modules\Breadcrumbs
 * @since   4.7.0
 */

namespace DiviTorqueLite\Modules\Breadcrumbs\BreadcrumbsTrait;

if (!defined('ABSPATH')) {
    exit;
}

use ET\Builder\Packages\Module\Module;

trait RenderCallbackTrait
{
    /**
     * Build the breadcrumb trail for the current request.
     *
     * @param string $home_text Home link label.
     * @param bool   $show_home Whether to include the home crumb.
     *
     * @return array List of [ 'label' => string, 'url' => string ] (url empty = current).
     */
    public static function build_trail($home_text, $show_home)
    {
        $crumbs = [];

        if ($show_home) {
            $crumbs[] = ['label' => $home_text, 'url' => home_url('/')];
        }

        if (is_front_page() || is_home()) {
            if (is_home() && !is_front_page()) {
                $crumbs[] = ['label' => single_post_title('', false), 'url' => ''];
            }
            return $crumbs;
        }

        if (is_singular()) {
            $post_id   = get_queried_object_id();
            $post_type = get_post_type($post_id);

            if ('post' === $post_type) {
                $cats = get_the_category($post_id);
                if (!empty($cats)) {
                    $cat       = $cats[0];
                    $ancestors = array_reverse(get_ancestors($cat->term_id, 'category'));
                    foreach ($ancestors as $aid) {
                        $term = get_term($aid, 'category');
                        if ($term && !is_wp_error($term)) {
                            $tlink    = get_term_link($term);
                            $crumbs[] = ['label' => $term->name, 'url' => is_wp_error($tlink) ? '' : $tlink];
                        }
                    }
                    $clink    = get_term_link($cat);
                    $crumbs[] = ['label' => $cat->name, 'url' => is_wp_error($clink) ? '' : $clink];
                }
            } elseif ('page' !== $post_type) {
                $pto = get_post_type_object($post_type);
                if ($pto && !empty($pto->has_archive)) {
                    // Can be false (e.g. rewrite disabled); '' renders as plain text.
                    $crumbs[] = ['label' => $pto->labels->name, 'url' => (string) get_post_type_archive_link($post_type)];
                }
            }

            foreach (array_reverse(get_post_ancestors($post_id)) as $aid) {
                $crumbs[] = ['label' => get_the_title($aid), 'url' => (string) get_permalink($aid)];
            }

            $crumbs[] = ['label' => get_the_title($post_id), 'url' => ''];
            return $crumbs;
        }

        if (is_category() || is_tag() || is_tax()) {
            $term = get_queried_object();
            if ($term && isset($term->taxonomy)) {
                foreach (array_reverse(get_ancestors($term->term_id, $term->taxonomy)) as $aid) {
                    $t = get_term($aid, $term->taxonomy);
                    if ($t && !is_wp_error($t)) {
                        $tl       = get_term_link($t);
                        $crumbs[] = ['label' => $t->name, 'url' => is_wp_error($tl) ? '' : $tl];
                    }
                }
                $crumbs[] = ['label' => $term->name, 'url' => ''];
            }
            return $crumbs;
        }

        if (is_search()) {
            /* translators: %s: the visitor's search query. */
            $crumbs[] = ['label' => sprintf(__('Search: %s', 'addons-for-divi'), get_search_query()), 'url' => ''];
            return $crumbs;
        }
        if (is_author()) {
            $crumbs[] = ['label' => get_the_author_meta('display_name', get_queried_object_id()), 'url' => ''];
            return $crumbs;
        }
        if (is_404()) {
            $crumbs[] = ['label' => __('404 Not Found', 'addons-for-divi'), 'url' => ''];
            return $crumbs;
        }
        if (is_post_type_archive()) {
            $crumbs[] = ['label' => post_type_archive_title('', false), 'url' => ''];
            return $crumbs;
        }
        if (is_archive()) {
            $crumbs[] = ['label' => wp_strip_all_tags(get_the_archive_title()), 'url' => ''];
            return $crumbs;
        }

        // Visual Builder / unknown context fallback.
        $title    = get_the_title();
        $crumbs[] = ['label' => '' !== $title ? $title : __('Current Page', 'addons-for-divi'), 'url' => ''];
        return $crumbs;
    }

    /**
     * Server-side render for Breadcrumbs.
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

        $home_text    = $advanced['homeText']['desktop']['value'] ?? 'Home';
        $separator    = $advanced['separator']['desktop']['value'] ?? '›';
        $show_home    = ($advanced['showHome']['desktop']['value'] ?? 'on') === 'on';
        $show_current = ($advanced['showCurrent']['desktop']['value'] ?? 'on') === 'on';
        $schema       = ($advanced['enableSchema']['desktop']['value'] ?? 'on') === 'on';

        $trail = self::build_trail($home_text, $show_home);

        // Drop the current (urlless last) crumb when "Show Current Page" is off.
        if (!$show_current && !empty($trail)) {
            $last = end($trail);
            if ('' === $last['url']) {
                array_pop($trail);
            }
        }

        $items   = [];
        $sep_html = sprintf('<span class="dtq-breadcrumbs__sep" aria-hidden="true">%1$s</span>', esc_html($separator));
        $count    = count($trail);
        foreach ($trail as $i => $crumb) {
            if ('' !== $crumb['url']) {
                $items[] = sprintf(
                    '<a class="dtq-breadcrumbs__link" href="%1$s">%2$s</a>',
                    esc_url($crumb['url']),
                    esc_html($crumb['label'])
                );
            } else {
                $items[] = sprintf('<span class="dtq-breadcrumbs__current" aria-current="page">%1$s</span>', esc_html($crumb['label']));
            }
            if ($i < $count - 1) {
                $items[] = $sep_html;
            }
        }

        // No trail, no schema: an empty itemListElement is invalid structured data.
        $schema_html = ($schema && !empty($trail)) ? self::schema_json($trail) : '';

        $nav_html = sprintf(
            '<nav class="dtq-breadcrumbs" aria-label="Breadcrumb">%1$s</nav>%2$s',
            implode('', $items),
            $schema_html
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
                    $nav_html,
                ],
            ]
        );
    }

    /**
     * Build the BreadcrumbList JSON-LD script for the trail.
     *
     * @param array $trail Crumb list.
     *
     * @return string
     */
    public static function schema_json($trail)
    {
        $elements = [];
        $position = 1;
        foreach ($trail as $crumb) {
            $item = [
                '@type'    => 'ListItem',
                'position' => $position,
                'name'     => wp_strip_all_tags($crumb['label']),
            ];
            if ('' !== $crumb['url']) {
                $item['item'] = esc_url_raw($crumb['url']);
            }
            $elements[] = $item;
            $position++;
        }

        $data = [
            '@context'        => 'https://schema.org',
            '@type'           => 'BreadcrumbList',
            'itemListElement' => $elements,
        ];

        return sprintf(
            '<script type="application/ld+json">%1$s</script>',
            // HEX_TAG/HEX_AMP: a title containing </script> must not end the element.
            wp_json_encode($data, JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP)
        );
    }
}
