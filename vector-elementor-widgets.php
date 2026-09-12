<?php
/**
 * Plugin Name:       Vector Elementor Widgets
 * Plugin URI:        https://vector.example/vector-elementor-widgets
 * Description:       A reusable production UI component library for Elementor — the Derby-standard component system as native Elementor widgets.
 * Version:           0.6.5
 * Requires at least: 6.5
 * Requires PHP:      8.1
 * Author:            Stephen Vidal
 * Author URI:        https://vector.example
 * License:           GPL-2.0-or-later
 * License URI:       https://spdx.org/licenses/GPL-2.0-or-later.html
 * Text Domain:       vector-elementor-widgets
 * Domain Path:       /languages
 *
 * @package Vector\ElementorWidgets
 */

declare( strict_types=1 );

// Exit if accessed directly. WordPress standard guard.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Hard PHP minimum — must run BEFORE the Composer autoloader (and any
// src/ file) so users on an unsupported PHP get a friendly admin notice
// instead of a white screen. This block uses only syntax available in
// PHP 7.x so it parses on every version. (Requires PHP: 8.1 in the header.)
if ( version_compare( PHP_VERSION, '8.1', '<' ) ) {
	add_action(
		'admin_notices',
		static function (): void {
			echo '<div class="notice notice-error"><p>';
			echo esc_html(
				sprintf(
					/* translators: 1: required PHP version, 2: actual PHP version */
					__( 'Vector Elementor Widgets requires PHP %1$s or higher. The site is running PHP %2$s. The plugin has been disabled.', 'vector-elementor-widgets' ),
					'8.1',
					PHP_VERSION
				)
			);
			echo '</p></div>';
		}
	);
	return;
}

// Load Composer autoloading. In production the plugin will be installed
// with vendor/ present. In development the autoloader is only required if
// a workflow installed it (e.g. for IDE static analysis), so we make it
// optional.
$vew_autoload = __DIR__ . '/vendor/autoload.php';
if ( file_exists( $vew_autoload ) ) {
	require_once $vew_autoload;
}

// Plugin constants. Defined here (not in src/) so they are present even
// when the autoloader is not installed (e.g. WP admin screen activation
// before vendor/ is present on first install).
if ( ! defined( 'VEW_VERSION' ) ) {
	define( 'VEW_VERSION', '0.6.5' );
}
if ( ! defined( 'VEW_PLUGIN_FILE' ) ) {
	define( 'VEW_PLUGIN_FILE', __FILE__ );
}
if ( ! defined( 'VEW_PLUGIN_DIR' ) ) {
	define( 'VEW_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
}
if ( ! defined( 'VEW_PLUGIN_URL' ) ) {
	define( 'VEW_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
}
if ( ! defined( 'VEW_PLUGIN_BASENAME' ) ) {
	define( 'VEW_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
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

/**
 * Return the kernel singleton.
 *
 * @return \Vector\ElementorWidgets\Plugin
 */
function vew_plugin(): \Vector\ElementorWidgets\Plugin {
	return \Vector\ElementorWidgets\Plugin::instance();
}

// Activation / deactivation hooks. Phase 1 has no activation work; the
// shims exist so the plugin can be activated without error and later
// phases can attach real work here.
// @phpstan-ignore-next-line
register_activation_hook(
	__FILE__,
	static function (): void {
		$vew_autoload = __DIR__ . '/vendor/autoload.php';
		if ( ! file_exists( $vew_autoload ) ) {
			return;
		}
		require_once $vew_autoload;
	}
);

// @phpstan-ignore-next-line
register_deactivation_hook(
	__FILE__,
	static function (): void {
		$vew_autoload = __DIR__ . '/vendor/autoload.php';
		if ( ! file_exists( $vew_autoload ) ) {
			return;
		}
		require_once $vew_autoload;
	}
);

// Boot the plugin after WordPress is ready.
// @phpstan-ignore-next-line
add_action(
	'plugins_loaded',
	static function (): void {
		vew_plugin()->boot();
	}
);

// Load translations on plugins_loaded priority 1 (earlier than admin_menu).
// @phpstan-ignore-next-line
add_action(
	'plugins_loaded',
	static function (): void {
		// @phpstan-ignore-next-line
		load_plugin_textdomain(
			'vector-elementor-widgets',
			false,
			dirname( plugin_basename( __FILE__ ) ) . '/languages'
		);
	},
	1
);
