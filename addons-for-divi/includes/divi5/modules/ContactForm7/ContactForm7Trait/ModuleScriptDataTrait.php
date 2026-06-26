<?php
/**
 * Contact Form 7: module script data trait.
 *
 * @package DiviTorqueLite\Modules\ContactForm7
 * @since   4.6.0
 */

namespace DiviTorqueLite\Modules\ContactForm7\ContactForm7Trait;

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
