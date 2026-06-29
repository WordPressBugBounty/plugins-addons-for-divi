<?php
/**
 * Table of Contents — Module classnames.
 *
 * @package divi-torque-lite
 */

namespace DiviTorqueLite\Modules\TableOfContents\TableOfContentsTrait;

use ET\Builder\Packages\Module\Options\Text\TextClassnames;

trait ModuleClassnamesTrait {

	public static function module_classnames( $args ) {
		$classnames_instance = $args['classnamesInstance'];
		$attrs               = $args['attrs'] ?? array();

		if ( class_exists( '\ET\Builder\Packages\Module\Options\Text\TextClassnames' ) ) {
			$classnames_instance->add(
				TextClassnames::text_options_classnames( $attrs['module']['advanced']['text'] ?? array() )
			);
		}
	}
}
