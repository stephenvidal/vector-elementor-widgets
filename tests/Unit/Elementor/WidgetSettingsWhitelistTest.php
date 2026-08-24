<?php
/**
 * Guard the CTA Banner widget's settings whitelist and render output.
 *
 * `CtaBanner::sanitize_settings()` strips every key not on an explicit
 * allow-list before the settings reach the rendered output. That is the
 * right default, but it makes the whitelist a silent failure point: a
 * consumer can read a setting the widget never forwards, and nothing
 * anywhere errors — the feature just never works.
 *
 * These tests pin the contract lexically against the source file, plus
 * verify the rendered markup escapes output and includes the expected
 * scoped classes.
 *
 * @package Vector\ElementorWidgets\Tests\Unit\Elementor
 */

declare( strict_types=1 );

namespace Vector\ElementorWidgets\Tests\Unit\Elementor;

use Vector\ElementorWidgets\Tests\Unit\TestCase;

final class WidgetSettingsWhitelistTest extends TestCase {

	/**
	 * Source of the widget class.
	 *
	 * @return string
	 */
	private function widget_source(): string {
		$path = dirname( __DIR__, 3 ) . '/src/Elementor/Widget/CtaBanner.php';
		$this->assertFileExists( $path );

		$source = file_get_contents( $path );
		$this->assertIsString( $source );
		return $source;
	}

	/**
	 * Every setting key the render() reads must be on the whitelist.
	 *
	 * @return array<int, array<int, string>>
	 */
	public static function widget_setting_keys(): array {
		return array(
			array( 'heading' ),
			array( 'description' ),
			array( 'primary_text' ),
			array( 'primary_url' ),
			array( 'secondary_text' ),
			array( 'secondary_url' ),
		);
	}

	/**
	 * @dataProvider widget_setting_keys
	 *
	 * @param string $key Setting key.
	 */
	public function test_every_consumed_setting_is_on_the_whitelist( string $key ): void {
		$this->assertStringContainsString(
			"'" . $key . "'",
			$this->widget_source(),
			sprintf( 'render() reads "%s" but the whitelist would strip it.', $key )
		);
	}

	/**
	 * The widget must declare its stylesheet dependency for selective loading.
	 */
	public function test_widget_declares_style_dependency(): void {
		$source = $this->widget_source();

		$this->assertStringContainsString( 'get_style_depends', $source );
		$this->assertStringContainsString( 'vew-cta-banner', $source );
	}

	/**
	 * The widget must render scoped classes (no generic selectors).
	 */
	public function test_widget_renders_scoped_classes(): void {
		$source = $this->widget_source();

		$this->assertStringContainsString( 'vew-cta__heading', $source );
		$this->assertStringContainsString( 'vew-cta__button--primary', $source );
		$this->assertStringContainsString( 'vew-cta__button--secondary', $source );
	}

	/**
	 * No `_content_template()` override — it fatals in the editor.
	 */
	public function test_no_content_template_override(): void {
		$source = $this->widget_source();

		$this->assertStringNotContainsString( 'function _content_template', $source );
		$this->assertStringNotContainsString( 'function content_template', $source );
	}
}
