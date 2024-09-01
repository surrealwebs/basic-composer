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
 */

namespace Surrealwebs\BasicComposer;

if ( file_exists( __DIR__ . '/vendor/autoload.php' ) ) {
	require_once __DIR__ . '/vendor/autoload.php';
} else {
	die( 'Please run composer install in the plugin directory.' );
}
