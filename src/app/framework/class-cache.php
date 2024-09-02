<?php
/**
 * Contract for the persistent cache APIs.
 *
 * @package Surrealwebs\BasicComposer\Framework
 */

namespace Surrealwebs\BasicComposer\Framework;

/**
 * Abstract Cache class.
 *
 * This is basically an interface, but with some default functionality.
 */
abstract class Cache {
	/**
	 * Store value in cache.
	 *
	 * @uses wp_cache_set()
	 *
	 * @param string $key    Cache key.
	 * @param mixed  $data   Cache value.
	 * @param int    $expire Max cache lifetime.
	 *
	 * @return bool If cache was stored. True on success, false on failure.
	 */
	public function set( string $key, mixed $data, int $expire = 0 ): bool {
		return \wp_cache_set(
			$key,
			$data,
			$this->group(),
			$this->sanitize_expire( $expire ) // phpcs:ignore WordPressVIPMinimum.Performance.LowExpiryCacheTime.CacheTimeUndetermined -- Reason: Cache time is determined by the caller.
		);
	}

	/**
	 * Get cache value by key.
	 *
	 * @uses wp_cache_get()
	 *
	 * @param string $key Cache key to look up.
	 *
	 * @return mixed The contents of the cache if any, otherwise false.
	 */
	public function get( string $key ): mixed {
		return \wp_cache_get( $key, $this->group() );
	}

	/**
	 * Delete cache value by key.
	 *
	 * @uses wp_cache_delete()
	 *
	 * @param string $key Cache key for the value to delete.
	 *
	 * @return bool True if cache value was deleted, otherwise false.
	 */
	public function delete( string $key ): bool {
		return \wp_cache_delete( $key, $this->group() );
	}

	/**
	 * Ensure the cache time-to-live is long enough
	 * for performance.
	 *
	 * @uses absint()
	 *
	 * @param int $expire Cache expiry in seconds.
	 *
	 * @return int Sanitized expiry time.
	 */
	protected function sanitize_expire( int $expire ): int {
		$expire = \absint( $expire );

		// Ensure we cache for 5 minutes or more (300 seconds).
		if ( $expire ) {
			return max( 300, $expire );
		}

		return $expire;
	}

	/**
	 * Get the shared cache group.
	 *
	 * @return string The group name for the cache.
	 */
	protected function group(): string {
		return __CLASS__;
	}
}
