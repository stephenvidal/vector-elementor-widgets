<?php
/**
 * Kit value object tests.
 *
 * @package Vector\ElementorWidgets\Tests\Unit\Kit
 */

declare( strict_types=1 );

namespace Vector\ElementorWidgets\Tests\Unit\Kit;

use Brain\Monkey\Functions;
use Vector\ElementorWidgets\Kit\Kit;
use Vector\ElementorWidgets\Tests\Unit\TestCase;

/**
 * Kit value-object contract.
 */
final class KitTest extends TestCase {

	/**
	 * Stub the WP sanitize functions the Kit depends on.
	 */
	protected function setUp(): void {
		parent::setUp();

		Functions\when( 'sanitize_key' )->returnArg();
		Functions\when( 'sanitize_text_field' )->returnArg();
	}

	/**
	 * from_array keeps valid tokens and drops unknown/invalid keys.
	 */
	public function test_from_array_keeps_valid_tokens(): void {
		$kit = Kit::from_array(
			array(
				'slug'   => 'acme',
				'tokens' => array(
					'brand' => '#1c628f',
					'evil'  => 'red;',
					'ink'   => '#111',
				),
			)
		);

		$this->assertSame( '#1c628f', $kit->token( 'brand' ) );
		$this->assertSame( '#111', $kit->token( 'ink' ) );
		$this->assertArrayNotHasKey( 'evil', $kit->tokens() );
	}

	/**
	 * Invalid color values are dropped entirely.
	 */
	public function test_invalid_color_is_dropped(): void {
		$kit = Kit::from_array(
			array(
				'slug'   => 'acme',
				'tokens' => array( 'accent' => 'not-a-color;' ),
			)
		);

		$this->assertSame( '', $kit->token( 'accent' ) );
	}

	/**
	 * to_array round-trips all known fields.
	 */
	public function test_to_array_round_trips(): void {
		$in = array(
			'slug'        => 'acme',
			'label'       => 'Acme Co',
			'description' => 'A corp',
			'tokens'      => array( 'brand' => '#1c628f', 'accent' => '#c9a24b' ),
			'fonts'       => array( 'sans' => 'Inter, sans-serif' ),
			'layout'      => array( 'gutter' => 24, 'section_space_y' => 'clamp(64px, 9vw, 96px)' ),
		);

		$kit   = Kit::from_array( $in );
		$out   = $kit->to_array();

		$this->assertSame( 'acme', $out['slug'] );
		$this->assertSame( 'Acme Co', $out['label'] );
		$this->assertSame( 'Inter, sans-serif', $out['fonts']['sans'] );
		$this->assertSame( 24, $out['layout']['gutter'] );
		$this->assertSame( 'clamp(64px, 9vw, 96px)', $out['layout']['section_space_y'] );
	}

	/**
	 * A slug must match the pattern and not be reserved.
	 */
	public function test_slug_must_match_pattern(): void {
		$this->expectException( \InvalidArgumentException::class );
		Kit::from_array( array( 'slug' => 'Bad Slug!' ) );
	}

	/**
	 * Reserved slugs are rejected.
	 */
	public function test_reserved_slugs_are_rejected(): void {
		$this->expectException( \InvalidArgumentException::class );
		Kit::from_array( array( 'slug' => 'default' ) );
	}

	/**
	 * Layout integers clamp negatives to zero and coerce strings.
	 */
	public function test_layout_int_is_sanitized(): void {
		$kit = Kit::from_array(
			array(
				'slug'   => 'acme',
				'layout' => array( 'container_width' => '1288', 'gutter' => -5 ),
			)
		);

		$this->assertSame( 1288, $kit->layout_value( 'container_width' ) );
		$this->assertSame( 0, $kit->layout_value( 'gutter' ) );
	}
}
