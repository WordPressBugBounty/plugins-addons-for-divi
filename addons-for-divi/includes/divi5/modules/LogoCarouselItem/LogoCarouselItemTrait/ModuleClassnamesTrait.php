<?php
/**
 * LogoCarouselItem: Module classnames trait.
 *
 * @package DiviTorqueLite\Modules\LogoCarouselItem
 * @since   4.5.0
 */

namespace DiviTorqueLite\Modules\LogoCarouselItem\LogoCarouselItemTrait;

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

        // Each slide must be a `.swiper-slide` (direct child of `.swiper-wrapper`).
        $classnames_instance->add('swiper-slide', true);

        if (class_exists(TextClassnames::class)) {
            $classnames_instance->add(
                TextClassnames::text_options_classnames($attrs['module']['advanced']['text'] ?? [])
            );
        }
    }
}
