<?php
/**
 * News Ticker D5 Module
 *
 * @package DiviTorqueLite\Modules\NewsTicker
 * @since   4.6.0
 */

namespace DiviTorqueLite\Modules\NewsTicker;

if (!defined('ABSPATH')) {
    exit;
}

use ET\Builder\Framework\DependencyManagement\Interfaces\DependencyInterface;
use ET\Builder\Packages\ModuleLibrary\ModuleRegistration;

/**
 * News Ticker module class.
 *
 * First dynamic (WP_Query-backed) Divi Torque module on Divi 5: the post list
 * is built server-side in the render callback; the Visual Builder preview
 * fetches posts over the plugin REST endpoint (see RestApi::get_posts).
 */
class NewsTicker implements DependencyInterface
{
    use NewsTickerTrait\RenderCallbackTrait;
    use NewsTickerTrait\ModuleClassnamesTrait;
    use NewsTickerTrait\ModuleStylesTrait;
    use NewsTickerTrait\ModuleScriptDataTrait;
    use NewsTickerTrait\CustomCssTrait;

    /**
     * Load and register the module with Divi 5.
     *
     * @return void
     */
    public function load()
    {
        $module_json_folder_path = DIVI_TORQUE_LITE_MODULES_JSON_PATH . 'news-ticker/';

        add_action(
            'init',
            function () use ($module_json_folder_path) {
                ModuleRegistration::register_module(
                    $module_json_folder_path,
                    [
                        'render_callback' => [self::class, 'render_callback'],
                    ]
                );
            }
        );
    }
}
