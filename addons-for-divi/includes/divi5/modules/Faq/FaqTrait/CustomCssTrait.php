<?php
/**
 * Faq: Custom CSS trait.
 *
 * @package DiviTorqueLite\Modules\Faq
 * @since   4.7.0
 */

namespace DiviTorqueLite\Modules\Faq\FaqTrait;

if (!defined('ABSPATH')) {
    exit;
}

trait CustomCssTrait
{
    public static function custom_css_fields()
    {
        $registry = \WP_Block_Type_Registry::get_instance();
        $block    = $registry ? $registry->get_registered('divitorque/faq') : null;

        if ($block && isset($block->customCssFields) && is_array($block->customCssFields)) {
            return $block->customCssFields;
        }
        return [];
    }
}
