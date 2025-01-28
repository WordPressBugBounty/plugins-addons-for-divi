<?php

if (!function_exists('dtp_fs')) {
    function dtp_fs()
    {
        global $dtp_fs;

        if (!isset($dtp_fs)) {

            require_once dirname(__FILE__) . '/freemius/start.php';

            $dtp_fs = fs_dynamic_init(array(
                'id'                => '14886',
                'slug'              => 'addons-for-divi',
                'type'              => 'plugin',
                'public_key'        => 'pk_8f558616fc3f1a6ad3193595141b1',
                'is_premium'        => false,
                'has_addons'        => false,
                'has_paid_plans'    => true,
                'is_org_compliant'  => true,
                'menu'              => array(
                    'slug'       => 'divitorque',
                    'account'    => false,
                    'contact'    => false,
                    'pricing'    => false,
                    'support'    => false,
                    'affiliation' => false,
                ),
                'is_live'         => true,
            ));
        }

        return $dtp_fs;
    }
}

// Register hooks and actions.
function dtl_init_hooks()
{
    $dtp_fs = dtp_fs();

    // Set plugin icon
    $dtp_fs->add_filter('plugin_icon', function () {
        return __DIR__ . '/assets/imgs/icon.png';
    });

    // Disable some Freemius features.
    dtp_fs()->add_filter('show_deactivation_feedback_form', '__return_false');
    dtp_fs()->add_filter('hide_freemius_powered_by', '__return_true');
    dtp_fs()->add_filter('permission_diagnostic_default', '__return_false');
    // Disable opt-in option by default
    dtp_fs()->add_filter('permission_extensions_default', '__return_false');

    // Hide Freemius notices that can easily annoy users.
    dtp_fs()->add_filter(
        'show_admin_notice',
        function ($show, $message) {
            if ($message['id'] === 'license_activated' || $message['id'] === 'premium_activated' || $message['id'] === 'connect_account') {
                return false;
            }
            return $show;
        },
        10,
        2
    );

    // Signal that SDK was initiated.
    do_action('dtp_fs_loaded');
}

dtl_init_hooks();
