<?php
/**
 * Fancy Text D5 Module.
 *
 * Divi 5-only native module (no D4 ancestor).
 *
 * @package DiviTorqueLite\Modules\FancyText
 * @since   4.7.0
 */

namespace DiviTorqueLite\Modules\FancyText;

if (!defined('ABSPATH')) {
    exit;
}

use ET\Builder\Framework\DependencyManagement\Interfaces\DependencyInterface;
use ET\Builder\Packages\ModuleLibrary\ModuleRegistration;

/**
 * Fancy Text module class.
 */
class FancyText implements DependencyInterface
{
    use FancyTextTrait\RenderCallbackTrait;
    use FancyTextTrait\ModuleClassnamesTrait;
    use FancyTextTrait\ModuleStylesTrait;
    use FancyTextTrait\ModuleScriptDataTrait;
    use FancyTextTrait\CustomCssTrait;

    /**
     * Load and register the module with Divi 5.
     *
     * @return void
     */
    public function load()
    {
        $module_json_folder_path = DIVI_TORQUE_LITE_MODULES_JSON_PATH . 'fancy-text/';

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
