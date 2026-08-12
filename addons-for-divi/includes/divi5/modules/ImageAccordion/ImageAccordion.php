<?php
/**
 * ImageAccordion D5 Module
 *
 * @package DiviTorqueLite\Modules\ImageAccordion
 * @since   4.9.0
 */

namespace DiviTorqueLite\Modules\ImageAccordion;

if (!defined('ABSPATH')) {
    exit;
}

use ET\Builder\Framework\DependencyManagement\Interfaces\DependencyInterface;
use ET\Builder\Packages\ModuleLibrary\ModuleRegistration;

/**
 * ImageAccordion module class.
 *
 * Implements the Divi 5 dependency interface so the module can be added to
 * the D5 dependency tree.
 */
class ImageAccordion implements DependencyInterface
{
    use ImageAccordionTrait\RenderCallbackTrait;
    use ImageAccordionTrait\ModuleClassnamesTrait;
    use ImageAccordionTrait\ModuleStylesTrait;
    use ImageAccordionTrait\ModuleScriptDataTrait;
    use ImageAccordionTrait\CustomCssTrait;

    /**
     * Load and register the module with Divi 5.
     *
     * @return void
     */
    public function load()
    {
        $module_json_folder_path = DIVI_TORQUE_LITE_MODULES_JSON_PATH . 'image-accordion/';

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
