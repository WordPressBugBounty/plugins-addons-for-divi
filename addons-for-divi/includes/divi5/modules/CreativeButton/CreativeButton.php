<?php
/**
 * CreativeButton D5 Module
 *
 * @package DiviTorqueLite\Modules\CreativeButton
 * @since   4.9.0
 */

namespace DiviTorqueLite\Modules\CreativeButton;

if (!defined('ABSPATH')) {
    exit;
}

use ET\Builder\Framework\DependencyManagement\Interfaces\DependencyInterface;
use ET\Builder\Packages\ModuleLibrary\ModuleRegistration;

/**
 * Creative Button module class.
 *
 * Implements the Divi 5 dependency interface so the module can be added to
 * the D5 dependency tree.
 */
class CreativeButton implements DependencyInterface
{
    use CreativeButtonTrait\RenderCallbackTrait;
    use CreativeButtonTrait\ModuleClassnamesTrait;
    use CreativeButtonTrait\ModuleStylesTrait;
    use CreativeButtonTrait\ModuleScriptDataTrait;
    use CreativeButtonTrait\CustomCssTrait;

    // Constants live in the class, not in the traits that read them: PHP
    // before 8.2 cannot declare a constant in a trait, and doing so stopped
    // every page of a Divi 5 site on PHP 7.4 to 8.1 with a fatal error.
    // self:: inside the traits resolves to this class.

    /**
     * Hover effects this module ships.
     *
     * The chosen value lands in a class name, so it is matched against this
     * list rather than trusted.
     */
    const EFFECTS = [
        'none',
        'fill-up',
        'fill-left',
        'fill-diagonal',
        'sweep-in',
        'shutter-h',
        'shutter-v',
        'radial-out',
        'border-draw',
        'shine',
        'text-slide-up',
    ];

    const ALIGNMENTS = ['left', 'center', 'right'];

    /**
     * Easing curves offered by the Effect Easing field.
     *
     * This value is interpolated where CSS expects a timing function, so it is
     * matched against the list rather than pattern-checked — the same treatment
     * navBorderStyle gets in CarouselEngine, and for the same reason.
     */
    const EASINGS = [
        'ease',
        'ease-in',
        'ease-out',
        'ease-in-out',
        'linear',
        'cubic-bezier(0.4, 0, 0.2, 1)',
        'cubic-bezier(0.34, 1.56, 0.64, 1)',
    ];

    /**
     * Load and register the module with Divi 5.
     *
     * @return void
     */
    public function load()
    {
        $module_json_folder_path = DIVI_TORQUE_LITE_MODULES_JSON_PATH . 'creative-button/';

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
