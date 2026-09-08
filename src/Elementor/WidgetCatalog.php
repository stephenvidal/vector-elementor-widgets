<?php
/**
 * Widget catalog.
 *
 * The source of truth for admin widget metadata (slug, class, title,
 * description, icon). Runtime class and asset registration remain explicit in
 * Plugin; the scaffold tool updates all three coordinated locations.
 *
 * The catalog is deliberately static and Elementor-free: it holds plain
 * data, not widget instances. The admin screen can render the catalog
 * without Elementor being loaded.
 *
 * @package Vector\ElementorWidgets\Elementor
 */

declare( strict_types=1 );

namespace Vector\ElementorWidgets\Elementor;

use Vector\ElementorWidgets\Elementor\Widget\Contact;
use Vector\ElementorWidgets\Elementor\Widget\CtaBanner;
use Vector\ElementorWidgets\Elementor\Widget\Faq;
use Vector\ElementorWidgets\Elementor\Widget\FeatureGrid;
use Vector\ElementorWidgets\Elementor\Widget\Footer;
use Vector\ElementorWidgets\Elementor\Widget\Header;
use Vector\ElementorWidgets\Elementor\Widget\Hero;
use Vector\ElementorWidgets\Elementor\Widget\Portfolio;
use Vector\ElementorWidgets\Elementor\Widget\Pricing;
use Vector\ElementorWidgets\Elementor\Widget\Process;
use Vector\ElementorWidgets\Elementor\Widget\Services;
use Vector\ElementorWidgets\Elementor\Widget\Testimonials;
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

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Widget catalog — static metadata for every widget.
 */
final class WidgetCatalog {

	/**
	 * All widget definitions, in registration order.
	 *
	 * Each entry:
	 *   slug        — the Elementor widget name (stable, permanent).
	 *   class       — fully-qualified widget class.
	 *   title       — human title shown in the admin + Elementor panel.
	 *   description — one-line admin description.
	 *   icon        — Elementor icon id (eicon-*).
	 *
	 * @return array<int, array{
	 *     slug: string,
	 *     class: string,
	 *     title: string,
	 *     description: string,
	 *     icon: string
	 * }>
	 */
	public static function all(): array {
		return array(
			array(
				'slug'        => 'vew-cta-banner',
				'class'       => CtaBanner::class,
				'title'       => __( 'CTA Banner', 'vector-elementor-widgets' ),
				'description' => __( 'A conversion banner with a heading, description, and up to two calls to action.', 'vector-elementor-widgets' ),
				'icon'        => 'eicon-call-to-action',
			),
			array(
				'slug'        => 'vew-hero',
				'class'       => Hero::class,
				'title'       => __( 'Hero', 'vector-elementor-widgets' ),
				'description' => __( 'A full-bleed hero with eyebrow, headline, body copy, CTAs, and a trust list.', 'vector-elementor-widgets' ),
				'icon'        => 'eicon-header',
			),
			array(
				'slug'        => 'vew-feature-grid',
				'class'       => FeatureGrid::class,
				'title'       => __( 'Feature Grid', 'vector-elementor-widgets' ),
				'description' => __( 'A uniform grid of feature cards with a heading block.', 'vector-elementor-widgets' ),
				'icon'        => 'eicon-gallery-grid',
			),
			array(
				'slug'        => 'vew-testimonials',
				'class'       => Testimonials::class,
				'title'       => __( 'Testimonials', 'vector-elementor-widgets' ),
				'description' => __( 'A responsive grid of review cards with quotes, authors, and star ratings.', 'vector-elementor-widgets' ),
				'icon'        => 'eicon-testimonial',
			),
			array(
				'slug'        => 'vew-faq',
				'class'       => Faq::class,
				'title'       => __( 'FAQ', 'vector-elementor-widgets' ),
				'description' => __( 'An accessible accordion of questions and answers.', 'vector-elementor-widgets' ),
				'icon'        => 'eicon-help',
			),
			array(
				'slug'        => 'vew-header',
				'class'       => Header::class,
				'title'       => __( 'Header', 'vector-elementor-widgets' ),
				'description' => __( 'A sticky site header with brand, navigation, and a call to action.', 'vector-elementor-widgets' ),
				'icon'        => 'eicon-header',
			),
			array(
				'slug'        => 'vew-services',
				'class'       => Services::class,
				'title'       => __( 'Services', 'vector-elementor-widgets' ),
				'description' => __( 'A variant-toggle grid of service cards (Design / Build / Launch).', 'vector-elementor-widgets' ),
				'icon'        => 'eicon-toggle',
			),
			array(
				'slug'        => 'vew-portfolio',
				'class'       => Portfolio::class,
				'title'       => __( 'Portfolio (Manual)', 'vector-elementor-widgets' ),
				'description' => __( 'A hand-curated grid of portfolio cards with image, tag, title, description, and link.', 'vector-elementor-widgets' ),
				'icon'        => 'eicon-gallery-grid',
			),
			array(
				'slug'        => 'vew-process',
				'class'       => Process::class,
				'title'       => __( 'Process', 'vector-elementor-widgets' ),
				'description' => __( 'A numbered list of process steps on a dark background.', 'vector-elementor-widgets' ),
				'icon'        => 'eicon-time-line',
			),
			array(
				'slug'        => 'vew-pricing',
				'class'       => Pricing::class,
				'title'       => __( 'Pricing', 'vector-elementor-widgets' ),
				'description' => __( 'A grid of pricing cards with a featured plan.', 'vector-elementor-widgets' ),
				'icon'        => 'eicon-price-table',
			),
			array(
				'slug'        => 'vew-contact',
				'class'       => Contact::class,
				'title'       => __( 'Contact', 'vector-elementor-widgets' ),
				'description' => __( 'A contact section with details and a consultation request form.', 'vector-elementor-widgets' ),
				'icon'        => 'eicon-mail',
			),
			array(
				'slug'        => 'vew-footer',
				'class'       => Footer::class,
				'title'       => __( 'Footer', 'vector-elementor-widgets' ),
				'description' => __( 'A site footer with brand, link columns, and a bottom bar.', 'vector-elementor-widgets' ),
				'icon'        => 'eicon-footer',
			),
			array(
				'slug'        => 'vew-stats',
				'class'       => Stats::class,
				'title'       => __( 'Stats', 'vector-elementor-widgets' ),
				'description' => __( 'A band of key numbers/metrics with a section heading.', 'vector-elementor-widgets' ),
				'icon'        => 'eicon-number-field',
			),
			array(
				'slug'        => 'vew-split',
				'class'       => Split::class,
				'title'       => __( 'Split', 'vector-elementor-widgets' ),
				'description' => __( 'Two-column image + text split section with a heading.', 'vector-elementor-widgets' ),
				'icon'        => 'eicon-image-box',
			),
			array(
				'slug'        => 'vew-gallery',
				'class'       => Gallery::class,
				'title'       => __( 'Gallery', 'vector-elementor-widgets' ),
				'description' => __( 'Mosaic, grid, carousel, or editorial image gallery with lightbox.', 'vector-elementor-widgets' ),
				'icon'        => 'eicon-gallery-grid',
			),
			array(
				'slug'        => 'vew-menu',
				'class'       => Menu::class,
				'title'       => __( 'Menu', 'vector-elementor-widgets' ),
				'description' => __( 'Restaurant/food menu with items, descriptions, and prices.', 'vector-elementor-widgets' ),
				'icon'        => 'eicon-menu-bar',
			),
			array(
				'slug'        => 'vew-watch',
				'class'       => Watch::class,
				'title'       => __( 'Watch', 'vector-elementor-widgets' ),
				'description' => __( 'Watch/live-stream band with a play-pin image block.', 'vector-elementor-widgets' ),
				'icon'        => 'eicon-play',
			),
			array(
				'slug'        => 'vew-give',
				'class'       => Give::class,
				'title'       => __( 'Give', 'vector-elementor-widgets' ),
				'description' => __( 'Donation band with a giving CTA and supporting message.', 'vector-elementor-widgets' ),
				'icon'        => 'eicon-heart',
			),
			array(
				'slug'        => 'vew-visit',
				'class'       => Visit::class,
				'title'       => __( 'Visit', 'vector-elementor-widgets' ),
				'description' => __( 'Visit/location band with a map image and contact CTA.', 'vector-elementor-widgets' ),
				'icon'        => 'eicon-map-pin',
			),
			array(
				'slug'        => 'vew-page-hero',
				'class'       => PageHero::class,
				'title'       => __( 'PageHero', 'vector-elementor-widgets' ),
				'description' => __( 'Interior page hero with breadcrumb, title, and eyebrow.', 'vector-elementor-widgets' ),
				'icon'        => 'eicon-page',
			),
			array(
				'slug'        => 'vew-logo-bar',
				'class'       => LogoBar::class,
				'title'       => __( 'Logo / Client Bar', 'vector-elementor-widgets' ),
				'description' => __( 'A "trusted by" logo row or auto-scrolling marquee.', 'vector-elementor-widgets' ),
				'icon'        => 'eicon-logo',
			),
			array(
				'slug'        => 'vew-team-grid',
				'class'       => TeamGrid::class,
				'title'       => __( 'Team Grid', 'vector-elementor-widgets' ),
				'description' => __( 'A grid of team/staff cards with photos and social links.', 'vector-elementor-widgets' ),
				'icon'        => 'eicon-person',
			),
			array(
				'slug'        => 'vew-posts-feed',
				'class'       => PostsFeed::class,
				'title'       => __( 'Posts / Blog Feed', 'vector-elementor-widgets' ),
				'description' => __( 'A dynamic grid of the latest posts from any post type.', 'vector-elementor-widgets' ),
				'icon'        => 'eicon-post-list',
			),
			array(
				'slug'        => 'vew-newsletter',
				'class'       => Newsletter::class,
				'title'       => __( 'Newsletter Signup', 'vector-elementor-widgets' ),
				'description' => __( 'An email capture form that stores subscribers.', 'vector-elementor-widgets' ),
				'icon'        => 'eicon-mail',
			),
			array(
				'slug'        => 'vew-google-map',
				'class'       => GoogleMap::class,
				'title'       => __( 'Google Map', 'vector-elementor-widgets' ),
				'description' => __( 'A privacy-aware Google Maps embed with location and zoom.', 'vector-elementor-widgets' ),
				'icon'        => 'eicon-google-maps',
			),
			array(
				'slug'        => 'vew-video-embed',
				'class'       => VideoEmbed::class,
				'title'       => __( 'Video / Embed', 'vector-elementor-widgets' ),
				'description' => __( 'Responsive YouTube, Vimeo, or direct-file video embed.', 'vector-elementor-widgets' ),
				'icon'        => 'eicon-video-playlist',
			),
			array(
				'slug'        => 'vew-countdown',
				'class'       => Countdown::class,
				'title'       => __( 'Countdown', 'vector-elementor-widgets' ),
				'description' => __( 'A live days/hours/minutes/seconds countdown timer.', 'vector-elementor-widgets' ),
				'icon'        => 'eicon-countdown',
			),
			array(
				'slug'        => 'vew-before-after',
				'class'       => BeforeAfter::class,
				'title'       => __( 'Before / After', 'vector-elementor-widgets' ),
				'description' => __( 'A draggable before/after image comparison slider.', 'vector-elementor-widgets' ),
				'icon'        => 'eicon-image-before-after',
			),
			array(
				'slug'        => 'vew-timeline',
				'class'       => Timeline::class,
				'title'       => __( 'Timeline', 'vector-elementor-widgets' ),
				'description' => __( 'A vertical milestone timeline (alternating or left column).', 'vector-elementor-widgets' ),
				'icon'        => 'eicon-time-line',
			),
			array(
				'slug'        => 'vew-portfolio-index',
				'class'       => PortfolioIndex::class,
				'title'       => __( 'Portfolio Index (Auto)', 'vector-elementor-widgets' ),
				'description' => __( 'A dynamic, auto-updating grid of the website-factory portfolio pages, each with a kit colour swatch.', 'vector-elementor-widgets' ),
				'icon'        => 'eicon-gallery-grid',
			),
			array(
				'slug'        => 'vew-blog-posts',
				'class'       => BlogPosts::class,
				'title'       => __( 'Blog Posts', 'vector-elementor-widgets' ),
				'description' => __( 'A Blog Posts component.', 'vector-elementor-widgets' ),
				'icon'        => 'eicon-post-list',
			),
		);
	}

	/**
	 * Find a widget definition by its class name.
	 *
	 * @param string $class Fully-qualified widget class.
	 *
	 * @return array{
	 *     slug: string,
	 *     class: string,
	 *     title: string,
	 *     description: string,
	 *     icon: string
	 * }|null
	 */
	public static function find_by_class( string $class ): ?array {
		foreach ( self::all() as $def ) {
			if ( $def['class'] === $class ) {
				return $def;
			}
		}
		return null;
	}

	/**
	 * Widget class names, in registration order.
	 *
	 * @return array<int, string>
	 */
	public static function classes(): array {
		return array_map(
			static fn ( array $def ): string => $def['class'],
			self::all()
		);
	}
}
