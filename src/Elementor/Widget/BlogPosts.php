<?php
/**
 * BlogPosts widget.
 *
 * A full-featured dynamic blog/posts grid: optional heading block, a shared
 * query configuration (post type, count, order, category/author filters), and
 * a responsive card grid with thumbnail, title, excerpt, meta, and read-more.
 * Supports a "Load more" gate for bounded initial render.
 *
 * @package Vector\ElementorWidgets\Elementor\Widget
 */

declare( strict_types=1 );

namespace Vector\ElementorWidgets\Elementor\Widget;

use Vector\ElementorWidgets\Elementor\Control\BlogPostsContentControls;
use Vector\ElementorWidgets\Elementor\Control\BlogPostsStyleControls;
use Vector\ElementorWidgets\Elementor\Component\QueryControls;
use Vector\ElementorWidgets\Elementor\Component\SectionHeading;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * BlogPosts widget.
 */
final class BlogPosts extends BaseWidget {

	/**
	 * Widget slug — stable forever.
	 *
	 * @return string
	 */
	public function get_name(): string {
		return 'vew-blog-posts';
	}

	/**
	 * Widget title.
	 *
	 * @return string
	 */
	public function get_title(): string {
		return __( 'Blog Posts', 'vector-elementor-widgets' );
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
		return array( 'post', 'blog', 'feed', 'news', 'latest', 'article', 'grid' );
	}

	/**
	 * Register controls.
	 *
	 * @return void
	 */
	protected function register_controls(): void {
		( new BlogPostsContentControls() )->register( $this );
		( new BlogPostsStyleControls() )->register( $this );
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
				QueryControls::setting_types(),
				array(
					'columns'        => 'string',
					'visible'        => 'int',
					'per_click'      => 'int',
					'show_excerpt'   => 'string',
					'excerpt_length' => 'int',
					'show_date'      => 'string',
					'show_author'    => 'string',
					'show_category'  => 'string',
					'show_read_more' => 'string',
				)
			)
		);

		$columns = isset( $safe['columns'] ) ? sanitize_key( (string) $safe['columns'] ) : '3';
		$columns = in_array( $columns, array( '2', '3', '4' ), true ) ? $columns : '3';

		$visible   = isset( $safe['visible'] ) ? max( 1, min( 24, $safe['visible'] ) ) : 6;
		$per_click = isset( $safe['per_click'] ) ? max( 1, min( 12, $safe['per_click'] ) ) : 3;

		$show_excerpt   = ( isset( $safe['show_excerpt'] ) && 'yes' === $safe['show_excerpt'] );
		$excerpt_length = isset( $safe['excerpt_length'] ) ? max( 10, min( 60, $safe['excerpt_length'] ) ) : 22;
		$show_date      = ( isset( $safe['show_date'] ) && 'yes' === $safe['show_date'] );
		$show_author    = ( isset( $safe['show_author'] ) && 'yes' === $safe['show_author'] );
		$show_category  = ( isset( $safe['show_category'] ) && 'yes' === $safe['show_category'] );
		$show_read_more = ( isset( $safe['show_read_more'] ) && 'yes' === $safe['show_read_more'] );

		$heading = SectionHeading::render(
			$safe,
			array( 'block_class' => 'vew-blog-posts' )
		);

		$query = new \WP_Query( QueryControls::query_args( $safe ) );

		// Collect card data so we can gate which are shown behind "Load more".
		$cards = array();
		if ( $query->have_posts() ) {
			while ( $query->have_posts() ) {
				$query->the_post();
				$cards[] = array(
					'permalink' => (string) get_permalink(),
					'title'     => (string) get_the_title(),
					'thumb'     => has_post_thumbnail() ? (string) get_the_post_thumbnail_url( get_the_ID(), 'large' ) : '',
					'date'      => (string) get_the_date(),
					'date_iso'  => (string) get_the_date( 'c' ),
					'excerpt'   => (string) wp_trim_words( get_the_excerpt(), $excerpt_length ),
					'author'    => (string) get_the_author(),
					'category'  => $this->first_category(),
				);
			}
			wp_reset_postdata();
		}

		$has_more = count( $cards ) > $visible;
		$shown    = $has_more ? array_slice( $cards, 0, $visible ) : $cards;
		$hidden   = $has_more ? array_slice( $cards, $visible ) : array();
		$slot_id  = wp_unique_id( 'vew-blog-posts-' );
		?>
		<section class="vew-blog-posts vew-blog-posts--<?php echo esc_attr( $columns ); ?>" aria-label="<?php echo esc_attr__( 'Blog posts', 'vector-elementor-widgets' ); ?>" data-vew-blog-posts data-vew-visible="<?php echo esc_attr( $visible ); ?>" data-vew-per-click="<?php echo esc_attr( $per_click ); ?>">
			<div class="vew-blog-posts__inner">
				<?php
				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- component escapes all values.
				echo $heading;
				?>
				<?php if ( ! empty( $cards ) ) : ?>
					<div class="vew-blog-posts__grid" id="<?php echo esc_attr( $slot_id ); ?>-grid">
						<?php foreach ( $shown as $card ) : ?>
							<?php $this->render_card( $card, false, $show_excerpt, $show_date, $show_author, $show_category, $show_read_more ); ?>
						<?php endforeach; ?>
						<?php foreach ( $hidden as $card ) : ?>
							<?php $this->render_card( $card, true, $show_excerpt, $show_date, $show_author, $show_category, $show_read_more ); ?>
						<?php endforeach; ?>
					</div>
					<?php if ( $has_more ) : ?>
						<button type="button" class="vew-blog-posts__load-more" data-vew-blog-more aria-expanded="false" aria-controls="<?php echo esc_attr( $slot_id ); ?>-grid">
							<?php echo esc_html__( 'Load more', 'vector-elementor-widgets' ); ?>
						</button>
					<?php endif; ?>
				<?php else : ?>
					<p class="vew-blog-posts__empty"><?php echo esc_html__( 'No posts found.', 'vector-elementor-widgets' ); ?></p>
				<?php endif; ?>
			</div>
		</section>
		<?php
	}

	/**
	 * Render a single blog card.
	 *
	 * @param array<string, string> $card           Card data.
	 * @param bool                  $hidden         Whether the card starts behind the load-more gate.
	 * @param bool                  $show_excerpt    Show the excerpt.
	 * @param bool                  $show_date       Show the date.
	 * @param bool                  $show_author     Show the author.
	 * @param bool                  $show_category   Show the category.
	 * @param bool                  $show_read_more Show the read-more link.
	 *
	 * @return void
	 */
	private function render_card( array $card, bool $hidden, bool $show_excerpt, bool $show_date, bool $show_author, bool $show_category, bool $show_read_more ): void {
		?>
		<article class="vew-blog-posts__card<?php echo $hidden ? ' vew-blog-posts__card--hidden' : ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- boolean-driven. ?>">
			<?php if ( '' !== $card['thumb'] ) : ?>
				<a class="vew-blog-posts__thumb" href="<?php echo esc_url( $card['permalink'] ); ?>" tabindex="-1" aria-hidden="true">
					<img src="<?php echo esc_url( $card['thumb'] ); ?>" alt="" loading="lazy">
				</a>
			<?php endif; ?>
			<div class="vew-blog-posts__body">
				<?php if ( $show_date || $show_author || $show_category ) : ?>
					<div class="vew-blog-posts__meta">
						<?php if ( $show_date ) : ?>
							<time class="vew-blog-posts__date" datetime="<?php echo esc_attr( $card['date_iso'] ); ?>"><?php echo esc_html( $card['date'] ); ?></time>
						<?php endif; ?>
						<?php if ( $show_author ) : ?>
							<span class="vew-blog-posts__author"><?php echo esc_html( $card['author'] ); ?></span>
						<?php endif; ?>
						<?php if ( $show_category && '' !== $card['category'] ) : ?>
							<span class="vew-blog-posts__category"><?php echo esc_html( $card['category'] ); ?></span>
						<?php endif; ?>
					</div>
				<?php endif; ?>
				<h3 class="vew-blog-posts__title"><a href="<?php echo esc_url( $card['permalink'] ); ?>"><?php echo esc_html( $card['title'] ); ?></a></h3>
				<?php if ( $show_excerpt ) : ?>
					<p class="vew-blog-posts__excerpt"><?php echo esc_html( $card['excerpt'] ); ?></p>
				<?php endif; ?>
				<?php if ( $show_read_more ) : ?>
					<a class="vew-blog-posts__more" href="<?php echo esc_url( $card['permalink'] ); ?>"><?php echo esc_html__( 'Read more', 'vector-elementor-widgets' ); ?> <span aria-hidden="true">→</span></a>
				<?php endif; ?>
			</div>
		</article>
		<?php
	}

	/**
	 * First category name for the current post in the loop.
	 *
	 * @return string
	 */
	private function first_category(): string {
		$categories = get_the_category();
		if ( is_array( $categories ) && ! empty( $categories ) ) {
			return (string) $categories[0]->name;
		}
		return '';
	}

	/**
	 * Declare the stylesheet dependency.
	 *
	 * @return array<int, string>
	 */
	public function get_style_depends(): array {
		return array( 'vew-section-heading', 'vew-blog-posts' );
	}

	/**
	 * Declare the script dependency (load-more gating).
	 *
	 * @return array<int, string>
	 */
	public function get_script_depends(): array {
		return array( 'vew-blog-posts' );
	}
}
