<?php
/**
 * Breadcrumbs: Module script data trait.
 *
 * @package DiviTorqueLite\Modules\Breadcrumbs
 * @since   4.7.0
 */

namespace DiviTorqueLite\Modules\Breadcrumbs\BreadcrumbsTrait;

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
