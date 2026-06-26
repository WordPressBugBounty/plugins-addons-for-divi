<?php
/**
 * Twitter Carousel: module classnames trait.
 *
 * @package DiviTorqueLite\Modules\TwitterFeedCarousel
 * @since   4.6.0
 */

namespace DiviTorqueLite\Modules\TwitterFeedCarousel\TwitterFeedCarouselTrait;

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
