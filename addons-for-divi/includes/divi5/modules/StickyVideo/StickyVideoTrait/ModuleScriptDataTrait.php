<?php
/**
 * StickyVideo: Module script data trait.
 *
 * @package DiviTorqueLite\Modules\StickyVideo
 * @since   4.9.0
 */

namespace DiviTorqueLite\Modules\StickyVideo\StickyVideoTrait;

if (!defined('ABSPATH')) {
    exit;
}

trait ModuleScriptDataTrait
{
    /**
     * Output script data for the module.
     *
     * @param array $args Script data args.
     *
     * @return void
     */
    public static function module_script_data($args)
    {
        $elements = $args['elements'] ?? null;
        if (!$elements) {
            return;
        }

        $elements->script_data(
            [
                'attrName' => 'module',
            ]
        );
    }
}
