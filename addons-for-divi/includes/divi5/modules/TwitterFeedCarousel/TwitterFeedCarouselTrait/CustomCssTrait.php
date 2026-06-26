<?php
/**
 * Twitter Carousel: custom CSS trait.
 *
 * @package DiviTorqueLite\Modules\TwitterFeedCarousel
 * @since   4.6.0
 */

namespace DiviTorqueLite\Modules\TwitterFeedCarousel\TwitterFeedCarouselTrait;

if (!defined('ABSPATH')) {
    exit;
}

trait CustomCssTrait
{
    public static function custom_css_fields()
    {
        $registry = \WP_Block_Type_Registry::get_instance();
        $block    = $registry ? $registry->get_registered('divitorque/twitter-feed-carousel') : null;

        if ($block && isset($block->customCssFields) && is_array($block->customCssFields)) {
            return $block->customCssFields;
        }

        return [];
    }
}
