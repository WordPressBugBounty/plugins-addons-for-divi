<?php
/**
 * Breadcrumbs: Module classnames trait.
 *
 * @package DiviTorqueLite\Modules\Breadcrumbs
 * @since   4.7.0
 */

namespace DiviTorqueLite\Modules\Breadcrumbs\BreadcrumbsTrait;

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
