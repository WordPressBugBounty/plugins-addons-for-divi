<?php
/**
 * SkillBarItem D5 Module (child bar).
 *
 * @package DiviTorqueLite\Modules\SkillBarItem
 * @since   4.4.0
 */

namespace DiviTorqueLite\Modules\SkillBarItem;

if (!defined('ABSPATH')) {
    exit;
}

use ET\Builder\Framework\DependencyManagement\Interfaces\DependencyInterface;
use ET\Builder\Packages\ModuleLibrary\ModuleRegistration;

/**
 * Skill Bar Item module class (child of Skill Bar).
 */
class SkillBarItem implements DependencyInterface
{
    use SkillBarItemTrait\RenderCallbackTrait;
    use SkillBarItemTrait\ModuleClassnamesTrait;
    use SkillBarItemTrait\ModuleStylesTrait;
    use SkillBarItemTrait\ModuleScriptDataTrait;
    use SkillBarItemTrait\CustomCssTrait;

    /**
     * Load and register the module with Divi 5.
     *
     * @return void
     */
    public function load()
    {
        $module_json_folder_path = DIVI_TORQUE_LITE_MODULES_JSON_PATH . 'skill-bar-item/';

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
