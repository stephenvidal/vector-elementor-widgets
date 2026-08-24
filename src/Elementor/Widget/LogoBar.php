<?php
/**
 * LogoBar widget.
 *
 * A "trusted by" client/partner logo row or marquee. Renders a heading block
 * plus a responsive grid (or auto-scrolling marquee) of logo images, each
 * optionally wrapped in a link, with a grayscale-until-hover treatment.
 *
 * @package Vector\ElementorWidgets\Elementor\Widget
 */

declare( strict_types=1 );

namespace Vector\ElementorWidgets\Elementor\Widget;

use Vector\ElementorWidgets\Elementor\Control\LogoBarContentControls;
use Vector\ElementorWidgets\Elementor\Control\LogoBarStyleControls;
use Vector\ElementorWidgets\Elementor\Component\SectionHeading;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * LogoBar widget.
 */
final class LogoBar extends BaseWidget {

	/**
	 * Widget slug — stable forever.
	 *
	 * @return string
	 */
	public function get_name(): string {
		return 'vew-logo-bar';
	}

	/**
	 * Widget title.
	 *
	 * @return string
	 */
	public function get_title(): string {
		return __( 'Logo / Client Bar', 'vector-elementor-widgets' );
	}

	/**
	 * Widget icon.
	 *
	 * @return string
	 */
	public function get_icon(): string {
		return 'eicon-logo';
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
		return array( 'logo', 'client', 'partner', 'trusted', 'brand', 'marquee' );
	}

	/**
	 * Register controls.
	 *
	 * @return void
	 */
	protected function register_controls(): void {
		( new LogoBarContentControls() )->register( $this );
		( new LogoBarStyleControls() )->register( $this );
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
					'layout'    => 'string',
					'grayscale' => 'string',
					'logos'     => 'array',
				)
			)
		);

		$layout    = isset( $safe['layout'] ) ? sanitize_key( $safe['layout'] ) : 'grid';
		$layout    = in_array( $layout, array( 'grid', 'marquee' ), true ) ? $layout : 'grid';
		$grayscale = ( isset( $safe['grayscale'] ) && 'yes' === $safe['grayscale'] ) ? ' vew-logo-bar--grayscale' : '';
		$logos     = $safe['logos'] ?? array();
		$is_marquee = 'marquee' === $layout;

		$heading = SectionHeading::render(
			$safe,
			array( 'block_class' => 'vew-logo-bar' )
		);

		// Build the logo list once.
		$logo_html = array();
		if ( is_array( $logos ) ) {
			foreach ( $logos as $logo ) {
				$logo   = is_array( $logo ) ? $logo : array();
				$url    = isset( $logo['logo']['url'] ) ? (string) $logo['logo']['url'] : '';
				$name   = isset( $logo['name'] ) ? sanitize_text_field( (string) $logo['name'] ) : '';
				$alt    = '' !== $name ? $name : __( 'Client logo', 'vector-elementor-widgets' );
				$target = ( isset( $logo['url']['is_external'] ) && $logo['url']['is_external'] ) ? ' target="_blank" rel="noopener"' : '';

				if ( '' === $url ) {
					continue;
				}

				$img = '<img src="' . esc_url( $url ) . '" alt="' . esc_attr( $alt ) . '" loading="lazy">';

				if ( ! empty( $logo['url']['url'] ) ) {
					$logo_html[] = '<a class="vew-logo-bar__item" href="' . esc_url( $logo['url']['url'] ) . '"' . $target . '>' . $img . '</a>';
				} else {
					$logo_html[] = '<span class="vew-logo-bar__item">' . $img . '</span>';
				}
			}
		}

		?>
		<section class="vew-logo-bar<?php echo esc_attr( $grayscale ); ?>" aria-label="<?php echo esc_attr__( 'Client logos', 'vector-elementor-widgets' ); ?>">
			<div class="vew-logo-bar__inner">
				<?php
				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- component escapes all values.
				echo $heading;
				?>
				<?php if ( $is_marquee ) : ?>
					<div class="vew-logo-bar__marquee" aria-label="<?php echo esc_attr__( 'Auto-scrolling client logos', 'vector-elementor-widgets' ); ?>">
						<div class="vew-logo-bar__track">
							<div class="vew-logo-bar__row"><?php echo implode( '', $logo_html ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- individual items escaped above. ?></div>
							<div class="vew-logo-bar__row vew-logo-bar__row--dup" aria-hidden="true"><?php echo implode( '', $logo_html ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- individual items escaped above. ?></div>
						</div>
					</div>
				<?php else : ?>
					<div class="vew-logo-bar__grid">
						<?php
						// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- individual items escaped above.
						echo implode( '', $logo_html );
						?>
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
		return array( 'vew-section-heading', 'vew-logo-bar' );
	}
}
