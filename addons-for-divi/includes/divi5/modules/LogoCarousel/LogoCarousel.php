<?php
/**
 * LogoCarousel D5 Module (parent).
 *
 * @package DiviTorqueLite\Modules\LogoCarousel
 * @since   4.5.0
 */

namespace DiviTorqueLite\Modules\LogoCarousel;

if (!defined('ABSPATH')) {
    exit;
}

use ET\Builder\Framework\DependencyManagement\Interfaces\DependencyInterface;
use ET\Builder\Packages\ModuleLibrary\ModuleRegistration;

/**
 * Logo Carousel module class (parent of Logo Carousel Item).
 */
class LogoCarousel implements DependencyInterface
{
    use LogoCarouselTrait\RenderCallbackTrait;
    use LogoCarouselTrait\ModuleClassnamesTrait;
    use LogoCarouselTrait\ModuleStylesTrait;
    use LogoCarouselTrait\ModuleScriptDataTrait;
    use LogoCarouselTrait\CustomCssTrait;

    /**
     * Load and register the module with Divi 5.
     *
     * @return void
     */
    public function load()
    {
        $module_json_folder_path = DIVI_TORQUE_LITE_MODULES_JSON_PATH . 'logo-carousel/';

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
