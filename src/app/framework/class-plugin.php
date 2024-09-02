<?php
/**
 * Plugin base class used to build the plugin class.
 *
 * @package Surrealwebs\BasicComposer\Framework
 */

namespace Surrealwebs\BasicComposer\Framework;

use Surrealwebs\BasicComposer\Modules\Blocks;

/**
 * Class Plugin.
 */
class Plugin extends Plugin_Base {

	/**
	 * Initialize the plugin resources.
	 */
	public function init(): void {
		// Init every module.
		foreach ( $this->get_modules() as $module ) {
			$module->init();
		}
	}

	/**
	 * Register every module in this array.
	 *
	 * @return Module[]
	 */
	private function get_modules() {
		return array(
			new Module( Blocks::class ),
		);
	}
}
