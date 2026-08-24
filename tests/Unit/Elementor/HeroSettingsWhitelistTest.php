<?php
/**
 * Guard the Hero widget's settings whitelist.
 *
 * `Hero::sanitize_settings()` strips every key not on an explicit allow-list
 * before the settings reach the rendered output. These tests pin the contract
 * lexically against the source file — a consumer can read a setting the widget
 * never forwards, and nothing anywhere errors, so the whitelist must be
 * complete and the consumed keys must match.
 *
 * @package Vector\ElementorWidgets\Tests\Unit\Elementor
 */

declare( strict_types=1 );

namespace Vector\ElementorWidgets\Tests\Unit\Elementor;

use Vector\ElementorWidgets\Tests\Unit\TestCase;

final class HeroSettingsWhitelistTest extends TestCase {

	/**
	 * Source of the Hero widget class.
	 *
	 * @return string
	 */
	private function widget_source(): string {
		$path = dirname( __DIR__, 3 ) . '/src/Elementor/Widget/Hero.php';
		$this->assertFileExists( $path );

		$source = file_get_contents( $path );
		$this->assertIsString( $source );
		return $source;
	}

	/**
	 * Every setting key render() reads must be on the whitelist.
	 *
	 * @return array<int, array<int, string>>
	 */
	public static function widget_setting_keys(): array {
		return array(
			array( 'eyebrow' ),
			array( 'headline' ),
			array( 'copy' ),
			array( 'primary_cta_text' ),
			array( 'primary_cta_url' ),
			array( 'secondary_cta_text' ),
			array( 'secondary_cta_url' ),
			array( 'trust_list' ),
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
		$this->assertStringContainsString( 'vew-hero', $source );
	}

	/**
	 * No `_content_template()` override — it fatals in the editor.
	 */
	public function test_no_content_template_override(): void {
		$source = $this->widget_source();

		$this->assertStringNotContainsString( 'function _content_template', $source );
		$this->assertStringNotContainsString( 'function content_template', $source );
	}

	/**
	 * The widget must render scoped classes and escape its output.
	 */
	public function test_widget_renders_scoped_and_escaped_output(): void {
		$source = $this->widget_source();

		$this->assertStringContainsString( 'vew-hero__eyebrow', $source );
		$this->assertStringContainsString( 'vew-hero__headline', $source );
		$this->assertStringContainsString( 'vew-hero__trust', $source );
		$this->assertStringContainsString( 'esc_html( $eyebrow )', $source );
		$this->assertStringContainsString( 'wp_kses_post( $headline )', $source );
	}
}
