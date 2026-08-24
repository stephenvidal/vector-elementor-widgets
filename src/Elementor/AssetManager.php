<?php
/**
 * Asset manager.
 *
 * Registers all widget CSS/JS centrally, but enqueues only via Elementor's
 * per-widget `get_script_depends()` / `get_style_depends()` mechanisms, so
 * assets load only when the widget is actually on the page.
 *
 * @package Vector\ElementorWidgets\Elementor
 */

declare( strict_types=1 );

namespace Vector\ElementorWidgets\Elementor;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Asset manager — central registration + conditional loading.
 */
final class AssetManager {

	/**
	 * Plugin base URL.
	 *
	 * @var string
	 */
	private string $base_url;

	/**
	 * Plugin base directory.
	 *
	 * @var string
	 */
	private string $base_dir;

	/**
	 * Constructor.
	 *
	 * @param string $base_url Plugin base URL (VEW_PLUGIN_URL).
	 * @param string $base_dir Plugin base directory (VEW_PLUGIN_DIR).
	 */
	public function __construct( string $base_url, string $base_dir ) {
		$this->base_url = $base_url;
		$this->base_dir = $base_dir;
	}

	/**
	 * Register a widget stylesheet.
	 *
	 * @param string $handle Asset handle (e.g. 'vew-hero').
	 * @param string $rel    Path relative to the plugin root (e.g. 'widgets/Hero/hero.css').
	 *
	 * @return void
	 */
	public function register_style( string $handle, string $rel ): void {
		$file = $this->base_dir . $rel;
		$ver  = file_exists( $file ) ? (string) filemtime( $file ) : VEW_VERSION;
		wp_register_style( $handle, $this->base_url . $rel, array(), $ver );
	}

	/**
	 * Register a widget script.
	 *
	 * @param string   $handle Asset handle (e.g. 'vew-faq').
	 * @param string   $rel    Path relative to the plugin root (e.g. 'widgets/FaqAccordion/faq-accordion.js').
	 * @param string[] $deps   Script dependencies (e.g. array( 'jquery' )).
	 *
	 * @return void
	 */
	public function register_script( string $handle, string $rel, array $deps = array() ): void {
		$file = $this->base_dir . $rel;
		$ver  = file_exists( $file ) ? (string) filemtime( $file ) : VEW_VERSION;
		wp_register_script(
			$handle,
			$this->base_url . $rel,
			$deps,
			$ver,
			array(
				'in_footer' => true,
				'strategy'  => 'defer',
			)
		);
	}
}
