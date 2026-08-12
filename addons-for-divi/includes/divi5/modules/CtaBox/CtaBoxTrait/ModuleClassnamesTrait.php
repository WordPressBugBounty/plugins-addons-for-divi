<?php
/**
 * CtaBox: Module classnames trait.
 *
 * @package DiviTorqueLite\Modules\CtaBox
 * @since   4.9.0
 */

namespace DiviTorqueLite\Modules\CtaBox\CtaBoxTrait;

if (!defined('ABSPATH')) {
    exit;
}

use ET\Builder\Packages\Module\Options\Text\TextClassnames;

trait ModuleClassnamesTrait
{
    /**
     * Add module classnames.
     *
     * @param array $args Classnames args.
     *
     * @return void
     */
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
