<?php
/**
 * Contact Form 7 D5 Module
 *
 * @package DiviTorqueLite\Modules\ContactForm7
 * @since   4.6.0
 */

namespace DiviTorqueLite\Modules\ContactForm7;

if (!defined('ABSPATH')) {
    exit;
}

use ET\Builder\Framework\DependencyManagement\Interfaces\DependencyInterface;
use ET\Builder\Packages\ModuleLibrary\ModuleRegistration;

/**
 * Contact Form 7 styler module. Renders a CF7 form (via shortcode) with an
 * optional header and an extensive styling surface ported from the D4 module.
 */
class ContactForm7 implements DependencyInterface
{
    use ContactForm7Trait\RenderCallbackTrait;
    use ContactForm7Trait\ModuleClassnamesTrait;
    use ContactForm7Trait\ModuleStylesTrait;
    use ContactForm7Trait\ModuleScriptDataTrait;
    use ContactForm7Trait\CustomCssTrait;

    /**
     * Load and register the module with Divi 5.
     *
     * @return void
     */
    public function load()
    {
        $module_json_folder_path = DIVI_TORQUE_LITE_MODULES_JSON_PATH . 'contact-form-7/';

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
