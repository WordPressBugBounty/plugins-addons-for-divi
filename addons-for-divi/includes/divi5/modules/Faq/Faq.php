<?php
/**
 * FAQ D5 Module (parent).
 *
 * Divi 5-only native module. Reuses the Accordion collapse engine (shared
 * `.dtq-accordion*` CSS + frontend.js) and outputs FAQPage schema.
 *
 * @package DiviTorqueLite\Modules\Faq
 * @since   4.7.0
 */

namespace DiviTorqueLite\Modules\Faq;

if (!defined('ABSPATH')) {
    exit;
}

use ET\Builder\Framework\DependencyManagement\Interfaces\DependencyInterface;
use ET\Builder\Packages\ModuleLibrary\ModuleRegistration;

class Faq implements DependencyInterface
{
    use FaqTrait\RenderCallbackTrait;
    use FaqTrait\ModuleClassnamesTrait;
    use FaqTrait\ModuleStylesTrait;
    use FaqTrait\ModuleScriptDataTrait;
    use FaqTrait\CustomCssTrait;

    public function load()
    {
        $module_json_folder_path = DIVI_TORQUE_LITE_MODULES_JSON_PATH . 'faq/';

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
