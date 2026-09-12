<?php
/**
 * Footer widget.
 *
 * Port of the vidal-studio site footer: a brand block + a set of link
 * columns (REPEATER of columns, each with a heading + REPEATER of links) +
 * a bottom bar. Per the control-mapping contract `data-copy` → TEXT,
 * `data-menu` → REPEATER.
 *
 * @package Vector\ElementorWidgets\Elementor\Widget
 */

declare( strict_types=1 );

namespace Vector\ElementorWidgets\Elementor\Widget;

use Vector\ElementorWidgets\Elementor\Control\FooterContentControls;
use Vector\ElementorWidgets\Elementor\Control\FooterStyleControls;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Footer widget.
 */
final class Footer extends BaseWidget {

	/**
	 * Widget slug — stable forever.
	 *
	 * @return string
	 */
	public function get_name(): string {
		return 'vew-footer';
	}

	/**
	 * Widget title.
	 *
	 * @return string
	 */
	public function get_title(): string {
		return __( 'Footer', 'vector-elementor-widgets' );
	}

	/**
	 * Widget icon.
	 *
	 * @return string
	 */
	public function get_icon(): string {
		return 'eicon-footer';
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
		return array( 'footer', 'bottom', 'links', 'copyright' );
	}

	/**
	 * Register controls.
	 *
	 * @return void
	 */
	protected function register_controls(): void {
		( new FooterContentControls() )->register( $this );
		( new FooterStyleControls() )->register( $this );
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
				'brand_name' => 'string',
				'brand_tag'  => 'string',
				'blurb'      => 'string',
				'columns'    => 'array',
				'copyright'  => 'string',
				'logo'       => 'array',
			)
		);

		$brand_name = $safe['brand_name'] ?? '';
		$brand_tag  = $safe['brand_tag'] ?? '';
		$blurb      = $safe['blurb'] ?? '';
		$columns    = $safe['columns'] ?? array();
		$copyright  = $safe['copyright'] ?? '';
		$logo       = $safe['logo'] ?? array();

		$logo_url = is_array( $logo ) && isset( $logo['url'] ) ? (string) $logo['url'] : '';
		$logo_alt = is_array( $logo ) && isset( $logo['alt'] ) ? (string) $logo['alt'] : $brand_name;

		?>
		<footer class="vew-footer">
			<div class="vew-footer__inner">
			<div class="vew-footer__grid">
				<div class="vew-footer__brand-col">
					<a class="vew-footer__brand" href="<?php echo esc_url( home_url( '/' ) ); ?>" aria-label="<?php echo esc_attr( $brand_name ); ?> home">
						<span class="vew-footer__mark" aria-hidden="true">
							<?php if ( '' !== $logo_url ) : ?>
								<img src="<?php echo esc_url( $logo_url ); ?>" alt="<?php echo esc_attr( $logo_alt ); ?>">
							<?php else : ?>
								<svg viewBox="0 0 44 44" aria-hidden="true"><path d="M22 3 38 9v11c0 10-6.5 17.2-16 21C12.5 37.2 6 30 6 20V9l16-6Z"/><path class="vew-footer__mark-detail" d="M14 15 22 9l8 6-4 14h-8l-4-14Z"/></svg>
							<?php endif; ?>
						</span>
						<span class="vew-footer__brand-text">
							<strong><?php echo esc_html( $brand_name ); ?></strong>
							<?php if ( '' !== $brand_tag ) : ?>
								<small><?php echo esc_html( $brand_tag ); ?></small>
							<?php endif; ?>
						</span>
					</a>
					<?php if ( '' !== $blurb ) : ?>
						<p class="vew-footer__blurb"><?php echo esc_html( $blurb ); ?></p>
					<?php endif; ?>
				</div>

				<?php if ( is_array( $columns ) && count( $columns ) > 0 ) : ?>
					<?php foreach ( $columns as $column ) : ?>
						<?php
						$col_heading = isset( $column['heading'] ) ? sanitize_text_field( (string) $column['heading'] ) : '';
						$col_links   = isset( $column['links'] ) && is_array( $column['links'] ) ? $column['links'] : array();
						if ( '' === $col_heading ) {
							continue;
						}
						?>
						<div class="vew-footer__col">
							<h3><?php echo esc_html( $col_heading ); ?></h3>
							<?php foreach ( $col_links as $link ) : ?>
								<?php
								$l_text = isset( $link['text'] ) ? sanitize_text_field( (string) $link['text'] ) : '';
								$l_url  = isset( $link['url'] ) ? ( is_array( $link['url'] ) ? ( $link['url']['url'] ?? '' ) : $link['url'] ) : '';
								$l_url  = esc_url_raw( (string) $l_url );
								if ( '' === $l_text ) {
									continue;
								}
								?>
								<a href="<?php echo esc_url( $l_url ); ?>"><?php echo esc_html( $l_text ); ?></a>
							<?php endforeach; ?>
						</div>
					<?php endforeach; ?>
				<?php endif; ?>
			</div>

			<?php if ( '' !== $copyright ) : ?>
				<div class="vew-footer__bottom">
					<span><?php echo esc_html( $copyright ); ?></span>
				</div>
			<?php endif; ?>
			</div>
		</footer>
		<?php
	}

	/**
	 * Declare the stylesheet dependency.
	 *
	 * @return array<int, string>
	 */
	public function get_style_depends(): array {
		return array( 'vew-footer' );
	}
}
