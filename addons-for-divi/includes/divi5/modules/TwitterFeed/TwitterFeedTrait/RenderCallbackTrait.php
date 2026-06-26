<?php
/**
 * Twitter Feed: server-side render callback.
 *
 * @package DiviTorqueLite\Modules\TwitterFeed
 * @since   4.6.0
 */

namespace DiviTorqueLite\Modules\TwitterFeed\TwitterFeedTrait;

if (!defined('ABSPATH')) {
    exit;
}

use ET\Builder\Packages\Module\Module;
use DiviTorqueLite\Modules\Shared\TwitterApi;

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
                '<div class="dtq-twitter-grid"><div class="dtq-tweet-error-message">%1$s</div></div>',
                esc_html__('Add your X (Twitter) handle and paid API credentials to display the feed.', 'addons-for-divi')
            );
        } else {
            $items = TwitterApi::get_tweets($key, $secret, $user, $limit);
            $items = TwitterApi::sort_and_limit($items, $sort_by, $limit);

            if (empty($items)) {
                $inner = sprintf(
                    '<div class="dtq-twitter-grid"><div class="dtq-tweet-error-message">%1$s</div></div>',
                    esc_html__('No tweets found. Check the username and that your X API plan allows reading timelines.', 'addons-for-divi')
                );
            } else {
                $inner = sprintf(
                    '<div class="dtq-twitter-grid dtq-twitter-%1$s">%2$s</div>',
                    esc_attr($align),
                    TwitterApi::build_items_html($advanced, $items)
                );
            }
        }

        $children = sprintf('<div class="dtq-module dtq-twitter-feed">%1$s</div>', $inner);

        return Module::render(
            [
                'orderIndex'          => $block->parsed_block['orderIndex'] ?? 0,
                'storeInstance'       => $block->parsed_block['storeInstance'] ?? null,
                'attrs'               => $attrs,
                'elements'            => $elements,
                'id'                  => $block->parsed_block['id'] ?? '',
                'name'                => $block->block_type->name ?? '',
                'moduleClassName'     => 'dtq_twitter_feed',
                'classnamesFunction'  => [self::class, 'module_classnames'],
                'stylesComponent'     => [self::class, 'module_styles'],
                'scriptDataComponent' => [self::class, 'module_script_data'],
                'children'            => $children,
            ]
        );
    }
}
