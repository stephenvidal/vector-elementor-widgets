<?php
/**
 * Kit CSS compiler tests.
 *
 * @package Vector\ElementorWidgets\Tests\Unit\Kit
 */

declare( strict_types=1 );

namespace Vector\ElementorWidgets\Tests\Unit\Kit;

use Brain\Monkey\Functions;
use Vector\ElementorWidgets\Kit\Kit;
use Vector\ElementorWidgets\Kit\KitCssCompiler;
use Vector\ElementorWidgets\Tests\Unit\TestCase;

/**
 * Compiler contract.
 */
final class KitCssCompilerTest extends TestCase {

	/**
	 * Stub WP sanitize functions.
	 *
	 * @return void
	 */
	protected function setUp(): void {
		parent::setUp();
		Functions\when( 'sanitize_key' )->returnArg();
		Functions\when( 'sanitize_text_field' )->returnArg();
	}

	/**
	 * compile emits :root tokens, body, and the scroll-margin helpers.
	 *
	 * @return void
	 */
	public function test_compiles_root_tokens_and_body(): void {
		$kit = Kit::from_array(
			array(
				'slug'   => 'acme',
				'tokens' => array(
					'brand'      => '#1c628f',
					'brand-dark' => '#123f5e',
					'ink'        => '#1a2b38',
					'paper'      => '#f5f7f9',
				),
				'fonts'  => array( 'sans' => 'Inter, sans-serif' ),
				'layout' => array( 'section_space_y' => 'clamp(64px, 9vw, 96px)', 'gutter' => 24 ),
			)
		);

		$css = ( new KitCssCompiler() )->compile( $kit );

		$this->assertStringContainsString( '--brand: #1c628f', $css );
		$this->assertStringContainsString( '--brand-dark: #123f5e', $css );
		$this->assertStringContainsString( '--sans: Inter, sans-serif', $css );
		$this->assertStringContainsString( '--section-space-y: clamp(64px, 9vw, 96px)', $css );
		$this->assertStringContainsString( '--gutter: 24px', $css );
		$this->assertStringContainsString( 'body {', $css );
		$this->assertStringContainsString( 'background-color: var(--paper, #f5f7f9)', $css );
		$this->assertStringContainsString( 'scroll-margin-top: 88px', $css );
	}

	/**
	 * On-color tokens (on-brand/on-brand-dark/on-accent) are emitted when set.
	 *
	 * These power the de-hardcoded widget CSS that paints text on top of
	 * brand/brand-dark/accent surfaces, so they must survive compilation.
	 *
	 * @return void
	 */
	public function test_compiles_on_color_tokens(): void {
		$kit = Kit::from_array(
			array(
				'slug'   => 'acme',
				'tokens' => array(
					'brand'        => '#1c628f',
					'brand-dark'   => '#123f5e',
					'accent'       => '#c9a24b',
					'on-brand'     => '#ffffff',
					'on-brand-dark' => '#f2f2f2',
					'on-accent'    => '#1a1b26',
				),
			)
		);

		$css = ( new KitCssCompiler() )->compile( $kit );

		$this->assertStringContainsString( '--on-brand: #ffffff', $css );
		$this->assertStringContainsString( '--on-brand-dark: #f2f2f2', $css );
		$this->assertStringContainsString( '--on-accent: #1a1b26', $css );
	}

	/**
	 * Omitted tokens fall back to sensible defaults without crashing.
	 *
	 * @return void
	 */
	public function test_minimal_kit_still_compiles(): void {
		$kit = Kit::from_array( array( 'slug' => 'min' ) );

		$css = ( new KitCssCompiler() )->compile( $kit );

		$this->assertStringContainsString( ':root {', $css );
		$this->assertStringContainsString( '--display:', $css );
		$this->assertStringContainsString( 'body {', $css );
	}
}
