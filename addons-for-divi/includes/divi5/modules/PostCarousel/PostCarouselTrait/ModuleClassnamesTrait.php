<?php
/**
 * PostCarousel — Dynamic classnames.
 *
 * @package divi-torque-lite
 */

namespace DiviTorqueLite\Modules\PostCarousel\PostCarouselTrait;

use ET\Builder\Packages\Module\Options\Text\TextClassnames;

trait ModuleClassnamesTrait {

	/**
	 * Add dynamic classnames to the module wrapper.
	 *
	 * @param array $args Classname arguments.
	 */
	public static function module_classnames( $args ) {
		$classnames_instance = $args['classnamesInstance'];
		$attrs               = $args['attrs'];

		if ( class_exists( '\ET\Builder\Packages\Module\Options\Text\TextClassnames' ) ) {
			$classnames_instance->add(
				TextClassnames::text_options_classnames( $attrs['module']['advanced']['text'] ?? array() )
			);
		}
	}
}
