<?php
/**
 * FaqItem: Module classnames trait. Each item is an accordion item so it shares
 * the Accordion collapse CSS + frontend.js handler.
 *
 * @package DiviTorqueLite\Modules\FaqItem
 * @since   4.7.0
 */

namespace DiviTorqueLite\Modules\FaqItem\FaqItemTrait;

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

        $classnames_instance->add('dtq-accordion__item');

        if (class_exists(TextClassnames::class)) {
            $classnames_instance->add(
                TextClassnames::text_options_classnames($attrs['module']['advanced']['text'] ?? [])
            );
        }
    }
}
