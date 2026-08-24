<?php
/**
 * Process widget.
 *
 * Port of the vidal-studio process component: a heading block + a numbered
 * list of steps (REPEATER) on a dark brand-dark background. Per the
 * control-mapping contract `data-copy` → TEXT, `data-process` → REPEATER.
 *
 * @package Vector\ElementorWidgets\Elementor\Widget
 */

declare( strict_types=1 );

namespace Vector\ElementorWidgets\Elementor\Widget;

use Vector\ElementorWidgets\Elementor\Control\ProcessContentControls;
use Vector\ElementorWidgets\Elementor\Control\ProcessStyleControls;
use Vector\ElementorWidgets\Elementor\Component\SectionHeading;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Process widget.
 */
final class Process extends BaseWidget {

	/**
	 * Widget slug — stable forever.
	 *
	 * @return string
	 */
	public function get_name(): string {
		return 'vew-process';
	}

	/**
	 * Widget title.
	 *
	 * @return string
	 */
	public function get_title(): string {
		return __( 'Process', 'vector-elementor-widgets' );
	}

	/**
	 * Widget icon.
	 *
	 * @return string
	 */
	public function get_icon(): string {
		return 'eicon-time-line';
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
		return array( 'process', 'steps', 'how', 'timeline', 'workflow' );
	}

	/**
	 * Register controls.
	 *
	 * @return void
	 */
	protected function register_controls(): void {
		( new ProcessContentControls() )->register( $this );
		( new ProcessStyleControls() )->register( $this );
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
				'note'    => 'string',
				'steps'   => 'array',
			)
		);

		$eyebrow = $safe['eyebrow'] ?? '';
		$title   = $safe['title'] ?? '';
		$note    = $safe['note'] ?? '';
		$steps   = $safe['steps'] ?? array();

		?>
		<section class="vew-process" aria-label="process">
			<div class="vew-process__inner">
			<?php echo SectionHeading::render( $safe, array( 'block_class' => 'vew-process' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- component escapes all values. ?>

			<?php if ( is_array( $steps ) && count( $steps ) > 0 ) : ?>
				<ol class="vew-process__list">
					<?php foreach ( $steps as $step ) : ?>
						<?php
						$s_title = isset( $step['title'] ) ? sanitize_text_field( (string) $step['title'] ) : '';
						$s_text  = isset( $step['text'] ) ? sanitize_text_field( (string) $step['text'] ) : '';
						if ( '' === $s_title ) {
							continue;
						}
						?>
						<li class="vew-process__step">
							<h3 class="vew-process__step-title"><?php echo esc_html( $s_title ); ?></h3>
							<?php if ( '' !== $s_text ) : ?>
								<p class="vew-process__step-text"><?php echo esc_html( $s_text ); ?></p>
							<?php endif; ?>
						</li>
					<?php endforeach; ?>
				</ol>
			<?php endif; ?>

			<?php if ( '' !== $note ) : ?>
				<p class="vew-process__note"><?php echo esc_html( $note ); ?></p>
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
		return array( 'vew-section-heading', 'vew-process' );
	}
}
