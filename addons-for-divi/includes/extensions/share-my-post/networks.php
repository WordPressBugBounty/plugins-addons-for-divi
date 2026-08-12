<?php
/**
 * Share My Post: the network catalog.
 *
 * One vocabulary, three consumers: the PHP render, the settings sanitiser, and
 * the React settings page (which receives all() through window.divitorqueData).
 * A network added here shows up in all three; there is no second list to keep
 * in step. The one thing that must be kept in step is network-icons.php, and
 * ShareMyPostCatalogTest asserts that rather than trusting a comment.
 *
 * Deliberately WordPress-light: only __() is used, so tests/phpunit/bootstrap.php
 * can require this file directly.
 *
 * @package DiviTorqueLite
 * @since   4.9.0
 */

namespace DiviTorqueLite;

if (!defined('ABSPATH')) {
    exit;
}

class Share_My_Post_Networks
{
    /**
     * Placeholders a template may contain. Every one is rawurlencode()d by
     * Share_My_Post_Url::build() before substitution, except {instance}, which
     * lands in the host position and is validated as a hostname instead.
     */
    const PLACEHOLDERS = array('{url}', '{title}', '{excerpt}', '{image}', '{instance}');

    /**
     * Protocols beyond wp_allowed_protocols() that esc_url() must be told about.
     *
     * Core already allows mailto and sms, so this list is exactly one entry
     * long. Pro's social-share module skips esc_url() altogether for this
     * reason, which throws away the escaping for the other 31 networks too;
     * passing the protocol list keeps escaping on everywhere.
     */
    const EXTRA_PROTOCOLS = array('viber');

    /**
     * The catalog.
     *
     * popup  — open in a sized window. False for scheme links (mailto:, sms:,
     *          viber:), where a popup would leave a blank window behind after
     *          the OS handler takes over.
     * action — '' renders an <a href>. Anything else renders a <button> that
     *          only JS can service, so it stays hidden until JS says it works.
     *
     * @return array<string, array>
     */
    public static function all()
    {
        static $catalog = null;

        if (null !== $catalog) {
            return $catalog;
        }

        $catalog = array(
            'facebook' => array(
                'label'    => __('Facebook', 'addons-for-divi'),
                'template' => 'https://www.facebook.com/sharer/sharer.php?u={url}',
                'popup'    => true,
                'action'   => '',
            ),
            'x' => array(
                'label'    => __('X', 'addons-for-divi'),
                'template' => 'https://x.com/intent/post?text={title}&url={url}',
                'popup'    => true,
                'action'   => '',
            ),
            'linkedin' => array(
                // LinkedIn has ignored title/summary/source since 2021 — it
                // reads Open Graph off the page instead. Sending them anyway
                // just makes a longer URL that behaves identically.
                'label'    => __('LinkedIn', 'addons-for-divi'),
                'template' => 'https://www.linkedin.com/sharing/share-offsite/?url={url}',
                'popup'    => true,
                'action'   => '',
            ),
            'whatsapp' => array(
                'label'    => __('WhatsApp', 'addons-for-divi'),
                'template' => 'https://api.whatsapp.com/send?text={title}%20{url}',
                'popup'    => true,
                'action'   => '',
            ),
            'pinterest' => array(
                // {image} resolves empty on a post with no featured image, and
                // build() then drops the whole `media=` pair. Pinterest treats
                // an empty media as a malformed pin rather than "no image".
                'label'    => __('Pinterest', 'addons-for-divi'),
                'template' => 'https://www.pinterest.com/pin/create/button/?url={url}&media={image}&description={excerpt}',
                'popup'    => true,
                'action'   => '',
            ),
            'telegram' => array(
                'label'    => __('Telegram', 'addons-for-divi'),
                'template' => 'https://t.me/share/url?url={url}&text={title}',
                'popup'    => true,
                'action'   => '',
            ),
            'reddit' => array(
                'label'    => __('Reddit', 'addons-for-divi'),
                'template' => 'https://www.reddit.com/submit?url={url}&title={title}',
                'popup'    => true,
                'action'   => '',
            ),
            'tumblr' => array(
                'label'    => __('Tumblr', 'addons-for-divi'),
                'template' => 'https://www.tumblr.com/widgets/share/tool?canonicalUrl={url}&title={title}&caption={excerpt}',
                'popup'    => true,
                'action'   => '',
            ),
            'vk' => array(
                'label'    => __('VK', 'addons-for-divi'),
                'template' => 'https://vk.com/share.php?url={url}&title={title}',
                'popup'    => true,
                'action'   => '',
            ),
            'viber' => array(
                'label'    => __('Viber', 'addons-for-divi'),
                'template' => 'viber://forward?text={title}%20{url}',
                'popup'    => false,
                'action'   => '',
            ),
            'mastodon' => array(
                // Mastodon is federated: there is no canonical host to post to,
                // so {instance} is filled from the author's setting. Asking the
                // visitor for their instance mid-share is the usual alternative
                // and it loses most of them at the prompt.
                'label'    => __('Mastodon', 'addons-for-divi'),
                'template' => 'https://{instance}/share?text={title}%20{url}',
                'popup'    => true,
                'action'   => '',
            ),
            'threads' => array(
                'label'    => __('Threads', 'addons-for-divi'),
                'template' => 'https://www.threads.net/intent/post?text={title}%20{url}',
                'popup'    => true,
                'action'   => '',
            ),
            'bluesky' => array(
                'label'    => __('Bluesky', 'addons-for-divi'),
                'template' => 'https://bsky.app/intent/compose?text={title}%20{url}',
                'popup'    => true,
                'action'   => '',
            ),
            'flipboard' => array(
                'label'    => __('Flipboard', 'addons-for-divi'),
                'template' => 'https://share.flipboard.com/bookmarklet/popout?v=2&url={url}&title={title}',
                'popup'    => true,
                'action'   => '',
            ),
            'line' => array(
                'label'    => __('LINE', 'addons-for-divi'),
                'template' => 'https://social-plugins.line.me/lineit/share?url={url}',
                'popup'    => true,
                'action'   => '',
            ),
            'wordpress' => array(
                'label'    => __('WordPress', 'addons-for-divi'),
                'template' => 'https://wordpress.com/wp-admin/press-this.php?u={url}&t={title}&s={excerpt}',
                'popup'    => true,
                'action'   => '',
            ),
            'blogger' => array(
                'label'    => __('Blogger', 'addons-for-divi'),
                'template' => 'https://www.blogger.com/blog-this.g?u={url}&n={title}&t={excerpt}',
                'popup'    => true,
                'action'   => '',
            ),
            'evernote' => array(
                'label'    => __('Evernote', 'addons-for-divi'),
                'template' => 'https://www.evernote.com/clip.action?url={url}&title={title}',
                'popup'    => true,
                'action'   => '',
            ),
            'buffer' => array(
                'label'    => __('Buffer', 'addons-for-divi'),
                'template' => 'https://buffer.com/add?url={url}&text={title}',
                'popup'    => true,
                'action'   => '',
            ),
            'hackernews' => array(
                'label'    => __('Hacker News', 'addons-for-divi'),
                'template' => 'https://news.ycombinator.com/submitlink?u={url}&t={title}',
                'popup'    => true,
                'action'   => '',
            ),
            'xing' => array(
                'label'    => __('Xing', 'addons-for-divi'),
                'template' => 'https://www.xing.com/spi/shares/new?url={url}',
                'popup'    => true,
                'action'   => '',
            ),
            'odnoklassniki' => array(
                'label'    => __('Odnoklassniki', 'addons-for-divi'),
                'template' => 'https://connect.ok.ru/offer?url={url}&title={title}',
                'popup'    => true,
                'action'   => '',
            ),
            'weibo' => array(
                'label'    => __('Weibo', 'addons-for-divi'),
                'template' => 'https://service.weibo.com/share/share.php?url={url}&title={title}',
                'popup'    => true,
                'action'   => '',
            ),
            'douban' => array(
                'label'    => __('Douban', 'addons-for-divi'),
                'template' => 'https://www.douban.com/share/service?href={url}&name={title}&text={excerpt}',
                'popup'    => true,
                'action'   => '',
            ),
            'qzone' => array(
                'label'    => __('Qzone', 'addons-for-divi'),
                'template' => 'https://sns.qzone.qq.com/cgi-bin/qzshare/cgi_qzshare_onekey?url={url}&title={title}&summary={excerpt}',
                'popup'    => true,
                'action'   => '',
            ),
            'naver' => array(
                'label'    => __('Naver', 'addons-for-divi'),
                'template' => 'https://share.naver.com/web/shareView?url={url}&title={title}',
                'popup'    => true,
                'action'   => '',
            ),
            'instapaper' => array(
                'label'    => __('Instapaper', 'addons-for-divi'),
                'template' => 'https://www.instapaper.com/hello2?url={url}&title={title}',
                'popup'    => true,
                'action'   => '',
            ),
            'diaspora' => array(
                'label'    => __('Diaspora', 'addons-for-divi'),
                'template' => 'https://share.diasporafoundation.org/?url={url}&title={title}',
                'popup'    => true,
                'action'   => '',
            ),
            'trello' => array(
                'label'    => __('Trello', 'addons-for-divi'),
                'template' => 'https://trello.com/add-card?mode=popup&url={url}&name={title}&desc={excerpt}',
                'popup'    => true,
                'action'   => '',
            ),
            'email' => array(
                'label'    => __('Email', 'addons-for-divi'),
                'template' => 'mailto:?subject={title}&body={url}',
                'popup'    => false,
                'action'   => '',
            ),
            'sms' => array(
                'label'    => __('Text Message', 'addons-for-divi'),
                'template' => 'sms:?body={title}%20{url}',
                'popup'    => false,
                'action'   => '',
            ),
            'copy' => array(
                'label'    => __('Copy Link', 'addons-for-divi'),
                'template' => '',
                'popup'    => false,
                'action'   => 'copy',
            ),
            'print' => array(
                'label'    => __('Print', 'addons-for-divi'),
                'template' => '',
                'popup'    => false,
                'action'   => 'print',
            ),
            'native' => array(
                // navigator.share() — the OS share sheet. Absent on most
                // desktop browsers, so the button stays hidden unless JS finds
                // the API.
                'label'    => __('Share', 'addons-for-divi'),
                'template' => '',
                'popup'    => false,
                'action'   => 'native',
            ),
        );

        return $catalog;
    }

    /**
     * Whether a slug is in the catalog.
     *
     * This — not escaping — is what makes a slug safe to interpolate into a CSS
     * class name and a data attribute. An unknown slug is dropped, never
     * sanitised into something adjacent.
     *
     * @param string $slug Network slug.
     *
     * @return bool
     */
    public static function exists($slug)
    {
        return is_string($slug) && isset(self::all()[$slug]);
    }

    /**
     * One network's definition.
     *
     * @param string $slug Network slug.
     *
     * @return array|null Null when unknown.
     */
    public static function get($slug)
    {
        return self::exists($slug) ? self::all()[$slug] : null;
    }

    /**
     * Every slug, in catalog order.
     *
     * @return string[]
     */
    public static function slugs()
    {
        return array_keys(self::all());
    }

    /**
     * Drop anything not in the catalog, and de-duplicate.
     *
     * Order is preserved because it is the author's chosen render order, not an
     * arbitrary set. array_values() re-indexes so the result JSON-encodes as an
     * array rather than an object.
     *
     * @param mixed $slugs Candidate list.
     *
     * @return string[]
     */
    public static function filter($slugs)
    {
        if (!is_array($slugs)) {
            return array();
        }

        $out = array();
        foreach ($slugs as $slug) {
            if (self::exists($slug) && !in_array($slug, $out, true)) {
                $out[] = $slug;
            }
        }

        return $out;
    }

    /**
     * The icon map: slug => array{hex, fg, svg}.
     *
     * @return array<string, array>
     */
    public static function icons()
    {
        static $icons = null;

        if (null === $icons) {
            $icons = require __DIR__ . '/network-icons.php';
        }

        return $icons;
    }
}
