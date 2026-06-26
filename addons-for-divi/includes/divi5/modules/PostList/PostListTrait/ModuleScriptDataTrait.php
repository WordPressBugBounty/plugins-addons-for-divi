<?php
/**
 * Post List: module script data trait.
 *
 * @package DiviTorqueLite\Modules\PostList
 * @since   4.6.0
 */

namespace DiviTorqueLite\Modules\PostList\PostListTrait;

if (!defined('ABSPATH')) {
    exit;
}

trait ModuleScriptDataTrait
{
    public static function module_script_data($args)
    {
        $elements = $args['elements'] ?? null;
        if (!$elements) {
            return;
        }

        $elements->script_data(['attrName' => 'module']);
    }
}
