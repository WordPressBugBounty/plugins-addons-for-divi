<?php
/**
 * Share My Post: admin menu registration.
 *
 * @package DiviTorqueLite
 * @since   4.9.0
 */

namespace DiviTorqueLite;

if (!defined('ABSPATH')) {
    exit;
}

class Share_My_Post_Admin
{
    /** @var Share_My_Post_Admin|null */
    private static $instance = null;

    /**
     * Own slug, not `divitorque&path=share-my-post`.
     *
     * With Pro active, the `divitorque` slug belongs to *Pro's* dashboard, whose
     * bundle has no share route — the page would load and render nothing.
     *
     * @var string
     */
    const SLUG = 'divitorque-share-my-post';

    /** @var string */
    const CAPABILITY = 'manage_options';

    /**
     * @return Share_My_Post_Admin
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
        // Priority 20: Dashboard::add_menu() runs at the default 10, so the
        // parent menu exists by the time this submenu asks to join it. At 10 the
        // ordering between the two would depend on instantiation order.
        add_action('admin_menu', array($this, 'add_menu'), 20);
        add_filter('divitorque/dashboard_extensions', array($this, 'register_extension'));
    }

    /**
     * Register this page with whichever dashboard app hosts it (Pro's when
     * Pro is active, Lite's otherwise): REST namespace for its endpoints,
     * WP menu slug for submenu highlighting, and the server data the page
     * paints with.
     *
     * @param array $extensions Extensions keyed by app route value.
     *
     * @return array
     */
    public function register_extension($extensions)
    {
        $extensions['share-my-post'] = array(
            'ns'         => 'divitorque-lite/v1',
            'slug'       => self::SLUG,
            // The compiled front-end stylesheet — the admin live preview
            // loads it (every selector is .dtq-share-scoped, admin-safe).
            'previewCss' => $this->preview_css_url(),
            'data'       => array(
                'networks'  => $this->networks_with_icons(),
                'postTypes' => Share_My_Post_Settings::selectable_post_types(),
            ),
        );

        return $extensions;
    }

    /**
     * Network catalog with the icon map folded in — the picker swatches and
     * the live preview need hex/fg/svg, which live in a separate file so the
     * front end can skip them until render.
     *
     * @return array
     */
    private function networks_with_icons()
    {
        $networks = Share_My_Post_Networks::all();
        $icons    = Share_My_Post_Networks::icons();

        foreach ($networks as $slug => $network) {
            if (isset($icons[$slug])) {
                $networks[$slug] = array_merge($network, $icons[$slug]);
            }
        }

        return $networks;
    }

    /**
     * @return string
     */
    private function preview_css_url()
    {
        $rel  = 'dist/divi5/share-my-post.css';
        $file = DIVI_TORQUE_LITE_DIR . $rel;

        if (!is_readable($file)) {
            return '';
        }

        return esc_url_raw(
            add_query_arg('ver', (string) filemtime($file), DIVI_TORQUE_LITE_URL . $rel)
        );
    }

    /**
     * Register the settings page.
     *
     * Registered whether or not Pro is active. Dashboard::add_menu() returns
     * early when Pro is installed — that hides Lite's *dashboard*, which is
     * correct, because Pro ships its own. This page has no Pro counterpart, so
     * hiding it would leave the feature unreachable on exactly the sites most
     * likely to want it.
     *
     * Not registered while the extension is switched off: an inactive
     * extension keeps no admin surface. That never strands the feature — the
     * Extensions screen re-enables it under Lite, and under Pro the in-app
     * route (Overview's Sharing Buttons card) still reaches the page, whose
     * master switch is the same option.
     *
     * @return void
     */
    public function add_menu()
    {
        if (!current_user_can(self::CAPABILITY)) {
            return;
        }

        $settings = Share_My_Post_Settings::get();
        if (empty($settings['general']['enabled'])) {
            return;
        }

        // Lite and Pro both register `divitorque` as their top-level slug, and
        // exactly one of them is ever active, so the parent is the same either
        // way — this page just joins whichever dashboard is there.
        $parent = 'divitorque';

        // If neither registered it (a capability filter, or Pro's loader
        // bailing), add_submenu_page() would silently drop the page and leave
        // no way to reach the settings. A top-level entry keeps it reachable.
        if (!$this->parent_exists($parent)) {
            add_menu_page(
                __('Sharing Buttons', 'addons-for-divi'),
                __('Sharing Buttons', 'addons-for-divi'),
                self::CAPABILITY,
                self::SLUG,
                array($this, 'render'),
                'dashicons-share',
                131
            );
            return;
        }

        add_submenu_page(
            $parent,
            __('Sharing Buttons', 'addons-for-divi'),
            __('Sharing Buttons', 'addons-for-divi'),
            self::CAPABILITY,
            self::SLUG,
            array($this, 'render')
        );
    }

    /**
     * Whether a top-level menu slug has been registered.
     *
     * @param string $slug Menu slug.
     *
     * @return bool
     */
    private function parent_exists($slug)
    {
        global $menu;

        if (!is_array($menu)) {
            return false;
        }

        foreach ($menu as $item) {
            if (isset($item[2]) && $item[2] === $slug) {
                return true;
            }
        }

        return false;
    }

    /**
     * One dashboard, never two: with Pro's v2 dashboard active it owns every
     * app screen — delegate so only Pro's bundle boots. Pro in legacy mode
     * (or absent) falls back to Lite's own app.
     *
     * @return void
     */
    public function render()
    {
        if (Dashboard::get_instance()->pro_owns_dashboard()) {
            \DiviTorque\Dashboard::get_instance()->render_extension_page('share-my-post');
            return;
        }

        Dashboard::get_instance()->render_extension_page('share-my-post');
    }
}
