<?php
/**
 * TabItem D5 Module (child).
 *
 * Divi 5-only native module (no D4 ancestor).
 *
 * @package DiviTorqueLite\Modules\TabItem
 * @since   4.7.0
 */

namespace DiviTorqueLite\Modules\TabItem;

if (!defined('ABSPATH')) {
    exit;
}

use ET\Builder\Framework\DependencyManagement\Interfaces\DependencyInterface;
use ET\Builder\Packages\ModuleLibrary\ModuleRegistration;

/**
 * Tab Item module class (child of Tabs).
 */
class TabItem implements DependencyInterface
{
    use TabItemTrait\RenderCallbackTrait;
    use TabItemTrait\ModuleClassnamesTrait;
    use TabItemTrait\ModuleStylesTrait;
    use TabItemTrait\ModuleScriptDataTrait;
    use TabItemTrait\CustomCssTrait;

    /**
     * Load and register the module with Divi 5.
     *
     * @return void
     */
    public function load()
    {
        $module_json_folder_path = DIVI_TORQUE_LITE_MODULES_JSON_PATH . 'tab-item/';

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
