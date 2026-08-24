<?php
/**
 * Testimonials widget.
 *
 * Phase 3 — direct port of the Derby reviews component: a heading + a
 * responsive grid of review cards (quote, author, role, star rating).
 * Exercises the REPEATER control with an int-typed star field.
 *
 * @package Vector\ElementorWidgets\Elementor\Widget
 */

declare( strict_types=1 );

namespace Vector\ElementorWidgets\Elementor\Widget;

use Vector\ElementorWidgets\Elementor\Control\TestimonialsContentControls;
use Vector\ElementorWidgets\Elementor\Control\TestimonialsStyleControls;
use Vector\ElementorWidgets\Elementor\Component\SectionHeading;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Testimonials widget.
 */
final class Testimonials extends BaseWidget {

	/**
	 * Widget slug — stable forever.
	 *
	 * @return string
	 */
	public function get_name(): string {
		return 'vew-testimonials';
	}

	/**
	 * Widget title.
	 *
	 * @return string
	 */
	public function get_title(): string {
		return __( 'Testimonials', 'vector-elementor-widgets' );
	}

	/**
	 * Widget icon.
	 *
	 * @return string
	 */
	public function get_icon(): string {
		return 'eicon-testimonial';
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
		return array( 'testimonial', 'review', 'quote', 'social proof' );
	}

	/**
	 * Register controls.
	 *
	 * @return void
	 */
	protected function register_controls(): void {
		( new TestimonialsContentControls() )->register( $this );
		( new TestimonialsStyleControls() )->register( $this );
	}

	/**
	 * Render the widget.
	 *
	 * @return void
	 */
	protected function render(): void {
		$settings = $this->get_settings_for_display();
		$safe     = $this->sanitize_settings(
			$settings,
			array_merge(
				SectionHeading::setting_types(),
				array(
					'reviews' => 'array',
				)
			)
		);

		$reviews = $safe['reviews'] ?? array();

		$heading = SectionHeading::render(
			$safe,
			array( 'block_class' => 'vew-testimonials' )
		);

		// JSON-LD Review schema for SEO-rich results.
		$reviews_json = array();
		if ( is_array( $reviews ) ) {
			foreach ( $reviews as $review ) {
				$quote = isset( $review['quote'] ) ? sanitize_text_field( (string) $review['quote'] ) : '';
				if ( '' === $quote ) {
					continue;
				}
				$author = isset( $review['author'] ) ? sanitize_text_field( (string) $review['author'] ) : '';
				$stars  = isset( $review['stars'] ) ? absint( $review['stars'] ) : 5;
				$reviews_json[] = array(
					'@type'         => 'Review',
					'reviewBody'    => $quote,
					'author'        => array(
						'@type' => 'Person',
						'name'  => '' !== $author ? $author : __( 'Verified customer', 'vector-elementor-widgets' ),
					),
					'reviewRating'  => array(
						'@type'       => 'Rating',
						'ratingValue' => min( 5, max( 1, $stars ) ),
						'bestRating'  => 5,
					),
				);
			}
		}

		?>
		<section class="vew-testimonials" aria-label="testimonials">
			<div class="vew-testimonials__inner">
			<?php
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- component escapes all values.
			echo $heading;
			?>

			<?php if ( count( $reviews_json ) > 0 ) : ?>
				<div class="vew-testimonials__grid">
					<?php foreach ( $reviews as $review ) : ?>
						<?php
						$quote  = isset( $review['quote'] ) ? sanitize_text_field( (string) $review['quote'] ) : '';
						$author = isset( $review['author'] ) ? sanitize_text_field( (string) $review['author'] ) : '';
						$role   = isset( $review['role'] ) ? sanitize_text_field( (string) $review['role'] ) : '';
						$stars  = isset( $review['stars'] ) ? absint( $review['stars'] ) : 5;
						$avatar = isset( $review['avatar']['url'] ) ? esc_url( (string) $review['avatar']['url'] ) : '';
						if ( '' === $quote ) {
							continue;
						}
						?>
						<article class="vew-testimonial">
							<div class="vew-testimonial__stars" role="img" aria-label="<?php echo esc_attr( sprintf( /* translators: %d: star rating (1-5). */ __( '%d out of 5 stars', 'vector-elementor-widgets' ), $stars ) ); ?>">
								<?php echo esc_html( str_repeat( '★', min( 5, max( 0, $stars ) ) ) ); ?>
							</div>
							<blockquote class="vew-testimonial__quote"><?php echo esc_html( $quote ); ?></blockquote>
							<div class="vew-testimonial__meta">
								<?php if ( '' !== $avatar ) : ?>
									<img class="vew-testimonial__avatar" src="<?php echo esc_url( $avatar ); ?>" alt="<?php echo esc_attr( '' !== $author ? $author : __( 'Customer avatar', 'vector-elementor-widgets' ) ); ?>" loading="lazy">
								<?php endif; ?>
								<div class="vew-testimonial__meta-text">
									<?php if ( '' !== $author ) : ?>
										<strong class="vew-testimonial__author"><?php echo esc_html( $author ); ?></strong>
									<?php endif; ?>
									<?php if ( '' !== $role ) : ?>
										<small class="vew-testimonial__role"><?php echo esc_html( $role ); ?></small>
									<?php endif; ?>
								</div>
							</div>
						</article>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
			</div>
		</section>

		<?php if ( count( $reviews_json ) > 0 ) : ?>
			<script type="application/ld+json">
			<?php
			echo wp_json_encode( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- JSON-safe output.
				array(
					'@context' => 'https://schema.org',
					'@graph'   => $reviews_json,
				)
			);
			?>
			</script>
		<?php endif; ?>
		<?php
	}

	/**
	 * Declare the stylesheet dependency.
	 *
	 * @return array<int, string>
	 */
	public function get_style_depends(): array {
		return array( 'vew-section-heading', 'vew-testimonials' );
	}
}
