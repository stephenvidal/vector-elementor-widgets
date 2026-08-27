<?php
/**
 * New P1 widgets contract (LogoBar, TeamGrid, PostsFeed, Newsletter).
 *
 * @package Vector\ElementorWidgets\Tests\Unit\Elementor
 */

declare( strict_types=1 );

namespace Vector\ElementorWidgets\Tests\Unit\Elementor;

use Vector\ElementorWidgets\Tests\Unit\TestCase;

final class WidgetAdditionsTest extends TestCase {

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
	 * All four new widgets are registered in the plugin and catalog.
	 */
	public function test_new_widgets_are_registered(): void {
		$plugin  = $this->source( 'src/Plugin.php' );
		$catalog = $this->source( 'src/Elementor/WidgetCatalog.php' );

		foreach ( array( 'LogoBar', 'TeamGrid', 'PostsFeed', 'Newsletter' ) as $class ) {
			$this->assertStringContainsString( $class . '::class', $plugin );
			$this->assertStringContainsString( $class . '::class', $catalog );
		}
	}

	/**
	 * Each new widget renders through the shared SectionHeading component.
	 */
	public function test_widgets_consume_shared_heading(): void {
		foreach ( array( 'LogoBar', 'TeamGrid', 'PostsFeed', 'Newsletter' ) as $class ) {
			$widget = $this->source( 'src/Elementor/Widget/' . $class . '.php' );
			$this->assertStringContainsString( 'SectionHeading::render', $widget, $class );
			$this->assertStringContainsString( "'vew-section-heading'", $widget, $class );
		}
	}

	/**
	 * LogoBar exposes a logos repeater, layout select, and grayscale toggle.
	 */
	public function test_logobar_controls(): void {
		$controls = $this->source( 'src/Elementor/Control/LogoBarContentControls.php' );
		$widget   = $this->source( 'src/Elementor/Widget/LogoBar.php' );
		$css      = $this->source( 'widgets/LogoBar/vew-logo-bar.css' );

		$this->assertStringContainsString( "'logos'", $controls );
		$this->assertStringContainsString( 'Controls_Manager::MEDIA', $controls );
		$this->assertStringContainsString( "'layout'", $controls );
		$this->assertStringContainsString( 'vew-logo-bar--grayscale', $widget );
		$this->assertStringContainsString( 'vew-logo-bar__marquee', $css );
	}

	/**
	 * TeamGrid renders a members repeater and scopes CSS per column count.
	 */
	public function test_teamgrid_controls(): void {
		$controls = $this->source( 'src/Elementor/Control/TeamGridContentControls.php' );
		$widget   = $this->source( 'src/Elementor/Widget/TeamGrid.php' );
		$css      = $this->source( 'widgets/TeamGrid/vew-team-grid.css' );

		$this->assertStringContainsString( "'members'", $controls );
		$this->assertStringContainsString( 'vew-team-grid--', $widget );
		$this->assertStringContainsString( 'grid-template-columns: repeat(3, 1fr)', $css );
	}

	/**
	 * PostsFeed is dynamic (WP_Query) and escapes all queried output.
	 */
	public function test_postsfeed_is_dynamic_and_escaped(): void {
		$widget = $this->source( 'src/Elementor/Widget/PostsFeed.php' );
		$css    = $this->source( 'widgets/PostsFeed/vew-posts-feed.css' );

		$this->assertStringContainsString( 'WP_Query', $widget );
		$this->assertStringContainsString( 'wp_reset_postdata', $widget );
		$this->assertStringContainsString( 'the_permalink', $widget );
		$this->assertStringContainsString( 'vew-posts-feed--3', $css );
	}

	/**
	 * Newsletter posts to the newsletter handler with a nonce, no baked color.
	 */
	public function test_newsletter_form_and_handler(): void {
		$widget   = $this->source( 'src/Elementor/Widget/Newsletter.php' );
		$handler  = $this->source( 'src/Support/NewsletterFormHandler.php' );
		$controls = $this->source( 'src/Elementor/Control/NewsletterContentControls.php' );

		$this->assertStringContainsString( 'NewsletterFormHandler::ACTION', $widget );
		$this->assertStringContainsString( 'admin-post.php', $widget );
		$this->assertStringContainsString( 'wp_verify_nonce', $handler );
		$this->assertStringContainsString( 'is_email', $handler );
		// No baked color defaults in the widget controls (kit-driven).
		$this->assertStringNotContainsString( "'default' => '#", $controls );
	}

	/**
	 * The a11y baseline enqueues a global reduced-motion + focus-visible file.
	 */
	public function test_accessibility_baseline_loaded(): void {
		$site_kit = $this->source( 'src/Elementor/SiteKit.php' );
		$a11y     = $this->source( 'assets/a11y.css' );

		$this->assertStringContainsString( 'enqueue_accessibility', $site_kit );
		$this->assertStringContainsString( 'assets/a11y.css', $site_kit );
		$this->assertStringContainsString( 'prefers-reduced-motion', $a11y );
		$this->assertStringContainsString( ':focus-visible', $a11y );
	}

	/**
	 * Services uses the WAI-ARIA tabs pattern (tablist/tab/tabpanel).
	 */
	public function test_services_uses_wai_aria_tabs(): void {
		$widget = $this->source( 'src/Elementor/Widget/Services.php' );
		$js     = $this->source( 'widgets/Services/vew-services.js' );

		$this->assertStringContainsString( 'role="tablist"', $widget );
		$this->assertStringContainsString( 'role="tab"', $widget );
		$this->assertStringContainsString( 'aria-selected', $widget );
		$this->assertStringContainsString( 'aria-controls', $widget );
		$this->assertStringContainsString( 'ArrowLeft', $js );
		$this->assertStringContainsString( 'Home', $js );
	}

	/**
	 * Services exposes configurable tab labels (no more hardcoded Design/Build/Launch).
	 */
	public function test_services_configurable_tab_labels(): void {
		$widget = $this->source( 'src/Elementor/Widget/Services.php' );
		$ctrl   = $this->source( 'src/Elementor/Control/ServicesContentControls.php' );

		// The control exposes a tab_labels repeater.
		$this->assertStringContainsString( "'tab_labels'", $ctrl );
		// The render path reads tab_labels and falls back to the variant key.
		$this->assertStringContainsString( '$tab_labels[ $key ]', $widget );
		$this->assertStringContainsString( 'ucfirst( $key )', $widget );
	}

	/**
	 * PortfolioIndex queries factory-tagged pages, renders previews, and gates behind "Load more".
	 */
	public function test_portfolio_index_queries_factory_sites(): void {
		$widget = $this->source( 'src/Elementor/Widget/PortfolioIndex.php' );
		$css    = $this->source( 'widgets/PortfolioIndex/vew-portfolio-index.css' );
		$js     = $this->source( 'widgets/PortfolioIndex/vew-portfolio-index.js' );

		// Queries pages tagged _vew_factory_site.
		$this->assertStringContainsString( "'_vew_factory_site'", $widget );
		$this->assertStringContainsString( 'meta_key', $widget );
		// Reads the per-site kit for a brand swatch and a preview screenshot.
		$this->assertStringContainsString( '_vew_site_kit', $widget );
		$this->assertStringContainsString( '_portfolio_preview', $widget );
		$this->assertStringContainsString( 'vew-portfolio-index__preview', $widget );
		// Preview accepts both an attachment ID and a URL string.
		$this->assertStringContainsString( 'is_numeric( $preview_raw )', $widget );
		$this->assertStringContainsString( 'esc_url_raw( $preview_raw )', $widget );
		// Responsive grid columns + lazy loading.
		$this->assertStringContainsString( 'vew-portfolio-index__grid', $css );
		$this->assertStringContainsString( 'grid-template-columns', $css );
		$this->assertStringContainsString( 'loading="lazy"', $widget );
		// Load-more gating: hidden cards collapsed, JS reveals them.
		$this->assertStringContainsString( 'vew-portfolio-index__card--hidden', $widget );
		$this->assertStringContainsString( 'vew-portfolio-index__card--hidden', $css );
		$this->assertStringContainsString( 'data-vew-portfolio-more', $widget );
		$this->assertStringContainsString( 'classList.remove', $js );
		$this->assertStringContainsString( "get_script_depends", $widget );
	}

	/**
	 * Portfolio (Manual) supports an "auto from linked page" preview source:
	 * the control exposes preview_source, and the render path resolves the
	 * linked page's _portfolio_preview via url_to_postid, falling back to the
	 * manual image.
	 */
	public function test_portfolio_auto_preview_source(): void {
		$widget = $this->source( 'src/Elementor/Widget/Portfolio.php' );
		$ctrl   = $this->source( 'src/Elementor/Control/PortfolioContentControls.php' );

		// The repeater exposes a preview_source selector (manual / auto).
		$this->assertStringContainsString( "'preview_source'", $ctrl );
		$this->assertStringContainsString( "'auto'", $ctrl );
		$this->assertStringContainsString( "'manual'", $ctrl );
		// The render path reads preview_source and resolves the linked page.
		$this->assertStringContainsString( "'preview_source'", $widget );
		$this->assertStringContainsString( 'resolve_auto_preview', $widget );
		$this->assertStringContainsString( 'url_to_postid', $widget );
		$this->assertStringContainsString( '_portfolio_preview', $widget );
		// Falls back to the manual image when auto resolution yields nothing.
		$this->assertStringContainsString( "'' !== \$auto", $widget );
	}

	/**
	 * Stats exposes a numeric count target and a reduced-motion-gated count-up.
	 */
	public function test_stats_count_up(): void {
		$widget = $this->source( 'src/Elementor/Widget/Stats.php' );
		$js     = $this->source( 'widgets/Stats/vew-stats.js' );

		$this->assertStringContainsString( 'data-count', $widget );
		$this->assertStringContainsString( 'get_script_depends', $widget );
		$this->assertStringContainsString( 'prefers-reduced-motion', $js );
		$this->assertStringContainsString( 'IntersectionObserver', $js );
	}

	/**
	 * Gallery carousel renders prev/next + dots controls.
	 */
	public function test_gallery_carousel_controls(): void {
		$widget = $this->source( 'src/Elementor/Widget/Gallery.php' );
		$js     = $this->source( 'widgets/Gallery/vew-gallery.js' );
		$css    = $this->source( 'widgets/Gallery/vew-gallery.css' );

		$this->assertStringContainsString( 'vew-gallery__carousel-nav', $widget );
		$this->assertStringContainsString( 'data-vew-carousel-dots', $widget );
		$this->assertStringContainsString( 'initCarousel', $js );
		$this->assertStringContainsString( 'vew-gallery__dot', $css );
	}

	/**
	 * Scripts register with the WP 6.3 defer strategy.
	 */
	public function test_scripts_use_defer_strategy(): void {
		$assets = $this->source( 'src/Elementor/AssetManager.php' );

		$this->assertStringContainsString( "'strategy'  => 'defer'", $assets );
		$this->assertStringContainsString( "'in_footer' => true", $assets );
	}

	/**
	 * Catalog metadata is finished (no placeholder descriptions/icons).
	 */
	public function test_catalog_metadata_complete(): void {
		$catalog = $this->source( 'src/Elementor/WidgetCatalog.php' );

		$this->assertStringNotContainsString( 'A Stats component', $catalog );
		$this->assertStringNotContainsString( 'A Gallery component', $catalog );
		// The placeholder eicon-code should no longer be the catalog default.
		$this->assertStringNotContainsString( "'icon'        => 'eicon-code'", $catalog );
	}

	/**
	 * De-baked links/strings: Pricing CTA URL, translatable /project, no #home.
	 */
	public function test_debaked_links_and_strings(): void {
		$pricing = $this->source( 'src/Elementor/Widget/Pricing.php' );
		$footer  = $this->source( 'src/Elementor/Widget/Footer.php' );

		$this->assertStringContainsString( "p_cta_url", $pricing );
		$this->assertStringNotContainsString( 'href="#contact"', $pricing );
		$this->assertStringContainsString( "esc_html__( '/ project'", $pricing );
		$this->assertStringNotContainsString( 'href="#home"', $footer );
	}

	/**
	 * Testimonials routes through SectionHeading, supports avatars, and emits
	 * Review schema.
	 */
	public function test_testimonials_refactor(): void {
		$widget = $this->source( 'src/Elementor/Widget/Testimonials.php' );

		$this->assertStringContainsString( 'SectionHeading::render', $widget );
		$this->assertStringContainsString( 'vew-testimonial__avatar', $widget );
		$this->assertStringContainsString( 'application/ld+json', $widget );
		$this->assertStringContainsString( 'schema.org', $widget );
		// i18n fix: star label is translatable.
		$this->assertStringContainsString( "'%d out of 5 stars'", $widget );
	}

	/**
	 * All five vertical widgets are registered and catalogued.
	 */
	public function test_vertical_widgets_are_registered(): void {
		$plugin  = $this->source( 'src/Plugin.php' );
		$catalog = $this->source( 'src/Elementor/WidgetCatalog.php' );

		foreach ( array( 'GoogleMap', 'VideoEmbed', 'Countdown', 'BeforeAfter', 'Timeline' ) as $class ) {
			$this->assertStringContainsString( $class . '::class', $plugin );
			$this->assertStringContainsString( $class . '::class', $catalog );
		}
	}

	/**
	 * GoogleMap builds a sanitized, urlencoded iframe src (no injection).
	 */
	public function test_google_map_sanitizes_iframe_src(): void {
		$widget = $this->source( 'src/Elementor/Widget/GoogleMap.php' );

		$this->assertStringContainsString( 'rawurlencode', $widget );
		$this->assertStringContainsString( 'output=embed', $widget );
		$this->assertStringContainsString( 'esc_url(', $widget );
		$this->assertStringContainsString( 'SectionHeading::render', $widget );
	}

	/**
	 * VideoEmbed detects YouTube/Vimeo/direct file and never echoes raw user URL.
	 */
	public function test_video_embed_rebuilds_embeds_from_id(): void {
		$widget = $this->source( 'src/Elementor/Widget/VideoEmbed.php' );

		$this->assertStringContainsString( 'youtube-nocookie.com', $widget );
		$this->assertStringContainsString( 'player.vimeo.com', $widget );
		$this->assertStringContainsString( 'youtube_id', $widget );
		$this->assertStringContainsString( 'vimeo_id', $widget );
	}

	/**
	 * Countdown exposes the data-end target and ticks via JS.
	 */
	public function test_countdown_has_target_and_driver(): void {
		$widget = $this->source( 'src/Elementor/Widget/Countdown.php' );
		$js     = $this->source( 'widgets/Countdown/vew-countdown.js' );

		$this->assertStringContainsString( 'data-end', $widget );
		$this->assertStringContainsString( 'data-expired', $widget );
		$this->assertStringContainsString( 'get_script_depends', $widget );
		$this->assertStringContainsString( 'setInterval', $js );
	}

	/**
	 * Before/After renders both layers and is keyboard-accessible in JS.
	 */
	public function test_before_after_drag_and_keyboard(): void {
		$widget = $this->source( 'src/Elementor/Widget/BeforeAfter.php' );
		$js     = $this->source( 'widgets/BeforeAfter/vew-before-after.js' );

		$this->assertStringContainsString( 'vew-before-after__after', $widget );
		$this->assertStringContainsString( 'aria-valuenow', $widget );
		$this->assertStringContainsString( 'get_script_depends', $widget );
		$this->assertStringContainsString( 'pointerdown', $js );
		$this->assertStringContainsString( "e.key === 'ArrowLeft'", $js );
	}

	/**
	 * Timeline renders a repeater of events with sanitized output.
	 */
	public function test_timeline_renders_events(): void {
		$widget = $this->source( 'src/Elementor/Widget/Timeline.php' );
		$controls = $this->source( 'src/Elementor/Control/TimelineContentControls.php' );
		$css    = $this->source( 'widgets/Timeline/vew-timeline.css' );

		$this->assertStringContainsString( 'REPEATER', $controls );
		$this->assertStringContainsString( 'vew-timeline__item', $widget );
		$this->assertStringContainsString( 'nth-child(even)', $css );
	}
}
