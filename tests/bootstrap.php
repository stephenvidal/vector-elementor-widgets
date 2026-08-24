<?php
/**
 * PHPUnit bootstrap for Vector Elementor Widgets.
 *
 * Loads Composer autoloading, then sets up brain/monkey so that WP
 * functions like `add_action`, `get_option`, `update_option`, etc. are
 * stubbed and the test suite can call them without a live WordPress.
 *
 * @package Vector\ElementorWidgets
 */

declare( strict_types=1 );

// Locate Composer autoload. Tests are run from the plugin root.
$autoload = __DIR__ . '/../vendor/autoload.php';
if ( ! file_exists( $autoload ) ) {
	fwrite( STDERR, "Composer autoloader not found at {$autoload}. Run `composer install` first.\n" );
	exit( 1 );
}
require_once $autoload;

// Set up brain/monkey stubs for WordPress functions used at load time.
if ( class_exists( '\\Brain\\Monkey' ) ) {
	\Brain\Monkey\setUp();
}

// Define a few constants the plugin code expects to read.
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/../fake-wp/' );
}
if ( ! defined( 'VEW_PLUGIN_FILE' ) ) {
	define( 'VEW_PLUGIN_FILE', __DIR__ . '/../vector-elementor-widgets.php' );
}
if ( ! defined( 'VEW_PLUGIN_DIR' ) ) {
	define( 'VEW_PLUGIN_DIR', __DIR__ . '/..' );
}
if ( ! defined( 'VEW_PLUGIN_URL' ) ) {
	define( 'VEW_PLUGIN_URL', 'http://example.test/wp-content/plugins/vector-elementor-widgets/' );
}
if ( ! defined( 'VEW_VERSION' ) ) {
	define( 'VEW_VERSION', '0.1.0-dev' );
}
if ( ! defined( 'VEW_MIN_PHP' ) ) {
	define( 'VEW_MIN_PHP', '8.1' );
}
if ( ! defined( 'VEW_MIN_WP' ) ) {
	define( 'VEW_MIN_WP', '6.5' );
}
if ( ! defined( 'VEW_MIN_ELEMENTOR' ) ) {
	define( 'VEW_MIN_ELEMENTOR', '3.18.0' );
}
if ( ! defined( 'VEW_CATEGORY_SLUG' ) ) {
	define( 'VEW_CATEGORY_SLUG', 'vector-widgets' );
}
if ( ! defined( 'VEW_OPTION_ENABLED_WIDGETS' ) ) {
	define( 'VEW_OPTION_ENABLED_WIDGETS', 'vew_enabled_widgets' );
}
if ( ! defined( 'ELEMENTOR_VERSION' ) ) {
	define( 'ELEMENTOR_VERSION', '4.1.4' );
}
