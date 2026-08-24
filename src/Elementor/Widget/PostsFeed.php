<?php
/**
 * PostsFeed widget.
 *
 * A dynamic grid of the latest posts from a chosen post type. Runs a
 * WP_Query in render(), showing thumbnail, title, excerpt, date, and link.
 * The first dynamic widget in the suite — every queried value is escaped.
 *
 * @package Vector\ElementorWidgets\Elementor\Widget
 */

declare( strict_types=1 );

namespace Vector\ElementorWidgets\Elementor\Widget;

use Vector\ElementorWidgets\Elementor\Control\PostsFeedContentControls;
use Vector\ElementorWidgets\Elementor\Control\PostsFeedStyleControls;
use Vector\ElementorWidgets\Elementor\Component\SectionHeading;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * PostsFeed widget.
 */
final class PostsFeed extends BaseWidget {

	/**
	 * Widget slug — stable forever.
	 *
	 * @return string
	 */
	public function get_name(): string {
		return 'vew-posts-feed';
	}

	/**
	 * Widget title.
	 *
	 * @return string
	 */
	public function get_title(): string {
		return __( 'Posts / Blog Feed', 'vector-elementor-widgets' );
	}

	/**
	 * Widget icon.
	 *
	 * @return string
	 */
	public function get_icon(): string {
		return 'eicon-post-list';
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
		return array( 'post', 'blog', 'feed', 'news', 'latest', 'article' );
	}

	/**
	 * Register controls.
	 *
	 * @return void
	 */
	protected function register_controls(): void {
		( new PostsFeedContentControls() )->register( $this );
		( new PostsFeedStyleControls() )->register( $this );
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
			array_merge(
				SectionHeading::setting_types(),
				array(
					'post_type'    => 'string',
					'count'        => 'int',
					'columns'      => 'string',
					'order'        => 'string',
					'show_excerpt' => 'string',
					'show_date'    => 'string',
				)
			)
		);

		$post_type    = isset( $safe['post_type'] ) ? sanitize_key( (string) $safe['post_type'] ) : 'post';
		$count        = isset( $safe['count'] ) ? max( 1, min( 12, $safe['count'] ) ) : 3;
		$columns      = isset( $safe['columns'] ) ? sanitize_key( (string) $safe['columns'] ) : '3';
		$columns      = in_array( $columns, array( '2', '3', '4' ), true ) ? $columns : '3';
		$order        = isset( $safe['order'] ) && 'asc' === $safe['order'] ? 'ASC' : 'DESC';
		$show_excerpt = ( isset( $safe['show_excerpt'] ) && 'yes' === $safe['show_excerpt'] );
		$show_date    = ( isset( $safe['show_date'] ) && 'yes' === $safe['show_date'] );

		$heading = SectionHeading::render(
			$safe,
			array( 'block_class' => 'vew-posts-feed' )
		);

		$query = new \WP_Query(
			array(
				'post_type'      => post_type_exists( $post_type ) ? $post_type : 'post',
				'posts_per_page' => $count,
				'orderby'        => 'date',
				'order'          => $order,
				'ignore_sticky_posts' => true,
			)
		);

		?>
		<section class="vew-posts-feed vew-posts-feed--<?php echo esc_attr( $columns ); ?>" aria-label="<?php echo esc_attr__( 'Latest posts', 'vector-elementor-widgets' ); ?>">
			<div class="vew-posts-feed__inner">
				<?php
				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- component escapes all values.
				echo $heading;
				?>
				<?php if ( $query->have_posts() ) : ?>
					<div class="vew-posts-feed__grid">
						<?php
						while ( $query->have_posts() ) :
							$query->the_post();
							?>
							<article class="vew-posts-feed__card">
								<?php if ( has_post_thumbnail() ) : ?>
									<a class="vew-posts-feed__thumb" href="<?php the_permalink(); ?>" tabindex="-1" aria-hidden="true">
										<?php the_post_thumbnail( 'large', array( 'loading' => 'lazy' ) ); ?>
									</a>
								<?php endif; ?>
								<div class="vew-posts-feed__body">
									<?php if ( $show_date ) : ?>
										<time class="vew-posts-feed__date" datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>"><?php echo esc_html( get_the_date() ); ?></time>
									<?php endif; ?>
									<h3 class="vew-posts-feed__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
									<?php if ( $show_excerpt ) : ?>
										<p class="vew-posts-feed__excerpt"><?php echo esc_html( wp_trim_words( get_the_excerpt(), 22 ) ); ?></p>
									<?php endif; ?>
									<a class="vew-posts-feed__more" href="<?php the_permalink(); ?>"><?php echo esc_html__( 'Read more', 'vector-elementor-widgets' ); ?> <span aria-hidden="true">→</span></a>
								</div>
							</article>
						<?php endwhile; ?>
					</div>
					<?php wp_reset_postdata(); ?>
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
		return array( 'vew-section-heading', 'vew-posts-feed' );
	}
}
