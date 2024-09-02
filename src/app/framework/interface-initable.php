<?php
/**
 * Contract to add an init method.
 *
 * @package Surrealwebs\BasicComposer\Framework
 */

namespace Surrealwebs\BasicComposer\Framework;

/**
 * Interface Initable.
 */
interface Initable {
	/**
	 * Init the feature.
	 *
	 * @return void
	 */
	public function init(): void;
}
