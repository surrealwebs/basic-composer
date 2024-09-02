<?php
/**
 * Gutenberg Blocks.
 *
 * Notes:
 * - Register all the blocks living in the folder 'blocks'.
 * - Verify if a block exists on the current page/post,
 *   and if it doesn't exist, dequeue/deregister the assets from that block.
 *
 * @package Surrealwebs\BasicComposer\Modules
 */

namespace Surrealwebs\BasicComposer\Modules;

use DateTime;
use DateTimeZone;
use Exception;
use RuntimeException;
use Surrealwebs\BasicComposer\Framework\Initable;
use Surrealwebs\BasicComposer\Framework\Utils;

/**
 * Class Blocks
 */
class Blocks implements Initable {
	/**
	 * Block patterns.
	 *
	 * @var array
	 */
	private array $patterns;

	/**
	 * Block patterns categories.
	 *
	 * @var array
	 */
	private array $patterns_categories;

	/**
	 * A mapping of all blocks with dynamic PHP templates.
	 *
	 * @var array
	 */
	private array $blocks_with_template = [];

	/**
	 * Constructor.
	 *
	 * @uses __() To translate the text.
	 */
	public function __construct() {
		// Sample Pattern.
		$this->patterns = [
			'accordion-pattern-001' => [
				'title'         => __( 'Accordion pattern 001', 'basic-composer' ),
				'keywords'      => [ 'surrealwebs-composer-blocks', 'accordion', '001' ],
				'categories'    => [ 'surrealwebs-composer-blocks-patterns' ],
				'viewportWidth' => 1116,
			],
		];

		// Sample Pattern Category.
		$this->patterns_categories = [
			'surrealwebs-composer-blocks-patterns' => [ 'label' => __( 'Surrealwebs Blocks', 'basic-composer' ) ],
		];
	}

	/**
	 * Register any needed hooks/filters.
	 *
	 * @uses add_action() To register action callbacks.
	 * @uses add_filter() To register filter callbacks.
	 *
	 * @return void
	 */
	public function init(): void {
		\add_action( 'init', [ $this, 'action_register_blocks' ] );
		\add_action( 'init', [ $this, 'action_register_patterns' ] );
		\add_action( 'init', [ $this, 'action_register_postmeta' ] );
		\add_action( 'wp_enqueue_scripts', [ $this, 'action_enqueue_block_styles' ], 20 );
		\add_action( 'admin_init', [ $this, 'action_enqueue_block_styles' ] );
		\add_action( 'wp_enqueue_scripts', [ $this, 'action_enqueue_scripts' ] );
		\add_action( 'enqueue_block_editor_assets', [ $this, 'action_enqueue_blocks_scripts' ] );
		\add_filter( 'wp_kses_allowed_html', [ $this, 'extend_kses_post_with_schema_tags' ] );
		\add_action( 'wp_enqueue_scripts', [ $this, 'load_wp_block_library_css' ], 100 );
		\add_filter( 'block_type_metadata', [ $this, 'update_block_version' ], 10, 1 );

		if ( version_compare( $GLOBALS['wp_version'], '5.8-alpha-1', '<' ) ) {
			\add_filter( 'block_categories', [ $this, 'action_add_block_category' ], 10, 2 );
		} else {
			\add_filter( 'block_categories_all', [ $this, 'action_add_block_category' ], 10, 2 );
		}
	}

	/**
	 * Update the block version to use the editor asset file version.
	 *
	 * @uses wp_normalize_path() To normalize the path.
	 *
	 * @param array $metadata The metadata.
	 *
	 * @return array The updated metadata.
	 */
	public function update_block_version( $metadata ): array {

		if ( ! str_contains( $metadata['name'], 'surrealwebs' ) ) {
			return $metadata;
		}

		// This is only for frontend styles.
		$file           = $metadata['file'] ?? false;
		$style_filename = $metadata['style'] ?? false;

		if ( empty( $file ) || empty( $style_filename ) ) {
			return $metadata;
		}

		preg_match( '/(file:style-)(.*)(\.css)/', $style_filename, $matches );
		$asset_file = ! empty( $matches ) ? "{$matches[2]}.asset.php" : false;

		if ( empty( $asset_file ) ) {
			return $metadata;
		}

		$dir             = dirname( $file );
		$asset_file_path = \wp_normalize_path(
			realpath( "$dir/{$asset_file}" )
		);

		$assets = ( ! empty( $asset_file_path ) ) ? include $asset_file_path : false;
		if ( is_array( $assets ) && ! empty( $assets['version'] ) ) {
			$metadata['version'] = $assets['version'];
		}

		return $metadata;
	}

	/**
	 * Load default block styles.
	 *
	 * @uses wp_enqueue_style() To enqueue the default block styles.
	 *
	 * @return void
	 */
	public function load_wp_block_library_css(): void {
		\wp_enqueue_style( 'wp-block-library' );
	}

	/**
	 * Register block patterns.
	 *
	 * @uses register_block_pattern_category() To register block pattern categories.
	 * @uses register_block_pattern() To register block patterns.
	 * @uses load_template() To load the template file.
	 *
	 * @return void
	 */
	public function action_register_patterns(): void {
		foreach ( $this->patterns_categories as $category_slug => $category ) {
			\register_block_pattern_category(
				$category_slug,
				$category
			);
		}

		foreach ( $this->patterns as $pattern_slug => $pattern ) {
			$template = Utils::plugin()->path_to( 'src/template-parts/patterns/' . $pattern_slug . '.php' );

			if ( ! file_exists( $template ) ) {
				continue;
			}

			ob_start();
			\load_template( $template, false );
			$content = ob_get_clean();

			\register_block_pattern(
				'surrealwebs-blocks/' . $pattern_slug,
				[
					'title'         => $pattern['title'],
					'content'       => $content,
					'categories'    => ! empty( $pattern['categories'] ) ? $pattern['categories'] : [],
					'keywords'      => ! empty( $pattern['title'] ) ? $pattern['title'] : [],
					'viewportWidth' => ! empty( $pattern['viewportWidth'] ) ? $pattern['viewportWidth'] : 1200,
				]
			);
		}
	}

	/**
	 * Register post meta for the blocks.
	 *
	 * @return void
	 */
	public function action_register_postmeta(): void {
		// No-op. Register meta for blocks here.
	}

	/**
	 * Attach extra styles to core blocks on-demand.
	 *
	 * @uses wp_enqueue_style() To enqueue the block styles.
	 * @uses add_editor_style() To add the editor styles.
	 *
	 * @return void
	 */
	public function action_enqueue_block_styles(): void {
		$css_uri = Utils::plugin()->url_to( 'build/assets/main.css' );
		$css_ver = filemtime( Utils::plugin()->path_to( 'build/assets/main.css' ) );

		// Add frontend styles.
		wp_enqueue_style( 'surrealwebs-composer-block-styles', $css_uri, [], $css_ver );
		// Add editor styles.
		add_editor_style( $css_uri );
	}

	/**
	 * Registers or enqueues scripts.
	 *
	 * @uses wp_enqueue_script() To enqueue the script.
	 *
	 * @return void
	 *
	 * @throws RuntimeException Throws if dist assets aren't built.
	 */
	public function action_enqueue_scripts(): void {
		if ( ! file_exists( Utils::plugin()->path_to( 'build/assets/main.asset.php' ) ) ) {
			throw new RuntimeException( 'Built JavaScript assets not found. Please run `npm run build`' );
		}

		$main_deps = require Utils::plugin()->path_to( 'build/assets/main.asset.php' ); // phpcs:ignore WordPressVIPMinimum.Files.IncludingFile.UsingVariable -- Reason: This is how we are loading main js dependencies.

		\wp_enqueue_script(
			'surrealwebs-composer-blocks-frontend-main',
			Utils::plugin()->url_to( 'build/assets/main.js' ),
			$main_deps['dependencies'],
			$main_deps['version'],
			true
		);
	}

	/**
	 * Registers or enqueues blocks scripts.
	 *
	 * @uses wp_enqueue_script() To enqueue the scripts.
	 * @uses wp_localize_script() To localize the script.
	 * @uses get_option() To load values from settings.
	 * @uses wp_create_nonce() To create a nonce.
	 *
	 * @throws RuntimeException|Exception Throws if problem loading.
	 */
	public function action_enqueue_blocks_scripts(): void {
		global $pagenow;

		// Disable editor scripts on widget page to avoid warnings.
		if ( 'widgets.php' === $pagenow ) {
			return;
		}

		if ( ! file_exists( Utils::plugin()->path_to( 'build/assets/editor.asset.php' ) ) ) {
			throw new RuntimeException( 'Built JavaScript assets not found. Please run `npm run build`' );
		}

		$editor_deps = require Utils::plugin()->path_to( 'build/assets/editor.asset.php' ); // phpcs:ignore WordPressVIPMinimum.Files.IncludingFile.UsingVariable -- Reason: This is how we are loading editor dependencies.

		\wp_enqueue_script(
			'surrealwebs-composer-blocks-editor-js',
			Utils::plugin()->url_to( 'build/assets/editor.js' ),
			$editor_deps['dependencies'],
			$editor_deps['version'],
			true
		);

		// Calculate the timezone abbr (EDT, PST) if possible.
		$timezone_string = \get_option( 'timezone_string', 'UTC' );
		$timezone_abbr   = '';

		if ( ! empty( $timezone_string ) ) {
			$timezone_date = new DateTime( 'now', new DateTimeZone( $timezone_string ) );
			$timezone_abbr = $timezone_date->format( 'T' );
		}

		\wp_localize_script(
			'surrealwebs-composer-blocks-editor-js',
			'SurrealwebsComposerBlockEditor',
			[
				// TODO:: remove this one after WordPress upgraded to 6.1, These configs will be available through @wordpress/date/getSettings.
				'dateTimeConfig' => [
					'formats'  => array(
						/* translators: Time format, see https://www.php.net/manual/datetime.format.php */
						'time'                => \get_option( 'time_format', 'g:i a' ),
						/* translators: Date format, see https://www.php.net/manual/datetime.format.php */
						'date'                => \get_option( 'date_format', 'F j, Y' ),
						/* translators: Date/Time format, see https://www.php.net/manual/datetime.format.php */
						'datetime'            => 'F j, Y g:i a',
						/* translators: Abbreviated date/time format, see https://www.php.net/manual/datetime.format.php */
						'datetimeAbbreviated' => 'M j, Y g:i a',
					),
					'timezone' => array(
						'offset' => \get_option( 'gmt_offset', 0 ),
						'string' => $timezone_string,
						'abbr'   => $timezone_abbr,
					),
				],
				'restNonce'      => \wp_create_nonce( 'wp_rest' ),
			]
		);
	}

	/**
	 * Adding a new (custom) block category.
	 *
	 * @uses __() To translate the text.
	 *
	 * @param array                    $block_categories     Array of categories for block types.
	 * @param \WP_Block_Editor_Context $block_editor_context The current block editor context.
	 *
	 * @return array The updated list of block categories.
	 */
	public function action_add_block_category( array $block_categories, \WP_Block_Editor_Context $block_editor_context ): array {
		return array_merge(
			$block_categories,
			array(
				array(
					'slug'  => 'surrealwebs-composer-blocks',
					'title' => __( 'Surrealwebs Basic Composer blocks', 'basic-composer' ),
				),
			)
		);
	}

	/**
	 * Register all blocks living in the "/blocks/" folder in the theme.
	 *
	 * @uses register_block_type() To register a block type.
	 *
	 * @return void
	 */
	public function action_register_blocks(): void {
		$folders = glob( Utils::plugin()->path_to( 'build/blocks/*' ), GLOB_ONLYDIR );

		foreach ( $folders as $folder ) {
			$block_json_file     = sprintf( '%s/block.json', $folder );
			$block_template_file = Utils::plugin()->path_to( sprintf( 'src/blocks/%s/template.php', basename( $folder ) ) );

			if ( file_exists( $block_json_file ) ) {
				$args = [];

				if ( file_exists( $block_template_file ) ) {
					$args['render_callback'] = [ $this, 'render' ];
				}

				$type = \register_block_type( $block_json_file, $args );

				if ( ! empty( $type->name ) ) {
					$this->blocks_with_template[ $type->name ] = $block_template_file;
				}
			}
		}
	}

	/**
	 * Get the absolute file path to a block template file by its name.
	 *
	 * @param string $name Block ID.
	 *
	 * @return string|null Block template file path if exists otherwise null.
	 */
	public function get_block_template_file( $name ): string|null {
		if ( isset( $this->blocks_with_template[ $name ] ) ) {
			return $this->blocks_with_template[ $name ];
		}

		return null;
	}

	/**
	 * Render Dynamic blocks.
	 *
	 * @uses load_template() To load the template file.
	 *
	 * @param array  $attributes Block attributes.
	 * @param string $content    Block content.
	 * @param object $block      Block object.
	 *
	 * @return false|string The rendered block content if exists otherwise false.
	 */
	public function render( $attributes, $content, $block ): bool|string {
		$template = $this->get_block_template_file( $block->name );

		if ( empty( $template ) || ! file_exists( $template ) ) {
			return false;
		}

		ob_start();

		\load_template(
			$template,
			false,
			[
				'attributes' => $attributes,
				'content'    => $content,
				'block'      => $block,
			]
		);

		return ob_get_clean();
	}

	/**
	 * Extends the allowed post tags with Schema related attributes.
	 *
	 * @param array $allowed_post_tags The allowed post tags.
	 *
	 * @return array The allowed tags including post tags, input tags and select tags.
	 */
	public static function extend_kses_post_with_schema_tags( $allowed_post_tags ): array {
		static $schema_tags;

		if ( isset( $schema_tags ) === false ) {
			$schema_tags = [
				'div'  => [
					'itemscope' => true,
					'itemtype'  => true,
					'itemprop'  => true,
				],
				'p'    => [
					'style'    => true,
					'hidden'   => true,
					'itemprop' => true,
				],
				'link' => [
					'href'     => true,
					'itemprop' => true,
				],
				'img'  => [
					'itemprop' => true,
				],
				'meta' => [
					'itemprop' => true,
					'content'  => true,
				],
				'h2'   => [
					'itemprop' => true,
				],
				'h3'   => [
					'itemprop' => true,
				],
				'h4'   => [
					'itemprop' => true,
				],
				'h5'   => [
					'itemprop' => true,
				],
				'h6'   => [
					'itemprop' => true,
				],
			];

			// Add the global allowed attributes to each html element.
			$schema_tags = array_map( '_wp_add_global_attributes', $schema_tags );
		}

		return array_merge_recursive( $allowed_post_tags, $schema_tags );
	}
}
