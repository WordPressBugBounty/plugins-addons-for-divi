<?php
/**
 * LogoGridItem D5 Module (child logo).
 *
 * @package DiviTorqueLite\Modules\LogoGridItem
 * @since   4.4.0
 */

namespace DiviTorqueLite\Modules\LogoGridItem;

if (!defined('ABSPATH')) {
    exit;
}

use ET\Builder\Framework\DependencyManagement\Interfaces\DependencyInterface;
use ET\Builder\Packages\ModuleLibrary\ModuleRegistration;

/**
 * Logo Grid Item module class (child of Logo Grid).
 */
class LogoGridItem implements DependencyInterface
{
    use LogoGridItemTrait\RenderCallbackTrait;
    use LogoGridItemTrait\ModuleClassnamesTrait;
    use LogoGridItemTrait\ModuleStylesTrait;
    use LogoGridItemTrait\ModuleScriptDataTrait;
    use LogoGridItemTrait\CustomCssTrait;

    /**
     * Load and register the module with Divi 5.
     *
     * @return void
     */
    public function load()
    {
        $module_json_folder_path = DIVI_TORQUE_LITE_MODULES_JSON_PATH . 'logo-grid-item/';

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
