<?php
/**
 * PortfolioIndex widget.
 *
 * A dynamic grid of the website-factory portfolio pages. Queries every published
 * page carrying the `_vew_factory_site` meta marker (set by the factory pipeline),
 * reads each page's `_vew_site_kit` for a brand-colour swatch and `_portfolio_preview`
 * for an auto-updated screenshot, and renders a responsive grid of cards linking to
 * each site. Cards are gated behind a "Load more" button to keep bandwidth bounded.
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
					'columns'         => 'string',
					'visible'         => 'int',
					'visible_mobile'  => 'int',
					'per_click'       => 'int',
				)
			)
		);

		$columns = isset( $safe['columns'] ) ? sanitize_key( (string) $safe['columns'] ) : '3';
		$columns = in_array( $columns, array( '2', '3', '4' ), true ) ? $columns : '3';
		$visible        = isset( $safe['visible'] ) ? max( 1, min( 48, $safe['visible'] ) ) : 6;
		$visible_mobile = isset( $safe['visible_mobile'] ) ? max( 1, min( 24, $safe['visible_mobile'] ) ) : 4;
		$per_click      = isset( $safe['per_click'] ) ? max( 1, min( 24, $safe['per_click'] ) ) : 6;

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

		// Collect card data so we can gate which are shown behind "Load more".
		$cards = array();
		if ( $query->have_posts() ) {
			while ( $query->have_posts() ) {
				$query->the_post();
				$site_kit_slug = get_post_meta( get_the_ID(), '_vew_site_kit', true );
				$brand         = '';
				if ( $site_kit_slug && $kit_store->has( $site_kit_slug ) ) {
					$kit   = $kit_store->get( $site_kit_slug );
					$brand = $kit->token( 'brand' );
				}
				$preview_raw = get_post_meta( get_the_ID(), '_portfolio_preview', true );
				$preview_url = '';
				// Accept either an attachment ID (int) or a full URL string. The
				// factory stores IDs; tolerate URL values so a URL doesn't silently
				// fall back to the swatch (casting a URL to int is 0).
				if ( is_numeric( $preview_raw ) && (int) $preview_raw > 0 ) {
					$src = wp_get_attachment_image_src( (int) $preview_raw, 'large' );
					if ( is_array( $src ) && ! empty( $src[0] ) ) {
						$preview_url = $src[0];
					}
				} elseif ( is_string( $preview_raw ) && '' !== $preview_raw ) {
					$candidate = esc_url_raw( $preview_raw );
					if ( '' !== $candidate ) {
						$preview_url = $candidate;
					}
				}
				$cards[] = array(
					'permalink' => (string) get_permalink(),
					'title'     => (string) get_the_title(),
					'brand'     => $brand,
					'preview'   => $preview_url,
				);
			}
			wp_reset_postdata();
		}

		$has_more = count( $cards ) > $visible;
		$shown    = $has_more ? array_slice( $cards, 0, $visible ) : $cards;
		$hidden   = $has_more ? array_slice( $cards, $visible ) : array();
		$slot_id  = wp_unique_id( 'vew-portfolio-index-' );
		?>
		<section class="vew-portfolio-index vew-portfolio-index--<?php echo esc_attr( $columns ); ?>" aria-label="<?php echo esc_attr__( 'Portfolio index', 'vector-elementor-widgets' ); ?>" data-vew-portfolio-index data-vew-visible="<?php echo esc_attr( $visible ); ?>" data-vew-visible-mobile="<?php echo esc_attr( $visible_mobile ); ?>" data-vew-per-click="<?php echo esc_attr( $per_click ); ?>">
			<div class="vew-portfolio-index__inner">
				<?php
				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- component escapes all values.
				echo $heading;
				?>
				<?php if ( ! empty( $cards ) ) : ?>
					<div class="vew-portfolio-index__grid" id="<?php echo esc_attr( $slot_id ); ?>-grid">
						<?php foreach ( $shown as $card ) : ?>
							<?php $this->render_card( $card, false ); ?>
						<?php endforeach; ?>
						<?php foreach ( $hidden as $card ) : ?>
							<?php $this->render_card( $card, true ); ?>
						<?php endforeach; ?>
					</div>
					<?php if ( $has_more ) : ?>
						<button type="button" class="vew-portfolio-index__more" data-vew-portfolio-more aria-expanded="false" aria-controls="<?php echo esc_attr( $slot_id ); ?>-grid">
							<?php echo esc_html__( 'Load more', 'vector-elementor-widgets' ); ?>
						</button>
					<?php endif; ?>
				<?php else : ?>
					<p class="vew-portfolio-index__empty"><?php echo esc_html__( 'No factory sites yet.', 'vector-elementor-widgets' ); ?></p>
				<?php endif; ?>
			</div>
		</section>
		<?php
	}

	/**
	 * Render a single portfolio card.
	 *
	 * @param array<string, string> $card   Card data (permalink, title, brand, preview).
	 * @param bool                  $hidden Whether the card starts behind the load-more gate.
	 *
	 * @return void
	 */
	private function render_card( array $card, bool $hidden ): void {
		$swatch_style = '' !== $card['brand'] ? ' style="background-color:' . esc_attr( $card['brand'] ) . '"' : '';
		?>
		<article class="vew-portfolio-index__card<?php echo $hidden ? ' vew-portfolio-index__card--hidden' : ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- boolean-driven. ?>">
			<a class="vew-portfolio-index__card-link" href="<?php echo esc_url( $card['permalink'] ); ?>">
				<?php if ( '' !== $card['preview'] ) : ?>
					<img class="vew-portfolio-index__preview" src="<?php echo esc_url( $card['preview'] ); ?>" alt="" loading="lazy" width="1280" height="960">
				<?php else : ?>
					<span class="vew-portfolio-index__swatch"<?php echo $swatch_style; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- esc_attr'd above. ?> aria-hidden="true"></span>
				<?php endif; ?>
				<h3 class="vew-portfolio-index__title"><?php echo esc_html( $card['title'] ); ?></h3>
			</a>
		</article>
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

	/**
	 * Declare the script dependency (load-more gating).
	 *
	 * @return array<int, string>
	 */
	public function get_script_depends(): array {
		return array( 'vew-portfolio-index' );
	}
}
