<?php
/**
 * GoogleMap widget.
 *
 * A privacy-aware Google Maps embed. Renders an optional section heading plus
 * an `<iframe>` to the no-key Google Maps embed endpoint, centred on the
 * configured location at the chosen zoom. All user input is sanitized and the
 * iframe src is built with `rawurlencode()` so the query cannot escape.
 *
 * @package Vector\ElementorWidgets\Elementor\Widget
 */

declare( strict_types=1 );

namespace Vector\ElementorWidgets\Elementor\Widget;

use Vector\ElementorWidgets\Elementor\Control\GoogleMapContentControls;
use Vector\ElementorWidgets\Elementor\Control\GoogleMapStyleControls;
use Vector\ElementorWidgets\Elementor\Component\SectionHeading;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * GoogleMap widget.
 */
final class GoogleMap extends BaseWidget {

	/**
	 * Widget slug — stable forever.
	 *
	 * @return string
	 */
	public function get_name(): string {
		return 'vew-google-map';
	}

	/**
	 * Widget title.
	 *
	 * @return string
	 */
	public function get_title(): string {
		return __( 'Google Map', 'vector-elementor-widgets' );
	}

	/**
	 * Widget icon.
	 *
	 * @return string
	 */
	public function get_icon(): string {
		return 'eicon-google-maps';
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
		return array( 'map', 'maps', 'location', 'address', 'google', 'embed' );
	}

	/**
	 * Register controls.
	 *
	 * @return void
	 */
	protected function register_controls(): void {
		( new GoogleMapContentControls() )->register( $this );
		( new GoogleMapStyleControls() )->register( $this );
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
					'location' => 'string',
					'zoom'     => 'array',
					'height'   => 'array',
				)
			)
		);

		$location = isset( $safe['location'] ) ? trim( (string) $safe['location'] ) : '';
		$zoom     = isset( $safe['zoom']['size'] ) ? (int) $safe['zoom']['size'] : 14;
		$height   = isset( $safe['height']['size'] ) ? (int) $safe['height']['size'] : 420;
		$zoom     = max( 1, min( 20, $zoom ) );
		$height   = max( 200, min( 800, $height ) );

		$heading = SectionHeading::render(
			$safe,
			array( 'block_class' => 'vew-google-map' )
		);

		if ( '' === $location ) {
			return;
		}

		// Google's no-key embed endpoint (output=embed). rawurlencode the query
		// so the location can never inject markup or escape the iframe.
		$map_url = 'https://www.google.com/maps?q=' . rawurlencode( $location ) . '&z=' . $zoom . '&output=embed';
		?>
		<section class="vew-google-map" aria-label="<?php echo esc_attr__( 'Location map', 'vector-elementor-widgets' ); ?>">
			<div class="vew-google-map__inner">
				<?php
				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- component escapes all values.
				echo $heading;
				?>
				<div class="vew-google-map__embed">
					<iframe
						src="<?php echo esc_url( $map_url ); ?>"
						title="<?php echo esc_attr( $location ); ?>"
						style="height: <?php echo esc_attr( (string) $height ); ?>px;"
						loading="lazy"
						allowfullscreen
						referrerpolicy="no-referrer-when-downgrade"
					></iframe>
				</div>
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
		return array( 'vew-section-heading', 'vew-google-map' );
	}
}
