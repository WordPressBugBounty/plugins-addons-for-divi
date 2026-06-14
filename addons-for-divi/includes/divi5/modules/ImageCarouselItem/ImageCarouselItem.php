<?php
/**
 * ImageCarouselItem D5 Module (child slide).
 *
 * @package DiviTorqueLite\Modules\ImageCarouselItem
 * @since   4.5.0
 */

namespace DiviTorqueLite\Modules\ImageCarouselItem;

if (!defined('ABSPATH')) {
    exit;
}

use ET\Builder\Framework\DependencyManagement\Interfaces\DependencyInterface;
use ET\Builder\Packages\ModuleLibrary\ModuleRegistration;

/**
 * Image Carousel Item module class (child of Image Carousel).
 */
class ImageCarouselItem implements DependencyInterface
{
    use ImageCarouselItemTrait\RenderCallbackTrait;
    use ImageCarouselItemTrait\ModuleClassnamesTrait;
    use ImageCarouselItemTrait\ModuleStylesTrait;
    use ImageCarouselItemTrait\ModuleScriptDataTrait;
    use ImageCarouselItemTrait\CustomCssTrait;

    /**
     * Load and register the module with Divi 5.
     *
     * @return void
     */
    public function load()
    {
        $module_json_folder_path = DIVI_TORQUE_LITE_MODULES_JSON_PATH . 'image-carousel-item/';

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
