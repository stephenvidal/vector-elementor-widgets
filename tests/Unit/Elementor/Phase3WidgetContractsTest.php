<?php
/**
 * Guard the Phase 3 widgets' settings whitelists and rendering contracts.
 *
 * Each widget strips every setting key not on its explicit allow-list before
 * the settings reach the rendered output. These tests pin the contract
 * lexically against the source files. Also verifies scoped classes, escaped
 * output, and the FAQ's accessible-accordion structure.
 *
 * @package Vector\ElementorWidgets\Tests\Unit\Elementor
 */

declare( strict_types=1 );

namespace Vector\ElementorWidgets\Tests\Unit\Elementor;

use Vector\ElementorWidgets\Tests\Unit\TestCase;

final class Phase3WidgetContractsTest extends TestCase {

	/**
	 * Read a widget source file relative to the project root.
	 *
	 * @param string $rel Path relative to src/Elementor/Widget/.
	 * @return string
	 */
	private function widget_source( string $rel ): string {
		$path = dirname( __DIR__, 3 ) . '/src/Elementor/Widget/' . $rel;
		$this->assertFileExists( $path );

		$source = file_get_contents( $path );
		$this->assertIsString( $source );
		return $source;
	}

	/**
	 * Data: widget file => consumed keys that must be on its whitelist.
	 *
	 * @return array<string, array{string, array<int, string>}>
	 */
	public static function whitelist_cases(): array {
		return array(
			'FeatureGrid'   => array(
				'FeatureGrid.php',
				array( 'eyebrow', 'title', 'intro', 'features' ),
			),
			'Testimonials'  => array(
				'Testimonials.php',
				array( 'reviews' ),
			),
			'Faq'           => array(
				'Faq.php',
				array( 'eyebrow', 'title', 'intro', 'items' ),
			),
		);
	}

	/**
	 * Every consumed key must be present in the widget source (the whitelist
	 * array and the render() reads both use the same literal).
	 *
	 * @dataProvider whitelist_cases
	 *
	 * @param string           $file Widget source file.
	 * @param array<int,string> $keys Consumed keys.
	 */
	public function test_consumed_settings_are_on_the_whitelist( string $file, array $keys ): void {
		$source = $this->widget_source( $file );
		foreach ( $keys as $key ) {
			$this->assertStringContainsString(
				"'" . $key . "'",
				$source,
				sprintf( '%s: "%s" must appear in the whitelist + render reads.', $file, $key )
			);
		}
	}

	/**
	 * Feature Grid declares its style dependency and scoped classes.
	 */
	public function test_feature_grid_contract(): void {
		$source = $this->widget_source( 'FeatureGrid.php' );

		$this->assertStringContainsString( 'get_style_depends', $source );
		$this->assertStringContainsString( 'vew-feature-grid', $source );
		$this->assertStringContainsString( 'vew-feature-card', $source );
		$this->assertStringNotContainsString( 'vew-feature-card--large', $source );
		$this->assertStringNotContainsString( 'function _content_template', $source );
	}

	/**
	 * Testimonials declares its style dependency, scoped classes, and escapes.
	 */
	public function test_testimonials_contract(): void {
		$source = $this->widget_source( 'Testimonials.php' );

		$this->assertStringContainsString( 'get_style_depends', $source );
		$this->assertStringContainsString( 'vew-testimonials', $source );
		$this->assertStringContainsString( 'vew-testimonial__stars', $source );
		$this->assertStringContainsString( 'str_repeat', $source ); // star rendering
		$this->assertStringNotContainsString( 'function _content_template', $source );
	}

	/**
	 * FAQ declares BOTH style and script dependencies, uses scoped classes,
	 * and renders an accessible accordion (aria-expanded, role="region", hidden).
	 */
	public function test_faq_contract(): void {
		$source = $this->widget_source( 'Faq.php' );

		$this->assertStringContainsString( 'get_style_depends', $source );
		$this->assertStringContainsString( 'get_script_depends', $source );
		$this->assertStringContainsString( 'vew-faq', $source );
		$this->assertStringContainsString( 'aria-expanded', $source );
		$this->assertStringContainsString( 'role="region"', $source );
		$this->assertStringContainsString( 'hidden', $source );
		$this->assertStringContainsString( 'wp_unique_id', $source ); // per-instance ids
		$this->assertStringNotContainsString( 'function _content_template', $source );
	}

	/**
	 * The FAQ JS exists and is non-trivial (contains the toggle handler).
	 */
	public function test_faq_js_exists_and_toggles(): void {
		$path = dirname( __DIR__, 3 ) . '/widgets/Faq/faq.js';
		$this->assertFileExists( $path );

		$js = file_get_contents( $path );
		$this->assertIsString( $js );
		$this->assertStringContainsString( 'aria-expanded', $js );
		$this->assertStringContainsString( 'addEventListener', $js );
	}
}
