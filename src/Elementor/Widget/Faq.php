<?php
/**
 * FAQ widget.
 *
 * Phase 3 — direct port of the Derby FAQ accordion component: an intro +
 * a REPEATER of Q/A items rendered as accessible accordions (button +
 * aria-expanded + hidden panel). This widget also demonstrates the JS
 * asset path (accordion toggle), loaded only when the widget is present.
 *
 * @package Vector\ElementorWidgets\Elementor\Widget
 */

declare( strict_types=1 );

namespace Vector\ElementorWidgets\Elementor\Widget;

use Vector\ElementorWidgets\Elementor\Control\FaqContentControls;
use Vector\ElementorWidgets\Elementor\Control\FaqStyleControls;
use Vector\ElementorWidgets\Elementor\Component\SectionHeading;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * FAQ widget.
 */
final class Faq extends BaseWidget {

	/**
	 * Widget slug — stable forever.
	 *
	 * @return string
	 */
	public function get_name(): string {
		return 'vew-faq';
	}

	/**
	 * Widget title.
	 *
	 * @return string
	 */
	public function get_title(): string {
		return __( 'FAQ', 'vector-elementor-widgets' );
	}

	/**
	 * Widget icon.
	 *
	 * @return string
	 */
	public function get_icon(): string {
		return 'eicon-help-o';
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
		return array( 'faq', 'accordion', 'questions', 'help', 'tabs' );
	}

	/**
	 * Register controls.
	 *
	 * @return void
	 */
	protected function register_controls(): void {
		( new FaqContentControls() )->register( $this );
		( new FaqStyleControls() )->register( $this );
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
				'eyebrow' => 'string',
				'title'   => 'string',
				'title_accent' => 'string',
				'title_after'  => 'string',
				'intro'   => 'string',
				'items'   => 'array',
			)
		);

		$eyebrow = $safe['eyebrow'] ?? '';
		$title   = $safe['title'] ?? '';
		$intro   = $safe['intro'] ?? '';
		$items   = $safe['items'] ?? array();

		$instance_id = wp_unique_id( 'vew-faq-' );

		?>
		<section class="vew-faq" aria-label="frequently asked questions" data-vew-faq data-instance="<?php echo esc_attr( $instance_id ); ?>">
			<div class="vew-faq__grid">
				<?php echo SectionHeading::render( $safe, array( 'block_class' => 'vew-faq' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- component escapes all values. ?>

				<?php if ( is_array( $items ) && count( $items ) > 0 ) : ?>
					<div class="vew-faq__list">
						<?php foreach ( $items as $index => $item ) : ?>
							<?php
							$question = isset( $item['question'] ) ? sanitize_text_field( (string) $item['question'] ) : '';
							$answer   = isset( $item['answer'] ) ? sanitize_text_field( (string) $item['answer'] ) : '';
							if ( '' === $question ) {
								continue;
							}
							$item_id  = $instance_id . '-' . $index;
							$btn_id   = $item_id . '-btn';
							$panel_id = $item_id . '-panel';
							?>
							<div class="vew-faq__item">
								<h3 class="vew-faq__question-wrap">
									<button
										type="button"
										class="vew-faq__question"
										aria-expanded="false"
										aria-controls="<?php echo esc_attr( $panel_id ); ?>"
										id="<?php echo esc_attr( $btn_id ); ?>"
									><span class="vew-faq__question-text"><?php echo esc_html( $question ); ?></span><span class="vew-faq__icon" aria-hidden="true">+</span></button>
								</h3>
								<div class="vew-faq__answer" id="<?php echo esc_attr( $panel_id ); ?>" role="region" aria-labelledby="<?php echo esc_attr( $btn_id ); ?>" hidden>
									<p><?php echo esc_html( $answer ); ?></p>
								</div>
							</div>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>
			</div>
		</section>
		<?php
	}

	/**
	 * Declare the stylesheet + script dependencies (selective loading).
	 *
	 * @return array<int, string>
	 */
	public function get_style_depends(): array {
		return array( 'vew-section-heading', 'vew-faq' );
	}

	/**
	 * Declare the script dependency (accordion toggle).
	 *
	 * @return array<int, string>
	 */
	public function get_script_depends(): array {
		return array( 'vew-faq' );
	}
}
