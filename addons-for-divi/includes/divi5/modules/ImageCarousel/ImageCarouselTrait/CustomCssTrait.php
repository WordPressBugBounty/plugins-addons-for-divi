<?php
/**
 * ImageCarousel: Custom CSS trait.
 *
 * @package DiviTorqueLite\Modules\ImageCarousel
 * @since   4.5.0
 */

namespace DiviTorqueLite\Modules\ImageCarousel\ImageCarouselTrait;

if (!defined('ABSPATH')) {
    exit;
}

trait CustomCssTrait
{
    /**
     * Return the custom CSS field list for this module from registered metadata.
     *
     * @return array
     */
    public static function custom_css_fields()
    {
        $registry = \WP_Block_Type_Registry::get_instance();
        $block    = $registry ? $registry->get_registered('divitorque/image-carousel') : null;

        if ($block && isset($block->customCssFields) && is_array($block->customCssFields)) {
            return $block->customCssFields;
        }

        return [];
    }
}
