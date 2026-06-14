<?php
/**
 * BusinessHour: Custom CSS trait.
 *
 * @package DiviTorqueLite\Modules\BusinessHour
 * @since   4.4.0
 */

namespace DiviTorqueLite\Modules\BusinessHour\BusinessHourTrait;

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
        $block    = $registry ? $registry->get_registered('divitorque/business-hour') : null;

        if ($block && isset($block->customCssFields) && is_array($block->customCssFields)) {
            return $block->customCssFields;
        }

        return [];
    }
}
