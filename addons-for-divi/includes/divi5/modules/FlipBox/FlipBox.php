<?php
/**
 * FlipBox D5 Module
 *
 * @package DiviTorqueLite\Modules\FlipBox
 * @since   4.4.0
 */

namespace DiviTorqueLite\Modules\FlipBox;

if (!defined('ABSPATH')) {
    exit;
}

use ET\Builder\Framework\DependencyManagement\Interfaces\DependencyInterface;
use ET\Builder\Packages\ModuleLibrary\ModuleRegistration;

/**
 * FlipBox module class.
 *
 * Implements the Divi 5 dependency interface so the module can be added to
 * the D5 dependency tree.
 */
class FlipBox implements DependencyInterface
{
    use FlipBoxTrait\RenderCallbackTrait;
    use FlipBoxTrait\ModuleClassnamesTrait;
    use FlipBoxTrait\ModuleStylesTrait;
    use FlipBoxTrait\ModuleScriptDataTrait;
    use FlipBoxTrait\CustomCssTrait;

    /**
     * Load and register the module with Divi 5.
     *
     * @return void
     */
    public function load()
    {
        $module_json_folder_path = DIVI_TORQUE_LITE_MODULES_JSON_PATH . 'flip-box/';

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
