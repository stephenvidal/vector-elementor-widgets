<?php
/**
 * Guard the mobile-first responsive defaults shared by full-page widgets.
 *
 * These lexical tests prevent a regression to page-container-dependent gutters,
 * non-sticky Elementor headers, or theme-default button states.
 *
 * @package Vector\ElementorWidgets\Tests\Unit\Elementor
 */

declare( strict_types=1 );

namespace Vector\ElementorWidgets\Tests\Unit\Elementor;

use Vector\ElementorWidgets\Tests\Unit\TestCase;

final class ResponsiveDefaultsTest extends TestCase {

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
	 * Widget, source file, CSS file, and internal shell class.
	 *
	 * @return array<string, array{string, string, string}>
	 */
	public static function shell_cases(): array {
		return array(
			'Services'  => array( 'src/Elementor/Widget/Services.php', 'widgets/Services/vew-services.css', 'vew-services__inner' ),
			'Portfolio' => array( 'src/Elementor/Widget/Portfolio.php', 'widgets/Portfolio/vew-portfolio.css', 'vew-portfolio__inner' ),
			'Process'   => array( 'src/Elementor/Widget/Process.php', 'widgets/Process/vew-process.css', 'vew-process__inner' ),
			'Pricing'   => array( 'src/Elementor/Widget/Pricing.php', 'widgets/Pricing/vew-pricing.css', 'vew-pricing__inner' ),
			'Contact'   => array( 'src/Elementor/Widget/Contact.php', 'widgets/Contact/vew-contact.css', 'vew-contact__inner' ),
			'Footer'    => array( 'src/Elementor/Widget/Footer.php', 'widgets/Footer/vew-footer.css', 'vew-footer__inner' ),
			'CtaBanner' => array( 'src/Elementor/Widget/CtaBanner.php', 'widgets/CtaBanner/cta-banner.css', 'vew-cta__inner' ),
			'FeatureGrid' => array( 'src/Elementor/Widget/FeatureGrid.php', 'widgets/FeatureGrid/feature-grid.css', 'vew-feature-grid__inner' ),
			'Testimonials' => array( 'src/Elementor/Widget/Testimonials.php', 'widgets/Testimonials/testimonials.css', 'vew-testimonials__inner' ),
			'BlogPosts' => array( 'src/Elementor/Widget/BlogPosts.php', 'widgets/BlogPosts/vew-blog-posts.css', 'vew-blog-posts__inner' ),
			'PostGallery' => array( 'src/Elementor/Widget/PostGallery.php', 'widgets/PostGallery/vew-post-gallery.css', 'vew-post-gallery__inner' ),
		);
	}

	/**
	 * Every section surface owns a rendered internal shell with desktop and
	 * phone/tablet safe gutters. It must not rely on Elementor page padding.
	 *
	 * @dataProvider shell_cases
	 *
	 * @param string $widget_file Widget source path.
	 * @param string $css_file    Widget CSS path.
	 * @param string $shell_class Internal shell class.
	 */
	public function test_full_page_sections_own_safe_internal_shells( string $widget_file, string $css_file, string $shell_class ): void {
		$widget = $this->source( $widget_file );
		$css    = $this->source( $css_file );

		$this->assertStringContainsString( 'class="' . $shell_class . '"', $widget );
		$this->assertStringContainsString( '.' . $shell_class, $css );
		$this->assertStringContainsString( 'width: 100vw;', $css );
		$this->assertStringContainsString( '24px', $css );
		$this->assertStringContainsString( '20px', $css );
	}

	/**
	 * Content-section shells that carry the shared vertical-rhythm token.
	 * (Process / CTA / Footer / PageHero keep their own authored rhythm.)
	 *
	 * @return array<string, array{string}>
	 */
	public static function vertical_rhythm_cases(): array {
		return array(
			'FeatureGrid' => array( 'widgets/FeatureGrid/feature-grid.css' ),
			'Testimonials' => array( 'widgets/Testimonials/testimonials.css' ),
			'BlogPosts'  => array( 'widgets/BlogPosts/vew-blog-posts.css' ),
			'PostGallery' => array( 'widgets/PostGallery/vew-post-gallery.css' ),
			'Services'   => array( 'widgets/Services/vew-services.css' ),
			'Portfolio'  => array( 'widgets/Portfolio/vew-portfolio.css' ),
			'Pricing'    => array( 'widgets/Pricing/vew-pricing.css' ),
			'Contact'    => array( 'widgets/Contact/vew-contact.css' ),
			'Give'       => array( 'widgets/Give/vew-give.css' ),
			'Stats'      => array( 'widgets/Stats/vew-stats.css' ),
			'Split'      => array( 'widgets/Split/vew-split.css' ),
			'Gallery'    => array( 'widgets/Gallery/vew-gallery.css' ),
			'Menu'       => array( 'widgets/Menu/vew-menu.css' ),
			'Visit'      => array( 'widgets/Visit/vew-visit.css' ),
			'Watch'      => array( 'widgets/Watch/vew-watch.css' ),
		);
	}

	/**
	 * Every content-section inner shell carries a vertical rhythm via the
	 * --section-space-y token (base + mobile), so widgets are self-sufficient
	 * top/bottom, not just left/right.
	 *
	 * @dataProvider vertical_rhythm_cases
	 *
	 * @param string $css_file Widget CSS path.
	 */
	public function test_content_shells_own_vertical_rhythm( string $css_file ): void {
		$css = $this->source( $css_file );

		$this->assertStringContainsString( 'padding-block: var(--section-space-y', $css );
		$this->assertStringContainsString( 'padding-block: var(--section-space-y-mobile', $css );
	}

	/**
	 * FAQ uses its grid as the internal shell and protects long question text.
	 */
	public function test_faq_has_safe_shell_and_shrinkable_question_text(): void {
		$widget = $this->source( 'src/Elementor/Widget/Faq.php' );
		$css    = $this->source( 'widgets/Faq/faq.css' );

		$this->assertStringContainsString( 'vew-faq__question-text', $widget );
		$this->assertStringContainsString( 'width: 100vw;', $css );
		$this->assertStringContainsString( 'padding-inline: 24px;', $css );
		$this->assertStringContainsString( 'padding-inline: 20px;', $css );
		$this->assertStringContainsString( 'min-width: 0;', $css );
		$this->assertStringContainsString( 'white-space: normal;', $css );
		$this->assertStringContainsString( 'overflow-wrap: anywhere;', $css );
	}

	/**
	 * Elementor's top-level container, not the constrained widget, owns sticky.
	 *
	 * The wrapper differs by Elementor version: newer builds emit `.e-con` flex
	 * containers, older/legacy builds emit `.elementor-section`. Resolving only
	 * `.e-con` left the header scrolled away on legacy-structure pages, so the
	 * runtime must handle both.
	 */
	public function test_header_promotes_sticky_ownership_to_elementor_host(): void {
		$css = $this->source( 'widgets/Header/vew-header.css' );
		$js  = $this->source( 'widgets/Header/vew-header.js' );

		$this->assertStringContainsString( "'.e-con'", $js );
		$this->assertStringContainsString( "'.elementor-section'", $js );
		$this->assertStringContainsString( "root.querySelector( '.vew-header__brand' )", $js );
		$this->assertStringContainsString( "host.classList.add( 'vew-header-host' )", $js );
		$this->assertStringContainsString( "brand.addEventListener( 'click'", $js );
		$this->assertStringContainsString( '.vew-header-host', $css );
		$this->assertStringContainsString( 'position: sticky !important;', $css );
	}

	/**
	 * The frosted layer must live on the full-width sticky host, not the boxed
	 * inner header. If the translucent background sits on the 1240px inner
	 * shell, the sides of the viewport show no background when zoomed out.
	 */
	public function test_header_frosted_layer_spans_full_width_host(): void {
		$css = $this->source( 'widgets/Header/vew-header.css' );

		$this->assertStringContainsString( '.vew-header-host', $css );
		// The frosted layer must derive from the kit surface token (so dark
		// kits render a dark header), not a hardcoded light band.
		$this->assertStringContainsString( 'color-mix(in srgb, var(--surface, #fffdf9) 88%, transparent)', $css );
		$this->assertStringContainsString( 'backdrop-filter: blur(12px);', $css );
		$this->assertStringContainsString( '.vew-header {', $css );
		$this->assertStringContainsString( 'background: transparent;', $css );
	}

	/**
	 * Services indicator movement must account for its positioned left inset.
	 */
	public function test_services_indicator_aligns_with_selected_tab(): void {
		$js = $this->source( 'widgets/Services/vew-services.js' );

		$this->assertStringContainsString( 'active.getBoundingClientRect()', $js );
		$this->assertStringContainsString( 'indicator.parentElement.getBoundingClientRect()', $js );
		$this->assertStringContainsString( 'activeRect.width', $js );
		$this->assertStringContainsString( 'activeRect.left - switchRect.left - indicator.offsetLeft', $js );
	}

	/**
	 * Hidden service variants must not be restored by the grid display rule.
	 */
	public function test_services_hidden_panels_are_not_visually_rendered(): void {
		$css = $this->source( 'widgets/Services/vew-services.css' );

		$this->assertMatchesRegularExpression(
			'/\.vew-services__grid\[hidden\]\s*\{[^}]*display:\s*none\s*!important;/s',
			$css
		);
	}

	/**
	 * Source-equivalent desktop heading composition is a scoped widget default.
	 */
	public function test_services_and_portfolio_use_split_desktop_heading_defaults(): void {
		foreach ( array( 'Services/vew-services.css', 'Portfolio/vew-portfolio.css' ) as $file ) {
			$css = $this->source( 'widgets/' . $file );

			$this->assertStringContainsString( 'grid-template-areas:', $css );
			$this->assertStringContainsString( '"eyebrow intro"', $css );
			$this->assertStringContainsString( '"title intro"', $css );
			$this->assertStringContainsString( '@media (max-width: 1100px)', $css );
		}
	}

	/**
	 * Hero defaults retain the canonical 760/750px composition at <=820px.
	 */
	public function test_hero_mobile_defaults_match_canonical_composition(): void {
		$css = $this->source( 'widgets/Hero/hero.css' );

		$this->assertMatchesRegularExpression( '/@media \(max-width: 820px\).*?\.vew-hero\s*\{[^}]*height:\s*760px;[^}]*min-height:\s*0;/s', $css );
		$this->assertMatchesRegularExpression( '/@media \(max-width: 560px\).*?\.vew-hero\s*\{[^}]*height:\s*750px;/s', $css );
		$this->assertStringNotContainsString( 'font-size: 0.9rem;', $css );
		$this->assertStringContainsString( 'background: #fff;', $css );
		$this->assertStringContainsString( 'color: var(--brand-dark, #2a174f);', $css );

		$controls = $this->source( 'src/Elementor/Control/HeroStyleControls.php' );
		$this->assertMatchesRegularExpression( "/'hero_overlay_opacity'.*?'size'\\s*=>\\s*100/s", $controls );
	}

	/**
	 * Hero background must NOT hardcode a color default, else it gets baked
	 * into post CSS and overrides the kit's --brand-dark on every page.
	 */
	public function test_hero_background_color_has_no_hardcoded_default(): void {
		$controls = $this->source( 'src/Elementor/Control/HeroStyleControls.php' );

		// Isolate the hero_background_color control block and assert it has no 'default'.
		$pos = strpos( $controls, "'hero_background_color'" );
		$this->assertNotFalse( $pos, 'hero_background_color control not found' );

		// Take the segment until the next add_control / end_controls_section.
		$segment = substr( $controls, $pos, 600 );
		$end     = strpos( $segment, 'add_control', 20 );
		if ( false !== $end ) {
			$segment = substr( $segment, 0, $end );
		}
		$this->assertStringNotContainsString( "'default'", $segment, 'hero_background_color must not set a hardcoded default' );
	}

	/**
	 * Footer brand returns to the real home URL, not the sticky host anchor.
	 */
	public function test_footer_brand_targets_home_url(): void {
		$footer = $this->source( 'src/Elementor/Widget/Footer.php' );

		$this->assertStringContainsString( 'class="vew-footer__brand" href="<?php echo esc_url( home_url( \'/\' ) ); ?>"', $footer );
		$this->assertStringNotContainsString( 'class="vew-footer__brand" href="#top"', $footer );
		$this->assertStringNotContainsString( 'href="#home"', $footer );
	}

	/**
	 * Interactive controls reset theme defaults for all pointer/focus states.
	 */
	public function test_interactive_controls_do_not_inherit_theme_button_colors(): void {
		$header   = $this->source( 'widgets/Header/vew-header.css' );
		$services = $this->source( 'widgets/Services/vew-services.css' );
		$faq      = $this->source( 'widgets/Faq/faq.css' );
		$contact  = $this->source( 'widgets/Contact/vew-contact.css' );

		$this->assertStringContainsString( '.vew-header__toggle:hover', $header );
		$this->assertStringContainsString( '.vew-header__toggle:focus-visible', $header );
		$this->assertStringContainsString( '.vew-services__tab:hover', $services );
		$this->assertStringContainsString( '.vew-services__tab:focus-visible', $services );
		$this->assertStringContainsString( '.vew-faq__question:hover', $faq );
		$this->assertStringContainsString( '.vew-faq__question:focus-visible', $faq );
		$this->assertStringContainsString( '.vew-contact__submit:focus', $contact );
		$this->assertStringContainsString( '.vew-contact__submit:focus-visible', $contact );
		$this->assertStringContainsString( 'background: transparent;', $faq );
		$this->assertStringContainsString( 'color: var(--ink, #201a2e);', $faq );
	}
}
