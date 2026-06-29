<?php
/**
 * Tabs D5 Module (parent).
 *
 * Divi 5-only native module (no D4 ancestor).
 *
 * @package DiviTorqueLite\Modules\Tabs
 * @since   4.7.0
 */

namespace DiviTorqueLite\Modules\Tabs;

if (!defined('ABSPATH')) {
    exit;
}

use ET\Builder\Framework\DependencyManagement\Interfaces\DependencyInterface;
use ET\Builder\Packages\ModuleLibrary\ModuleRegistration;

/**
 * Tabs module class (parent of Tab).
 */
class Tabs implements DependencyInterface
{
    use TabsTrait\RenderCallbackTrait;
    use TabsTrait\ModuleClassnamesTrait;
    use TabsTrait\ModuleStylesTrait;
    use TabsTrait\ModuleScriptDataTrait;
    use TabsTrait\CustomCssTrait;

    /**
     * Load and register the module with Divi 5.
     *
     * @return void
     */
    public function load()
    {
        $module_json_folder_path = DIVI_TORQUE_LITE_MODULES_JSON_PATH . 'tabs/';

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
