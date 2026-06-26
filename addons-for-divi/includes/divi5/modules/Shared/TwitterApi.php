<?php
/**
 * Shared X (Twitter) API v2 fetch helper for the D5 Twitter modules.
 *
 * The D4 modules called the v1.1 `statuses/user_timeline` endpoint, which X
 * retired. This helper targets the v2 endpoints (users/by/username + users/:id
 * /tweets). v2 read access requires a PAID X API plan (Basic tier or higher) —
 * the free tier cannot read timelines. App-only Bearer auth is minted from the
 * consumer key/secret (same credentials the D4 module stored), so saved layouts
 * keep working once the account is on a paid plan.
 *
 * Responses are normalised into the same item shape the D4 render produced and
 * cached in a transient (default 1 hour, filterable) to minimise paid API calls.
 *
 * @package DiviTorqueLite\Modules\Shared
 * @since   4.6.0
 */

namespace DiviTorqueLite\Modules\Shared;

if (!defined('ABSPATH')) {
    exit;
}

class TwitterApi
{
    /**
     * Mint (and cache) an app-only Bearer token from the consumer key/secret.
     *
     * @param string $key    Consumer (API) key.
     * @param string $secret Consumer (API) secret.
     *
     * @return string Bearer token, or '' on failure.
     */
    protected static function get_bearer($key, $secret)
    {
        $cache_key = 'dtq_tw_bearer_' . md5($key . ':' . $secret);
        $cached    = get_transient($cache_key);
        if (is_string($cached) && '' !== $cached) {
            return $cached;
        }

        $response = wp_remote_post(
            'https://api.twitter.com/oauth2/token',
            [
                'timeout' => 15,
                'headers' => [
                    'Authorization' => 'Basic ' . base64_encode($key . ':' . $secret), // phpcs:ignore
                    'Content-Type'  => 'application/x-www-form-urlencoded;charset=UTF-8',
                ],
                'body'    => ['grant_type' => 'client_credentials'],
            ]
        );

        if (is_wp_error($response)) {
            return '';
        }

        $body  = json_decode(wp_remote_retrieve_body($response), true);
        $token = $body['access_token'] ?? '';

        if ('' !== $token) {
            // Bearer tokens are long-lived; cache for a day.
            set_transient($cache_key, $token, DAY_IN_SECONDS);
        }

        return $token;
    }

    /**
     * GET a v2 endpoint and return the decoded JSON.
     *
     * @param string $url    Endpoint URL.
     * @param string $bearer Bearer token.
     *
     * @return array
     */
    protected static function api_get($url, $bearer)
    {
        $response = wp_remote_get(
            $url,
            [
                'timeout' => 15,
                'headers' => ['Authorization' => 'Bearer ' . $bearer],
            ]
        );

        if (is_wp_error($response)) {
            return [];
        }

        $data = json_decode(wp_remote_retrieve_body($response), true);
        return is_array($data) ? $data : [];
    }

    /**
     * Fetch recent tweets for a username via the X API v2, normalised into the
     * legacy item shape and cached.
     *
     * @param string $key      Consumer key.
     * @param string $secret   Consumer secret.
     * @param string $username Screen name (with or without leading @).
     * @param int    $limit    Number of tweets to show.
     *
     * @return array List of items: { id, full_text, created_at, favorite_count,
     *               retweet_count, entities, user: { name, screen_name,
     *               profile_image_url_https } }.
     */
    public static function get_tweets($key, $secret, $username, $limit = 8)
    {
        $username = ltrim(trim((string) $username), '@');
        $limit    = (int) $limit > 0 ? (int) $limit : 8;

        if ('' === $username || '' === $key || '' === $secret) {
            return [];
        }

        $cache_key = 'dtq_tw_v2_' . md5($username . '|' . $limit);
        $cached    = get_transient($cache_key);
        if (is_array($cached)) {
            return $cached;
        }

        $bearer = self::get_bearer($key, $secret);
        if ('' === $bearer) {
            return [];
        }

        // 1. Resolve the user (id + profile fields).
        $user_data = self::api_get(
            'https://api.twitter.com/2/users/by/username/' . rawurlencode($username) . '?user.fields=profile_image_url,name,username',
            $bearer
        );
        $user = $user_data['data'] ?? [];
        $uid  = $user['id'] ?? '';
        if ('' === $uid) {
            return [];
        }

        // 2. Fetch the user's recent tweets. v2 max_results is 5..100.
        $count = max(5, min(100, $limit + 5));
        $tweets = self::api_get(
            'https://api.twitter.com/2/users/' . rawurlencode($uid) . '/tweets'
                . '?max_results=' . $count
                . '&exclude=retweets,replies'
                . '&tweet.fields=created_at,public_metrics',
            $bearer
        );

        $items   = [];
        $profile = [
            'name'                    => $user['name'] ?? $username,
            'screen_name'             => $user['username'] ?? $username,
            'profile_image_url_https' => $user['profile_image_url'] ?? '',
        ];

        foreach (($tweets['data'] ?? []) as $tweet) {
            $items[] = [
                'id'             => $tweet['id'] ?? '',
                'full_text'      => $tweet['text'] ?? '',
                'created_at'     => $tweet['created_at'] ?? '',
                'favorite_count' => $tweet['public_metrics']['like_count'] ?? 0,
                'retweet_count'  => $tweet['public_metrics']['retweet_count'] ?? 0,
                'entities'       => [],
                'user'           => $profile,
            ];
        }

        $ttl = (int) apply_filters('divitorque_twitter_cache_ttl', HOUR_IN_SECONDS);
        set_transient($cache_key, $items, $ttl);

        return $items;
    }

    /**
     * Read a desktop attr value from a module.advanced array.
     *
     * @param array  $a        The module.advanced attrs.
     * @param string $key      Sub-key.
     * @param mixed  $fallback Fallback.
     *
     * @return mixed
     */
    protected static function adv($a, $key, $fallback = '')
    {
        $v = $a[$key]['desktop']['value'] ?? null;
        return (null === $v || '' === $v) ? $fallback : $v;
    }

    /**
     * Build the tweet grid items HTML, shared by the Twitter Feed and Twitter
     * Carousel modules. Mirrors the D4 per-tweet markup.
     *
     * @param array $a         The module.advanced attrs.
     * @param array $items     Normalised tweet items.
     * @param bool  $as_slides Wrap each item in a `.swiper-slide` (carousel).
     *
     * @return string
     */
    public static function build_items_html($a, $items, $as_slides = false)
    {
        $user_name = ltrim(trim((string) self::adv($a, 'userName', '')), '@');
        $show_icon = self::adv($a, 'showTwitterIcon', 'on');
        $show_img  = self::adv($a, 'showUserImage', 'on');
        $show_name = self::adv($a, 'showName', 'on');
        $show_user = self::adv($a, 'showUserName', 'off');
        $show_date = self::adv($a, 'showDate', 'on');
        $show_fav  = self::adv($a, 'showFavorite', 'on');
        $show_rt   = self::adv($a, 'showRetweet', 'on');
        $read_more = self::adv($a, 'readMore', 'on');
        $rm_text   = self::adv($a, 'readMoreText', 'Read More');
        $profile   = 'https://twitter.com/' . rawurlencode($user_name);

        $svg = '<svg version="1.1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path style="fill:#1da1f2;" d="M512,97.248c-19.04,8.352-39.328,13.888-60.48,16.576c21.76-12.992,38.368-33.408,46.176-58.016c-20.288,12.096-42.688,20.64-66.56,25.408C411.872,60.704,384.416,48,354.464,48c-58.112,0-104.896,47.168-104.896,104.992c0,8.32,0.704,16.32,2.432,23.936c-87.264-4.256-164.48-46.08-216.352-109.792c-9.056,15.712-14.368,33.696-14.368,53.056c0,36.352,18.72,68.576,46.624,87.232c-16.864-0.32-33.408-5.216-47.424-12.928c0,0.32,0,0.736,0,1.152c0,51.008,36.384,93.376,84.096,103.136c-8.544,2.336-17.856,3.456-27.52,3.456c-6.72,0-13.504-0.384-19.872-1.792c13.6,41.568,52.192,72.128,98.08,73.12c-35.712,27.936-81.056,44.768-130.144,44.768c-8.608,0-16.864-0.384-25.12-1.44C46.496,446.88,101.6,464,161.024,464c193.152,0,298.752-160,298.752-298.688c0-4.64-0.16-9.12-0.384-13.568C480.224,136.96,497.728,118.496,512,97.248z"/></svg>';

        $html = '';
        foreach ($items as $item) {
            $content = $item['full_text'] ?? '';

            $icon = ('on' === $show_icon)
                ? '<div class="dtq-twitter-grid-icon"><span>' . $svg . '</span></div>'
                : '';

            $avatar = ('on' === $show_img && !empty($item['user']['profile_image_url_https']))
                ? sprintf('<a class="dtq-twitter-grid-avatar-wrapper" href="%1$s"><img src="%2$s" alt="%3$s" class="dtq-twitter-grid-avatar"></a>', esc_url($profile), esc_url($item['user']['profile_image_url_https']), esc_attr($item['user']['name'] ?? ''))
                : '';

            $name = ('on' === $show_name)
                ? sprintf('<a href="%1$s" class="dtq-twitter-grid-author-name">%2$s</a>', esc_url($profile), esc_html($item['user']['name'] ?? ''))
                : '';

            $uname = ('on' === $show_user)
                ? sprintf('<a href="%1$s" class="dtq-twitter-grid-username">@%2$s</a>', esc_url($profile), esc_html($user_name))
                : '';

            $rm = ('on' === $read_more)
                ? sprintf('<a href="%1$s" target="_blank">%2$s</a>', esc_url('//twitter.com/' . rawurlencode($item['user']['screen_name'] ?? $user_name) . '/status/' . rawurlencode($item['id'] ?? '')), esc_html($rm_text))
                : '';

            $date = ('on' === $show_date)
                ? sprintf('<div class="dtq-twitter-grid-date">%1$s</div>', esc_html(gmdate('M d Y', strtotime($item['created_at'] ?? 'now'))))
                : '';

            $footer = '';
            if ('on' === $show_fav || 'on' === $show_rt) {
                $fav = ('on' === $show_fav) ? sprintf('<div class="dtq-tweet-favorite">%1$s<span class="et-pb-icon dtq-icon dtq-tweet-favorite-icon"></span></div>', esc_html($item['favorite_count'] ?? 0)) : '';
                $rt  = ('on' === $show_rt) ? sprintf('<div class="dtq-tweet-retweet">%1$s<span class="et-pb-icon dtq-icon dtq-tweet-retweet-icon"></span></div>', esc_html($item['retweet_count'] ?? 0)) : '';
                $footer = sprintf('<div class="dtq-twitter-grid-footer-wrapper"><div class="dtq-twitter-grid-footer">%1$s%2$s</div></div>', $fav, $rt);
            }

            $item = sprintf(
                '<div class="dtq-twitter-grid-item"><div class="dtq-twitter-grid-item-inner">%1$s<div class="dtq-twitter-grid-inner-wrapper"><div class="dtq-twitter-grid-author">%2$s<div class="dtq-twitter-grid-user">%3$s%4$s</div></div><div class="dtq-twitter-grid-content"><div class="dtq-inner-twitter-grid-content"><p>%5$s %6$s</p></div>%7$s</div></div>%8$s</div></div>',
                $icon,
                $avatar,
                $name,
                $uname,
                esc_html($content),
                $rm,
                $date,
                $footer
            );

            $html .= $as_slides ? '<div class="swiper-slide">' . $item . '</div>' : $item;
        }

        return $html;
    }

    /**
     * Sort + limit normalised items (mirrors the D4 sort_by handling).
     *
     * @param array  $items   Normalised items.
     * @param string $sort_by recent-posts | old-posts | favorite_count | retweet_count.
     * @param int    $limit   Max items.
     *
     * @return array
     */
    public static function sort_and_limit($items, $sort_by, $limit)
    {
        if (empty($items)) {
            return [];
        }

        switch ($sort_by) {
            case 'old-posts':
                usort($items, function ($a, $b) {
                    return strtotime($a['created_at']) <=> strtotime($b['created_at']);
                });
                break;
            case 'favorite_count':
                usort($items, function ($a, $b) {
                    return $b['favorite_count'] <=> $a['favorite_count'];
                });
                break;
            case 'retweet_count':
                usort($items, function ($a, $b) {
                    return $b['retweet_count'] <=> $a['retweet_count'];
                });
                break;
            // 'recent-posts' = API default order.
        }

        $limit = (int) $limit;
        if ($limit > 0 && count($items) > $limit) {
            $items = array_slice($items, 0, $limit);
        }

        return $items;
    }
}
