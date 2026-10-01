<?php
/**
 * Tooltip D5 Module
 *
 * @package DiviTorqueLite\Modules\Tooltip
 * @since   4.9.0
 */

namespace DiviTorqueLite\Modules\Tooltip;

if (!defined('ABSPATH')) {
    exit;
}

use ET\Builder\Framework\DependencyManagement\Interfaces\DependencyInterface;
use ET\Builder\Packages\ModuleLibrary\ModuleRegistration;

/**
 * Tooltip module class.
 *
 * Implements the Divi 5 dependency interface so the module can be added to
 * the D5 dependency tree.
 */
class Tooltip implements DependencyInterface
{
    use TooltipTrait\RenderCallbackTrait;
    use TooltipTrait\ModuleClassnamesTrait;
    use TooltipTrait\ModuleStylesTrait;
    use TooltipTrait\ModuleScriptDataTrait;
    use TooltipTrait\CustomCssTrait;

    // Constants live in the class, not in the traits that read them: PHP
    // before 8.2 cannot declare a constant in a trait, and doing so stopped
    // every page of a Divi 5 site on PHP 7.4 to 8.1 with a fatal error.
    // self:: inside the traits resolves to this class.

    /**
     * Placements tippy understands.
     *
     * The chosen value is emitted as configuration, so it is matched against
     * this list rather than passed through.
     */
    const PLACEMENTS = [
        'top', 'top-start', 'top-end',
        'right', 'right-start', 'right-end',
        'bottom', 'bottom-start', 'bottom-end',
        'left', 'left-start', 'left-end',
    ];

    /**
     * Only two animations ship. tippy's UMD injects its own core CSS, which
     * covers `fade`, and assets/libs/tippy/tippy.min.css is 394 bytes holding
     * exactly one animation — `scale`.
     */
    const ANIMATIONS = ['fade', 'scale'];

    const TRIGGER_TYPES = ['text', 'icon', 'image'];

    const ALIGNMENTS = ['left', 'center', 'right'];

    /**
     * Load and register the module with Divi 5.
     *
     * @return void
     */
    public function load()
    {
        $module_json_folder_path = DIVI_TORQUE_LITE_MODULES_JSON_PATH . 'tooltip/';

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
