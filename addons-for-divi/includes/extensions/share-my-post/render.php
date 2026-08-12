<?php
/**
 * Share My Post: markup.
 *
 * One bar builder serves both placements — only the wrapper class differs.
 * Appearance mode changes no markup at all: the icon and the label are always
 * both emitted and CSS decides which is visible. That keeps a single render
 * path, and makes it impossible for a settings change to produce a button with
 * no accessible name.
 *
 * @package DiviTorqueLite
 * @since   4.9.0
 */

namespace DiviTorqueLite;

if (!defined('ABSPATH')) {
    exit;
}

class Share_My_Post_Render
{
    /** Ensures the aria-labelledby ids are unique when both bars are on a page. */
    private static $sequence = 0;

    /**
     * Render one share bar.
     *
     * @param string $placement 'inline' or 'floating'.
     * @param array  $settings  Full settings tree.
     * @param array  $context   Share context (see Share_My_Post_Url).
     *
     * @return string Empty when the placement has no usable networks.
     */
    public static function bar($placement, array $settings, array $context)
    {
        $config   = $settings[$placement] ?? array();
        $networks = Share_My_Post_Networks::filter($config['networks'] ?? array());

        if (empty($networks)) {
            return '';
        }

        $buttons = '';
        foreach ($networks as $slug) {
            $buttons .= self::button($slug, $settings, $context);
        }

        if ('' === $buttons) {
            return '';
        }

        self::$sequence++;
        $label_id = 'dtq-share-label-' . $placement . '-' . self::$sequence;

        // A visually-hidden span, not a heading: Divi Torque's own Table of
        // Contents module walks h2–h6 and would list "Share this post" as a
        // section of the article. And a <section aria-label> would add a
        // landmark per bar, so a page with both bars grows two.
        $heading = '';
        if ('inline' === $placement && !empty($config['show_heading']) && '' !== trim((string) ($config['heading'] ?? ''))) {
            $heading = sprintf(
                '<span class="dtq-share__heading">%s</span>',
                esc_html($config['heading'])
            );
        }

        $group_label = 'floating' === $placement
            ? __('Share this post', 'addons-for-divi')
            : ('' !== $heading ? $config['heading'] : __('Share this post', 'addons-for-divi'));

        return sprintf(
            '<div class="%1$s" role="group" aria-labelledby="%2$s"%3$s>'
                . '<span id="%2$s" class="dtq-share__sr">%4$s</span>'
                . '%5$s'
                . '<div class="dtq-share__list">%6$s</div>'
                . '</div>',
            esc_attr(self::wrapper_class($placement, $settings)),
            esc_attr($label_id),
            self::data_attributes($placement, $settings),
            esc_html($group_label),
            $heading,
            $buttons
        );
    }

    /**
     * Wrapper class list.
     *
     * Every interpolated fragment is an enum value that Share_My_Post_Settings
     * has already constrained, so none of them can introduce a new class.
     *
     * @param string $placement 'inline' or 'floating'.
     * @param array  $settings  Full settings tree.
     *
     * @return string
     */
    public static function wrapper_class($placement, array $settings)
    {
        $appearance = $settings['appearance'] ?? array();

        $classes = array(
            'dtq-share',
            'dtq-share--' . $placement,
            'dtq-share--display-' . ($appearance['display'] ?? 'icon'),
            'dtq-share--color-' . ($appearance['color_mode'] ?? 'brand'),
            'dtq-share--shape-' . ($appearance['shape'] ?? 'rounded'),
            'dtq-share--size-' . ($appearance['size'] ?? 'medium'),
        );

        if ('floating' === $placement) {
            $classes[] = 'dtq-share--side-' . ($settings['floating']['side'] ?? 'left');
        }

        return implode(' ', $classes);
    }

    /**
     * Wrapper data attributes.
     *
     * The mobile flags are attributes rather than classes because the generated
     * media query keys off them — that keeps the generated CSS to a fixed size
     * regardless of how many bars are on the page.
     *
     * @param string $placement 'inline' or 'floating'.
     * @param array  $settings  Full settings tree.
     *
     * @return string
     */
    private static function data_attributes($placement, array $settings)
    {
        $mobile  = $settings['mobile'] ?? array();
        $visible = 'floating' === $placement
            ? !empty($mobile['show_floating'])
            : !empty($mobile['show_inline']);

        $attrs = sprintf(' data-mobile="%s"', $visible ? 'show' : 'hide');

        if ('floating' === $placement) {
            $offset  = (int) ($settings['floating']['offset'] ?? 50);
            $attrs  .= sprintf(' style="--dtq-share-offset:%d%%"', max(0, min(100, $offset)));
        }

        return $attrs;
    }

    /**
     * Render one share button.
     *
     * @param string $slug     Network slug (already catalog-checked by the caller).
     * @param array  $settings Full settings tree.
     * @param array  $context  Share context.
     *
     * @return string
     */
    public static function button($slug, array $settings, array $context)
    {
        $network = Share_My_Post_Networks::get($slug);

        if (null === $network) {
            return '';
        }

        $icons = Share_My_Post_Networks::icons();
        $icon  = $icons[$slug]['svg'] ?? '';

        /* translators: %s: social network name, e.g. Facebook. */
        $accessible_name = sprintf(__('Share on %s', 'addons-for-divi'), $network['label']);

        // Special-cased because "Share on Copy Link" / "Share on Print" is not
        // English. These three act on this page rather than posting to a service.
        if ('copy' === $network['action']) {
            $accessible_name = __('Copy link to this post', 'addons-for-divi');
        } elseif ('print' === $network['action']) {
            $accessible_name = __('Print this post', 'addons-for-divi');
        } elseif ('native' === $network['action']) {
            $accessible_name = __('Share this post', 'addons-for-divi');
        }

        // The accessible name comes from exactly one place. The icon and the
        // visible label are both hidden from assistive tech — otherwise "both"
        // mode announces "Share on Facebook Facebook", and "label" mode
        // announces a bare "Facebook", which fails WCAG 2.4.4 in a row of eight
        // identically-shaped links.
        $inner = sprintf(
            '<span class="dtq-share__sr">%1$s</span>'
                . '<span class="dtq-share__icon" aria-hidden="true">%2$s</span>'
                . '<span class="dtq-share__label" aria-hidden="true">%3$s</span>',
            esc_html($accessible_name),
            $icon, // Generated at build time from Simple Icons; not author input.
            esc_html($network['label'])
        );

        $class = 'dtq-share__btn dtq-share__btn--' . $slug;

        // copy / print / native have no URL to fall back to, so they are real
        // buttons. CSS hides them until frontend.js confirms the API exists —
        // a visible control that does nothing is worse than an absent one.
        if ('' !== $network['action']) {
            return sprintf(
                '<button type="button" class="%1$s" data-dtq-share-action="%2$s" data-dtq-share-url="%3$s" hidden>%4$s</button>',
                esc_attr($class),
                esc_attr($network['action']),
                esc_url($context['url'] ?? ''),
                $inner
            );
        }

        $href = Share_My_Post_Url::build($slug, $context);

        if ('' === $href) {
            return '';
        }

        $popup = Share_My_Post_Url::is_popup($slug) && !empty($settings['advanced']['open_in_popup']);

        // Scheme links (mailto:, sms:, viber:) must not get target="_blank":
        // the OS handler takes over and the new tab is left blank and orphaned.
        $external = Share_My_Post_Url::is_popup($slug);

        return sprintf(
            '<a class="%1$s" href="%2$s"%3$s%4$s>%5$s</a>',
            esc_attr($class),
            // The protocol list is what lets viber:// through. Pro's module skips
            // esc_url() entirely to work around this, losing escaping everywhere.
            esc_url($href, array_merge(wp_allowed_protocols(), Share_My_Post_Networks::EXTRA_PROTOCOLS)),
            $external ? ' target="_blank" rel="noopener nofollow"' : '',
            $popup ? ' data-dtq-share-popup="1"' : '',
            $inner
        );
    }

    /**
     * Inline CSS for the parts that depend on settings.
     *
     * Three pieces, all through wp_add_inline_style() rather than a generated
     * stylesheet on disk: custom colours, brand colours for the enabled networks
     * only, and the media query.
     *
     * @param array $settings Full settings tree.
     *
     * @return string
     */
    public static function inline_css(array $settings)
    {
        $css        = '';
        $appearance = $settings['appearance'] ?? array();

        if ('custom' === ($appearance['color_mode'] ?? 'brand')) {
            $css .= sprintf(
                '.dtq-share--color-custom{--dtq-share-bg:%s;--dtq-share-fg:%s;}',
                dtq_css_color($appearance['custom_bg'] ?? '', '#2b6cb0'),
                dtq_css_color($appearance['custom_fg'] ?? '', '#ffffff')
            );
        } else {
            $css .= self::brand_css($settings);
        }

        $css .= self::breakpoint_css($settings);

        /**
         * Filters the inline CSS emitted for the share bars.
         *
         * @param string $css      Generated CSS.
         * @param array  $settings Full settings tree.
         */
        return apply_filters('divitorque_share_my_post_inline_css', $css, $settings);
    }

    /**
     * Brand colour rules for the networks actually on the page.
     *
     * Real CSS rather than inline style="" attributes: a page with both bars
     * would otherwise repeat every colour once per button per bar, and inline
     * styles need 'unsafe-inline' in a CSP style-src.
     *
     * @param array $settings Full settings tree.
     *
     * @return string
     */
    private static function brand_css(array $settings)
    {
        $slugs = array_unique(
            array_merge(
                Share_My_Post_Networks::filter($settings['inline']['networks'] ?? array()),
                Share_My_Post_Networks::filter($settings['floating']['networks'] ?? array())
            )
        );

        $icons = Share_My_Post_Networks::icons();
        $css   = '';

        foreach ($slugs as $slug) {
            if (!isset($icons[$slug])) {
                continue;
            }

            // hex/fg are generated at build time, never author input — but they
            // still go through dtq_css_color, because "generated" is an argument
            // about today's code rather than a guarantee about the value.
            $css .= sprintf(
                '.dtq-share--color-brand .dtq-share__btn--%1$s{--dtq-share-bg:%2$s;--dtq-share-fg:%3$s;}',
                $slug, // Catalog membership already guarantees [a-z] only.
                dtq_css_color($icons[$slug]['hex'], '#2b6cb0'),
                dtq_css_color($icons[$slug]['fg'], '#ffffff')
            );
        }

        return $css;
    }

    /**
     * The mobile media query.
     *
     * Generated rather than static because a custom property cannot appear in a
     * media condition — `@media (max-width: var(--x))` is invalid and the whole
     * block is dropped. Only the condition varies; the rules inside key off the
     * data-mobile attribute, so this stays a fixed ~200 bytes however many bars
     * are on the page.
     *
     * @param array $settings Full settings tree.
     *
     * @return string
     */
    private static function breakpoint_css(array $settings)
    {
        $breakpoint = (int) ($settings['mobile']['breakpoint'] ?? 768);
        $breakpoint = max(
            Share_My_Post_Settings::BREAKPOINT_MIN,
            min(Share_My_Post_Settings::BREAKPOINT_MAX, $breakpoint)
        );

        return sprintf(
            '@media (max-width:%dpx){.dtq-share[data-mobile="hide"]{display:none;}}',
            $breakpoint
        );
    }
}
