<?php
/**
 * ImageAccordionItem: Module classnames trait.
 *
 * @package DiviTorqueLite\Modules\ImageAccordionItem
 * @since   4.9.0
 */

namespace DiviTorqueLite\Modules\ImageAccordionItem\ImageAccordionItemTrait;

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

        // The child's own order-class div IS the panel — same approach as
        // accordion-item — so the panel classes go here rather than on an extra
        // wrapper inside it. PHP twin of module-classnames.js.
        $classnames_instance->add('dtq-image-accordion__panel');

        if (($attrs['module']['advanced']['activeByDefault']['desktop']['value'] ?? 'off') === 'on') {
            $classnames_instance->add('is-active');
        }
    }
}
