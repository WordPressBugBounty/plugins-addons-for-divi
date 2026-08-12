<?php
/**
 * StickyVideo: Custom CSS trait.
 *
 * @package DiviTorqueLite\Modules\StickyVideo
 * @since   4.9.0
 */

namespace DiviTorqueLite\Modules\StickyVideo\StickyVideoTrait;

if (!defined('ABSPATH')) {
    exit;
}

trait CustomCssTrait
{
    /**
     * Return the custom CSS field list for this module by reading the
     * registered block type metadata.
     *
     * @return array
     */
    public static function custom_css_fields()
    {
        $registry = \WP_Block_Type_Registry::get_instance();
        $block    = $registry ? $registry->get_registered('divitorque/sticky-video') : null;

        if ($block && isset($block->customCssFields) && is_array($block->customCssFields)) {
            return $block->customCssFields;
        }

        return [];
    }
}
