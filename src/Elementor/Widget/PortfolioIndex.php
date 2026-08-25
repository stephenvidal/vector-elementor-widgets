<?php
/**
 * PortfolioIndex widget.
 *
 * A dynamic grid of the website-factory portfolio pages. Queries every published
 * page carrying the `_vew_factory_site` meta marker (set by the factory pipeline),
 * reads each page's `_vew_site_kit` for a brand-colour swatch, and renders a
 * responsive grid of cards linking to each site. Gives Stephen a single index
 * to navigate every site the factory has produced.
 *
 * @package Vector\ElementorWidgets\Elementor\Widget
 */

declare( strict_types=1 );

namespace Vector\ElementorWidgets\Elementor\Widget;

use Vector\ElementorWidgets\Elementor\Control\PortfolioIndexContentControls;
use Vector\ElementorWidgets\Elementor\Control\PortfolioIndexStyleControls;
use Vector\ElementorWidgets\Elementor\Component\SectionHeading;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * PortfolioIndex widget.
 */
final class PortfolioIndex extends BaseWidget {

	/**
	 * Widget slug — stable forever.
	 *
	 * @return string
	 */
	public function get_name(): string {
		return 'vew-portfolio-index';
	}

	/**
	 * Widget title.
	 *
	 * @return string
	 */
	public function get_title(): string {
		return __( 'Portfolio Index', 'vector-elementor-widgets' );
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
		return array( 'portfolio', 'index', 'grid', 'gallery', 'sites', 'catalog' );
	}

	/**
	 * Register controls.
	 *
	 * @return void
	 */
	protected function register_controls(): void {
		( new PortfolioIndexContentControls() )->register( $this );
		( new PortfolioIndexStyleControls() )->register( $this );
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
					'columns' => 'string',
				)
			)
		);

		$columns = isset( $safe['columns'] ) ? sanitize_key( (string) $safe['columns'] ) : '3';
		$columns = in_array( $columns, array( '2', '3', '4' ), true ) ? $columns : '3';

		$heading = SectionHeading::render(
			$safe,
			array( 'block_class' => 'vew-portfolio-index' )
		);

		// Query every page tagged as a factory site, newest first.
		$query = new \WP_Query(
			array(
				'post_type'           => 'page',
				'post_status'         => 'publish',
				'posts_per_page'      => -1,
				'orderby'             => 'date',
				'order'               => 'DESC',
				'ignore_sticky_posts' => true,
				'meta_key'            => '_vew_factory_site', // phpcs:ignore WordPress.DB.SlowDBQuery -- exact-meta, low cardinality.
				'meta_value'          => '1',
				'meta_compare'        => '=',
			)
		);

		$kit_store = new \Vector\ElementorWidgets\Kit\KitStore();
		?>
		<section class="vew-portfolio-index vew-portfolio-index--<?php echo esc_attr( $columns ); ?>" aria-label="<?php echo esc_attr__( 'Portfolio index', 'vector-elementor-widgets' ); ?>">
			<div class="vew-portfolio-index__inner">
				<?php
				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- component escapes all values.
				echo $heading;
				?>
				<?php if ( $query->have_posts() ) : ?>
					<div class="vew-portfolio-index__grid">
						<?php
						while ( $query->have_posts() ) :
							$query->the_post();
							$site_kit_slug = get_post_meta( get_the_ID(), '_vew_site_kit', true );
							$brand         = '';
							if ( $site_kit_slug && $kit_store->has( $site_kit_slug ) ) {
								$kit   = $kit_store->get( $site_kit_slug );
								$brand = $kit->token( 'brand' );
							}
							$swatch_style = '' !== $brand ? ' style="background-color:' . esc_attr( $brand ) . '"' : '';
							?>
							<article class="vew-portfolio-index__card">
								<a class="vew-portfolio-index__card-link" href="<?php the_permalink(); ?>">
									<span class="vew-portfolio-index__swatch"<?php echo $swatch_style; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- esc_attr'd above. ?> aria-hidden="true"></span>
									<h3 class="vew-portfolio-index__title"><?php the_title(); ?></h3>
								</a>
							</article>
							<?php
						endwhile;
						wp_reset_postdata();
						?>
					</div>
				<?php else : ?>
					<p class="vew-portfolio-index__empty"><?php echo esc_html__( 'No factory sites yet.', 'vector-elementor-widgets' ); ?></p>
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
		return array( 'vew-section-heading', 'vew-portfolio-index' );
	}
}
