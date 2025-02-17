<?php

if (! function_exists('dtp_fs')) {
    // Create a helper function for easy SDK access.
    function dtFreemius()
    {
        global $dtFreemius;

        if (! isset($dtFreemius)) {

            try {
                $dtFreemius = fs_dynamic_init(array(
                    'id'             => '14886',
                    'slug'           => 'addons-for-divi',
                    'type'           => 'plugin',
                    'public_key'     => 'pk_8f558616fc3f1a6ad3193595141b1',
                    'is_premium'       => false,
                    'has_addons'       => false,
                    'has_paid_plans'   => true,
                    'is_org_compliant' => true,
                    'has_affiliation'  => 'selected',
                    'menu'            => [
                        'slug'        => 'divitorque',
                        'pricing'     => true,
                        'support'     => false,
                        'contact'     => true,
                        'affiliation'  => false,
                    ],
                    'is_live'          => true,
                ));
            } catch (Freemius_Exception $e) {
                return null;
            }
        }

        $dtFreemius->override_i18n([
            'account'    => __('License', 'addons-for-divi'),
            'contact-us' => __('Help', 'addons-for-divi'),
        ]);
        return $dtFreemius;
    }

    // Init Freemius.
    dtFreemius();

    // Set plugin icon
    dtFreemius()->add_filter('plugin_icon', function () {
        return __DIR__ . '/assets/imgs/icon.png';
    });

    // Disable affiliate notice
    dtFreemius()->add_filter('show_affiliate_program_notice', '__return_false');
    // Disable auto deactivation
    dtFreemius()->add_filter('deactivate_on_activation', '__return_false');
    // Disable redirect on activation
    dtFreemius()->add_filter('redirect_on_activation', '__return_false');

    // Signal that SDK was initiated.
    do_action('dt_freemius_loaded');
}
