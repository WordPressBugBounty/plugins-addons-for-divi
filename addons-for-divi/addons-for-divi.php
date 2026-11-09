<?php
/*
 * Plugin Name: Divi Torque Lite
 * Plugin URI:  https://divitorque.com
 * Description: Enhance your Divi website with powerful addons and modules.
 * Author:      PlugPress
 * Author URI:  https://plugpress.io
 * Version:     4.16.0
 * Requires at least: 7.0
 * Requires PHP: 7.4
 * License:     GPL3
 * License URI: https://www.gnu.org/licenses/gpl-3.0.txt
 * Text Domain: addons-for-divi
 * Domain Path: /languages
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

define('DIVI_TORQUE_LITE_FILE', __FILE__);
define('DIVI_TORQUE_LITE_BASE', plugin_basename(__FILE__));
define('DIVI_TORQUE_LITE_VERSION', '4.16.0');
define('DIVI_TORQUE_LITE_DIR', plugin_dir_path(__FILE__));
define('DIVI_TORQUE_LITE_URL', plugin_dir_url(__FILE__));
define('DIVI_TORQUE_LITE_ASSETS', trailingslashit(DIVI_TORQUE_LITE_URL . 'assets'));
define('DIVI_TORQUE_LITE_DIST_URL', trailingslashit(DIVI_TORQUE_LITE_URL . 'dist'));
define('DIVI_TORQUE_LITE_MODULES_JSON_PATH', trailingslashit(DIVI_TORQUE_LITE_DIR . 'modules-json'));

if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
}

// PlugPress Insight: the opt-in card on Overview and the Plugins screen, and
// quick feedback on Deactivate. Nothing leaves the site until the owner clicks
// Allow, Subscribe or Send and deactivate. Lite and Pro report as one brand.
require_once DIVI_TORQUE_LITE_DIR . 'includes/insight/insight.php';
plugpress_insight(
    array(
        'file'    => __FILE__,
        'product' => 'divi-torque',
        'brand'   => 'Divi Torque',
        'name'    => 'Divi Torque Lite',
        'version' => DIVI_TORQUE_LITE_VERSION,
        'page'    => 'divitorque',
        // Overview's first card only; the welcome tour has no hero.
        'slot'    => '.divitorque-app .pp-content:has(> .dt-hero)',
    )
);

require_once DIVI_TORQUE_LITE_DIR . 'includes/plugin.php';
require_once DIVI_TORQUE_LITE_DIR . 'includes/divi5/Modules.php';
