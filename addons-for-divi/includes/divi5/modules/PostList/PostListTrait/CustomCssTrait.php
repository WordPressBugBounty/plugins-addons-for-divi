<?php
/**
 * Post List: custom CSS trait.
 *
 * @package DiviTorqueLite\Modules\PostList
 * @since   4.6.0
 */

namespace DiviTorqueLite\Modules\PostList\PostListTrait;

if (!defined('ABSPATH')) {
    exit;
}

trait CustomCssTrait
{
    public static function custom_css_fields()
    {
        $registry = \WP_Block_Type_Registry::get_instance();
        $block    = $registry ? $registry->get_registered('divitorque/post-list') : null;

        if ($block && isset($block->customCssFields) && is_array($block->customCssFields)) {
            return $block->customCssFields;
        }

        return [];
    }
}
