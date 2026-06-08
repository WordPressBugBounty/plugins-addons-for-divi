<?php
/**
 * InlineNotice D5 Module
 *
 * @package DiviTorqueLite\Modules\InlineNotice
 * @since   4.4.0
 */

namespace DiviTorqueLite\Modules\InlineNotice;

if (!defined('ABSPATH')) {
    exit;
}

use ET\Builder\Framework\DependencyManagement\Interfaces\DependencyInterface;
use ET\Builder\Packages\ModuleLibrary\ModuleRegistration;

/**
 * InlineNotice module class.
 *
 * Implements the Divi 5 dependency interface so the module can be added to
 * the D5 dependency tree.
 */
class InlineNotice implements DependencyInterface
{
    use InlineNoticeTrait\RenderCallbackTrait;
    use InlineNoticeTrait\ModuleClassnamesTrait;
    use InlineNoticeTrait\ModuleStylesTrait;
    use InlineNoticeTrait\ModuleScriptDataTrait;
    use InlineNoticeTrait\CustomCssTrait;

    /**
     * Load and register the module with Divi 5.
     *
     * @return void
     */
    public function load()
    {
        $module_json_folder_path = DIVI_TORQUE_LITE_MODULES_JSON_PATH . 'inline-notice/';

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
