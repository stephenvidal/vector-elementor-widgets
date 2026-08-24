<?php
/**
 * Widget Manager page tests.
 *
 * @package Vector\ElementorWidgets\Tests\Unit\Admin
 */

declare( strict_types=1 );

namespace Vector\ElementorWidgets\Tests\Unit\Admin;

use Brain\Monkey\Functions;
use Vector\ElementorWidgets\Admin\WidgetManagerPage;
use Vector\ElementorWidgets\Elementor\WidgetRegistry;
use Vector\ElementorWidgets\Tests\Unit\TestCase;

final class WidgetManagerPageTest extends TestCase {

	/**
	 * Set up WP function stubs.
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
		Functions\when( 'wp_create_nonce' )->alias( static fn ( string $action ): string => 'nonce-' . $action );
		Functions\when( 'wp_verify_nonce' )->alias( static fn ( string $nonce, string $action ): bool => 'nonce-' . $action === $nonce );
		Functions\when( 'current_user_can' )->alias( static fn ( string $cap ): bool => true );
		Functions\when( 'sanitize_text_field' )->alias( static fn ( string $s ): string => $s );
		Functions\when( 'wp_unslash' )->alias( static fn ( $v ) => $v );
		Functions\when( 'update_option' )->alias( static fn ( string $option, $value ): bool => true );
	}

	/**
	 * Build a page with capturing seams for the WP API calls.
	 *
	 * The die seam throws a sentinel exception so execution halts exactly as
	 * `wp_die()` does in production (it exits). Tests that expect a die
	 * catch the exception and assert on the recorded message.
	 *
	 * The redirect/die captures live on a mutable holder object (not plain
	 * arrays) so the closures' writes are visible after handle_save() runs —
	 * returning arrays by value would hand back empty copies.
	 *
	 * @param array<string, mixed> $post $_POST simulation.
	 *
	 * @return array{page: WidgetManagerPage, holder: \stdClass}
	 */
	private function page_with_seams( array $post ): array {
		$holder = new \stdClass();
		$holder->redirects = array();
		$holder->dies      = array();

		$page = new WidgetManagerPage(
			new WidgetRegistry(),
			null,
			static fn ( string $path ): string => 'http://example.test/wp-admin/' . ltrim( $path, '/' ),
			static function ( string $url ) use ( $holder ): void {
				$holder->redirects[] = $url;
			},
			static function ( string $msg ) use ( $holder ): void {
				$holder->dies[] = $msg;
				throw new \RuntimeException( 'vew-die:' . $msg );
			}
		);

		// Simulate $_POST.
		$_POST = $post;

		return array(
			'page'   => $page,
			'holder' => $holder,
		);
	}

	/**
	 * A valid save persists the enabled classes and redirects.
	 *
	 * @return void
	 */
	public function test_valid_save_persists_and_redirects(): void {
		$captured = array();
		Functions\when( 'update_option' )->alias(
			static function ( string $option, $value ) use ( &$captured ): bool {
				$captured[ $option ] = $value;
				return true;
			}
		);

		$result = $this->page_with_seams( array(
			'_vew_widget_manager_nonce' => 'nonce-vew_save_widget_manager',
			'vew_widgets'               => array( 'vew-hero' ),
		) );

		$result['page']->handle_save();

		$this->assertSame( array( 'Vector\\ElementorWidgets\\Elementor\\Widget\\Hero' ), $captured[ VEW_OPTION_ENABLED_WIDGETS ] );
		$this->assertCount( 1, $result['holder']->redirects );
		$this->assertStringContainsString( 'vew_saved=1', $result['holder']->redirects[0] );
		$this->assertCount( 0, $result['holder']->dies );
	}

	/**
	 * Unknown widget classes are filtered out before saving.
	 *
	 * @return void
	 */
	public function test_unknown_classes_are_filtered(): void {
		$captured = array();
		Functions\when( 'update_option' )->alias(
			static function ( string $option, $value ) use ( &$captured ): bool {
				$captured[ $option ] = $value;
				return true;
			}
		);

		$result = $this->page_with_seams( array(
			'_vew_widget_manager_nonce' => 'nonce-vew_save_widget_manager',
			'vew_widgets'               => array(
				'vew-hero',
				'no-such-widget',
			),
		) );

		$result['page']->handle_save();

		$this->assertSame( array( 'Vector\\ElementorWidgets\\Elementor\\Widget\\Hero' ), $captured[ VEW_OPTION_ENABLED_WIDGETS ] );
	}

	/**
	 * An invalid nonce dies without saving.
	 *
	 * @return void
	 */
	public function test_invalid_nonce_dies(): void {
		$saved = false;
		Functions\when( 'update_option' )->alias(
			static function () use ( &$saved ): bool {
				$saved = true;
				return true;
			}
		);

		$result = $this->page_with_seams( array(
			'_vew_widget_manager_nonce' => 'wrong-nonce',
			'vew_widgets'               => array( 'vew-hero' ),
		) );

		try {
			$result['page']->handle_save();
		} catch ( \RuntimeException $e ) {
			// Expected — the die seam halts execution.
		}

		$this->assertFalse( $saved );
		$this->assertCount( 1, $result['holder']->dies );
		$this->assertCount( 0, $result['holder']->redirects );
	}

	/**
	 * A missing nonce dies without saving.
	 *
	 * @return void
	 */
	public function test_missing_nonce_dies(): void {
		$saved = false;
		Functions\when( 'update_option' )->alias(
			static function () use ( &$saved ): bool {
				$saved = true;
				return true;
			}
		);

		$result = $this->page_with_seams( array(
			'vew_widgets' => array( 'vew-hero' ),
		) );

		try {
			$result['page']->handle_save();
		} catch ( \RuntimeException $e ) {
			// Expected — the die seam halts execution.
		}

		$this->assertFalse( $saved );
		$this->assertCount( 1, $result['holder']->dies );
	}

	/**
	 * register_hooks wires the menu + admin_post actions.
	 *
	 * @return void
	 */
	public function test_register_hooks_wires_actions(): void {
		$added = array();
		Functions\when( 'add_action' )->alias(
			static function ( string $hook, $cb, int $priority = 10, int $accepted = 1 ) use ( &$added ): void {
				$added[] = $hook;
			}
		);

		$page = new WidgetManagerPage( new WidgetRegistry() );
		$page->register_hooks();

		$this->assertContains( 'admin_menu', $added );
		$this->assertContains( 'admin_post_vew_save_widget_manager', $added );
	}
}
