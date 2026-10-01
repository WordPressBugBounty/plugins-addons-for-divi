<?php
/**
 * StickyVideo D5 Module
 *
 * @package DiviTorqueLite\Modules\StickyVideo
 * @since   4.9.0
 */

namespace DiviTorqueLite\Modules\StickyVideo;

if (!defined('ABSPATH')) {
    exit;
}

use ET\Builder\Framework\DependencyManagement\Interfaces\DependencyInterface;
use ET\Builder\Packages\ModuleLibrary\ModuleRegistration;

/**
 * Sticky Video module class.
 *
 * Implements the Divi 5 dependency interface so the module can be added to
 * the D5 dependency tree.
 */
class StickyVideo implements DependencyInterface
{
    use StickyVideoTrait\RenderCallbackTrait;
    use StickyVideoTrait\ModuleClassnamesTrait;
    use StickyVideoTrait\ModuleStylesTrait;
    use StickyVideoTrait\ModuleScriptDataTrait;
    use StickyVideoTrait\CustomCssTrait;

    // Constants live in the class, not in the traits that read them: PHP
    // before 8.2 cannot declare a constant in a trait, and doing so stopped
    // every page of a Divi 5 site on PHP 7.4 to 8.1 with a fatal error.
    // self:: inside the traits resolves to this class.

    const SOURCES    = ['youtube', 'vimeo', 'self'];

    const POSITIONS  = ['bottom-right', 'bottom-left', 'top-right', 'top-left'];

    const ANIMATIONS = ['slide', 'fade', 'none'];

    const RATIOS     = ['16:9', '4:3', '1:1', '21:9'];

    const DISABLE_ON = ['none', 'phone', 'tablet-phone'];

    const STICK_ON   = ['played', 'always'];

    const CLOSE_ACTIONS = ['unstick', 'stop'];

    /**
     * Load and register the module with Divi 5.
     *
     * @return void
     */
    public function load()
    {
        $module_json_folder_path = DIVI_TORQUE_LITE_MODULES_JSON_PATH . 'sticky-video/';

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
