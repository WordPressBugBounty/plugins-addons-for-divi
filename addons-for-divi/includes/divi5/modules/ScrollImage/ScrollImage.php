<?php
/**
 * ScrollImage D5 Module
 *
 * @package DiviTorqueLite\Modules\ScrollImage
 * @since   4.4.0
 */

namespace DiviTorqueLite\Modules\ScrollImage;

if (!defined('ABSPATH')) {
    exit;
}

use ET\Builder\Framework\DependencyManagement\Interfaces\DependencyInterface;
use ET\Builder\Packages\ModuleLibrary\ModuleRegistration;

/**
 * ScrollImage module class.
 *
 * Implements the Divi 5 dependency interface so the module can be added to
 * the D5 dependency tree.
 */
class ScrollImage implements DependencyInterface
{
    use ScrollImageTrait\RenderCallbackTrait;
    use ScrollImageTrait\ModuleClassnamesTrait;
    use ScrollImageTrait\ModuleStylesTrait;
    use ScrollImageTrait\ModuleScriptDataTrait;
    use ScrollImageTrait\CustomCssTrait;

    /**
     * Load and register the module with Divi 5.
     *
     * @return void
     */
    public function load()
    {
        $module_json_folder_path = DIVI_TORQUE_LITE_MODULES_JSON_PATH . 'scroll-image/';

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
