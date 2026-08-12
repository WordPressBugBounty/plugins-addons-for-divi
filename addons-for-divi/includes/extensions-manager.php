<?php

namespace DiviTorqueLite;

/**
 * Extensions Manager — the manifest behind the Extensions screen.
 *
 * Extensions are the things Divi Torque adds that are not builder modules:
 * a shortcode, a settings-driven feature, a whole sub-app. Until now they had
 * no home in the dashboard at all — Sharing Buttons sat as a top-level nav item
 * and the library shortcode was invisible, so there was no page that answered
 * "what else does this plugin do?"
 *
 * Two rules this file exists to enforce:
 *
 * 1. A switch is only rendered for something that is genuinely switchable, and
 *    it writes an option that already exists. Sharing Buttons has a real master
 *    switch; the library shortcode is gated on a legacy install marker, which is
 *    not a user preference, so it reports its state and offers no control. A
 *    switch that silently does nothing is worse than no switch.
 *
 * 2. `pro` lives here, not in the catalog. assets/module-catalog.json is
 *    generated from the website and currently marks every extension
 *    `pro: false`, which is wrong for a Pro feature set. The catalog is a
 *    presentation source — label, description, icon, docs — and nothing else.
 *
 * @package addons-for-divi
 */

if (!defined('ABSPATH')) {
    exit;
}

class Extensions_Manager
{
    private static $instance;

    /**
     * Pro extensions, shown to Lite users as locked cards.
     *
     * Slugs match assets/module-catalog.json's `extensions` map, which supplies
     * the label, description, icon and docs link. Listed here rather than read
     * from the catalog's `pro` flag because that flag is wrong (see above).
     */
    const PRO_SLUGS = array('popup-maker', 'mega-menu', 'dark-mode', 'mailer', 'maintenance-mode');

    public static function get_instance()
    {
        if (!isset(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * The full card list: what this plugin ships, then what Pro adds.
     *
     * @return array
     */
    public static function all()
    {
        $extensions = array(
            array(
                'slug'       => 'share-my-post',
                'label'      => __('Sharing Buttons', 'addons-for-divi'),
                'desc'       => __('Inline or floating share buttons.', 'addons-for-divi'),
                'active'     => self::share_my_post_active(),
                'toggleable' => true,
                'configure'  => 'share-my-post',
                'locked'     => false,
                'note'       => '',
            ),
            array(
                'slug'       => 'divi-library-shortcode',
                'label'      => __('Library Shortcode', 'addons-for-divi'),
                'desc'       => __('Shortcode for any Library layout.', 'addons-for-divi'),
                'active'     => self::library_shortcode_active(),
                'toggleable' => false,
                'configure'  => '',
                'locked'     => false,
                // Honest about why there is no switch: it is not a preference.
                'note'       => self::library_shortcode_active()
                    ? __('Always on. Find the shortcode in the Divi Library list.', 'addons-for-divi')
                    : __('Replaced by the same feature in Divi Torque Pro.', 'addons-for-divi'),
            ),
        );

        foreach (self::PRO_SLUGS as $slug) {
            $extensions[] = array(
                'slug'       => $slug,
                'label'      => '',
                'desc'       => '',
                'active'     => false,
                'toggleable' => false,
                'configure'  => '',
                'locked'     => true,
                'note'       => '',
            );
        }

        return $extensions;
    }

    /**
     * Flip an extension on or off. Only ever writes an option that already
     * exists, and refuses anything not declared toggleable.
     *
     * @param string $slug
     * @param bool   $active
     * @return bool True when the state was applied.
     */
    public static function set_active($slug, $active)
    {
        if ('share-my-post' !== $slug) {
            return false;
        }

        $settings                       = Share_My_Post_Settings::get();
        $settings['general']['enabled'] = (bool) $active;
        Share_My_Post_Settings::update($settings);

        return true;
    }

    private static function share_my_post_active()
    {
        $settings = Share_My_Post_Settings::get();

        return !empty($settings['general']['enabled']);
    }

    /**
     * Mirrors the load condition in Plugin::init() — the shortcode is only
     * registered on sites that never had the old Divi Torque plugin, because
     * Pro ships the same shortcode and two registrations would collide.
     */
    private static function library_shortcode_active()
    {
        return !get_option('divitorque_version');
    }
}
