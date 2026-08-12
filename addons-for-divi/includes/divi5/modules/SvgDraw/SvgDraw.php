<?php
/**
 * SvgDraw D5 Module
 *
 * @package DiviTorqueLite\Modules\SvgDraw
 * @since   4.9.0
 */

namespace DiviTorqueLite\Modules\SvgDraw;

if (!defined('ABSPATH')) {
    exit;
}

use ET\Builder\Framework\DependencyManagement\Interfaces\DependencyInterface;
use ET\Builder\Packages\ModuleLibrary\ModuleRegistration;

/**
 * SVG Draw module class.
 *
 * Implements the Divi 5 dependency interface so the module can be added to
 * the D5 dependency tree.
 */
class SvgDraw implements DependencyInterface
{
    use SvgDrawTrait\RenderCallbackTrait;
    use SvgDrawTrait\ModuleClassnamesTrait;
    use SvgDrawTrait\ModuleStylesTrait;
    use SvgDrawTrait\ModuleScriptDataTrait;
    use SvgDrawTrait\CustomCssTrait;

    /**
     * Load and register the module with Divi 5.
     *
     * @return void
     */
    public function load()
    {
        $module_json_folder_path = DIVI_TORQUE_LITE_MODULES_JSON_PATH . 'svg-draw/';

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
