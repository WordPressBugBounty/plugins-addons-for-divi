<?php
/**
 * AccordionItem: Module classnames trait.
 *
 * @package DiviTorqueLite\Modules\AccordionItem
 * @since   4.7.0
 */

namespace DiviTorqueLite\Modules\AccordionItem\AccordionItemTrait;

if (!defined('ABSPATH')) {
    exit;
}

use ET\Builder\Packages\Module\Options\Text\TextClassnames;

trait ModuleClassnamesTrait
{
    /**
     * Add module classnames.
     *
     * Front end honours the Open By Default setting (the Visual Builder always
     * renders items open so content stays editable — see module-classnames.js).
     *
     * @param array $args Classnames args.
     *
     * @return void
     */
    public static function module_classnames($args)
    {
        $classnames_instance = $args['classnamesInstance'];
        $attrs               = $args['attrs'] ?? [];

        $classnames_instance->add('dtq-accordion__item');

        $keep_open       = ($attrs['module']['advanced']['keepOpen']['desktop']['value'] ?? 'off') === 'on';
        $open_by_default = ($attrs['module']['advanced']['openByDefault']['desktop']['value'] ?? 'off') === 'on';
        $classnames_instance->add('dtq-accordion__item--open', $open_by_default || $keep_open);
        $classnames_instance->add('dtq-accordion__item--locked', $keep_open);

        if (class_exists(TextClassnames::class)) {
            $classnames_instance->add(
                TextClassnames::text_options_classnames($attrs['module']['advanced']['text'] ?? [])
            );
        }
    }
}
