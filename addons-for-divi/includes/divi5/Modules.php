<?php
/**
 * Divi 5 module loader for Divi Torque Lite.
 *
 * Auto-detects whether Divi 5 is active by looking for its
 * DependencyInterface. On Divi 4 (or any site without D5), this file
 * returns early and zero D5 code runs — D4 modules keep working as-is.
 *
 * @package DiviTorqueLite
 * @since   4.3.0
 */

if (!defined('ABSPATH')) {
    exit;
}

// Auto-detect Divi 5. Without it, exit silently — D4 modules keep working.
$dtl_d5_dependency_interface = ABSPATH . 'wp-content/themes/Divi/includes/builder-5/server/Framework/DependencyManagement/Interfaces/DependencyInterface.php';
if (!file_exists($dtl_d5_dependency_interface)) {
    return;
}
require_once $dtl_d5_dependency_interface;

// slug => [ folder, fully-qualified class ]
$dtl_d5_modules = array(
    'gradient_heading' => array('GradientHeading', '\\DiviTorqueLite\\Modules\\GradientHeading\\GradientHeading'),
    'divider'          => array('Divider', '\\DiviTorqueLite\\Modules\\Divider\\Divider'),
    'icon_box'         => array('IconBox', '\\DiviTorqueLite\\Modules\\IconBox\\IconBox'),
    'inline_notice'    => array('InlineNotice', '\\DiviTorqueLite\\Modules\\InlineNotice\\InlineNotice'),
    'dual_button'      => array('DualButton', '\\DiviTorqueLite\\Modules\\DualButton\\DualButton'),
    'info_box'         => array('InfoBox', '\\DiviTorqueLite\\Modules\\InfoBox\\InfoBox'),
    'info_card'        => array('InfoCard', '\\DiviTorqueLite\\Modules\\InfoCard\\InfoCard'),
    'team_box'         => array('TeamBox', '\\DiviTorqueLite\\Modules\\TeamBox\\TeamBox'),
);

$dtl_d5_loaded = array();
foreach ($dtl_d5_modules as $slug => $info) {
    $module_file = __DIR__ . '/modules/' . $info[0] . '/' . $info[0] . '.php';
    if (!file_exists($module_file)) {
        continue;
    }
    require_once $module_file;
    $dtl_d5_loaded[$slug] = $info[1];
}

// Register modules in the D5 dependency tree.
add_action(
    'divi_module_library_modules_dependency_tree',
    function ($dependency_tree) use ($dtl_d5_loaded) {
        foreach ($dtl_d5_loaded as $class) {
            if (class_exists($class)) {
                $dependency_tree->add_dependency(new $class());
            }
        }
    }
);

// Tell Divi 5 where each module's conversion-outline.json lives.
add_filter(
    'divi.moduleLibrary.conversion.moduleConversionOutlineFile',
    function ($file_path, $module_name) {
        $outlines = array(
            'divitorque/gradient-heading' => 'gradient-heading/conversion-outline.json',
            'divitorque/divider'          => 'divider/conversion-outline.json',
            'divitorque/icon-box'         => 'icon-box/conversion-outline.json',
            'divitorque/inline-notice'    => 'inline-notice/conversion-outline.json',
            'divitorque/dual-button'      => 'dual-button/conversion-outline.json',
            'divitorque/info-box'         => 'info-box/conversion-outline.json',
            'divitorque/info-card'        => 'info-card/conversion-outline.json',
            'divitorque/team-box'         => 'team-box/conversion-outline.json',
        );
        if (isset($outlines[$module_name])) {
            return DIVI_TORQUE_LITE_MODULES_JSON_PATH . $outlines[$module_name];
        }
        return $file_path;
    },
    9,
    2
);

/**
 * Enqueue D5 Visual Builder bundle inside the VB iframe.
 */
add_action(
    'divi_visual_builder_assets_before_enqueue_scripts',
    function () {
        if (!class_exists('\ET\Builder\VisualBuilder\Assets\PackageBuildManager')) {
            return;
        }

        $dist_url = DIVI_TORQUE_LITE_DIST_URL . 'divi5/';

        wp_enqueue_script('wp-api-fetch');

        \ET\Builder\VisualBuilder\Assets\PackageBuildManager::register_package_build([
            'name'    => 'divi-torque-lite-d5-bundle-script',
            'version' => DIVI_TORQUE_LITE_VERSION,
            'script'  => [
                'src'                => $dist_url . 'bundle.js',
                'deps'               => ['divi-module-library', 'divi-vendor-wp-hooks'],
                'enqueue_top_window' => false,
                'enqueue_app_window' => true,
            ],
        ]);

        \ET\Builder\VisualBuilder\Assets\PackageBuildManager::register_package_build([
            'name'    => 'divi-torque-lite-d5-bundle-style',
            'version' => DIVI_TORQUE_LITE_VERSION,
            'style'   => [
                'src'                => $dist_url . 'bundle.css',
                'deps'               => [],
                'enqueue_top_window' => false,
                'enqueue_app_window' => true,
            ],
        ]);
    }
);

/**
 * Frontend assets for D5 modules — gated, fires only on non-admin requests.
 */
add_action(
    'wp_enqueue_scripts',
    function () {
        if (is_admin()) {
            return;
        }

        $dist_url = DIVI_TORQUE_LITE_DIST_URL . 'divi5/';

        wp_enqueue_style(
            'divi-torque-lite-d5-frontend',
            $dist_url . 'bundle.css',
            [],
            DIVI_TORQUE_LITE_VERSION
        );

        wp_enqueue_script(
            'divi-torque-lite-d5-frontend',
            $dist_url . 'frontend.js',
            ['jquery'],
            DIVI_TORQUE_LITE_VERSION,
            true
        );
    }
);
