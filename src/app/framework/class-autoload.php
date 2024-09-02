<?php
/**
 * Autoload class used to load classes for the plugin.
 *
 * @package Surrealwebs\BasicComposer\Framework
 */

namespace Surrealwebs\BasicComposer\Framework;

/**
 * Class Autoload.
 */
class Autoload {

	/**
	 * Namespace to directory mapping.
	 *
	 * @var array $namespace_dir_map
	 */
	protected array $namespace_dir_map = [];

	/**
	 * Set up the autoloader.
	 */
	public function __construct() {
		spl_autoload_register( [ $this, 'autoload' ] );
	}

	/**
	 * Add mapping for a directory to a namespace.
	 *
	 * @param string $plugin_namespace Namespace.
	 * @param string $dir              Absolute path to the directory.
	 *
	 * @return void
	 */
	public function add( string $plugin_namespace, string $dir ): void {
		$this->namespace_dir_map[ trim( $plugin_namespace, '/\\' ) ] = rtrim( $dir, '/\\' );

		krsort( $this->namespace_dir_map ); // Ensure the sub-namespaces are matched first.
	}

	/**
	 * Autoload the registered classes.
	 *
	 * @param string $class_name Fully qualified class name.
	 */
	public function autoload( string $class_name ) {
		$paths = $this->resolve( $class_name );

		foreach ( $paths as $path ) {
			if ( is_readable( $path ) ) {
				require_once $path; // phpcs:ignore WordPressVIPMinimum.Files.IncludingFile.UsingVariable -- Reason: This is how files are autoloaded.

				return; // Return as soon as we've resolved the first one.
			}
		}
	}

	/**
	 * Resolve the requested classname to the possible file path.
	 *
	 * Attempts to resolve to a registered namespace by type (class, interface,
	 * trait).
	 *
	 * @param string $class_name Fully qualified class name.
	 *
	 * @return array List of mapped file paths.
	 */
	public function resolve( string $class_name ): array {
		// The types of classes we are loading.
		$prefixes = [ 'class', 'interface', 'trait' ];

		foreach ( $this->namespace_dir_map as $namespace => $path ) {
			// Append the trailing slash to not match Some_Class_Name where Some_Class is defined.
			if ( str_starts_with( $class_name, $namespace . '\\' ) ) {
				$class_name = substr( $class_name, strlen( $namespace ) + 1 );

				$file_path_template = $this->file_path_from_parts(
					[
						$path,
						$this->class_to_file_path_template( $class_name, '{prefix}' ),
					]
				);

				return array_map(
					function ( $prefix ) use ( $file_path_template ) {
						return str_replace( '{prefix}', $prefix, $file_path_template );
					},
					$prefixes
				);
			}
		}

		return [];
	}

	/**
	 * Map fully qualified class names to file path.
	 *
	 * The classes are mapped according to WP coding standard rules.
	 *
	 * @param string $full_class_name Fully qualified class name.
	 * @param string $placeholder     Placeholder string to use for the file
	 *                                type designation.
	 *
	 * @return string Path to class file.
	 */
	protected function class_to_file_path_template( string $full_class_name, string $placeholder = '{prefix}' ): string {
		$class_parts   = explode( '\\', $full_class_name );
		$instance_name = array_pop( $class_parts );

		// Map nested namespaces to sub-directories.
		if ( ! empty( $class_parts ) ) {
			$class_parts = array_map(
				[ $this, 'class_to_file_name' ],
				$class_parts
			);
		}

		// Add filename at the end.
		$class_parts[] = sprintf(
			'%s-%s.php',
			$placeholder,
			$this->class_to_file_name( $instance_name )
		);

		return $this->file_path_from_parts( $class_parts );
	}

	/**
	 * Generate file path based on components and the directory separator.
	 *
	 * @param array $parts File path parts.
	 *
	 * @return string Path to file based on passed in parts.
	 */
	protected function file_path_from_parts( array $parts ): string {
		return implode( DIRECTORY_SEPARATOR, $parts );
	}

	/**
	 * Sanitize class name to filename according to WP coding standards.
	 *
	 * @param string $class_name Class name.
	 *
	 * @return string Filename of the class.
	 */
	protected function class_to_file_name( string $class_name ): string {
		return strtolower( str_replace( '_', '-', $class_name ) );
	}
}
