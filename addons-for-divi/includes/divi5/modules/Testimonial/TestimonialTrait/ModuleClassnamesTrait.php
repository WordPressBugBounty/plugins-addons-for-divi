<?php
/**
 * Testimonial: Module classnames trait.
 *
 * @package DiviTorqueLite\Modules\Testimonial
 * @since   4.4.0
 */

namespace DiviTorqueLite\Modules\Testimonial\TestimonialTrait;

if (!defined('ABSPATH')) {
    exit;
}

use ET\Builder\Packages\Module\Options\Text\TextClassnames;

trait ModuleClassnamesTrait
{
    /**
     * Add module classnames.
     *
     * The D4 layout-matrix classes (dtq-align-{alignment},
     * img-pos-{imgPosition}) live on the module's own inner markup — exactly
     * where D4 emitted them (see RenderCallbackTrait) — so only the standard
     * text-option classnames are added to the module wrapper here. Keep in
     * lockstep with src/divi5/modules/testimonial/module-classnames.js.
     *
     * @param array $args Classnames args.
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
