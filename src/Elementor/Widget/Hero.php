<?php
/**
 * Hero widget.
 *
 * Phase 2 widget — a direct port of the Derby hero component. Establishes the
 * content/style control conventions, the trust-list repeater, and the
 * full-bleed scoped CSS. Per the control-mapping contract, `data-copy` →
 * TEXT/WYSIWYG controls, `data-list` → REPEATER, CTAs → TEXT + URL.
 *
 * @package Vector\ElementorWidgets\Elementor\Widget
 */

declare( strict_types=1 );

namespace Vector\ElementorWidgets\Elementor\Widget;

use Vector\ElementorWidgets\Elementor\Control\HeroContentControls;
use Vector\ElementorWidgets\Elementor\Control\HeroStyleControls;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Hero widget.
 */
final class Hero extends BaseWidget {

	/**
	 * Widget slug — stable forever (changing it breaks saved pages).
	 *
	 * @return string
	 */
	public function get_name(): string {
		return 'vew-hero';
	}

	/**
	 * Widget title.
	 *
	 * @return string
	 */
	public function get_title(): string {
		return __( 'Hero', 'vector-elementor-widgets' );
	}

	/**
	 * Widget icon.
	 *
	 * @return string
	 */
	public function get_icon(): string {
		return 'eicon-header';
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
		return array( 'hero', 'header', 'banner', 'jumbotron', 'eyebrow', 'cta' );
	}

	/**
	 * Register controls.
	 *
	 * @return void
	 */
	protected function register_controls(): void {
		( new HeroContentControls() )->register( $this );
		( new HeroStyleControls() )->register( $this );
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
				'eyebrow'            => 'string',
				'headline'           => 'html',
				'copy'               => 'string',
				'primary_cta_text'   => 'string',
				'primary_cta_url'    => 'url',
				'secondary_cta_text' => 'string',
				'secondary_cta_url'  => 'url',
				'trust_list'         => 'array',
			)
		);

		$eyebrow      = $safe['eyebrow'] ?? '';
		$headline     = $safe['headline'] ?? '';
		$copy         = $safe['copy'] ?? '';
		$primary_txt  = $safe['primary_cta_text'] ?? '';
		$primary_url  = $safe['primary_cta_url'] ?? '';
		$secondary_tx = $safe['secondary_cta_text'] ?? '';
		$secondary_ur = $safe['secondary_cta_url'] ?? '';
		$trust        = $safe['trust_list'] ?? array();

		// Background image (MEDIA control) — resolves to the selected attachment URL.
		$bg_image = '';
		if ( isset( $settings['hero_background_image']['url'] ) && is_string( $settings['hero_background_image']['url'] ) ) {
			$bg_image = esc_url( $settings['hero_background_image']['url'] );
		}
		$style = '' !== $bg_image ? ' style="background-image:url(' . $bg_image . ');background-size:cover;background-position:center;"' : '';
		?>
		<section class="vew-hero" aria-label="hero"<?php echo $style; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- $style is a fully-escaped url + static attributes. ?>>
			<div class="vew-hero__overlay" aria-hidden="true"></div>
			<div class="vew-hero__content">
				<?php if ( '' !== $eyebrow ) : ?>
					<p class="vew-hero__eyebrow"><?php echo esc_html( $eyebrow ); ?></p>
				<?php endif; ?>
				<?php if ( '' !== $headline ) : ?>
					<h1 class="vew-hero__headline"><?php echo wp_kses_post( $headline ); ?></h1>
				<?php endif; ?>
				<?php if ( '' !== $copy ) : ?>
					<p class="vew-hero__copy"><?php echo esc_html( $copy ); ?></p>
				<?php endif; ?>
				<?php if ( '' !== $primary_txt || '' !== $secondary_tx ) : ?>
					<div class="vew-hero__actions">
						<?php if ( '' !== $primary_txt && '' !== $primary_url ) : ?>
							<a class="vew-hero__button vew-hero__button--primary" href="<?php echo esc_url( $primary_url ); ?>"><?php echo esc_html( $primary_txt ); ?></a>
						<?php endif; ?>
						<?php if ( '' !== $secondary_tx && '' !== $secondary_ur ) : ?>
							<a class="vew-hero__link" href="<?php echo esc_url( $secondary_ur ); ?>"><?php echo esc_html( $secondary_tx ); ?> <span aria-hidden="true">↘</span></a>
						<?php endif; ?>
					</div>
				<?php endif; ?>
				<?php if ( is_array( $trust ) && count( $trust ) > 0 ) : ?>
					<ul class="vew-hero__trust">
						<?php foreach ( $trust as $point ) : ?>
							<?php
							$text = isset( $point['text'] ) ? sanitize_text_field( (string) $point['text'] ) : '';
							if ( '' === $text ) {
								continue;
							}
							?>
							<li><?php echo esc_html( $text ); ?></li>
						<?php endforeach; ?>
					</ul>
				<?php endif; ?>
			</div>
		</section>
		<?php
	}

	/**
	 * Declare the stylesheet dependency so it loads only when this widget
	 * is on the page.
	 *
	 * @return array<int, string>
	 */
	public function get_style_depends(): array {
		return array( 'vew-hero' );
	}
}
