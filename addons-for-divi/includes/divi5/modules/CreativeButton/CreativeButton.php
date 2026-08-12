<?php
/**
 * CreativeButton D5 Module
 *
 * @package DiviTorqueLite\Modules\CreativeButton
 * @since   4.9.0
 */

namespace DiviTorqueLite\Modules\CreativeButton;

if (!defined('ABSPATH')) {
    exit;
}

use ET\Builder\Framework\DependencyManagement\Interfaces\DependencyInterface;
use ET\Builder\Packages\ModuleLibrary\ModuleRegistration;

/**
 * Creative Button module class.
 *
 * Implements the Divi 5 dependency interface so the module can be added to
 * the D5 dependency tree.
 */
class CreativeButton implements DependencyInterface
{
    use CreativeButtonTrait\RenderCallbackTrait;
    use CreativeButtonTrait\ModuleClassnamesTrait;
    use CreativeButtonTrait\ModuleStylesTrait;
    use CreativeButtonTrait\ModuleScriptDataTrait;
    use CreativeButtonTrait\CustomCssTrait;

    /**
     * Load and register the module with Divi 5.
     *
     * @return void
     */
    public function load()
    {
        $module_json_folder_path = DIVI_TORQUE_LITE_MODULES_JSON_PATH . 'creative-button/';

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
