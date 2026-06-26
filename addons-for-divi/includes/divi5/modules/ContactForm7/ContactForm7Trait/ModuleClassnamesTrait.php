<?php
/**
 * Contact Form 7: module classnames trait.
 *
 * @package DiviTorqueLite\Modules\ContactForm7
 * @since   4.6.0
 */

namespace DiviTorqueLite\Modules\ContactForm7\ContactForm7Trait;

if (!defined('ABSPATH')) {
    exit;
}

use ET\Builder\Packages\Module\Options\Text\TextClassnames;

trait ModuleClassnamesTrait
{
    public static function module_classnames($args)
    {
        $classnames_instance = $args['classnamesInstance'];
        $attrs               = $args['attrs'] ?? [];

        if (class_exists(TextClassnames::class)) {
            $classnames_instance->add(
                TextClassnames::text_options_classnames($attrs['module']['advanced']['text'] ?? [])
            );
        }
    }
}
