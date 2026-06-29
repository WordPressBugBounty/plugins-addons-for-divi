<?php
/**
 * PostCarousel — Custom CSS fields.
 *
 * @package divi-torque-lite
 */

namespace DiviTorqueLite\Modules\PostCarousel\PostCarouselTrait;

trait CustomCssTrait {

	/**
	 * Get custom CSS fields from the registered block type metadata.
	 *
	 * @return array
	 */
	public static function custom_css_fields() {
		$module_metadata = \WP_Block_Type_Registry::get_instance()
			->get_registered( 'dbp/post-carousel' );

		return $module_metadata->customCssFields ?? array();
	}
}
