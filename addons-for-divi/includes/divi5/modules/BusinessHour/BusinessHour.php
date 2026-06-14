<?php
/**
 * BusinessHour D5 Module (parent).
 *
 * @package DiviTorqueLite\Modules\BusinessHour
 * @since   4.4.0
 */

namespace DiviTorqueLite\Modules\BusinessHour;

if (!defined('ABSPATH')) {
    exit;
}

use ET\Builder\Framework\DependencyManagement\Interfaces\DependencyInterface;
use ET\Builder\Packages\ModuleLibrary\ModuleRegistration;

/**
 * Business Hours module class (parent of Business Hour Item).
 */
class BusinessHour implements DependencyInterface
{
    use BusinessHourTrait\RenderCallbackTrait;
    use BusinessHourTrait\ModuleClassnamesTrait;
    use BusinessHourTrait\ModuleStylesTrait;
    use BusinessHourTrait\ModuleScriptDataTrait;
    use BusinessHourTrait\CustomCssTrait;
    use BusinessHourTrait\PatternHelperTrait;

    /**
     * Load and register the module with Divi 5.
     *
     * @return void
     */
    public function load()
    {
        $module_json_folder_path = DIVI_TORQUE_LITE_MODULES_JSON_PATH . 'business-hour/';

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
