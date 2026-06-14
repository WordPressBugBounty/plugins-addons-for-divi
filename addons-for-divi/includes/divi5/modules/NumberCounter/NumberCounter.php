<?php
/**
 * Number Counter D5 Module
 *
 * @package DiviTorqueLite\Modules\NumberCounter
 * @since   4.4.0
 */

namespace DiviTorqueLite\Modules\NumberCounter;

if (!defined('ABSPATH')) {
    exit;
}

use ET\Builder\Framework\DependencyManagement\Interfaces\DependencyInterface;
use ET\Builder\Packages\ModuleLibrary\ModuleRegistration;

/**
 * Number Counter module class.
 *
 * Implements the Divi 5 dependency interface so the module can be added to
 * the D5 dependency tree.
 */
class NumberCounter implements DependencyInterface
{
    use NumberCounterTrait\RenderCallbackTrait;
    use NumberCounterTrait\ModuleClassnamesTrait;
    use NumberCounterTrait\ModuleStylesTrait;
    use NumberCounterTrait\ModuleScriptDataTrait;
    use NumberCounterTrait\CustomCssTrait;

    /**
     * Load and register the module with Divi 5.
     *
     * @return void
     */
    public function load()
    {
        $module_json_folder_path = DIVI_TORQUE_LITE_MODULES_JSON_PATH . 'number-counter/';

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
