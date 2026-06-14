<?php
/**
 * Before & After Slider (Compare Image) D5 Module
 *
 * @package DiviTorqueLite\Modules\CompareImage
 * @since   4.4.0
 */

namespace DiviTorqueLite\Modules\CompareImage;

if (!defined('ABSPATH')) {
    exit;
}

use ET\Builder\Framework\DependencyManagement\Interfaces\DependencyInterface;
use ET\Builder\Packages\ModuleLibrary\ModuleRegistration;

/**
 * Compare Image module class.
 *
 * Implements the Divi 5 dependency interface so the module can be added to
 * the D5 dependency tree.
 */
class CompareImage implements DependencyInterface
{
    use CompareImageTrait\RenderCallbackTrait;
    use CompareImageTrait\ModuleClassnamesTrait;
    use CompareImageTrait\ModuleStylesTrait;
    use CompareImageTrait\ModuleScriptDataTrait;
    use CompareImageTrait\CustomCssTrait;

    /**
     * Load and register the module with Divi 5.
     *
     * @return void
     */
    public function load()
    {
        $module_json_folder_path = DIVI_TORQUE_LITE_MODULES_JSON_PATH . 'compare-image/';

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
