<?php
/**
 * Accordion D5 Module (parent).
 *
 * Divi 5-only native module (no D4 ancestor).
 *
 * @package DiviTorqueLite\Modules\Accordion
 * @since   4.7.0
 */

namespace DiviTorqueLite\Modules\Accordion;

if (!defined('ABSPATH')) {
    exit;
}

use ET\Builder\Framework\DependencyManagement\Interfaces\DependencyInterface;
use ET\Builder\Packages\ModuleLibrary\ModuleRegistration;

/**
 * Accordion module class (parent of Accordion Item).
 */
class Accordion implements DependencyInterface
{
    use AccordionTrait\RenderCallbackTrait;
    use AccordionTrait\ModuleClassnamesTrait;
    use AccordionTrait\ModuleStylesTrait;
    use AccordionTrait\ModuleScriptDataTrait;
    use AccordionTrait\CustomCssTrait;

    /**
     * Load and register the module with Divi 5.
     *
     * @return void
     */
    public function load()
    {
        $module_json_folder_path = DIVI_TORQUE_LITE_MODULES_JSON_PATH . 'accordion/';

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
