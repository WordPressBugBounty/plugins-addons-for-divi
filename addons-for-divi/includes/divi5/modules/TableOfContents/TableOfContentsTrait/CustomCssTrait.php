<?php
/**
 * Table of Contents — Custom CSS fields.
 *
 * @package divi-torque-lite
 */

namespace DiviTorqueLite\Modules\TableOfContents\TableOfContentsTrait;

trait CustomCssTrait {

	/**
	 * Get custom CSS fields from the registered block type metadata.
	 *
	 * @return array
	 */
	public static function custom_css_fields() {
		$module_metadata = \WP_Block_Type_Registry::get_instance()
			->get_registered( 'divitorque/table-of-contents' );

		return $module_metadata->customCssFields ?? array();
	}
}
