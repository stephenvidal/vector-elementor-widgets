<?php
/**
 * BeforeAfter widget.
 *
 * A draggable before/after image comparison slider. Renders an optional
 * section heading plus a clip-path "after" layer over the "before" base, with
 * a pointer-drag divider. Driven by vew-before-after.js (pointer + keyboard
 * accessible). All URLs are escaped; labels are sanitized text.
 *
 * @package Vector\ElementorWidgets\Elementor\Widget
 */

declare( strict_types=1 );

namespace Vector\ElementorWidgets\Elementor\Widget;

use Vector\ElementorWidgets\Elementor\Control\BeforeAfterContentControls;
use Vector\ElementorWidgets\Elementor\Control\BeforeAfterStyleControls;
use Vector\ElementorWidgets\Elementor\Component\SectionHeading;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * BeforeAfter widget.
 */
final class BeforeAfter extends BaseWidget {

	/**
	 * Widget slug — stable forever.
	 *
	 * @return string
	 */
	public function get_name(): string {
		return 'vew-before-after';
	}

	/**
	 * Widget title.
	 *
	 * @return string
	 */
	public function get_title(): string {
		return __( 'Before / After', 'vector-elementor-widgets' );
	}

	/**
	 * Widget icon.
	 *
	 * @return string
	 */
	public function get_icon(): string {
		return 'eicon-image-before-after';
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
		return array( 'before', 'after', 'compare', 'slider', 'image', 'comparison' );
	}

	/**
	 * Register controls.
	 *
	 * @return void
	 */
	protected function register_controls(): void {
		( new BeforeAfterContentControls() )->register( $this );
		( new BeforeAfterStyleControls() )->register( $this );
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
					'before_image' => 'array',
					'before_label' => 'string',
					'after_image'  => 'array',
					'after_label'  => 'string',
					'orientation'  => 'string',
				)
			)
		);

		$before_url = isset( $safe['before_image']['url'] ) ? (string) $safe['before_image']['url'] : '';
		$after_url  = isset( $safe['after_image']['url'] ) ? (string) $safe['after_image']['url'] : '';
		if ( '' === $before_url || '' === $after_url ) {
			return;
		}

		$before_label = isset( $safe['before_label'] ) ? (string) $safe['before_label'] : '';
		$after_label  = isset( $safe['after_label'] ) ? (string) $safe['after_label'] : '';
		$orientation  = isset( $safe['orientation'] ) ? sanitize_key( $safe['orientation'] ) : 'horizontal';
		$orientation  = in_array( $orientation, array( 'horizontal', 'vertical' ), true ) ? $orientation : 'horizontal';
		$orient_class = 'vew-before-after--' . $orientation;

		$heading = SectionHeading::render(
			$safe,
			array( 'block_class' => 'vew-before-after' )
		);
		?>
		<section class="vew-before-after <?php echo esc_attr( $orient_class ); ?>" aria-label="<?php echo esc_attr__( 'Before and after comparison', 'vector-elementor-widgets' ); ?>">
			<div class="vew-before-after__inner">
				<?php
				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- component escapes all values.
				echo $heading;
				?>
				<div class="vew-before-after__stage" data-orientation="<?php echo esc_attr( $orientation ); ?>">
					<img class="vew-before-after__img vew-before-after__before" src="<?php echo esc_url( $before_url ); ?>" alt="<?php echo esc_attr( $before_label ); ?>" loading="lazy">
					<img class="vew-before-after__img vew-before-after__after" src="<?php echo esc_url( $after_url ); ?>" alt="<?php echo esc_attr( $after_label ); ?>" loading="lazy">
					<?php if ( '' !== $before_label ) : ?>
						<span class="vew-before-after__tag vew-before-after__tag--before"><?php echo esc_html( $before_label ); ?></span>
					<?php endif; ?>
					<?php if ( '' !== $after_label ) : ?>
						<span class="vew-before-after__tag vew-before-after__tag--after"><?php echo esc_html( $after_label ); ?></span>
					<?php endif; ?>
					<button type="button" class="vew-before-after__handle" aria-label="<?php echo esc_attr__( 'Drag to compare images', 'vector-elementor-widgets' ); ?>" aria-valuemin="0" aria-valuemax="100" aria-valuenow="50">
						<span class="vew-before-after__grip" aria-hidden="true">⇔</span>
					</button>
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
		return array( 'vew-section-heading', 'vew-before-after' );
	}

	/**
	 * Declare the script dependency (drag divider).
	 *
	 * @return array<int, string>
	 */
	public function get_script_depends(): array {
		return array( 'vew-before-after' );
	}
}
