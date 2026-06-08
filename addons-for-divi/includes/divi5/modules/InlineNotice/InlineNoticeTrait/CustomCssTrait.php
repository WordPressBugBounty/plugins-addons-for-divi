<?php
/**
 * InlineNotice: Custom CSS trait.
 *
 * @package DiviTorqueLite\Modules\InlineNotice
 * @since   4.4.0
 */

namespace DiviTorqueLite\Modules\InlineNotice\InlineNoticeTrait;

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
        $block    = $registry ? $registry->get_registered('divitorque/inline-notice') : null;

        if ($block && isset($block->customCssFields) && is_array($block->customCssFields)) {
            return $block->customCssFields;
        }

        return [];
    }
}
