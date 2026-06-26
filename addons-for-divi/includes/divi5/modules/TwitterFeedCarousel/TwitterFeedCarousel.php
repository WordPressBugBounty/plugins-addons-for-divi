<?php
/**
 * Twitter Carousel D5 Module (X API v2 + Swiper).
 *
 * @package DiviTorqueLite\Modules\TwitterFeedCarousel
 * @since   4.6.0
 */

namespace DiviTorqueLite\Modules\TwitterFeedCarousel;

if (!defined('ABSPATH')) {
    exit;
}

use ET\Builder\Framework\DependencyManagement\Interfaces\DependencyInterface;
use ET\Builder\Packages\ModuleLibrary\ModuleRegistration;

/**
 * Twitter Carousel module. Fetches tweets via the X API v2 (paid plan,
 * cached) and renders them in a Swiper carousel — reusing the same shared
 * CarouselEngine the Image/Logo carousels use (no slick).
 */
class TwitterFeedCarousel implements DependencyInterface
{
    use TwitterFeedCarouselTrait\RenderCallbackTrait;
    use TwitterFeedCarouselTrait\ModuleClassnamesTrait;
    use TwitterFeedCarouselTrait\ModuleStylesTrait;
    use TwitterFeedCarouselTrait\ModuleScriptDataTrait;
    use TwitterFeedCarouselTrait\CustomCssTrait;

    public function load()
    {
        $module_json_folder_path = DIVI_TORQUE_LITE_MODULES_JSON_PATH . 'twitter-feed-carousel/';

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
