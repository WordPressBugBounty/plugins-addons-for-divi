<?php
/**
 * Tooltip D5 Module
 *
 * @package DiviTorqueLite\Modules\Tooltip
 * @since   4.9.0
 */

namespace DiviTorqueLite\Modules\Tooltip;

if (!defined('ABSPATH')) {
    exit;
}

use ET\Builder\Framework\DependencyManagement\Interfaces\DependencyInterface;
use ET\Builder\Packages\ModuleLibrary\ModuleRegistration;

/**
 * Tooltip module class.
 *
 * Implements the Divi 5 dependency interface so the module can be added to
 * the D5 dependency tree.
 */
class Tooltip implements DependencyInterface
{
    use TooltipTrait\RenderCallbackTrait;
    use TooltipTrait\ModuleClassnamesTrait;
    use TooltipTrait\ModuleStylesTrait;
    use TooltipTrait\ModuleScriptDataTrait;
    use TooltipTrait\CustomCssTrait;

    /**
     * Load and register the module with Divi 5.
     *
     * @return void
     */
    public function load()
    {
        $module_json_folder_path = DIVI_TORQUE_LITE_MODULES_JSON_PATH . 'tooltip/';

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
