<?php
/**
 * PHPStan bootstrap.
 *
 * Defines the constants + the few globals that the plugin code uses which
 * the bundled szepeviktor/phpstan-wordpress stubs do not cover. Most
 * WordPress function stubs come from szepeviktor's extension — we only
 * add the Vector Elementor Widgets-specific constants and the few functions
 * that are part of our own contract.
 *
 * @package Vector\ElementorWidgets
 */

declare( strict_types=1 );

// Plugin constants (mirror of vector-elementor-widgets.php).
if ( ! defined( 'VEW_VERSION' ) ) {
	define( 'VEW_VERSION', '0.1.0-dev' );
}
if ( ! defined( 'VEW_PLUGIN_FILE' ) ) {
	define( 'VEW_PLUGIN_FILE', __DIR__ . '/vector-elementor-widgets.php' );
}
if ( ! defined( 'VEW_PLUGIN_DIR' ) ) {
	define( 'VEW_PLUGIN_DIR', __DIR__ );
}
if ( ! defined( 'VEW_PLUGIN_URL' ) ) {
	define( 'VEW_PLUGIN_URL', 'http://example.test/wp-content/plugins/vector-elementor-widgets/' );
}
if ( ! defined( 'VEW_PLUGIN_BASENAME' ) ) {
	define( 'VEW_PLUGIN_BASENAME', 'vector-elementor-widgets/vector-elementor-widgets.php' );
}
if ( ! defined( 'VEW_TEXT_DOMAIN' ) ) {
	define( 'VEW_TEXT_DOMAIN', 'vector-elementor-widgets' );
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

// Core WordPress constants PHPStan needs to know about.
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/fake-wp/' );
}
if ( ! defined( 'ELEMENTOR_VERSION' ) ) {
	define( 'ELEMENTOR_VERSION', '' );
}

// Vector Elementor Widgets plugin-level function. The main plugin file
// wraps this in an if ( ! function_exists ) guard; szepeviktor doesn't
// stub it because it's ours.
if ( ! function_exists( 'vew_plugin' ) ) {
	/**
	 * Return the plugin singleton.
	 *
	 * @return \Vector\ElementorWidgets\Plugin
	 */
	function vew_plugin(): \Vector\ElementorWidgets\Plugin {
		return \Vector\ElementorWidgets\Plugin::instance();
	}
}
