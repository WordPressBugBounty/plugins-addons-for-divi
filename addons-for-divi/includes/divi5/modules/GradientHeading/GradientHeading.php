<?php
/**
 * GradientHeading D5 Module
 *
 * @package DiviTorqueLite\Modules\GradientHeading
 * @since   4.3.0
 */

namespace DiviTorqueLite\Modules\GradientHeading;

if (!defined('ABSPATH')) {
    exit;
}

use ET\Builder\Framework\DependencyManagement\Interfaces\DependencyInterface;
use ET\Builder\Packages\ModuleLibrary\ModuleRegistration;

/**
 * GradientHeading module class.
 *
 * Implements the Divi 5 dependency interface so the module can be added to
 * the D5 dependency tree.
 */
class GradientHeading implements DependencyInterface
{
    use GradientHeadingTrait\RenderCallbackTrait;
    use GradientHeadingTrait\ModuleClassnamesTrait;
    use GradientHeadingTrait\ModuleStylesTrait;
    use GradientHeadingTrait\ModuleScriptDataTrait;
    use GradientHeadingTrait\CustomCssTrait;

    /**
     * Load and register the module with Divi 5.
     *
     * @return void
     */
    public function load()
    {
        $module_json_folder_path = DIVI_TORQUE_LITE_MODULES_JSON_PATH . 'gradient-heading/';

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
