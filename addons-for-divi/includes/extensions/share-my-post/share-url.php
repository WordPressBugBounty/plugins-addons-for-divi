<?php
/**
 * Share My Post: share URL construction.
 *
 * build() is deliberately pure — it takes a context array rather than calling
 * get_the_ID()/get_permalink() itself. That is the whole reason all 31 URL
 * templates are testable without a WordPress install: context_from_post() does
 * the WordPress lookups, build() does the string work, and only the former needs
 * a running site.
 *
 * @package DiviTorqueLite
 * @since   4.9.0
 */

namespace DiviTorqueLite;

if (!defined('ABSPATH')) {
    exit;
}

class Share_My_Post_Url
{
    /**
     * Words of post content to send as the "description"/"summary" argument.
     *
     * Long enough to be a useful preview, short enough that the URL stays under
     * the ~2000 characters some of these endpoints silently truncate at.
     */
    const EXCERPT_WORDS = 40;

    /**
     * Build the share URL for a network.
     *
     * @param string $slug    Network slug.
     * @param array  $context url / title / excerpt / image / instance.
     *
     * @return string Empty for button-actions (copy, print, native) and unknown
     *                slugs — the caller renders a <button> for the former.
     */
    public static function build($slug, array $context)
    {
        $network = Share_My_Post_Networks::get($slug);

        if (null === $network || '' === $network['template']) {
            return '';
        }

        $values = array(
            '{url}'      => rawurlencode((string) ($context['url'] ?? '')),
            '{title}'    => rawurlencode((string) ($context['title'] ?? '')),
            '{excerpt}'  => rawurlencode((string) ($context['excerpt'] ?? '')),
            '{image}'    => rawurlencode((string) ($context['image'] ?? '')),
            // Not encoded: it lands in the host position, where %2E would break
            // the URL. sanitize_instance() is what makes it safe there.
            '{instance}' => self::sanitize_instance($context['instance'] ?? ''),
        );

        $url = strtr($network['template'], $values);

        return self::drop_empty_params($url);
    }

    /**
     * Remove query parameters whose value came out empty.
     *
     * Not cosmetic. Pinterest reads `media=` as a malformed pin rather than as
     * "no image", so a post without a featured image would produce a broken pin
     * dialog. The same rule keeps `title=`/`summary=` off every other endpoint
     * when a post has no excerpt.
     *
     * Only whole `key=` pairs are dropped. `text={title}%20{url}` keeps its
     * value because the pair is not empty once the URL is substituted.
     *
     * @param string $url Substituted URL.
     *
     * @return string
     */
    private static function drop_empty_params($url)
    {
        $split = explode('?', $url, 2);

        if (2 !== count($split) || '' === $split[1]) {
            return $split[0];
        }

        list($base, $query) = $split;

        $kept = array();
        foreach (explode('&', $query) as $pair) {
            // A bare flag (`mode` with no `=`) is not an empty value, so it stays.
            if (false === strpos($pair, '=')) {
                $kept[] = $pair;
                continue;
            }

            list($key, $value) = explode('=', $pair, 2);
            if ('' !== $value) {
                $kept[] = $key . '=' . $value;
            }
        }

        return empty($kept) ? $base : $base . '?' . implode('&', $kept);
    }

    /**
     * Validate a Mastodon instance hostname.
     *
     * This value is interpolated into the *host* of a URL the visitor is sent
     * to, so a bad one is an open redirect rather than a cosmetic bug. Only
     * host-legal characters survive, and the result must still look like a
     * dotted hostname afterwards; anything else falls back to mastodon.social.
     *
     * @param mixed $instance Raw setting value.
     *
     * @return string
     */
    public static function sanitize_instance($instance)
    {
        $instance = strtolower(trim((string) $instance));

        // Authors paste the full URL as often as the bare host.
        $instance = preg_replace('#^https?://#', '', $instance);
        $instance = explode('/', $instance)[0];
        // Strip credentials and port — both would move the effective host.
        $instance = explode('@', $instance);
        $instance = end($instance);
        $instance = explode(':', $instance)[0];

        if (!preg_match('/^[a-z0-9]([a-z0-9-]*[a-z0-9])?(\.[a-z0-9]([a-z0-9-]*[a-z0-9])?)+$/', $instance)) {
            return 'mastodon.social';
        }

        return $instance;
    }

    /**
     * Whether this network opens in a popup window.
     *
     * @param string $slug Network slug.
     *
     * @return bool
     */
    public static function is_popup($slug)
    {
        $network = Share_My_Post_Networks::get($slug);

        return null !== $network && !empty($network['popup']);
    }

    /**
     * Gather the share context for a post.
     *
     * The half of this pair that needs WordPress. Kept small on purpose so the
     * untestable surface stays small.
     *
     * @param int    $post_id  Post ID.
     * @param string $instance Mastodon instance from settings.
     *
     * @return array
     */
    public static function context_from_post($post_id, $instance = '')
    {
        $post_id = (int) $post_id;

        $image = '';
        if (has_post_thumbnail($post_id)) {
            $src = wp_get_attachment_image_url(get_post_thumbnail_id($post_id), 'full');
            if (is_string($src)) {
                $image = $src;
            }
        }

        $excerpt = has_excerpt($post_id)
            ? get_the_excerpt($post_id)
            : wp_trim_words(strip_shortcodes(strip_tags(get_post_field('post_content', $post_id))), self::EXCERPT_WORDS, '');

        $context = array(
            'url'      => (string) get_permalink($post_id),
            'title'    => wp_strip_all_tags((string) get_the_title($post_id)),
            'excerpt'  => wp_strip_all_tags((string) $excerpt),
            'image'    => $image,
            'instance' => (string) $instance,
        );

        /**
         * Filters the values interpolated into every share URL on this request.
         *
         * Use to send a canonical URL instead of the permalink, or a campaign
         * -tagged one.
         *
         * @param array $context url / title / excerpt / image / instance.
         * @param int   $post_id Post being shared.
         */
        return apply_filters('divitorque_share_my_post_context', $context, $post_id);
    }
}
