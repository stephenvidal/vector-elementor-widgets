<?php
/**
 * Site Kit Manager view tests.
 *
 * @package Vector\ElementorWidgets\Tests\Unit\Admin
 */

declare( strict_types=1 );

namespace Vector\ElementorWidgets\Tests\Unit\Admin;

use Brain\Monkey\Functions;
use Vector\ElementorWidgets\Admin\SiteKitManagerView;
use Vector\ElementorWidgets\Kit\Kit;
use Vector\ElementorWidgets\Tests\Unit\TestCase;

/**
 * View emitter contract.
 */
final class SiteKitManagerViewTest extends TestCase {

	/**
	 * Stub WP functions used by the view.
	 *
	 * @return void
	 */
	protected function setUp(): void {
		parent::setUp();

		Functions\when( '__' )->alias( static fn ( string $s, $d = '' ) => (string) $s );
		Functions\when( 'esc_html__' )->alias( static fn ( string $s, $d = '' ) => (string) $s );
		Functions\when( 'esc_html' )->alias( static fn ( string $s ): string => htmlspecialchars( $s, ENT_QUOTES, 'UTF-8' ) );
		Functions\when( 'esc_attr' )->alias( static fn ( string $s ): string => htmlspecialchars( $s, ENT_QUOTES, 'UTF-8' ) );
		Functions\when( 'esc_url' )->alias( static fn ( string $s ): string => $s );
		Functions\when( 'esc_textarea' )->alias( static fn ( string $s ): string => htmlspecialchars( $s, ENT_QUOTES, 'UTF-8' ) );
		Functions\when( 'esc_js' )->alias( static fn ( string $s ): string => $s );
		Functions\when( 'admin_url' )->alias( static fn ( string $p ): string => 'http://example.test/wp-admin/' . ltrim( $p, '/' ) );
		Functions\when( 'add_query_arg' )->alias(
			static function ( array $args, string $url ): string {
				return $url . ( str_contains( $url, '?' ) ? '&' : '?' ) . http_build_query( $args );
			}
		);
		Functions\when( 'wp_nonce_field' )->alias( static fn ( string $a, string $n, bool $ref = true, bool $echo = true ): string => '<input type="hidden" name="' . $n . '" />' );
		Functions\when( 'sanitize_key' )->returnArg();
		Functions\when( 'sanitize_text_field' )->returnArg();
	}

	/**
	 * Build a kit for the view.
	 *
	 * @param string $slug Slug.
	 *
	 * @return Kit
	 */
	private function make_kit( string $slug ): Kit {
		return Kit::from_array(
			array(
				'slug'        => $slug,
				'label'       => ucfirst( $slug ),
				'tokens'      => array( 'brand' => '#1c628f', 'accent' => '#c9a24b' ),
				'fonts'       => array( 'sans' => 'Inter, sans-serif' ),
				'layout'      => array( 'gutter' => 24 ),
			)
		);
	}

	/**
	 * render includes the kit list and the create form.
	 *
	 * @return void
	 */
	public function test_render_includes_list_and_form(): void {
		$html = SiteKitManagerView::render(
			array( 'gslg' => $this->make_kit( 'gslg' ) ),
			'',
			'nonce',
			'vew_site_kit_manager',
			null,
			'http://example.test/wp-admin/admin.php?page=vew-site-kits'
		);

		$this->assertStringContainsString( 'Site Kits', $html );
		$this->assertStringContainsString( 'gslg', $html );
		$this->assertStringContainsString( 'Create a new site kit', $html );
		$this->assertStringContainsString( 'vew_kit[tokens][brand]', $html );
		$this->assertStringContainsString( 'vew_kit[fonts][sans]', $html );
		$this->assertStringContainsString( 'vew_kit[layout][gutter]', $html );
	}

	/**
	 * The global kit selector reflects the currently selected global slug.
	 *
	 * @return void
	 */
	public function test_render_marks_global_kit_selected(): void {
		$html = SiteKitManagerView::render(
			array( 'gslg' => $this->make_kit( 'gslg' ), 'rore' => $this->make_kit( 'rore' ) ),
			'gslg',
			'nonce',
			'vew_site_kit_manager'
		);

		$this->assertStringContainsString( 'value="gslg" selected="selected"', $html );
	}

	/**
	 * Editing mode renders the edit heading and pre-filled values.
	 *
	 * @return void
	 */
	public function test_render_edit_mode_prefills(): void {
		$html = SiteKitManagerView::render(
			array(),
			'',
			'nonce',
			'vew_site_kit_manager',
			$this->make_kit( 'acme' ),
			'http://example.test/wp-admin/admin.php?page=vew-site-kits'
		);

		$this->assertStringContainsString( 'Edit kit:', $html );
		$this->assertStringContainsString( 'value="Acme"', $html );
		$this->assertStringContainsString( 'value="#1c628f"', $html );
	}
}
