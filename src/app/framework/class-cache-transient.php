<?php
/**
 * Object cache API using transients.
 *
 * WP core will use the persistent object cache if available instead of writing
 * to the database. This is mainly for consideration of VIP hosting which does
 * not store transients in the database.
 *
 * @package Surrealwebs\BasicComposer\Framework
 */

namespace Surrealwebs\BasicComposer\Framework;

/**
 * Cache Transient class.
 */
class Cache_Transient extends Cache {
	/**
	 * Store cache value.
	 *
	 * @uses set_site_transient()
	 *
	 * @param string $key    Cache key.
	 * @param mixed  $data   Cache value.
	 * @param int    $expire Max cache lifetime.
	 *
	 * @return bool True if cache was stored otherwise false.
	 */
	public function set( string $key, mixed $data, int $expire = 0 ): bool {
		return \set_site_transient(
			$this->key( $key ),
			$data,
			$this->sanitize_expire( $expire )
		);
	}

	/**
	 * Get cache value by key.
	 *
	 * @uses get_site_transient()
	 *
	 * @param string $key Cache key to look up.
	 *
	 * @return mixed The cached value if any, otherwise false.
	 */
	public function get( string $key ): mixed {
		return \get_site_transient( $this->key( $key ) );
	}

	/**
	 * Delete cache value by key.
	 *
	 * @uses delete_site_transient()
	 *
	 * @param string $key Cache key for the value to delete.
	 *
	 * @return bool True if cache value was deleted, otherwise false.
	 */
	public function delete( string $key ): bool {
		return \delete_site_transient( $this->key( $key ) );
	}

	/**
	 * Cache key with group to avoid conflicts with other plugins.
	 *
	 * @param string $key Cache key to format.
	 *
	 * @return string The cache key with the appended group.
	 */
	protected function key( string $key ): string {
		$key_with_group = sprintf(
			'%s--%s__%s',
			$this->group(),
			substr( $key, 0, 20 ), // Add part of it for debugging purposes.
			md5( $key )
		);

		return substr( $key_with_group, 0, 172 );
	}
}
