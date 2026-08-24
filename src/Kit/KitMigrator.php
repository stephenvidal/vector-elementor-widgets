<?php
/**
 * Kit migrator.
 *
 * Seeds site kits from the legacy `assets/kits/*.css` files into the kit
 * CPT so the design tokens become data (editable in the admin). Runs once on
 * plugin activation / the first time the manager is visited; idempotent —
 * a kit is only created if a kit with that slug does not already exist.
 *
 * @package Vector\ElementorWidgets\Kit
 */

declare( strict_types=1 );

namespace Vector\ElementorWidgets\Kit;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Seeds kits from legacy CSS files.
 */
final class KitMigrator {

	/**
	 * Option that records that migration ran.
	 */
	public const OPTION = 'vew_site_kit_migrated';

	/**
	 * Kit store.
	 *
	 * @var KitStore
	 */
	private KitStore $store;

	/**
	 * Plugin kits directory.
	 *
	 * @var string
	 */
	private string $kits_dir;

	/**
	 * Optional file-listing callable (glob seam for tests).
	 *
	 * @var callable|null
	 */
	private $glob_cb;

	/**
	 * Optional file-reader callable (file_get_contents seam for tests).
	 *
	 * @var callable|null
	 */
	private $fetch_cb;

	/**
	 * Constructor.
	 *
	 * @param KitStore      $store    Kit store.
	 * @param string        $kits_dir Absolute path to assets/kits/.
	 * @param callable|null $glob_cb  Optional file-lister seam.
	 * @param callable|null $fetch_cb Optional file-reader seam.
	 */
	public function __construct( KitStore $store, string $kits_dir, ?callable $glob_cb = null, ?callable $fetch_cb = null ) {
		$this->store    = $store;
		$this->kits_dir = rtrim( $kits_dir, '/' ) . '/';
		$this->glob_cb  = $glob_cb;
		$this->fetch_cb = $fetch_cb;
	}

	/**
	 * Register the migration hook (runs on admin_init).
	 *
	 * @return void
	 */
	public function register_hooks(): void {
		add_action(
			'admin_init',
			function (): void {
				$this->maybe_migrate();
			}
		);
	}

	/**
	 * Run the migration once, unless already done.
	 *
	 * @return void
	 */
	public function maybe_migrate(): void {
		if ( get_option( self::OPTION, false ) ) {
			return;
		}
		$this->migrate();
		update_option( self::OPTION, 1 );
	}

	/**
	 * Seed a kit post for every legacy CSS kit that does not already exist.
	 *
	 * @return int Number of kits seeded.
	 */
	public function migrate(): int {
		$seeded = 0;

		foreach ( $this->css_files() as $slug => $path ) {
			if ( $this->store->has( $slug ) ) {
				continue;
			}
			$kit = $this->kit_from_css( $slug, $path );
			if ( null !== $kit ) {
				$this->store->save( $kit );
				++$seeded;
			}
		}

		return $seeded;
	}

	/**
	 * List the kit CSS files as slug => absolute path.
	 *
	 * @return array<string, string>
	 */
	private function css_files(): array {
		$files = $this->glob_cb
			? (array) ( $this->glob_cb )( $this->kits_dir . '*.css' )
			: glob( $this->kits_dir . '*.css' );

		$out = array();
		if ( false === $files ) {
			return $out;
		}
		foreach ( $files as $file ) {
			$base         = basename( $file, '.css' );
			$out[ $base ] = $file;
		}
		ksort( $out );
		return $out;
	}

	/**
	 * Build a Kit from a legacy CSS file by extracting its :root tokens.
	 *
	 * @param string $slug Kit slug.
	 * @param string $file Absolute CSS path.
	 *
	 * @return Kit|null
	 */
	private function kit_from_css( string $slug, string $file ): ?Kit {
		$contents = $this->fetch_cb
			? (string) ( $this->fetch_cb )( $file )
			: (string) file_get_contents( $file );
		if ( '' === $contents ) {
			return null;
		}

		$tokens = $this->parse_tokens( $contents );
		if ( ! isset( $tokens['brand'] ) ) {
			return null;
		}

		$fonts = array();
		foreach ( array( 'display', 'sans' ) as $font_key ) {
			if ( isset( $tokens[ $font_key ] ) ) {
				$fonts[ $font_key ] = $tokens[ $font_key ];
				unset( $tokens[ $font_key ] );
			}
		}

		$layout = array();
		foreach ( array( 'section-space-y', 'section-space-y-mobile' ) as $layout_key ) {
			$css_key = str_replace( '-', '_', $layout_key );
			if ( isset( $tokens[ $layout_key ] ) ) {
				$layout[ $css_key ] = $tokens[ $layout_key ];
				unset( $tokens[ $layout_key ] );
			}
		}

		return Kit::from_array(
			array(
				'slug'        => $slug,
				'label'       => ucwords( str_replace( '-', ' ', $slug ) ),
				'description' => sprintf(
					// translators: %s: legacy CSS filename.
					__( 'Migrated from %s', 'vector-elementor-widgets' ),
					basename( $file )
				),
				'tokens'      => $tokens,
				'fonts'       => $fonts,
				'layout'      => $layout,
			)
		);
	}

	/**
	 * Parse `--key: value;` declarations from the CSS :root block.
	 *
	 * @param string $css Raw CSS.
	 *
	 * @return array<string, string>
	 */
	private function parse_tokens( string $css ): array {
		$tokens = array();
		if ( ! preg_match_all( '/--([a-z0-9-]+)\s*:\s*([^;]+);/i', $css, $matches, PREG_SET_ORDER ) ) {
			return $tokens;
		}
		foreach ( $matches as $match ) {
			$tokens[ $match[1] ] = trim( $match[2] );
		}
		return $tokens;
	}
}
