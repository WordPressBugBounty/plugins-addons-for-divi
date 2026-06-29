<?php
/**
 * FAQ Item D5 Module (child).
 *
 * Divi 5-only native module. Renders an accordion-structured question + answer.
 *
 * @package DiviTorqueLite\Modules\FaqItem
 * @since   4.7.0
 */

namespace DiviTorqueLite\Modules\FaqItem;

if (!defined('ABSPATH')) {
    exit;
}

use ET\Builder\Framework\DependencyManagement\Interfaces\DependencyInterface;
use ET\Builder\Packages\ModuleLibrary\ModuleRegistration;

class FaqItem implements DependencyInterface
{
    use FaqItemTrait\RenderCallbackTrait;
    use FaqItemTrait\ModuleClassnamesTrait;
    use FaqItemTrait\ModuleStylesTrait;
    use FaqItemTrait\ModuleScriptDataTrait;
    use FaqItemTrait\CustomCssTrait;

    public function load()
    {
        $module_json_folder_path = DIVI_TORQUE_LITE_MODULES_JSON_PATH . 'faq-item/';

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
