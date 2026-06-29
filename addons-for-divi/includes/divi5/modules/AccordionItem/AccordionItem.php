<?php
/**
 * AccordionItem D5 Module (child).
 *
 * Divi 5-only native module (no D4 ancestor).
 *
 * @package DiviTorqueLite\Modules\AccordionItem
 * @since   4.7.0
 */

namespace DiviTorqueLite\Modules\AccordionItem;

if (!defined('ABSPATH')) {
    exit;
}

use ET\Builder\Framework\DependencyManagement\Interfaces\DependencyInterface;
use ET\Builder\Packages\ModuleLibrary\ModuleRegistration;

/**
 * Accordion Item module class (child of Accordion).
 */
class AccordionItem implements DependencyInterface
{
    use AccordionItemTrait\RenderCallbackTrait;
    use AccordionItemTrait\ModuleClassnamesTrait;
    use AccordionItemTrait\ModuleStylesTrait;
    use AccordionItemTrait\ModuleScriptDataTrait;
    use AccordionItemTrait\CustomCssTrait;

    /**
     * Load and register the module with Divi 5.
     *
     * @return void
     */
    public function load()
    {
        $module_json_folder_path = DIVI_TORQUE_LITE_MODULES_JSON_PATH . 'accordion-item/';

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
