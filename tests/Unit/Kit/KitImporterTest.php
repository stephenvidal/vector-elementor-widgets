<?php
/**
 * Kit importer tests.
 *
 * @package Vector\ElementorWidgets\Tests\Unit\Kit
 */

declare( strict_types=1 );

namespace Vector\ElementorWidgets\Tests\Unit\Kit;

use Brain\Monkey\Functions;
use Vector\ElementorWidgets\Kit\KitImporter;
use Vector\ElementorWidgets\Tests\Unit\TestCase;

/**
 * Importer contract.
 */
final class KitImporterTest extends TestCase {

	/**
	 * Stub WP sanitize functions.
	 *
	 * @return void
	 */
	protected function setUp(): void {
		parent::setUp();
		Functions\when( 'sanitize_key' )->returnArg();
		Functions\when( 'sanitize_text_field' )->returnArg();
		Functions\when( '__' )->alias( static fn ( string $s, $d = '' ) => (string) $s );
	}

	/**
	 * from_json imports a valid export payload.
	 *
	 * @return void
	 */
	public function test_imports_valid_payload(): void {
		$json  = wp_json_encode(
			array(
				'_vew_site_kit_version' => 1,
				'slug'                  => 'acme',
				'label'                 => 'Acme',
				'tokens'                => array( 'brand' => '#1c628f' ),
				'fonts'                 => array( 'sans' => 'Inter, sans-serif' ),
				'layout'                => array( 'gutter' => 24 ),
			)
		);
		$result = ( new KitImporter() )->from_json( $json );

		$this->assertNotNull( $result['kit'] );
		$this->assertSame( 'acme', $result['kit']->slug() );
		$this->assertSame( '#1c628f', $result['kit']->token( 'brand' ) );
		$this->assertSame( '', $result['error'] );
	}

	/**
	 * from_json returns an error for invalid JSON.
	 *
	 * @return void
	 */
	public function test_rejects_invalid_json(): void {
		$result = ( new KitImporter() )->from_json( 'not json at all' );

		$this->assertNull( $result['kit'] );
		$this->assertNotSame( '', $result['error'] );
	}

	/**
	 * from_json returns an error when the slug is missing.
	 *
	 * @return void
	 */
	public function test_rejects_missing_slug(): void {
		$result = ( new KitImporter() )->from_json( wp_json_encode( array( 'label' => 'No slug' ) ) );

		$this->assertNull( $result['kit'] );
		$this->assertNotSame( '', $result['error'] );
	}
}
