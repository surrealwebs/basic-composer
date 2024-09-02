<?php
/**
 * Utility class for the plugin.
 *
 * @package Surrealwebs\BasicComposer\Framework
 */

namespace Surrealwebs\BasicComposer\Framework;

use RuntimeException;
use function Surrealwebs\BasicComposer\get_plugin_instance;

/**
 * Class Utils
 */
class Utils {
	/**
	 * Get the instance of the plugin.
	 *
	 * @return Plugin
	 */
	public static function plugin(): Plugin {
		return get_plugin_instance();
	}

	/**
	 * Get the instance of the cache API.
	 *
	 * @return Cache
	 */
	public static function cache(): Cache {
		static $cache;

		if ( ! isset( $cache ) ) {
			/**
			 * WP core will use the persistent object cache if available.
			 *
			 * Replace this with a different cache handling object if caching
			 * with transients is not desired.
			 */
			$cache = new Cache_Transient();
		}

		return $cache;
	}

	/**
	 * Check whether a class inherits a specific type. If not, throw exception.
	 *
	 * This method does not check a specific type of inheritance, so it can be
	 * used to check if an interface is implemented or a class is extended.
	 *
	 * @uses esc_html() For escaping the exception message.
	 *
	 * @throws RuntimeException Throws exception if condition passes.
	 *
	 * @param string|object $class_being_checked Class being checked.
	 * @param string        $target_object_name  Interface to check against.
	 *
	 * @return void
	 */
	public static function throw_if_not_of_type( string|object $class_being_checked, string $target_object_name ): void {
		$exception_template = '%s must implement or extend %s';
		if ( is_object( $class_being_checked ) && ! ( $class_being_checked instanceof $target_object_name ) ) {
			throw new RuntimeException(
				\esc_html(
					sprintf(
						$exception_template,
						get_class( $class_being_checked ),
						$target_object_name
					)
				)
			);
		}

		if ( ! is_a( $class_being_checked, $target_object_name, true ) ) {
			throw new RuntimeException(
				\esc_html(
					sprintf(
						$exception_template,
						$class_being_checked,
						$target_object_name
					)
				)
			);
		}
	}

	/**
	 * Provides the results of a callable with caching.
	 *
	 * @param callable $callback A callback method.
	 * @param string   $key      Cache key.
	 * @param int      $timeout  The length of time in seconds after which to
	 *                           invalidate the cache.
	 *
	 * @return mixed Result of the callback.
	 */
	public static function with_cache( callable $callback, string $key, int $timeout = 0 ): mixed {
		$result = self::cache()->get( $key );

		if ( ! is_array( $result ) || ! isset( $result['data'] ) ) {
			$result = [
				'data' => call_user_func( $callback ),
			];

			if ( $result['data'] ) {
				self::cache()->set( $key, $result, $timeout );
			}
		}

		return $result['data'];
	}
}
