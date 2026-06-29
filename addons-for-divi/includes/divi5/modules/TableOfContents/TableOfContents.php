<?php
/**
 * Table of Contents D5 module — main class.
 *
 * Divi 5 only. Fully dynamic: the list is built on the frontend by scanning the
 * page content for headings.
 *
 * @package divi-torque-lite
 */

namespace DiviTorqueLite\Modules\TableOfContents;

use ET\Builder\Framework\DependencyManagement\Interfaces\DependencyInterface;

require_once __DIR__ . '/TableOfContentsTrait/RenderCallbackTrait.php';
require_once __DIR__ . '/TableOfContentsTrait/ModuleStylesTrait.php';
require_once __DIR__ . '/TableOfContentsTrait/ModuleClassnamesTrait.php';
require_once __DIR__ . '/TableOfContentsTrait/ModuleScriptDataTrait.php';
require_once __DIR__ . '/TableOfContentsTrait/CustomCssTrait.php';

class TableOfContents implements DependencyInterface {

	use TableOfContentsTrait\RenderCallbackTrait;
	use TableOfContentsTrait\ModuleStylesTrait;
	use TableOfContentsTrait\ModuleClassnamesTrait;
	use TableOfContentsTrait\ModuleScriptDataTrait;
	use TableOfContentsTrait\CustomCssTrait;

	/**
	 * Load the module.
	 */
	public function load() {
		$module_json_folder_path = DIVI_TORQUE_LITE_MODULES_JSON_PATH . 'table-of-contents/';

		// Inject anchor ids into content headings so the server-rendered
		// (no-JS / AMP) table of contents links resolve.
		add_filter( 'the_content', array( self::class, 'add_heading_ids' ), 99 );

		add_action(
			'init',
			function () use ( $module_json_folder_path ) {
				if ( ! class_exists( '\ET\Builder\Packages\ModuleLibrary\ModuleRegistration' ) ) {
					return;
				}

				\ET\Builder\Packages\ModuleLibrary\ModuleRegistration::register_module(
					$module_json_folder_path,
					array(
						'render_callback' => array( self::class, 'render_callback' ),
					)
				);
			}
		);
	}
}
