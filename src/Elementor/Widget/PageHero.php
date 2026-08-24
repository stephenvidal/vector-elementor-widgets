<?php
/**
 * PageHero widget.
 *
 * Port of the gslg page-hero component: a breadcrumb nav, eyebrow, H1 title,
 * and lead paragraph used on subpages. Per the control-mapping contract
 * `data-copy` → TEXT.
 *
 * @package Vector\ElementorWidgets\Elementor\Widget
 */

declare( strict_types=1 );

namespace Vector\ElementorWidgets\Elementor\Widget;

use Vector\ElementorWidgets\Elementor\Control\PageHeroContentControls;
use Vector\ElementorWidgets\Elementor\Control\PageHeroStyleControls;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * PageHero widget.
 */
final class PageHero extends BaseWidget {

	/**
	 * Widget slug — stable forever.
	 *
	 * @return string
	 */
	public function get_name(): string {
		return 'vew-page-hero';
	}

	/**
	 * Widget title.
	 *
	 * @return string
	 */
	public function get_title(): string {
		return __( 'Page Hero', 'vector-elementor-widgets' );
	}

	/**
	 * Widget icon.
	 *
	 * @return string
	 */
	public function get_icon(): string {
		return 'eicon-page';
	}

	/**
	 * Widget category.
	 *
	 * @return array<int, string>
	 */
	public function get_categories(): array {
		return array( 'vector-widgets' );
	}

	/**
	 * Widget keywords.
	 *
	 * @return array<int, string>
	 */
	public function get_keywords(): array {
		return array( 'page', 'hero', 'breadcrumb', 'subpage', 'banner' );
	}

	/**
	 * Register controls.
	 *
	 * @return void
	 */
	protected function register_controls(): void {
		( new PageHeroContentControls() )->register( $this );
		( new PageHeroStyleControls() )->register( $this );
	}

	/**
	 * Render the widget.
	 *
	 * The ONLY render path. No `_content_template()` override.
	 *
	 * @return void
	 */
	protected function render(): void {
		$settings = $this->get_settings_for_display();
		$safe     = $this->sanitize_settings(
			$settings,
			array(
				'breadcrumb_home_text' => 'string',
				'breadcrumb_home_url'  => 'url',
				'breadcrumb_mid_text'  => 'string',
				'breadcrumb_mid_url'   => 'url',
				'breadcrumb_current'   => 'string',
				'eyebrow'              => 'string',
				'title'                => 'string',
				'lead'                 => 'string',
			)
		);

		$home_text = $safe['breadcrumb_home_text'] ?? __( 'Home', 'vector-elementor-widgets' );
		$home_url  = $safe['breadcrumb_home_url'] ?? '';
		$mid_text  = $safe['breadcrumb_mid_text'] ?? '';
		$mid_url   = $safe['breadcrumb_mid_url'] ?? '';
		$current   = $safe['breadcrumb_current'] ?? '';
		$eyebrow   = $safe['eyebrow'] ?? '';
		$title     = $safe['title'] ?? '';
		$lead      = $safe['lead'] ?? '';
		?>
		<section class="vew-page-hero" aria-label="page hero">
			<div class="vew-page-hero__inner">
				<?php if ( '' !== $home_text || '' !== $current ) : ?>
					<nav class="vew-page-hero__breadcrumb" aria-label="Breadcrumb">
						<?php if ( '' !== $home_text && '' !== $home_url ) : ?>
							<a href="<?php echo esc_url( $home_url ); ?>"><?php echo esc_html( $home_text ); ?></a>
						<?php endif; ?>
						<?php if ( '' !== $mid_text && '' !== $mid_url ) : ?>
							<span aria-hidden="true">/</span>
							<a href="<?php echo esc_url( $mid_url ); ?>"><?php echo esc_html( $mid_text ); ?></a>
						<?php endif; ?>
						<?php if ( '' !== $current ) : ?>
							<span aria-hidden="true">/</span>
							<span aria-current="page"><?php echo esc_html( $current ); ?></span>
						<?php endif; ?>
					</nav>
				<?php endif; ?>

				<?php if ( '' !== $eyebrow ) : ?>
					<p class="vew-page-hero__eyebrow"><?php echo esc_html( $eyebrow ); ?></p>
				<?php endif; ?>

				<?php if ( '' !== $title ) : ?>
					<h1 class="vew-page-hero__title"><?php echo esc_html( $title ); ?></h1>
				<?php endif; ?>

				<?php if ( '' !== $lead ) : ?>
					<p class="vew-page-hero__lead"><?php echo esc_html( $lead ); ?></p>
				<?php endif; ?>
			</div>
		</section>
		<?php
	}

	/**
	 * Declare the stylesheet dependency.
	 *
	 * @return array<int, string>
	 */
	public function get_style_depends(): array {
		return array( 'vew-page-hero' );
	}
}
