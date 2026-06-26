<?php
/**
 * Post List D5 Module
 *
 * @package DiviTorqueLite\Modules\PostList
 * @since   4.6.0
 */

namespace DiviTorqueLite\Modules\PostList;

if (!defined('ABSPATH')) {
    exit;
}

use ET\Builder\Framework\DependencyManagement\Interfaces\DependencyInterface;
use ET\Builder\Packages\ModuleLibrary\ModuleRegistration;

/**
 * Post List module class. Dynamic (WP_Query-backed): the post list is built
 * server-side in the render callback; the Visual Builder preview fetches posts
 * over the plugin REST endpoint (see RestApi::get_posts).
 */
class PostList implements DependencyInterface
{
    use PostListTrait\RenderCallbackTrait;
    use PostListTrait\ModuleClassnamesTrait;
    use PostListTrait\ModuleStylesTrait;
    use PostListTrait\ModuleScriptDataTrait;
    use PostListTrait\CustomCssTrait;

    /**
     * Load and register the module with Divi 5.
     *
     * @return void
     */
    public function load()
    {
        $module_json_folder_path = DIVI_TORQUE_LITE_MODULES_JSON_PATH . 'post-list/';

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
