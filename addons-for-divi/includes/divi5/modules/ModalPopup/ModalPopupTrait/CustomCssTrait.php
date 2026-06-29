<?php
/**
 * ModalPopup: Custom CSS trait.
 *
 * @package DiviTorqueLite\Modules\ModalPopup
 * @since   4.7.0
 */

namespace DiviTorqueLite\Modules\ModalPopup\ModalPopupTrait;

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
        $block    = $registry ? $registry->get_registered('divitorque/modal-popup') : null;

        if ($block && isset($block->customCssFields) && is_array($block->customCssFields)) {
            return $block->customCssFields;
        }

        return [];
    }
}
