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

    // Constants live in the class, not in the traits that read them: PHP
    // before 8.2 cannot declare a constant in a trait, and doing so stopped
    // every page of a Divi 5 site on PHP 7.4 to 8.1 with a fatal error.
    // self:: inside the traits resolves to this class.

    /**
     * Languages bundled in assets/libs/prism.
     *
     * The value lands in a `language-*` class Prism reads, so it is matched
     * against this list rather than passed through — and this is also exactly
     * what is bundled, since there is no autoloader to fetch anything else.
     */
    const LANGUAGES = [
        'none', 'markup', 'css', 'javascript', 'typescript', 'jsx', 'php',
        'python', 'bash', 'json', 'sql', 'yaml', 'markdown',
    ];

    const THEMES = ['dark', 'light', 'midnight', 'paper'];

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
