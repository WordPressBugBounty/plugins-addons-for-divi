<?php
/**
 * ImageAccordionItem D5 Module
 *
 * @package DiviTorqueLite\Modules\ImageAccordionItem
 * @since   4.9.0
 */

namespace DiviTorqueLite\Modules\ImageAccordionItem;

if (!defined('ABSPATH')) {
    exit;
}

use ET\Builder\Framework\DependencyManagement\Interfaces\DependencyInterface;
use ET\Builder\Packages\ModuleLibrary\ModuleRegistration;

/**
 * ImageAccordionItem module class.
 *
 * Implements the Divi 5 dependency interface so the module can be added to
 * the D5 dependency tree.
 */
class ImageAccordionItem implements DependencyInterface
{
    use ImageAccordionItemTrait\RenderCallbackTrait;
    use ImageAccordionItemTrait\ModuleClassnamesTrait;
    use ImageAccordionItemTrait\ModuleStylesTrait;
    use ImageAccordionItemTrait\ModuleScriptDataTrait;
    use ImageAccordionItemTrait\CustomCssTrait;

    /**
     * Load and register the module with Divi 5.
     *
     * @return void
     */
    public function load()
    {
        $module_json_folder_path = DIVI_TORQUE_LITE_MODULES_JSON_PATH . 'image-accordion-item/';

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
