<?php
/**
 * CtaBox D5 Module
 *
 * @package DiviTorqueLite\Modules\CtaBox
 * @since   4.9.0
 */

namespace DiviTorqueLite\Modules\CtaBox;

if (!defined('ABSPATH')) {
    exit;
}

use ET\Builder\Framework\DependencyManagement\Interfaces\DependencyInterface;
use ET\Builder\Packages\ModuleLibrary\ModuleRegistration;

/**
 * Call To Action module class.
 *
 * Implements the Divi 5 dependency interface so the module can be added to
 * the D5 dependency tree.
 */
class CtaBox implements DependencyInterface
{
    use CtaBoxTrait\RenderCallbackTrait;
    use CtaBoxTrait\ModuleClassnamesTrait;
    use CtaBoxTrait\ModuleStylesTrait;
    use CtaBoxTrait\ModuleScriptDataTrait;
    use CtaBoxTrait\CustomCssTrait;

    /**
     * Load and register the module with Divi 5.
     *
     * @return void
     */
    public function load()
    {
        $module_json_folder_path = DIVI_TORQUE_LITE_MODULES_JSON_PATH . 'cta-box/';

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
