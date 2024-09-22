<?php
/*
 * Plugin Name: Divi Torque Lite
 * Plugin URI:  https://diviepic.com/torque-pro/
 * Description: Create beautiful and attracting posts, pages, and landing pages with Divi Torque Lite.
 * Author:      DiviEpic
 * Author URI:  https://diviepic.com
 * Version: 4.0.5
 * 
 * License:     GPL3
 * License URI: https://www.gnu.org/licenses/gpl-3.0.txt
 * Text Domain: addons-for-divi
 * Domain Path: /languages
 * 
 * Requires at least: 5.0
 * Tested up to:      6.5.3
 * WordPress Available: yes
 * Requires License:    no
 * Divi: yes
 * 
 * Divi Torque Lite is a plugin that adds a lot of new modules and features to the Divi Builder. It is a free version of Divi Torque Pro.
 * 
 * @package Divi Torque Lite
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

define('DIVI_TORQUE_LITE_FILE', __FILE__);
define('DIVI_TORQUE_LITE_BASE', plugin_basename(__FILE__));
define('DIVI_TORQUE_LITE_VERSION', '4.0.5');
define('DIVI_TORQUE_LITE_DIR', plugin_dir_path(__FILE__));
define('DIVI_TORQUE_LITE_URL', plugin_dir_url(__FILE__));
define('DIVI_TORQUE_LITE_ASSETS', trailingslashit(DIVI_TORQUE_LITE_URL . 'assets'));

do_action('divitorque_loaded');

if (!file_exists(__DIR__ . '/vendor/autoload.php')) {
    return;
}

require_once __DIR__ . '/vendor/autoload.php';

function dt_is_pro_installed()
{
    return defined('DTP_VERSION');
}

function dt_is_dm_pro_installed()
{
    return defined('DTP_BASENAME') && 'et-divitorque-pro' === DTP_BASENAME;
}

// Load Freemius
if (!function_exists('dtp_init_freemius')) {
    require_once __DIR__ . '/freemius.php';
}

require_once 'plugin-loader.php';
