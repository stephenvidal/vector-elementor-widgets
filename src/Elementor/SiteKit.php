<?php
/**
 * Site kit.
 *
 * Resolves the design-token kit for the current request (page meta → global
 * option → built-in default) and enqueues it as inline CSS on `wp_enqueue_scripts`.
 * Every Vector widget resolves its `var(--token, fallback)` against these
 * `:root` variables, so this one file re-themes the whole site.
 *
 * Resolution precedence:
 *   1. The current post's `_vew_site_kit` meta (per-page override).
 *   2. The plugin-wide `vew_global_site_kit` option (global theme).
 *   3. The built-in default kit (`assets/site-kit.css`).
 *
 * @package Vector\ElementorWidgets\Elementor
 */

declare( strict_types=1 );

namespace Vector\ElementorWidgets\Elementor;

use Vector\ElementorWidgets\Kit\Kit;
use Vector\ElementorWidgets\Kit\KitCssCompiler;
use Vector\ElementorWidgets\Kit\KitStore;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Site kit — resolves + enqueues the design-token CSS.
 */
final class SiteKit {

	/**
	 * Post meta key for per-page kit assignment.
	 */
	public const META_KEY = '_vew_site_kit';

	/**
	 * Option key for the global site kit.
	 */
	public const GLOBAL_OPTION = 'vew_global_site_kit';

	/**
	 * Handle for the enqueued stylesheet + inline style.
	 */
	public const HANDLE = 'vew-site-kit';

	/**
	 * Kit store.
	 *
	 * @var KitStore
	 */
	private KitStore $store;

	/**
	 * CSS compiler.
	 *
	 * @var KitCssCompiler
	 */
	private KitCssCompiler $compiler;

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
	 * @param KitStore       $store    Kit store.
	 * @param KitCssCompiler $compiler CSS compiler.
	 * @param string         $base_url Plugin base URL (VEW_PLUGIN_URL).
	 * @param string         $base_dir Plugin base directory (VEW_PLUGIN_DIR).
	 */
	public function __construct( KitStore $store, KitCssCompiler $compiler, string $base_url, string $base_dir ) {
		$this->store    = $store;
		$this->compiler = $compiler;
		$this->base_url = $base_url;
		$this->base_dir = $base_dir;
	}

	/**
	 * Register hooks.
	 *
	 * @return void
	 */
	public function register_hooks(): void {
		// Late priority so the kit's body/theme rules load after the theme's
		// reset.css (which sets body background to white) and win the cascade.
		add_action( 'wp_enqueue_scripts', $this->enqueue( ... ), 999 );
	}

	/**
	 * Enqueue the site-kit stylesheet + inline compiled tokens.
	 *
	 * @return void
	 */
	public function enqueue(): void {
		// Accessibility baseline — always loaded, independent of which kit
		// path resolves, so reduced-motion + focus-visible apply on every page.
		$this->enqueue_accessibility();

		$kit = $this->resolve_kit();

		if ( null !== $kit ) {
			// Register an empty handle first: wp_add_inline_style() only prints
			// for a *registered* style handle, so an unregistered handle silently
			// drops the tokens.
			wp_register_style( self::HANDLE, false, array(), VEW_VERSION );
			wp_enqueue_style( self::HANDLE );
			wp_add_inline_style( self::HANDLE, $this->compiler->compile( $kit ) );
			return;
		}

		// Fallback: no DB kit — enqueue the legacy file kit.
		$file = $this->base_dir . 'assets/site-kit.css';
		if ( file_exists( $file ) ) {
			wp_register_style(
				self::HANDLE,
				$this->base_url . 'assets/site-kit.css',
				array(),
				(string) filemtime( $file )
			);
			wp_enqueue_style( self::HANDLE );
		}
	}

	/**
	 * Resolve the kit for the current request.
	 *
	 * Precedence: page meta → global option → built-in default.
	 *
	 * @return Kit|null Resolved kit, or null if none is available.
	 */
	public function resolve_kit(): ?Kit {
		$post_id = (int) get_the_ID();
		if ( $post_id > 0 ) {
			$slug = (string) get_post_meta( $post_id, self::META_KEY, true );
			$kit  = $this->store->get( $slug );
			if ( null !== $kit ) {
				return $kit;
			}
		}

		$global = (string) get_option( self::GLOBAL_OPTION, '' );
		if ( '' !== $global ) {
			$kit = $this->store->get( $global );
			if ( null !== $kit ) {
				return $kit;
			}
		}

		return null;
	}

	/**
	 * Enqueue the accessibility baseline stylesheet.
	 *
	 * Loads the reduced-motion + focus-visible rules globally, on every page,
	 * regardless of whether the kit came from the DB or the legacy file.
	 *
	 * @return void
	 */
	private function enqueue_accessibility(): void {
		$file = $this->base_dir . 'assets/a11y.css';
		if ( file_exists( $file ) ) {
			wp_register_style(
				'vew-a11y',
				$this->base_url . 'assets/a11y.css',
				array(),
				(string) filemtime( $file )
			);
			wp_enqueue_style( 'vew-a11y' );
		}
	}
}
