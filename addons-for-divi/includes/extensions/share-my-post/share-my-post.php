<?php
/**
 * Share My Post — social sharing for posts, configured site-wide.
 *
 * The plugin's first extension: not a Divi module, not placed in the builder.
 * Turned on once from the dashboard, it renders itself on whichever post types
 * the author picked.
 *
 * @package DiviTorqueLite
 * @since   4.9.0
 */

namespace DiviTorqueLite;

if (!defined('ABSPATH')) {
    exit;
}

// Explicit requires. Composer classmaps includes/, and a stale classmap silently
// skips newly added classes — includes/plugin.php already works around exactly
// this for module-usage.php. Loading the siblings here rather than relying on
// autoload means "the file is on disk" is the only condition.
require_once __DIR__ . '/networks.php';
require_once __DIR__ . '/share-url.php';
require_once __DIR__ . '/settings.php';
require_once __DIR__ . '/render.php';
require_once __DIR__ . '/api.php';

if (is_admin()) {
    require_once __DIR__ . '/admin.php';
}

class Share_My_Post
{
    /** @var Share_My_Post|null */
    private static $instance = null;

    /** @var array Settings tree, resolved once at `wp`. */
    private $settings = array();

    /** @var array Share context for the post being viewed. */
    private $context = array();

    /** @var int The post a bar would be rendered for, 0 when none. */
    private $post_id = 0;

    /** Guards against a plugin re-running the_content over our own output. */
    private $rendering = false;

    /**
     * True while Divi's Post Content module is rendering the post.
     *
     * Divi's Theme Builder does not run a WordPress loop. It calls
     * ET_Post_Stack::replace(), fires `loop_start` by hand, then calls
     * the_content() — see et_theme_builder_frontend_render_post_content() in
     * Divi/includes/builder/frontend-builder/theme-builder/frontend.php. It
     * never calls the_post(), which is the only place core sets `in_the_loop`
     * (WP_Query::the_post()). So on any site with a Theme Builder body layout —
     * the normal way to use Divi — in_the_loop() is false and the loop check
     * below would silently drop the share bar.
     *
     * @var bool
     */
    private $in_post_content = false;

    /**
     * @return Share_My_Post
     */
    public static function get_instance()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    private function __construct()
    {
        // The REST routes and the admin page register unconditionally, even when
        // the feature is switched off. Dark-mode's constructor returns early on
        // its disabled flag; copying that here would also remove the settings
        // API and the settings screen, leaving an author who turned the feature
        // off with no way to turn it back on.
        Share_My_Post_Api::get_instance();

        if (is_admin()) {
            Share_My_Post_Admin::get_instance();
        }

        // Front-end setup waits for `wp`. The builder guard needs
        // et_core_is_fb_enabled(), which the Divi *theme* defines at
        // after_setup_theme — later than plugins_loaded. `wp` is also before
        // both wp_enqueue_scripts and the_content, so one decision serves the
        // asset gate and the render gate.
        add_action('wp', array($this, 'setup'));
    }

    /**
     * Whether this request is a Divi Builder render.
     *
     * Order matters: `et_fb=1` is checked first because it is the only signal
     * that covers the Divi 5 visual builder, which is an ordinary front-end
     * render where et_core_is_fb_enabled() returns false and wp_footer fires
     * normally. Ported from DarkMode_Extension::is_builder().
     *
     * @return bool
     */
    public static function is_builder()
    {
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only builder detection.
        if (isset($_GET['et_fb']) && '1' === sanitize_text_field(wp_unslash($_GET['et_fb']))) {
            return true;
        }

        if (function_exists('et_core_is_fb_enabled') && et_core_is_fb_enabled()) {
            return true;
        }

        return false;
    }

    /**
     * Tier 1 — could this request show a share bar at all?
     *
     * Answered once at `wp`, and it drives both the asset gate and the render
     * gate, so a page that will not render a bar also ships none of the CSS/JS.
     *
     * Pure: takes the request's facts as a struct rather than calling the
     * conditionals itself, which makes the whole matrix a truth table in the
     * tests instead of something only a live site can exercise.
     *
     * @param array $state Request facts.
     *
     * @return bool
     */
    public static function should_render_for(array $state)
    {
        $required_false = array('is_admin', 'is_builder', 'is_feed', 'is_embed', 'is_rest', 'password_required');

        foreach ($required_false as $key) {
            if (!empty($state[$key])) {
                return false;
            }
        }

        if (empty($state['is_singular'])) {
            return false;
        }

        if (empty($state['enabled'])) {
            return false;
        }

        $post_types = isset($state['post_types']) && is_array($state['post_types']) ? $state['post_types'] : array();

        return in_array((string) ($state['post_type'] ?? ''), $post_types, true);
    }

    /**
     * Register the front-end hooks. Runs on `wp`.
     *
     * @return void
     */
    public function setup()
    {
        $this->settings = Share_My_Post_Settings::get();

        // is_preview() is deliberately absent from the guard: an author
        // previewing a draft should see what the published post will look like.
        $post_id = (int) get_queried_object_id();

        $state = array(
            'is_admin'          => is_admin(),
            'is_builder'        => self::is_builder(),
            'is_feed'           => is_feed(),
            'is_embed'          => is_embed(),
            // Blunt: this also switches off legitimate headless rendering.
            // Overridable through the filter below, which is why it is safe to
            // be blunt — the default protects the block editor's own preview
            // (which fetches content.rendered) from growing a share bar.
            'is_rest'           => defined('REST_REQUEST') && REST_REQUEST,
            'is_singular'       => is_singular(),
            'password_required' => $post_id > 0 && post_password_required($post_id),
            'post_type'         => get_post_type($post_id),
            'post_types'        => $this->settings['general']['post_types'] ?? array(),
            'enabled'           => !empty($this->settings['general']['enabled']),
        );

        $should = self::should_render_for($state);

        /**
         * Filters whether Share My Post renders on this request.
         *
         * The REST guard above is intentionally broad; use this filter to allow
         * share bars into a headless consumer's content.rendered.
         *
         * @param bool  $should  Whether to render.
         * @param array $state   The facts the decision was made from.
         * @param int   $post_id Post being viewed.
         */
        if (!apply_filters('divitorque_share_my_post_should_render', $should, $state, $post_id)) {
            return;
        }

        $this->post_id = $post_id;
        $this->context = Share_My_Post_Url::context_from_post(
            $post_id,
            $this->settings['advanced']['mastodon_instance'] ?? ''
        );

        $inline   = !empty($this->settings['inline']['enabled'])
            && !empty(Share_My_Post_Networks::filter($this->settings['inline']['networks'] ?? array()));
        $floating = !empty($this->settings['floating']['enabled'])
            && !empty(Share_My_Post_Networks::filter($this->settings['floating']['networks'] ?? array()));

        if (!$inline && !$floating) {
            return;
        }

        add_action('wp_enqueue_scripts', array($this, 'enqueue'), 20);

        // Divi fires these two around the_content() inside its Post Content
        // module specifically so filters can detect that context — its own
        // docblock says as much. Paired, not a one-way latch: leaving the flag
        // set would hand the same exemption to every later the_content() call
        // on the page, and a related-posts grid below the article would grow a
        // share bar per item.
        add_action('et_theme_builder_before_render_post_content', array($this, 'enter_post_content'));
        add_action('et_theme_builder_after_render_post_content', array($this, 'exit_post_content'));

        if ($inline) {
            /**
             * Filters the_content priority for the inline bar.
             *
             * 20 puts it after wpautop (10), do_shortcode (11) and Divi's own
             * content wrappers, so the markup is emitted verbatim and Divi's
             * shortcodes have already been rendered.
             *
             * @param int $priority Filter priority.
             */
            add_filter('the_content', array($this, 'filter_content'), apply_filters('divitorque_share_my_post_content_priority', 20));
        }

        if ($floating) {
            add_action('wp_footer', array($this, 'render_floating'), 20);
        }
    }

    /**
     * Enqueue the extension's CSS and JS.
     *
     * Reached only when a bar will actually render, so a page without one ships
     * zero bytes of either.
     *
     * @return void
     */
    public function enqueue()
    {
        $dist_url = DIVI_TORQUE_LITE_DIST_URL . 'divi5/';
        $dist_dir = DIVI_TORQUE_LITE_DIR . 'dist/divi5/';

        $ver = function ($file) use ($dist_dir) {
            $path = $dist_dir . $file;
            return file_exists($path) ? (string) filemtime($path) : DIVI_TORQUE_LITE_VERSION;
        };

        wp_enqueue_style(
            'divi-torque-lite-share-my-post',
            $dist_url . 'share-my-post.css',
            array(),
            $ver('share-my-post.css')
        );

        wp_add_inline_style(
            'divi-torque-lite-share-my-post',
            Share_My_Post_Render::inline_css($this->settings)
        );

        wp_enqueue_script(
            'divi-torque-lite-share-my-post',
            $dist_url . 'share-my-post.js',
            array(),
            $ver('share-my-post.js'),
            true
        );

        wp_localize_script(
            'divi-torque-lite-share-my-post',
            'DiviTorqueShareMyPost',
            array(
                'copied' => __('Link copied', 'addons-for-divi'),
                'failed' => __('Could not copy the link', 'addons-for-divi'),
            )
        );
    }

    /**
     * Tier 2 — is *this* the_content call the post's own main content?
     *
     * Tier 1 already established that the request could show a bar. This adds
     * the loop-level facts, which change from call to call within one request:
     * a sidebar widget, a related-posts grid and an excerpt all run the_content
     * on the same page, and each would otherwise get its own share bar.
     *
     * @param string $content Post content.
     *
     * @return string
     */
    public function filter_content($content)
    {
        if ($this->rendering) {
            return $content;
        }

        // The loop check is skipped inside Divi's Post Content module, which
        // renders the post without one (see $in_post_content). Everywhere else
        // it stands: a sidebar widget, a related-posts grid and an excerpt all
        // run the_content on the same page, and each would otherwise get its
        // own share bar.
        if (!$this->in_post_content && (!in_the_loop() || !is_main_query())) {
            return $content;
        }

        // Never relaxed. This is what keeps the bar off a *different* post
        // rendered through the same module, and it is what makes skipping the
        // loop check above safe rather than a hole.
        if (get_the_ID() !== $this->post_id) {
            return $content;
        }

        // get_the_excerpt() runs the_content on the way to building an excerpt.
        // Without this, every excerpt on the site would contain a share bar.
        if (doing_filter('get_the_excerpt')) {
            return $content;
        }

        $this->rendering = true;
        $bar = Share_My_Post_Render::bar('inline', $this->settings, $this->context);
        $this->rendering = false;

        if ('' === $bar) {
            return $content;
        }

        $position = $this->settings['inline']['position'] ?? 'below';

        if ('above' === $position) {
            return $bar . $content;
        }

        if ('both' === $position) {
            return $bar . $content . $bar;
        }

        return $content . $bar;
    }

    /**
     * Divi's Post Content module is about to render. See $in_post_content.
     *
     * @return void
     */
    public function enter_post_content()
    {
        $this->in_post_content = true;
    }

    /**
     * Divi's Post Content module has finished.
     *
     * @return void
     */
    public function exit_post_content()
    {
        $this->in_post_content = false;
    }

    /**
     * Print the floating bar.
     *
     * wp_footer rather than wp_body_open: wp_body_open gives better DOM order,
     * but it is a WP 5.2 hook that older and custom themes do not all call, and
     * a position:fixed element gains nothing from being early in the document.
     *
     * @return void
     */
    public function render_floating()
    {
        // Escaped per attribute inside Share_My_Post_Render. Deliberately not
        // routed through wp_kses_post(): its SVG allow-list is narrow and moves
        // between WordPress versions, and it strips stroke/width/height — which
        // would render every stroked utility glyph as an empty box.
        echo Share_My_Post_Render::bar('floating', $this->settings, $this->context); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
    }
}
