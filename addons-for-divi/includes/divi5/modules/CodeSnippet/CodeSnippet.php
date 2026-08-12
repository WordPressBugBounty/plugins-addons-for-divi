<?php
/**
 * CodeSnippet D5 Module
 *
 * @package DiviTorqueLite\Modules\CodeSnippet
 * @since   4.9.0
 */

namespace DiviTorqueLite\Modules\CodeSnippet;

if (!defined('ABSPATH')) {
    exit;
}

use ET\Builder\Framework\DependencyManagement\Interfaces\DependencyInterface;
use ET\Builder\Packages\ModuleLibrary\ModuleRegistration;

/**
 * Code Snippet module class.
 *
 * Implements the Divi 5 dependency interface so the module can be added to
 * the D5 dependency tree.
 */
class CodeSnippet implements DependencyInterface
{
    use CodeSnippetTrait\RenderCallbackTrait;
    use CodeSnippetTrait\ModuleClassnamesTrait;
    use CodeSnippetTrait\ModuleStylesTrait;
    use CodeSnippetTrait\ModuleScriptDataTrait;
    use CodeSnippetTrait\CustomCssTrait;

    /**
     * Load and register the module with Divi 5.
     *
     * @return void
     */
    public function load()
    {
        $module_json_folder_path = DIVI_TORQUE_LITE_MODULES_JSON_PATH . 'code-snippet/';

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
