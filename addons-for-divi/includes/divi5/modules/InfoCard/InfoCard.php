<?php
/**
 * InfoCard D5 Module
 *
 * @package DiviTorqueLite\Modules\InfoCard
 * @since   4.4.0
 */

namespace DiviTorqueLite\Modules\InfoCard;

if (!defined('ABSPATH')) {
    exit;
}

use ET\Builder\Framework\DependencyManagement\Interfaces\DependencyInterface;
use ET\Builder\Packages\ModuleLibrary\ModuleRegistration;

/**
 * InfoCard module class.
 *
 * Implements the Divi 5 dependency interface so the module can be added to
 * the D5 dependency tree.
 */
class InfoCard implements DependencyInterface
{
    use InfoCardTrait\RenderCallbackTrait;
    use InfoCardTrait\ModuleClassnamesTrait;
    use InfoCardTrait\ModuleStylesTrait;
    use InfoCardTrait\ModuleScriptDataTrait;
    use InfoCardTrait\CustomCssTrait;

    /**
     * Load and register the module with Divi 5.
     *
     * @return void
     */
    public function load()
    {
        $module_json_folder_path = DIVI_TORQUE_LITE_MODULES_JSON_PATH . 'info-card/';

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
