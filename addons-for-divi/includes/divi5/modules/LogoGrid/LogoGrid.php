<?php
/**
 * LogoGrid D5 Module (parent).
 *
 * @package DiviTorqueLite\Modules\LogoGrid
 * @since   4.4.0
 */

namespace DiviTorqueLite\Modules\LogoGrid;

if (!defined('ABSPATH')) {
    exit;
}

use ET\Builder\Framework\DependencyManagement\Interfaces\DependencyInterface;
use ET\Builder\Packages\ModuleLibrary\ModuleRegistration;

/**
 * Logo Grid module class (parent of Logo Grid Item).
 */
class LogoGrid implements DependencyInterface
{
    use LogoGridTrait\RenderCallbackTrait;
    use LogoGridTrait\ModuleClassnamesTrait;
    use LogoGridTrait\ModuleStylesTrait;
    use LogoGridTrait\ModuleScriptDataTrait;
    use LogoGridTrait\CustomCssTrait;

    /**
     * Load and register the module with Divi 5.
     *
     * @return void
     */
    public function load()
    {
        $module_json_folder_path = DIVI_TORQUE_LITE_MODULES_JSON_PATH . 'logo-grid/';

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
