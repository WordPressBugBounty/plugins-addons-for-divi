<?php
/**
 * PostCarousel D5 module — main class.
 *
 * @package divi-torque-lite
 */

namespace DiviTorqueLite\Modules\PostCarousel;

use ET\Builder\Framework\DependencyManagement\Interfaces\DependencyInterface;

require_once __DIR__ . '/PostCarouselTrait/PostElementsTrait.php';
require_once __DIR__ . '/PostCarouselTrait/PostLayoutsTrait.php';
require_once __DIR__ . '/PostCarouselTrait/RenderCallbackTrait.php';
require_once __DIR__ . '/PostCarouselTrait/ModuleStylesTrait.php';
require_once __DIR__ . '/PostCarouselTrait/ModuleClassnamesTrait.php';
require_once __DIR__ . '/PostCarouselTrait/ModuleScriptDataTrait.php';
require_once __DIR__ . '/PostCarouselTrait/CustomCssTrait.php';

class PostCarousel implements DependencyInterface {

	use PostCarouselTrait\PostElementsTrait;
	use PostCarouselTrait\PostLayoutsTrait;
	use PostCarouselTrait\RenderCallbackTrait;
	use PostCarouselTrait\ModuleStylesTrait;
	use PostCarouselTrait\ModuleClassnamesTrait;
	use PostCarouselTrait\ModuleScriptDataTrait;
	use PostCarouselTrait\CustomCssTrait;

	/**
	 * Load the module.
	 */
	public function load() {
		$module_json_folder_path = DIVI_TORQUE_LITE_MODULES_JSON_PATH . 'post-carousel/';

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
