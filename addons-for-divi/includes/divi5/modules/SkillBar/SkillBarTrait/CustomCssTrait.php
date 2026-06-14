<?php
/**
 * SkillBar: Custom CSS trait.
 *
 * @package DiviTorqueLite\Modules\SkillBar
 * @since   4.4.0
 */

namespace DiviTorqueLite\Modules\SkillBar\SkillBarTrait;

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
        $block    = $registry ? $registry->get_registered('divitorque/skill-bar') : null;

        if ($block && isset($block->customCssFields) && is_array($block->customCssFields)) {
            return $block->customCssFields;
        }

        return [];
    }
}
