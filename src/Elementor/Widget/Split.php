<?php
/**
 * Split widget.
 *
 * Port of the vidal/blackstone/ember-oak "firm / story / about / coaching"
 * pattern: a two-column split with an image + caption on one side and an
 * eyebrow/title/paragraphs/blockquote/signature on the other. Per the
 * control-mapping contract `data-image` → MEDIA, `data-copy` → TEXT.
 *
 * @package Vector\ElementorWidgets\Elementor\Widget
 */

declare( strict_types=1 );

namespace Vector\ElementorWidgets\Elementor\Widget;

use Vector\ElementorWidgets\Elementor\Control\SplitContentControls;
use Vector\ElementorWidgets\Elementor\Control\SplitStyleControls;
use Vector\ElementorWidgets\Elementor\Component\SectionHeading;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Split widget.
 */
final class Split extends BaseWidget {

	/**
	 * Widget slug — stable forever.
	 *
	 * @return string
	 */
	public function get_name(): string {
		return 'vew-split';
	}

	/**
	 * Widget title.
	 *
	 * @return string
	 */
	public function get_title(): string {
		return __( 'Split', 'vector-elementor-widgets' );
	}

	/**
	 * Widget icon.
	 *
	 * @return string
	 */
	public function get_icon(): string {
		return 'eicon-image-box';
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
		return array( 'split', 'about', 'story', 'firm', 'image', 'copy' );
	}

	/**
	 * Register controls.
	 *
	 * @return void
	 */
	protected function register_controls(): void {
		( new SplitContentControls() )->register( $this );
		( new SplitStyleControls() )->register( $this );
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
				'eyebrow'      => 'string',
				'title'        => 'string',
				'title_accent' => 'string',
				'title_after'  => 'string',
				'image'        => 'array',
				'image_caption' => 'string',
				'paragraphs'   => 'array',
				'quote'        => 'string',
				'signature'    => 'string',
				'signature_role' => 'string',
			)
		);

		$image = $safe['image'] ?? array();
		$image_url = is_array( $image ) && isset( $image['url'] ) ? (string) $image['url'] : '';
		$image_alt = is_array( $image ) && isset( $image['alt'] ) ? (string) $image['alt'] : '';
		$caption = $safe['image_caption'] ?? '';
		$paragraphs = $safe['paragraphs'] ?? array();
		$quote = $safe['quote'] ?? '';
		$signature = $safe['signature'] ?? '';
		$signature_role = $safe['signature_role'] ?? '';
		?>
		<section class="vew-split" aria-label="split">
			<div class="vew-split__inner">
				<div class="vew-split__grid">
					<?php if ( '' !== $image_url ) : ?>
						<div class="vew-split__media">
							<img class="vew-split__image" src="<?php echo esc_url( $image_url ); ?>" alt="<?php echo esc_attr( $image_alt ); ?>" loading="lazy">
							<?php if ( '' !== $caption ) : ?>
								<span class="vew-split__caption"><?php echo esc_html( $caption ); ?></span>
							<?php endif; ?>
						</div>
					<?php endif; ?>

					<div class="vew-split__copy">
					<?php echo SectionHeading::render( $safe, array( 'block_class' => 'vew-split' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- component escapes all values. ?>

					<?php if ( is_array( $paragraphs ) && count( $paragraphs ) > 0 ) : ?>
						<?php foreach ( $paragraphs as $paragraph ) : ?>
							<?php
							$p_text = isset( $paragraph['text'] ) ? sanitize_text_field( (string) $paragraph['text'] ) : '';
							if ( '' === $p_text ) {
								continue;
							}
							?>
							<p class="vew-split__paragraph"><?php echo esc_html( $p_text ); ?></p>
						<?php endforeach; ?>
					<?php endif; ?>

					<?php if ( '' !== $quote ) : ?>
						<blockquote class="vew-split__quote"><?php echo esc_html( $quote ); ?></blockquote>
					<?php endif; ?>

					<?php if ( '' !== $signature ) : ?>
						<div class="vew-split__signature">
							<span class="vew-split__signature-name"><?php echo esc_html( $signature ); ?></span>
							<?php if ( '' !== $signature_role ) : ?>
								<small class="vew-split__signature-role"><?php echo esc_html( $signature_role ); ?></small>
							<?php endif; ?>
						</div>
					<?php endif; ?>
					</div>
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
		return array( 'vew-section-heading', 'vew-split' );
	}
}
