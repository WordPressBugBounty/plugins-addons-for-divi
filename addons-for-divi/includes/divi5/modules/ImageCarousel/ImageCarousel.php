<?php
/**
 * ImageCarousel D5 Module (parent).
 *
 * @package DiviTorqueLite\Modules\ImageCarousel
 * @since   4.5.0
 */

namespace DiviTorqueLite\Modules\ImageCarousel;

if (!defined('ABSPATH')) {
    exit;
}

use ET\Builder\Framework\DependencyManagement\Interfaces\DependencyInterface;
use ET\Builder\Packages\ModuleLibrary\ModuleRegistration;

/**
 * Image Carousel module class (parent of Image Carousel Item).
 */
class ImageCarousel implements DependencyInterface
{
    use ImageCarouselTrait\RenderCallbackTrait;
    use ImageCarouselTrait\ModuleClassnamesTrait;
    use ImageCarouselTrait\ModuleStylesTrait;
    use ImageCarouselTrait\ModuleScriptDataTrait;
    use ImageCarouselTrait\CustomCssTrait;

    /**
     * Load and register the module with Divi 5.
     *
     * @return void
     */
    public function load()
    {
        $module_json_folder_path = DIVI_TORQUE_LITE_MODULES_JSON_PATH . 'image-carousel/';

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
