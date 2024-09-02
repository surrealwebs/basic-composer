<?php
/**
 * Proxy class to easily check which module should be active.
 *
 * @package Surrealwebs\BasicComposer\Framework
 */

namespace Surrealwebs\BasicComposer\Framework;

/**
 * Module class.
 */
final class Module {
	/**
	 * Class name of the module being proxied.
	 *
	 * @var string
	 */
	private string $module_class;

	/**
	 * Module constructor.
	 *
	 * @param string $module_class The class being proxied as a Module. Must
	 *                             implement the Initable interface.
	 */
	public function __construct( string $module_class ) {
		Utils::throw_if_not_of_type( $module_class, Initable::class );

		$this->module_class = $module_class;
	}

	/**
	 * Inits the module after it passes validation.
	 *
	 * @return void
	 */
	public function init(): void {
		$instance = new $this->module_class();
		$instance->init();
	}
}
