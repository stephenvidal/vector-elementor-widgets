<?php
/**
 * Shared query-controls component contract.
 *
 * @package Vector\ElementorWidgets\Tests\Unit\Elementor
 */

declare( strict_types=1 );

namespace Vector\ElementorWidgets\Tests\Unit\Elementor;

use Brain\Monkey\Functions;
use Vector\ElementorWidgets\Elementor\Component\QueryControls;
use Vector\ElementorWidgets\Tests\Unit\TestCase;

final class QueryControlsTest extends TestCase {

	/**
	 * Read a project file.
	 *
	 * @param string $relative_path Project-relative path.
	 * @return string
	 */
	private function source( string $relative_path ): string {
		$path = dirname( __DIR__, 3 ) . '/' . $relative_path;
		$this->assertFileExists( $path );

		$source = file_get_contents( $path );
		$this->assertIsString( $source );
		return $source;
	}

	/**
	 * The component exposes the full query control set with prefixed IDs.
	 */
	public function test_component_exposes_prefixed_query_controls(): void {
		$source = $this->source( 'src/Elementor/Component/QueryControls.php' );

		foreach ( array( 'query_post_type', 'query_count', 'query_orderby', 'query_order', 'query_ignore_sticky', 'query_category', 'query_author' ) as $id ) {
			$this->assertStringContainsString( "'{$id}'", $source );
		}
		$this->assertStringContainsString( 'Controls_Manager::SELECT2', $source );
		$this->assertStringContainsString( 'register_content_controls', $source );
	}

	/**
	 * The component exposes a settings whitelist fragment.
	 */
	public function test_component_exposes_setting_types(): void {
		$types = QueryControls::setting_types();

		$this->assertSame( 'string', $types['query_post_type'] );
		$this->assertSame( 'int', $types['query_count'] );
		$this->assertSame( 'array', $types['query_category'] );
		$this->assertSame( 'array', $types['query_author'] );
	}

	/**
	 * query_args() builds a sanitized WP_Query array with safe defaults.
	 */
	public function test_query_args_builds_sanitized_array(): void {
		Functions\when( 'post_type_exists' )->justReturn( true );
		Functions\when( 'sanitize_key' )->returnArg();

		$args = QueryControls::query_args(
			array(
				'query_post_type'     => 'post',
				'query_count'         => 6,
				'query_orderby'       => 'date',
				'query_order'         => 'desc',
				'query_ignore_sticky' => 'yes',
				'query_category'      => array( 3, 7 ),
				'query_author'        => array( 1 ),
			)
		);

		$this->assertSame( 'post', $args['post_type'] );
		$this->assertSame( 6, $args['posts_per_page'] );
		$this->assertSame( 'date', $args['orderby'] );
		$this->assertSame( 'DESC', $args['order'] );
		$this->assertTrue( $args['ignore_sticky_posts'] );
		$this->assertSame( 'category', $args['tax_query'][0]['taxonomy'] );
		$this->assertSame( array( 3, 7 ), $args['tax_query'][0]['terms'] );
		$this->assertSame( array( 1 ), $args['author__in'] );
	}

	/**
	 * query_args() clamps count and rejects unknown orderby/order values.
	 */
	public function test_query_args_clamps_and_whitelists(): void {
		Functions\when( 'post_type_exists' )->justReturn( true );
		Functions\when( 'sanitize_key' )->returnArg();

		$args = QueryControls::query_args(
			array(
				'query_post_type' => 'post',
				'query_count'     => 999,
				'query_orderby'   => 'bogus',
				'query_order'     => 'sideways',
			)
		);

		$this->assertSame( 24, $args['posts_per_page'] );
		$this->assertSame( 'date', $args['orderby'] );
		$this->assertSame( 'DESC', $args['order'] );
	}

	/**
	 * query_args() omits tax_query/author__in when no filters are set.
	 */
	public function test_query_args_omits_empty_filters(): void {
		Functions\when( 'post_type_exists' )->justReturn( true );
		Functions\when( 'sanitize_key' )->returnArg();

		$args = QueryControls::query_args(
			array(
				'query_post_type' => 'post',
				'query_count'     => 3,
			)
		);

		$this->assertArrayNotHasKey( 'tax_query', $args );
		$this->assertArrayNotHasKey( 'author__in', $args );
	}

	/**
	 * The component is consumed by the dynamic widgets (blog + post gallery).
	 * Consumption is asserted in each widget's own test once scaffolded.
	 */
}
