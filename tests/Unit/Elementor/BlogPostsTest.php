<?php
/**
 * BlogPosts widget contract.
 *
 * @package Vector\ElementorWidgets\Tests\Unit\Elementor
 */

declare( strict_types=1 );

namespace Vector\ElementorWidgets\Tests\Unit\Elementor;

use Vector\ElementorWidgets\Tests\Unit\TestCase;

final class BlogPostsTest extends TestCase {

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
	 * The BlogPosts widget owns a full-width surface and a safe internal shell.
	 */
	public function test_blog_posts_owns_full_width_surface_and_internal_shell(): void {
		$widget = $this->source( 'src/Elementor/Widget/BlogPosts.php' );
		$css    = $this->source( 'widgets/BlogPosts/vew-blog-posts.css' );

		$this->assertStringContainsString( 'class="vew-blog-posts__inner"', $widget );
		$this->assertStringContainsString( 'width: 100vw;', $css );
		$this->assertStringContainsString( 'width: min(100%, 1288px);', $css );
		$this->assertStringContainsString( 'padding-inline: 24px;', $css );
		$this->assertStringContainsString( 'padding-inline: 20px;', $css );
	}

	/**
	 * The BlogPosts widget consumes the shared query + heading components.
	 */
	public function test_blog_posts_consumes_shared_components(): void {
		$widget = $this->source( 'src/Elementor/Widget/BlogPosts.php' );

		$this->assertStringContainsString( 'QueryControls::query_args', $widget );
		$this->assertStringContainsString( 'SectionHeading::render', $widget );
		$this->assertStringContainsString( 'SectionHeading::setting_types()', $widget );
	}

	/**
	 * The BlogPosts widget renders a responsive card grid with load-more gating.
	 */
	public function test_blog_posts_renders_grid_and_load_more(): void {
		$widget = $this->source( 'src/Elementor/Widget/BlogPosts.php' );
		$js     = $this->source( 'widgets/BlogPosts/vew-blog-posts.js' );

		$this->assertStringContainsString( 'vew-blog-posts__grid', $widget );
		$this->assertStringContainsString( 'vew-blog-posts__card--hidden', $widget );
		$this->assertStringContainsString( 'data-vew-blog-more', $widget );
		$this->assertStringContainsString( 'data-vew-per-click', $widget );
		$this->assertStringContainsString( 'data-vew-blog-more', $js );
		$this->assertStringContainsString( 'vew-blog-posts__card--hidden', $js );
	}

	/**
	 * The BlogPosts widget is registered in the catalog and plugin.
	 */
	public function test_blog_posts_is_registered(): void {
		$plugin  = $this->source( 'src/Plugin.php' );
		$catalog = $this->source( 'src/Elementor/WidgetCatalog.php' );

		$this->assertStringContainsString( 'BlogPosts::class', $plugin );
		$this->assertStringContainsString( 'vew-blog-posts', $plugin );
		$this->assertStringContainsString( 'BlogPosts::class', $catalog );
		$this->assertStringContainsString( "'vew-blog-posts'", $catalog );
	}

	/**
	 * The BlogPosts widget exposes the full card-content control set.
	 */
	public function test_blog_posts_has_card_content_controls(): void {
		$controls = $this->source( 'src/Elementor/Control/BlogPostsContentControls.php' );

		foreach ( array( 'columns', 'visible', 'per_click', 'show_excerpt', 'excerpt_length', 'show_date', 'show_author', 'show_category', 'show_read_more' ) as $id ) {
			$this->assertStringContainsString( "'{$id}'", $controls );
		}
		$this->assertStringContainsString( 'QueryControls::register_content_controls', $controls );
	}

	/**
	 * The BlogPosts widget escapes all queried values (no raw output).
	 */
	public function test_blog_posts_escapes_output(): void {
		$widget = $this->source( 'src/Elementor/Widget/BlogPosts.php' );

		$this->assertStringContainsString( 'esc_url( $card[\'permalink\'] )', $widget );
		$this->assertStringContainsString( 'esc_html( $card[\'title\'] )', $widget );
		$this->assertStringContainsString( 'esc_html( $card[\'excerpt\'] )', $widget );
		$this->assertStringContainsString( 'esc_attr( $card[\'date_iso\'] )', $widget );
	}
}
