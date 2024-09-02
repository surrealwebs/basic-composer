<?php
/**
 * Plugin Name: Basic Composer
 * Plugin URI:https://github.com/surrealwebs/basic-composer
 * Description: Plugin built to demonstrate Composer in a WordPress Plugin.
 * Version: 1.0.0
 * Author:  Adam Richards <surrealwebs@gmail.com>
 * Author URI: https://adamhasawebsite.com/
 * License: GPL-3.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain: basic-composer
 * Domain Path: /languages
 *
 * @package Surrealwebs\BasicComposer
 */

namespace Surrealwebs\BasicComposer;

use Surrealwebs\BasicComposer\Framework\Autoload;
use Surrealwebs\BasicComposer\Framework\Plugin;

require_once __DIR__ . '/src/app/framework/class-autoload.php';

$autoload = new Autoload();
$autoload->add( __NAMESPACE__, sprintf( '%s/src/app', __DIR__ ) );

/**
 * Get an instance of the plugin object. Instantiate it if it doesn't exist.
 *
 * @return Plugin
 */
function get_plugin_instance(): Plugin {
	static $basic_composer_plugin;

	if ( is_null( $basic_composer_plugin ) ) {
		$basic_composer_plugin = new Plugin( __FILE__ );
		$basic_composer_plugin->init();
	}

	return $basic_composer_plugin;
}

// Start the plugin.
\add_action(
	'after_setup_theme',
	__NAMESPACE__ . '\\get_plugin_instance',
	PHP_INT_MAX
);
