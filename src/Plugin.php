<?php
/**
 * Plugin kernel.
 *
 * Phase 1: real kernel that wires the service container, registers the
 * Elementor integration (category + widget registry + asset manager) and
 * the Elementor detector. Replacement for a Phase 0 placeholder.
 *
 * @package Vector\ElementorWidgets
 */

declare( strict_types=1 );

namespace Vector\ElementorWidgets;

use Vector\ElementorWidgets\Admin\WidgetManagerPage;
use Vector\ElementorWidgets\Admin\SiteKitManagerPage;
use Vector\ElementorWidgets\Admin\SiteKitMetaBox;
use Vector\ElementorWidgets\Elementor\AssetManager;
use Vector\ElementorWidgets\Elementor\Detector as ElementorDetector;
use Vector\ElementorWidgets\Elementor\Plugin as ElementorPlugin;
use Vector\ElementorWidgets\Elementor\SiteKit;
use Vector\ElementorWidgets\Elementor\Widget\CtaBanner;
use Vector\ElementorWidgets\Elementor\Widget\Faq;
use Vector\ElementorWidgets\Elementor\Widget\FeatureGrid;
use Vector\ElementorWidgets\Elementor\Widget\Hero;
use Vector\ElementorWidgets\Elementor\Widget\Testimonials;
use Vector\ElementorWidgets\Elementor\Widget\PostGallery;
use Vector\ElementorWidgets\Elementor\Widget\BlogPosts;
use Vector\ElementorWidgets\Elementor\Widget\PortfolioIndex;
use Vector\ElementorWidgets\Elementor\Widget\Timeline;
use Vector\ElementorWidgets\Elementor\Widget\BeforeAfter;
use Vector\ElementorWidgets\Elementor\Widget\Countdown;
use Vector\ElementorWidgets\Elementor\Widget\VideoEmbed;
use Vector\ElementorWidgets\Elementor\Widget\GoogleMap;
use Vector\ElementorWidgets\Elementor\Widget\Newsletter;
use Vector\ElementorWidgets\Elementor\Widget\PostsFeed;
use Vector\ElementorWidgets\Elementor\Widget\TeamGrid;
use Vector\ElementorWidgets\Elementor\Widget\LogoBar;
use Vector\ElementorWidgets\Elementor\Widget\PageHero;
use Vector\ElementorWidgets\Elementor\Widget\Visit;
use Vector\ElementorWidgets\Elementor\Widget\Give;
use Vector\ElementorWidgets\Elementor\Widget\Watch;
use Vector\ElementorWidgets\Elementor\Widget\Menu;
use Vector\ElementorWidgets\Elementor\Widget\Gallery;
use Vector\ElementorWidgets\Elementor\Widget\Split;
use Vector\ElementorWidgets\Elementor\Widget\Stats;
use Vector\ElementorWidgets\Elementor\Widget\Header;
use Vector\ElementorWidgets\Elementor\Widget\Services;
use Vector\ElementorWidgets\Elementor\Widget\Portfolio;
use Vector\ElementorWidgets\Elementor\Widget\Process;
use Vector\ElementorWidgets\Elementor\Widget\Pricing;
use Vector\ElementorWidgets\Elementor\Widget\Contact;
use Vector\ElementorWidgets\Elementor\Widget\Footer;
use Vector\ElementorWidgets\Elementor\WidgetRegistry;
use Vector\ElementorWidgets\Kit\KitCssCompiler;
use Vector\ElementorWidgets\Kit\KitMigrator;
use Vector\ElementorWidgets\Kit\KitStore;
use Vector\ElementorWidgets\Support\Compatibility;
use Vector\ElementorWidgets\Support\ContactFormHandler;
use Vector\ElementorWidgets\Support\NewsletterFormHandler;
use Vector\ElementorWidgets\Support\ServiceContainer;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Plugin kernel.
 */
final class Plugin {

	/**
	 * Singleton instance.
	 *
	 * @var self|null
	 */
	private static ?self $instance = null;

	/**
	 * Service container.
	 *
	 * @var ServiceContainer
	 */
	private ServiceContainer $container;

	/**
	 * Boot state — true once {@see boot()} has been called.
	 *
	 * @var bool
	 */
	private bool $booted = false;

	/**
	 * Private constructor — singleton.
	 */
	private function __construct() {
		$this->container = new ServiceContainer();
		$this->register_services();
	}

	/**
	 * Return the singleton, creating it on first call.
	 */
	public static function instance(): self {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Fetch a registered service by id.
	 *
	 * @param string $id Service id (e.g. 'compatibility').
	 *
	 * @return object
	 */
	public function container_get( string $id ): object {
		return $this->container->get( $id );
	}

	/**
	 * Reset the singleton — test-only.
	 *
	 * @internal
	 *
	 * @return void
	 */
	public static function reset_instance(): void {
		self::$instance = null;
	}

	/**
	 * Get the service container (for tests + later phases).
	 *
	 * @return ServiceContainer
	 */
	public function container(): ServiceContainer {
		return $this->container;
	}

	/**
	 * Wire all services into the container.
	 *
	 * Phase 1 services:
	 *   - compatibility       (stateless)
	 *   - asset_manager       (per-request — singleton)
	 *   - widget_registry     (per-request — singleton)
	 *   - elementor_plugin    (per-request — singleton)
	 *   - elementor_detector  (per-request — singleton)
	 *
	 * @return void
	 */
	private function register_services(): void {
		$this->container->register( 'compatibility', static fn (): Compatibility => new Compatibility() );
		$this->container->register(
			'asset_manager',
			static fn (): AssetManager => new AssetManager(
				defined( 'VEW_PLUGIN_URL' ) ? (string) VEW_PLUGIN_URL : '',
				defined( 'VEW_PLUGIN_DIR' ) ? (string) VEW_PLUGIN_DIR : ''
			)
		);
		$this->container->register( 'widget_registry', static fn (): WidgetRegistry => new WidgetRegistry() );
		// Register the initial widget suite.
		// Phase 1: CTA Banner. Phase 2: Hero. Phase 3: Feature Grid, Testimonials, FAQ.
		$this->container->get( 'widget_registry' )->register( CtaBanner::class );
		$this->container->get( 'widget_registry' )->register( Hero::class );
		$this->container->get( 'widget_registry' )->register( FeatureGrid::class );
		$this->container->get( 'widget_registry' )->register( Testimonials::class );
		$this->container->get( 'widget_registry' )->register( Faq::class );
		$this->container->get( 'widget_registry' )->register( PostGallery::class );
		$this->container->get( 'widget_registry' )->register( BlogPosts::class );
		$this->container->get( 'widget_registry' )->register( PortfolioIndex::class );
		$this->container->get( 'widget_registry' )->register( Timeline::class );
		$this->container->get( 'widget_registry' )->register( BeforeAfter::class );
		$this->container->get( 'widget_registry' )->register( Countdown::class );
		$this->container->get( 'widget_registry' )->register( VideoEmbed::class );
		$this->container->get( 'widget_registry' )->register( GoogleMap::class );
		$this->container->get( 'widget_registry' )->register( Newsletter::class );
		$this->container->get( 'widget_registry' )->register( PostsFeed::class );
		$this->container->get( 'widget_registry' )->register( TeamGrid::class );
		$this->container->get( 'widget_registry' )->register( LogoBar::class );
		$this->container->get( 'widget_registry' )->register( PageHero::class );
		$this->container->get( 'widget_registry' )->register( Visit::class );
		$this->container->get( 'widget_registry' )->register( Give::class );
		$this->container->get( 'widget_registry' )->register( Watch::class );
		$this->container->get( 'widget_registry' )->register( Menu::class );
		$this->container->get( 'widget_registry' )->register( Gallery::class );
		$this->container->get( 'widget_registry' )->register( Split::class );
		$this->container->get( 'widget_registry' )->register( Stats::class );
		$this->container->get( 'widget_registry' )->register( Header::class );
		$this->container->get( 'widget_registry' )->register( Services::class );
		$this->container->get( 'widget_registry' )->register( Portfolio::class );
		$this->container->get( 'widget_registry' )->register( Process::class );
		$this->container->get( 'widget_registry' )->register( Pricing::class );
		$this->container->get( 'widget_registry' )->register( Contact::class );
		$this->container->get( 'widget_registry' )->register( Footer::class );
		$this->container->register(
			'elementor_plugin',
			static fn ( ServiceContainer $c ): ElementorPlugin => new ElementorPlugin(
				$c->get( 'widget_registry' ),
				$c->get( 'asset_manager' ),
			)
		);
		$this->container->register( 'elementor_detector', static fn (): ElementorDetector => new ElementorDetector() );
		$this->container->register(
			'site_kit',
			static fn ( ServiceContainer $c ): SiteKit => new SiteKit(
				$c->get( 'kit_store' ),
				new KitCssCompiler(),
				defined( 'VEW_PLUGIN_URL' ) ? (string) VEW_PLUGIN_URL : '',
				defined( 'VEW_PLUGIN_DIR' ) ? (string) VEW_PLUGIN_DIR : ''
			)
		);
		$this->container->register(
			'kit_store',
			static fn (): KitStore => new KitStore()
		);
		$this->container->register(
			'widget_manager_page',
			static fn ( ServiceContainer $c ): WidgetManagerPage => new WidgetManagerPage(
				$c->get( 'widget_registry' )
			)
		);
		$this->container->register(
			'site_kit_manager_page',
			static fn ( ServiceContainer $c ): SiteKitManagerPage => new SiteKitManagerPage(
				$c->get( 'kit_store' )
			)
		);
		$this->container->register(
			'site_kit_meta_box',
			static fn ( ServiceContainer $c ): SiteKitMetaBox => new SiteKitMetaBox(
				$c->get( 'kit_store' )
			)
		);
		$this->container->register(
			'kit_migrator',
			static fn ( ServiceContainer $c ): KitMigrator => new KitMigrator(
				$c->get( 'kit_store' ),
				defined( 'VEW_PLUGIN_DIR' ) ? (string) VEW_PLUGIN_DIR . 'assets/kits/' : ''
			)
		);
		$this->container->register(
			'contact_form_handler',
			static fn (): ContactFormHandler => new ContactFormHandler()
		);
		$this->container->register(
			'newsletter_form_handler',
			static fn (): NewsletterFormHandler => new NewsletterFormHandler()
		);
	}

	/**
	 * Boot the kernel. Idempotent.
	 *
	 * @return void
	 */
	public function boot(): void {
		if ( $this->booted ) {
			return;
		}
		$this->booted = true;

		// Late init: Elementor detector + integration.
		// Hooked at priority 20 so most other plugins are loaded first.
		add_action( 'plugins_loaded', $this->register_elementor_hooks( ... ), 20 );

		// Register widget assets centrally (loaded only when the widget is on a page).
		add_action( 'init', $this->register_assets( ... ) );

		// Register the kit CPT (kit storage + per-page theming).
		$this->container->get( 'kit_store' )->register_hooks();

		// Seed legacy CSS kits into the DB (idempotent, admin context only).
		$this->container->get( 'kit_migrator' )->register_hooks();

		// Enqueue the site-kit design tokens (theming).
		$this->container->get( 'site_kit' )->register_hooks();

		// Contact form submission handler (admin-post).
		$this->container->get( 'contact_form_handler' )->register_hooks();

		// Newsletter signup handler (admin-post).
		$this->container->get( 'newsletter_form_handler' )->register_hooks();

		// Register the admin Widget Manager + Site Kit screens (admin context only).
		if ( is_admin() ) {
			$this->container->get( 'widget_manager_page' )->register_hooks();
			$this->container->get( 'site_kit_manager_page' )->register_hooks();
			$this->container->get( 'site_kit_meta_box' )->register_hooks();
		}
	}

	/**
	 * Register all widget assets centrally.
	 *
	 * @return void
	 */
	private function register_assets(): void {
		$assets = $this->container->get( 'asset_manager' );
		$assets->register_style( 'vew-section-heading', 'assets/components/section-heading.css' );
		$assets->register_style( 'vew-cta-banner', 'widgets/CtaBanner/cta-banner.css' );
		$assets->register_style( 'vew-hero', 'widgets/Hero/hero.css' );
		$assets->register_style( 'vew-feature-grid', 'widgets/FeatureGrid/feature-grid.css' );
		$assets->register_style( 'vew-testimonials', 'widgets/Testimonials/testimonials.css' );
		$assets->register_style( 'vew-faq', 'widgets/Faq/faq.css' );
		$assets->register_script( 'vew-faq', 'widgets/Faq/faq.js' );
		$assets->register_style( 'vew-post-gallery', 'widgets/PostGallery/vew-post-gallery.css' );
		$assets->register_script( 'vew-post-gallery', 'widgets/PostGallery/vew-post-gallery.js' );
		$assets->register_style( 'vew-blog-posts', 'widgets/BlogPosts/vew-blog-posts.css' );
		$assets->register_script( 'vew-blog-posts', 'widgets/BlogPosts/vew-blog-posts.js' );
		$assets->register_style( 'vew-portfolio-index', 'widgets/PortfolioIndex/vew-portfolio-index.css' );
		$assets->register_script( 'vew-portfolio-index', 'widgets/PortfolioIndex/vew-portfolio-index.js' );
		$assets->register_style( 'vew-timeline', 'widgets/Timeline/vew-timeline.css' );
		$assets->register_style( 'vew-before-after', 'widgets/BeforeAfter/vew-before-after.css' );
		$assets->register_script( 'vew-before-after', 'widgets/BeforeAfter/vew-before-after.js' );
		$assets->register_style( 'vew-countdown', 'widgets/Countdown/vew-countdown.css' );
		$assets->register_script( 'vew-countdown', 'widgets/Countdown/vew-countdown.js' );
		$assets->register_style( 'vew-video-embed', 'widgets/VideoEmbed/vew-video-embed.css' );
		$assets->register_style( 'vew-google-map', 'widgets/GoogleMap/vew-google-map.css' );
		$assets->register_style( 'vew-newsletter', 'widgets/Newsletter/vew-newsletter.css' );
		$assets->register_style( 'vew-posts-feed', 'widgets/PostsFeed/vew-posts-feed.css' );
		$assets->register_style( 'vew-team-grid', 'widgets/TeamGrid/vew-team-grid.css' );
		$assets->register_style( 'vew-logo-bar', 'widgets/LogoBar/vew-logo-bar.css' );
		$assets->register_style( 'vew-page-hero', 'widgets/PageHero/vew-page-hero.css' );
		$assets->register_style( 'vew-visit', 'widgets/Visit/vew-visit.css' );
		$assets->register_style( 'vew-give', 'widgets/Give/vew-give.css' );
		$assets->register_style( 'vew-watch', 'widgets/Watch/vew-watch.css' );
		$assets->register_style( 'vew-menu', 'widgets/Menu/vew-menu.css' );
		$assets->register_style( 'vew-gallery', 'widgets/Gallery/vew-gallery.css' );
		$assets->register_script( 'vew-gallery', 'widgets/Gallery/vew-gallery.js' );
		$assets->register_style( 'vew-split', 'widgets/Split/vew-split.css' );
		$assets->register_style( 'vew-stats', 'widgets/Stats/vew-stats.css' );
		$assets->register_script( 'vew-stats', 'widgets/Stats/vew-stats.js' );
		$assets->register_style( 'vew-header', 'widgets/Header/vew-header.css' );
		$assets->register_script( 'vew-header', 'widgets/Header/vew-header.js' );
		$assets->register_style( 'vew-services', 'widgets/Services/vew-services.css' );
		$assets->register_script( 'vew-services', 'widgets/Services/vew-services.js' );
		$assets->register_style( 'vew-portfolio', 'widgets/Portfolio/vew-portfolio.css' );
		$assets->register_style( 'vew-process', 'widgets/Process/vew-process.css' );
		$assets->register_style( 'vew-pricing', 'widgets/Pricing/vew-pricing.css' );
		$assets->register_style( 'vew-contact', 'widgets/Contact/vew-contact.css' );
		$assets->register_style( 'vew-footer', 'widgets/Footer/vew-footer.css' );
	}

	/**
	 * Register the Elementor detector + integration if Elementor is active
	 * and meets the minimum version.
	 *
	 * @return void
	 */
	private function register_elementor_hooks(): void {
		if ( ! function_exists( 'did_action' ) || ! function_exists( 'add_action' ) ) {
			return;
		}
		$compat = $this->container->get( 'compatibility' );
		if ( ! method_exists( $compat, 'check' ) ) {
			return;
		}
		$report = $compat->check();
		if ( ! $report->hard_compatible ) {
			// Hard PHP/WP failure — surface it, then register nothing.
			$this->render_hard_incompat_notice( $report->hard_incompat );
			return;
		}

		// Always surface the detector notice when Elementor is missing/old.
		$this->container->get( 'elementor_detector' )->register_hooks();

		if ( ! $report->elementor_meets_min || null === $report->elementor_version ) {
			return; // Elementor missing/old — detector handles the notice.
		}
		$this->container->get( 'elementor_plugin' )->register_hooks();
	}

	/**
	 * Render the hard PHP/WP incompatibility admin notice.
	 *
	 * Runs only on a supported PHP where the kernel can parse, so a WP
	 * version below the minimum is the common trigger. Mirrors the
	 * Detector's notice style.
	 *
	 * @param string|null $message Hard-incompatibility message.
	 *
	 * @return void
	 */
	private function render_hard_incompat_notice( ?string $message ): void {
		if ( ! is_admin() || '' === (string) $message ) {
			return;
		}
		add_action(
			'admin_notices',
			static function () use ( $message ): void {
				printf(
					'<div class="notice notice-error vew-hard-incompat"><p>%s</p></div>',
					esc_html( (string) $message )
				);
			}
		);
	}
}
