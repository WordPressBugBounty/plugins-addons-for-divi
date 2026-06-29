<?php
/**
 * Modal Popup D5 Module.
 *
 * Divi 5-only native module (no D4 ancestor).
 *
 * @package DiviTorqueLite\Modules\ModalPopup
 * @since   4.7.0
 */

namespace DiviTorqueLite\Modules\ModalPopup;

if (!defined('ABSPATH')) {
    exit;
}

use ET\Builder\Framework\DependencyManagement\Interfaces\DependencyInterface;
use ET\Builder\Packages\ModuleLibrary\ModuleRegistration;

/**
 * Modal Popup module class.
 */
class ModalPopup implements DependencyInterface
{
    use ModalPopupTrait\RenderCallbackTrait;
    use ModalPopupTrait\ModuleClassnamesTrait;
    use ModalPopupTrait\ModuleStylesTrait;
    use ModalPopupTrait\ModuleScriptDataTrait;
    use ModalPopupTrait\CustomCssTrait;

    /**
     * Load and register the module with Divi 5.
     *
     * @return void
     */
    public function load()
    {
        $module_json_folder_path = DIVI_TORQUE_LITE_MODULES_JSON_PATH . 'modal-popup/';

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
