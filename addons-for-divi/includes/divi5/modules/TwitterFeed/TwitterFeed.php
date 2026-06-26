<?php
/**
 * Twitter Feed D5 Module (X API v2).
 *
 * @package DiviTorqueLite\Modules\TwitterFeed
 * @since   4.6.0
 */

namespace DiviTorqueLite\Modules\TwitterFeed;

if (!defined('ABSPATH')) {
    exit;
}

use ET\Builder\Framework\DependencyManagement\Interfaces\DependencyInterface;
use ET\Builder\Packages\ModuleLibrary\ModuleRegistration;

/**
 * Twitter Feed module. Fetches tweets via the X API v2 (paid plan required),
 * cached, and renders them in a responsive grid.
 */
class TwitterFeed implements DependencyInterface
{
    use TwitterFeedTrait\RenderCallbackTrait;
    use TwitterFeedTrait\ModuleClassnamesTrait;
    use TwitterFeedTrait\ModuleStylesTrait;
    use TwitterFeedTrait\ModuleScriptDataTrait;
    use TwitterFeedTrait\CustomCssTrait;

    public function load()
    {
        $module_json_folder_path = DIVI_TORQUE_LITE_MODULES_JSON_PATH . 'twitter-feed/';

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
