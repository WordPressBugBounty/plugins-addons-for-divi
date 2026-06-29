<?php
/**
 * TabItem: Module classnames trait.
 *
 * @package DiviTorqueLite\Modules\TabItem
 * @since   4.7.0
 */

namespace DiviTorqueLite\Modules\TabItem\TabItemTrait;

if (!defined('ABSPATH')) {
    exit;
}

use ET\Builder\Packages\Module\Options\Text\TextClassnames;

trait ModuleClassnamesTrait
{
    /**
     * Add module classnames. Every child is a tab panel.
     *
     * @param array $args Classnames args.
     *
     * @return void
     */
    public static function module_classnames($args)
    {
        $classnames_instance = $args['classnamesInstance'];
        $attrs               = $args['attrs'] ?? [];

        $classnames_instance->add('dtq-tabs__panel');

        if (class_exists(TextClassnames::class)) {
            $classnames_instance->add(
                TextClassnames::text_options_classnames($attrs['module']['advanced']['text'] ?? [])
            );
        }
    }
}
