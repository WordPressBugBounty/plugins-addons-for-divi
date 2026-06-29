<?php
/**
 * Table of Contents — Script data.
 *
 * @package divi-torque-lite
 */

namespace DiviTorqueLite\Modules\TableOfContents\TableOfContentsTrait;

trait ModuleScriptDataTrait {

	/**
	 * Provide script data for the module.
	 *
	 * @param array $args Script data arguments.
	 */
	public static function module_script_data( $args ) {
		$elements = $args['elements'];
		$elements->script_data( array( 'attrName' => 'module' ) );
	}
}
