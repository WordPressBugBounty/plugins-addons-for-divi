<?php
/**
 * IconBox D5 Module
 *
 * @package DiviTorqueLite\Modules\IconBox
 * @since   4.4.0
 */

namespace DiviTorqueLite\Modules\IconBox;

if (!defined('ABSPATH')) {
    exit;
}

use ET\Builder\Framework\DependencyManagement\Interfaces\DependencyInterface;
use ET\Builder\Packages\ModuleLibrary\ModuleRegistration;

/**
 * IconBox module class.
 *
 * Implements the Divi 5 dependency interface so the module can be added to
 * the D5 dependency tree.
 */
class IconBox implements DependencyInterface
{
    use IconBoxTrait\RenderCallbackTrait;
    use IconBoxTrait\ModuleClassnamesTrait;
    use IconBoxTrait\ModuleStylesTrait;
    use IconBoxTrait\ModuleScriptDataTrait;
    use IconBoxTrait\CustomCssTrait;

    /**
     * Load and register the module with Divi 5.
     *
     * @return void
     */
    public function load()
    {
        $module_json_folder_path = DIVI_TORQUE_LITE_MODULES_JSON_PATH . 'icon-box/';

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
