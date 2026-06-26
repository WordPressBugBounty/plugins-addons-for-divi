<?php
/**
 * Contact Form 7: custom CSS trait.
 *
 * @package DiviTorqueLite\Modules\ContactForm7
 * @since   4.6.0
 */

namespace DiviTorqueLite\Modules\ContactForm7\ContactForm7Trait;

if (!defined('ABSPATH')) {
    exit;
}

trait CustomCssTrait
{
    public static function custom_css_fields()
    {
        $registry = \WP_Block_Type_Registry::get_instance();
        $block    = $registry ? $registry->get_registered('divitorque/contact-form-7') : null;

        if ($block && isset($block->customCssFields) && is_array($block->customCssFields)) {
            return $block->customCssFields;
        }

        return [];
    }
}
