<?php
/**
 * SkillBar D5 Module (parent).
 *
 * @package DiviTorqueLite\Modules\SkillBar
 * @since   4.4.0
 */

namespace DiviTorqueLite\Modules\SkillBar;

if (!defined('ABSPATH')) {
    exit;
}

use ET\Builder\Framework\DependencyManagement\Interfaces\DependencyInterface;
use ET\Builder\Packages\ModuleLibrary\ModuleRegistration;

/**
 * Skill Bar module class (parent of Skill Bar Item).
 */
class SkillBar implements DependencyInterface
{
    use SkillBarTrait\RenderCallbackTrait;
    use SkillBarTrait\ModuleClassnamesTrait;
    use SkillBarTrait\ModuleStylesTrait;
    use SkillBarTrait\ModuleScriptDataTrait;
    use SkillBarTrait\CustomCssTrait;

    /**
     * Load and register the module with Divi 5.
     *
     * @return void
     */
    public function load()
    {
        $module_json_folder_path = DIVI_TORQUE_LITE_MODULES_JSON_PATH . 'skill-bar/';

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
