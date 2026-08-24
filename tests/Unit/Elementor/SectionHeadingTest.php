<?php
/**
 * Shared section-heading component contract.
 *
 * @package Vector\ElementorWidgets\Tests\Unit\Elementor
 */

declare( strict_types=1 );

namespace Vector\ElementorWidgets\Tests\Unit\Elementor;

use Brain\Monkey\Functions;
use Vector\ElementorWidgets\Elementor\Component\SectionHeading;
use Vector\ElementorWidgets\Tests\Unit\TestCase;

final class SectionHeadingTest extends TestCase {

	protected function setUp(): void {
		parent::setUp();
		Functions\when( 'esc_html' )->alias(
			static fn ( string $value ): string => htmlspecialchars( $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8' )
		);
		Functions\when( 'esc_attr' )->alias(
			static fn ( string $value ): string => htmlspecialchars( $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8' )
		);
	}

	public function test_legacy_plain_title_keeps_plain_markup(): void {
		$html = SectionHeading::render(
			array(
				'eyebrow' => 'Work',
				'title'   => 'Plain saved title',
				'intro'   => 'Supporting copy.',
			),
			array(
				'block_class' => 'vew-example',
			)
		);

		$this->assertStringContainsString( '<h2 class="vew-example__title vew-section-heading__title">Plain saved title</h2>', $html );
		$this->assertStringNotContainsString( '<em', $html );
	}

	public function test_structured_title_is_escaped_and_semantic(): void {
		$html = SectionHeading::render(
			array(
				'title'        => 'Before <unsafe>',
				'title_accent' => 'Emphasized & safe',
				'title_after'  => 'after',
			),
			array(
				'block_class' => 'vew-example',
			)
		);

		$this->assertStringContainsString( 'vew-section-heading__before', $html );
		$this->assertStringContainsString( '<em class="vew-section-heading__accent">Emphasized &amp; safe</em>', $html );
		$this->assertStringContainsString( 'vew-section-heading__after', $html );
		$this->assertStringContainsString( 'Before &lt;unsafe&gt;', $html );
	}

	public function test_component_exposes_responsive_placement_and_native_accent_styles(): void {
		$path   = dirname( __DIR__, 3 ) . '/src/Elementor/Component/SectionHeading.php';
		$source = file_get_contents( $path );

		$this->assertIsString( $source );
		$this->assertStringContainsString( "'title_accent'", $source );
		$this->assertStringContainsString( "'title_after'", $source );
		$this->assertStringContainsString( "'title_accent_display'", $source );
		$this->assertStringContainsString( 'add_responsive_control', $source );
		$this->assertStringContainsString( 'Group_Control_Typography', $source );
		$this->assertStringContainsString( '{{WRAPPER}} .vew-section-heading__accent', $source );

		$css = file_get_contents( dirname( __DIR__, 3 ) . '/assets/components/section-heading.css' );
		$this->assertIsString( $css );
		$this->assertStringContainsString( 'color: var(--accent, #ff6b4a);', $css );
	}

	/**
	 * @dataProvider section_heading_consumers
	 */
	public function test_section_widgets_consume_shared_heading( string $widget ): void {
		$path   = dirname( __DIR__, 3 ) . '/src/Elementor/Widget/' . $widget . '.php';
		$source = file_get_contents( $path );

		$this->assertIsString( $source );
		$this->assertStringContainsString( 'SectionHeading::render', $source );
		$this->assertStringContainsString( "'title_accent'", $source );
		$this->assertStringContainsString( "'title_after'", $source );
	}

	/**
	 * @return array<int, array{string}>
	 */
	public static function section_heading_consumers(): array {
		return array(
			array( 'Services' ),
			array( 'Portfolio' ),
			array( 'Process' ),
			array( 'Pricing' ),
			array( 'Faq' ),
			array( 'Contact' ),
		);
	}
}
