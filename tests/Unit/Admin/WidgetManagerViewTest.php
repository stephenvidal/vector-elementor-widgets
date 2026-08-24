<?php
/**
 * Widget Manager view tests.
 *
 * @package Vector\ElementorWidgets\Tests\Unit\Admin
 */

declare( strict_types=1 );

namespace Vector\ElementorWidgets\Tests\Unit\Admin;

use Brain\Monkey\Functions;
use Vector\ElementorWidgets\Admin\WidgetManagerView;
use Vector\ElementorWidgets\Tests\Unit\TestCase;

final class WidgetManagerViewTest extends TestCase {

	/**
	 * A minimal widget catalog fixture.
	 *
	 * @return array<int, array{slug: string, class: string, title: string, description: string, icon: string}>
	 */
	private function widgets(): array {
		return array(
			array(
				'slug'        => 'vew-hero',
				'class'       => 'Vector\\ElementorWidgets\\Elementor\\Widget\\Hero',
				'title'       => 'Hero',
				'description' => 'A hero section.',
				'icon'        => 'eicon-header',
			),
			array(
				'slug'        => 'vew-faq',
				'class'       => 'Vector\\ElementorWidgets\\Elementor\\Widget\\Faq',
				'title'       => 'FAQ',
				'description' => 'An accordion.',
				'icon'        => 'eicon-help',
			),
		);
	}

	/**
	 * Set up WP function stubs. esc_html/esc_attr strip tags (real WP
	 * behavior) so the XSS guards are exercised.
	 *
	 * @return void
	 */
	protected function setUp(): void {
		parent::setUp();

		Functions\when( 'esc_html' )->alias( static fn ( string $s ): string => htmlspecialchars( $s, ENT_QUOTES, 'UTF-8' ) );
		Functions\when( 'esc_attr' )->alias( static fn ( string $s ): string => htmlspecialchars( $s, ENT_QUOTES, 'UTF-8' ) );
		Functions\when( 'esc_url' )->alias( static fn ( string $s ): string => $s );
		Functions\when( 'esc_html__' )->alias( static fn ( string $s, $d = '' ) => htmlspecialchars( (string) $s, ENT_QUOTES, 'UTF-8' ) );
		Functions\when( '__' )->alias( static fn ( string $s, $d = '' ) => (string) $s );
		Functions\when( 'admin_url' )->alias( static fn ( string $path = '' ): string => 'http://example.test/wp-admin/' . ltrim( $path, '/' ) );
		Functions\when( 'wp_nonce_field' )->alias( static fn ( string $action, string $name, bool $referer = true, bool $echo = true ): string => '<input type="hidden" name="' . $name . '" value="nonce-' . $action . '" />' );
	}

	/**
	 * Parse HTML into a DOMDocument.
	 *
	 * @param string $html HTML.
	 *
	 * @return \DOMDocument
	 */
	private function dom( string $html ): \DOMDocument {
		$prev = libxml_use_internal_errors( true );
		$dom  = new \DOMDocument();
		$dom->loadHTML( '<?xml encoding="UTF-8">' . $html );
		libxml_clear_errors();
		libxml_use_internal_errors( $prev );
		return $dom;
	}

	/**
	 * The form posts to admin-post.php.
	 *
	 * @return void
	 */
	public function test_form_posts_to_admin_post(): void {
		$html = WidgetManagerView::render( $this->widgets(), array(), 'nonce', 'vew_save_widget_manager' );
		$dom  = $this->dom( $html );

		$form = $dom->getElementsByTagName( 'form' )->item( 0 );
		$this->assertNotNull( $form );
		$this->assertStringContainsString( 'admin-post.php', $form->getAttribute( 'action' ) );
	}

	/**
	 * The form carries the hidden action + nonce fields.
	 *
	 * @return void
	 */
	public function test_form_has_action_and_nonce(): void {
		$html = WidgetManagerView::render( $this->widgets(), array(), 'nonce', 'vew_save_widget_manager' );
		$dom  = $this->dom( $html );

		$hidden = $dom->getElementsByTagName( 'input' );
		$found_action = false;
		$found_nonce  = false;
		foreach ( $hidden as $input ) {
			$name = $input->getAttribute( 'name' );
			if ( 'vew_widget_manager_action' === $name ) {
				$found_action = true;
				$this->assertSame( 'vew_save_widget_manager', $input->getAttribute( 'value' ) );
			}
			if ( '_vew_widget_manager_nonce' === $name ) {
				$found_nonce = true;
			}
		}
		$this->assertTrue( $found_action );
		$this->assertTrue( $found_nonce );
	}

	/**
	 * A checkbox is rendered per widget.
	 *
	 * @return void
	 */
	public function test_renders_one_checkbox_per_widget(): void {
		$html = WidgetManagerView::render( $this->widgets(), array(), 'nonce', 'vew_save_widget_manager' );
		$dom  = $this->dom( $html );

		$checkboxes = $dom->getElementsByTagName( 'input' );
		$count = 0;
		foreach ( $checkboxes as $input ) {
			if ( 'checkbox' === $input->getAttribute( 'type' ) ) {
				$count++;
			}
		}
		$this->assertSame( 2, $count );
	}

	/**
	 * Enabled widgets render checked; disabled render unchecked.
	 *
	 * @return void
	 */
	public function test_enabled_widgets_are_checked(): void {
		$enabled = array( 'vew-hero' );
		$html    = WidgetManagerView::render( $this->widgets(), $enabled, 'nonce', 'vew_save_widget_manager' );
		$dom     = $this->dom( $html );

		$checkboxes = $dom->getElementsByTagName( 'input' );
		$hero_checked = null;
		$faq_checked  = null;
		foreach ( $checkboxes as $input ) {
			if ( 'checkbox' !== $input->getAttribute( 'type' ) ) {
				continue;
			}
			$value = $input->getAttribute( 'value' );
			if ( 'vew-hero' === $value ) {
				$hero_checked = $input->hasAttribute( 'checked' );
			}
			if ( 'vew-faq' === $value ) {
				$faq_checked = $input->hasAttribute( 'checked' );
			}
		}
		$this->assertTrue( $hero_checked );
		$this->assertFalse( $faq_checked );
	}

	/**
	 * The widget title is escaped to prevent XSS.
	 *
	 * @return void
	 */
	public function test_title_is_escaped_to_prevent_xss(): void {
		$widgets = $this->widgets();
		$widgets[0]['title'] = '<script>alert(1)</script>Hero';
		$html = WidgetManagerView::render( $widgets, array(), 'nonce', 'vew_save_widget_manager' );
		$dom  = $this->dom( $html );

		$this->assertSame( 0, $dom->getElementsByTagName( 'script' )->length );
	}

	/**
	 * The description is escaped to prevent XSS.
	 *
	 * @return void
	 */
	public function test_description_is_escaped_to_prevent_xss(): void {
		$widgets = $this->widgets();
		$widgets[0]['description'] = '<img src=x onerror=alert(1)>desc';
		$html = WidgetManagerView::render( $widgets, array(), 'nonce', 'vew_save_widget_manager' );
		$dom  = $this->dom( $html );

		$this->assertSame( 0, $dom->getElementsByTagName( 'img' )->length );
	}

	/**
	 * The slug is escaped.
	 *
	 * @return void
	 */
	public function test_slug_is_escaped(): void {
		$widgets = $this->widgets();
		$widgets[0]['slug'] = 'vew-<b>hero</b>';
		$html = WidgetManagerView::render( $widgets, array(), 'nonce', 'vew_save_widget_manager' );
		$dom  = $this->dom( $html );

		$this->assertSame( 0, $dom->getElementsByTagName( 'b' )->length );
	}

	/**
	 * The submit button is present.
	 *
	 * @return void
	 */
	public function test_submit_button_present(): void {
		$html = WidgetManagerView::render( $this->widgets(), array(), 'nonce', 'vew_save_widget_manager' );
		$dom  = $this->dom( $html );

		$buttons = $dom->getElementsByTagName( 'button' );
		$this->assertGreaterThanOrEqual( 1, $buttons->length );
	}
}
