<?php
/**
 * Plugin Base class used to build the plugin class.
 *
 * @package Surrealwebs\BasicComposer\Framework
 */

namespace Surrealwebs\BasicComposer\Framework;

use Exception;

/**
 * Class Plugin_Base
 */
abstract class Plugin_Base {
	/**
	 * Cache key used for storing the
	 * parsed plugin header meta.
	 *
	 * @var string
	 */
	const CACHE_KEY_PLUGIN_META = 'plugin-meta';

	/**
	 * Absolute path to the current plugin with the meta header.
	 *
	 * @var string
	 */
	protected string $file;

	/**
	 * Plugin slug.
	 *
	 * @var string
	 */
	public string $slug;

	/**
	 * Plugin directory path.
	 *
	 * @var string
	 */
	public string $dir_path;

	/**
	 * Plugin directory URL.
	 *
	 * @var string
	 */
	public string $dir_url;

	/**
	 * Set the environment type for toggling logging.
	 *
	 * Environment type defaults to production.
	 *
	 * @var string
	 */
	protected string $environment_type = 'production';

	/**
	 * Autoloader.
	 *
	 * @var Autoload
	 */
	protected Autoload $autoload;

	/**
	 * Plugin_Base constructor.
	 *
	 * @uses plugin_dir_url()
	 *
	 * @param string $file Absolute path to the main plugin file.
	 */
	public function __construct( string $file ) {
		$this->file     = $file;
		$this->dir_path = dirname( $file );
		$this->slug     = basename( $this->dir_path );
		$this->dir_url  = \plugin_dir_url( $file );
	}

	/**
	 * Version of plugin_dir_url() which works for plugins installed in the plugins directory,
	 * and for plugins bundled with themes.
	 *
	 * @return array Plugin details.
	 *
	 * @throws Exception If the plugin is not located in the expected location.
	 */
	public function locate_plugin(): array {
		return [
			'dir_url'      => $this->dir_url,
			'dir_path'     => $this->dir_path,
			'dir_basename' => $this->slug,
		];
	}

	/**
	 * Get the public URL to the asset file.
	 *
	 * @uses \untrailingslashit()
	 *
	 * @param string $path_relative Relative path to the plugin directory root.
	 *
	 * @return string The URL to the asset.
	 */
	public function url_to( string $path_relative ): string {
		return sprintf(
			'%s/%s',
			\untrailingslashit( $this->dir_url ),
			ltrim( $path_relative, '/\\' )
		);
	}

	/**
	 * Get the absolute path to the asset file.
	 *
	 * @param string $path_relative Relative path to the plugin directory root.
	 *
	 * @return string Absolute path to the file.
	 */
	public function path_to( string $path_relative ): string {
		return sprintf(
			'%s/%s',
			$this->dir_path,
			ltrim( $path_relative, '/\\' )
		);
	}

	/**
	 * Configure the current site environment type.
	 *
	 * @param string $environment_type Environment type such as local,
	 *                                 development, staging or production.
	 *
	 * @return void
	 */
	public function set_site_environment_type( string $environment_type ): void {
		$this->environment_type = $environment_type;
	}

	/**
	 * Call trigger_error() if not on production.
	 *
	 * @uses esc_html()
	 *
	 * @param string $message Warning message.
	 * @param int    $code    Warning code.
	 *
	 * @return void
	 */
	public function trigger_warning( string $message, int $code = \E_USER_WARNING ): void {
		if ( ! $this->is_production() && $this->is_debug() ) {
			trigger_error( // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_trigger_error -- Reason: this is meant trigger errors.
				\esc_html(
					sprintf(
						'%s: %s',
						get_class( $this ),
						$message
					)
				),
				$code
			);
		}
	}

	/**
	 * Return whether we're on production or not.
	 *
	 * @return bool True if on production, otherwise false.
	 */
	public function is_production(): bool {
		return ( 'production' === $this->environment_type );
	}

	/**
	 * Is WP debug mode enabled.
	 *
	 * @return bool True if DEBUG is enabled, otherwise false.
	 */
	public function is_debug(): bool {
		return ( defined( '\WP_DEBUG' ) && \WP_DEBUG );
	}

	/**
	 * Is WP script debug mode enabled.
	 *
	 * @return bool True if script debug is enabled, otherwise false.
	 */
	public function is_script_debug(): bool {
		return ( defined( '\SCRIPT_DEBUG' ) && \SCRIPT_DEBUG );
	}

	/**
	 * Return the current version of the plugin.
	 *
	 * @uses get_file_data()
	 *
	 * @return mixed The version of the plugin if determined, otherwise null.
	 */
	public function version(): mixed {
		$meta = Utils::with_cache(
			function () {
				return \get_file_data(
					$this->file,
					[
						'Version' => 'Version',
					]
				);
			},
			self::CACHE_KEY_PLUGIN_META
		);

		if ( isset( $meta['Version'] ) ) {
			return $meta['Version'];
		}

		return null;
	}

	/**
	 * Sync the plugin version with the asset version.
	 *
	 * @return string The asset version or the current time if debug enabled.
	 */
	public function asset_version(): string {
		if ( $this->is_debug() || $this->is_script_debug() ) {
			return time();
		}

		return $this->version();
	}
}
