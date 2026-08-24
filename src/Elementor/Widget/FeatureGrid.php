<?php
/**
 * Feature Grid widget.
 *
 * Phase 3 — port of the vidal-studio services component: a heading block +
 * a uniform grid of equal feature cards (no large first card). Exercises the
 * REPEATER control and a responsive grid that collapses at 820px.
 *
 * @package Vector\ElementorWidgets\Elementor\Widget
 */

declare( strict_types=1 );

namespace Vector\ElementorWidgets\Elementor\Widget;

use Vector\ElementorWidgets\Elementor\Control\FeatureGridContentControls;
use Vector\ElementorWidgets\Elementor\Control\FeatureGridStyleControls;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Feature Grid widget.
 */
final class FeatureGrid extends BaseWidget {

	/**
	 * Widget slug — stable forever.
	 *
	 * @return string
	 */
	public function get_name(): string {
		return 'vew-feature-grid';
	}

	/**
	 * Widget title.
	 *
	 * @return string
	 */
	public function get_title(): string {
		return __( 'Feature Grid', 'vector-elementor-widgets' );
	}

	/**
	 * Widget icon.
	 *
	 * @return string
	 */
	public function get_icon(): string {
		return 'eicon-gallery-grid';
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
		return array( 'feature', 'grid', 'cards', 'services', 'mosaic' );
	}

	/**
	 * Register controls.
	 *
	 * @return void
	 */
	protected function register_controls(): void {
		( new FeatureGridContentControls() )->register( $this );
		( new FeatureGridStyleControls() )->register( $this );
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
				'eyebrow' => 'string',
				'title'   => 'string',
				'title_accent' => 'string',
				'intro'   => 'string',
				'features' => 'array',
			)
		);

		$eyebrow  = $safe['eyebrow'] ?? '';
		$title    = $safe['title'] ?? '';
		$accent   = $safe['title_accent'] ?? '';
		$intro    = $safe['intro'] ?? '';
		$features = $safe['features'] ?? array();

		?>
		<section class="vew-feature-grid" aria-label="features">
			<div class="vew-feature-grid__inner">
			<?php if ( '' !== $eyebrow || '' !== $title || '' !== $intro ) : ?>
				<header class="vew-feature-grid__heading">
					<?php if ( '' !== $eyebrow ) : ?>
						<p class="vew-feature-grid__eyebrow"><?php echo esc_html( $eyebrow ); ?></p>
					<?php endif; ?>
					<?php if ( '' !== $title || '' !== $accent ) : ?>
						<h2 class="vew-feature-grid__title">
							<?php
							if ( '' !== $title ) :
								?>
								<span class="vew-section-heading__before"><?php echo esc_html( $title ); ?></span><?php endif; ?>
							<?php
							if ( '' !== $accent ) :
								?>
								<em class="vew-section-heading__accent"><?php echo esc_html( $accent ); ?></em><?php endif; ?>
						</h2>
					<?php endif; ?>
					<?php if ( '' !== $intro ) : ?>
						<p class="vew-feature-grid__intro"><?php echo esc_html( $intro ); ?></p>
					<?php endif; ?>
				</header>
			<?php endif; ?>

			<?php if ( is_array( $features ) && count( $features ) > 0 ) : ?>
				<div class="vew-feature-grid__cards">
					<?php foreach ( $features as $card ) : ?>
						<?php
						$c_title  = isset( $card['title'] ) ? sanitize_text_field( (string) $card['title'] ) : '';
						$c_text   = isset( $card['text'] ) ? sanitize_text_field( (string) $card['text'] ) : '';
						$c_detail = isset( $card['detail'] ) ? sanitize_text_field( (string) $card['detail'] ) : '';
						$c_number = isset( $card['number'] ) ? sanitize_text_field( (string) $card['number'] ) : '';
						$c_url    = isset( $card['link']['url'] ) ? esc_url_raw( (string) $card['link']['url'] ) : '';
						if ( '' === $c_title ) {
							continue;
						}
						?>
						<article class="vew-feature-card">
							<?php if ( '' !== $c_number ) : ?>
								<span class="vew-feature-card__number"><?php echo esc_html( $c_number ); ?></span>
							<?php endif; ?>
							<h3 class="vew-feature-card__title"><?php echo esc_html( $c_title ); ?></h3>
							<?php if ( '' !== $c_text ) : ?>
								<p class="vew-feature-card__text"><?php echo esc_html( $c_text ); ?></p>
							<?php endif; ?>
							<?php if ( '' !== $c_detail ) : ?>
								<?php if ( '' !== $c_url ) : ?>
									<small class="vew-feature-card__detail"><a class="vew-feature-card__link" href="<?php echo esc_url( $c_url ); ?>"><?php echo esc_html( $c_detail ); ?> →</a></small>
								<?php else : ?>
									<small class="vew-feature-card__detail"><?php echo esc_html( $c_detail ); ?></small>
								<?php endif; ?>
							<?php endif; ?>
						</article>
					<?php endforeach; ?>
				</div>
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
		return array( 'vew-feature-grid' );
	}
}
