<?php
/**
 * Header widget.
 *
 * Port of the vidal-studio site header: a sticky bar with a brand, a
 * desktop nav (REPEATER of links), a CTA button, and a mobile menu toggle.
 * Per the control-mapping contract `data-copy` → TEXT, `data-menu` → REPEATER.
 *
 * @package Vector\ElementorWidgets\Elementor\Widget
 */

declare( strict_types=1 );

namespace Vector\ElementorWidgets\Elementor\Widget;

use Vector\ElementorWidgets\Elementor\Control\HeaderContentControls;
use Vector\ElementorWidgets\Elementor\Control\HeaderStyleControls;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Header widget.
 */
final class Header extends BaseWidget {

	/**
	 * Widget slug — stable forever.
	 *
	 * @return string
	 */
	public function get_name(): string {
		return 'vew-header';
	}

	/**
	 * Widget title.
	 *
	 * @return string
	 */
	public function get_title(): string {
		return __( 'Header', 'vector-elementor-widgets' );
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
		return array( 'header', 'nav', 'navigation', 'menu', 'brand' );
	}

	/**
	 * Register controls.
	 *
	 * @return void
	 */
	protected function register_controls(): void {
		( new HeaderContentControls() )->register( $this );
		( new HeaderStyleControls() )->register( $this );
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
				'cta_text'   => 'string',
				'cta_url'    => 'url',
				'links'      => 'array',
			)
		);

		$brand_name = $safe['brand_name'] ?? '';
		$brand_tag  = $safe['brand_tag'] ?? '';
		$cta_text   = $safe['cta_text'] ?? '';
		$cta_url    = $safe['cta_url'] ?? '';
		$links      = $safe['links'] ?? array();

		$instance_id = wp_unique_id( 'vew-header-' );

		?>
		<header class="vew-header" data-vew-header data-instance="<?php echo esc_attr( $instance_id ); ?>">
			<div class="vew-header__shell">
				<a class="vew-header__brand" href="#home" aria-label="<?php echo esc_attr( $brand_name ); ?> home">
					<span class="vew-header__mark" aria-hidden="true">
						<svg viewBox="0 0 44 44" aria-hidden="true"><path d="M22 3 38 9v11c0 10-6.5 17.2-16 21C12.5 37.2 6 30 6 20V9l16-6Z"/><path class="vew-header__mark-detail" d="M14 15 22 9l8 6-4 14h-8l-4-14Z"/></svg>
					</span>
					<span class="vew-header__brand-text">
						<strong><?php echo esc_html( $brand_name ); ?></strong>
						<?php if ( '' !== $brand_tag ) : ?>
							<small><?php echo esc_html( $brand_tag ); ?></small>
						<?php endif; ?>
					</span>
				</a>

				<?php if ( is_array( $links ) && count( $links ) > 0 ) : ?>
					<nav class="vew-header__nav" aria-label="<?php echo esc_attr__( 'Primary navigation', 'vector-elementor-widgets' ); ?>">
						<?php foreach ( $links as $link ) : ?>
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
					</nav>
				<?php endif; ?>

				<?php if ( '' !== $cta_text && '' !== $cta_url ) : ?>
					<a class="vew-header__cta" href="<?php echo esc_url( $cta_url ); ?>"><?php echo esc_html( $cta_text ); ?></a>
				<?php endif; ?>

				<button class="vew-header__toggle" type="button" aria-label="<?php echo esc_attr__( 'Open menu', 'vector-elementor-widgets' ); ?>" aria-expanded="false" aria-controls="<?php echo esc_attr( $instance_id ); ?>-menu"><span></span><span></span><span></span></button>
			</div>

			<div class="vew-header__mobile" id="<?php echo esc_attr( $instance_id ); ?>-menu" hidden>
				<?php if ( is_array( $links ) && count( $links ) > 0 ) : ?>
					<nav aria-label="<?php echo esc_attr__( 'Mobile navigation', 'vector-elementor-widgets' ); ?>">
						<?php foreach ( $links as $link ) : ?>
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
					</nav>
				<?php endif; ?>
				<?php if ( '' !== $cta_text && '' !== $cta_url ) : ?>
					<a class="vew-header__cta" href="<?php echo esc_url( $cta_url ); ?>"><?php echo esc_html( $cta_text ); ?></a>
				<?php endif; ?>
			</div>
		</header>
		<?php
	}

	/**
	 * Declare the stylesheet dependency.
	 *
	 * @return array<int, string>
	 */
	public function get_style_depends(): array {
		return array( 'vew-header' );
	}

	/**
	 * Declare the script dependency (mobile menu toggle).
	 *
	 * @return array<int, string>
	 */
	public function get_script_depends(): array {
		return array( 'vew-header' );
	}
}
