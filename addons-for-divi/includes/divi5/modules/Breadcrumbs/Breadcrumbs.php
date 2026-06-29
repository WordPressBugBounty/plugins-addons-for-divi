<?php
/**
 * Breadcrumbs D5 Module.
 *
 * Divi 5-only native module (no D4 ancestor).
 *
 * @package DiviTorqueLite\Modules\Breadcrumbs
 * @since   4.7.0
 */

namespace DiviTorqueLite\Modules\Breadcrumbs;

if (!defined('ABSPATH')) {
    exit;
}

use ET\Builder\Framework\DependencyManagement\Interfaces\DependencyInterface;
use ET\Builder\Packages\ModuleLibrary\ModuleRegistration;

/**
 * Breadcrumbs module class.
 */
class Breadcrumbs implements DependencyInterface
{
    use BreadcrumbsTrait\RenderCallbackTrait;
    use BreadcrumbsTrait\ModuleClassnamesTrait;
    use BreadcrumbsTrait\ModuleStylesTrait;
    use BreadcrumbsTrait\ModuleScriptDataTrait;
    use BreadcrumbsTrait\CustomCssTrait;

    /**
     * Load and register the module with Divi 5.
     *
     * @return void
     */
    public function load()
    {
        $module_json_folder_path = DIVI_TORQUE_LITE_MODULES_JSON_PATH . 'breadcrumbs/';

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
