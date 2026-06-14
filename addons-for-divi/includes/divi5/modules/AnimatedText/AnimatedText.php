<?php
/**
 * Animated Text: Main module class (Divi 5).
 *
 * @package DiviTorqueLite\Modules\AnimatedText
 * @since   4.4.0
 */

namespace DiviTorqueLite\Modules\AnimatedText;

if (!defined('ABSPATH')) {
    exit;
}

use ET\Builder\Framework\DependencyManagement\Interfaces\DependencyInterface;
use ET\Builder\Packages\ModuleLibrary\ModuleRegistration;

class AnimatedText implements DependencyInterface
{
    use AnimatedTextTrait\RenderCallbackTrait;
    use AnimatedTextTrait\ModuleClassnamesTrait;
    use AnimatedTextTrait\ModuleStylesTrait;
    use AnimatedTextTrait\ModuleScriptDataTrait;
    use AnimatedTextTrait\CustomCssTrait;

    /**
     * Register the module on init (block-editor contexts only).
     *
     * @return void
     */
    public function load()
    {
        $module_json_folder_path = DIVI_TORQUE_LITE_MODULES_JSON_PATH . 'animated-text/';

        add_action(
            'init',
            function () use ($module_json_folder_path) {
                ModuleRegistration::register_module(
                    $module_json_folder_path,
                    ['render_callback' => [self::class, 'render_callback']]
                );
            }
        );
    }
}
