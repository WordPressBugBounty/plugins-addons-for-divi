<?php
/**
 * BusinessHourItem D5 Module (child row).
 *
 * @package DiviTorqueLite\Modules\BusinessHourItem
 * @since   4.4.0
 */

namespace DiviTorqueLite\Modules\BusinessHourItem;

if (!defined('ABSPATH')) {
    exit;
}

use ET\Builder\Framework\DependencyManagement\Interfaces\DependencyInterface;
use ET\Builder\Packages\ModuleLibrary\ModuleRegistration;

/**
 * Business Hour Item module class (child of Business Hours).
 */
class BusinessHourItem implements DependencyInterface
{
    use BusinessHourItemTrait\RenderCallbackTrait;
    use BusinessHourItemTrait\ModuleClassnamesTrait;
    use BusinessHourItemTrait\ModuleStylesTrait;
    use BusinessHourItemTrait\ModuleScriptDataTrait;
    use BusinessHourItemTrait\CustomCssTrait;
    // SVG pattern helpers shared with the parent (module-local copy of the D4
    // helpers; the JS twin lives in src/divi5/modules/business-hour/pattern.js).
    use \DiviTorqueLite\Modules\BusinessHour\BusinessHourTrait\PatternHelperTrait;

    /**
     * Load and register the module with Divi 5.
     *
     * @return void
     */
    public function load()
    {
        $module_json_folder_path = DIVI_TORQUE_LITE_MODULES_JSON_PATH . 'business-hour-item/';

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
