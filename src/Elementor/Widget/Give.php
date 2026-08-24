<?php
/**
 * Give widget.
 *
 * Port of the vidal/rore/rorecclesia "give" band: a centered heading block +
 * a quote with verse reference + a CTA button. Per the control-mapping
 * contract `data-copy` → TEXT.
 *
 * @package Vector\ElementorWidgets\Elementor\Widget
 */

declare( strict_types=1 );

namespace Vector\ElementorWidgets\Elementor\Widget;

use Vector\ElementorWidgets\Elementor\Control\GiveContentControls;
use Vector\ElementorWidgets\Elementor\Control\GiveStyleControls;
use Vector\ElementorWidgets\Elementor\Component\SectionHeading;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Give widget.
 */
final class Give extends BaseWidget {

	/**
	 * Widget slug — stable forever.
	 *
	 * @return string
	 */
	public function get_name(): string {
		return 'vew-give';
	}

	/**
	 * Widget title.
	 *
	 * @return string
	 */
	public function get_title(): string {
		return __( 'Give', 'vector-elementor-widgets' );
	}

	/**
	 * Widget icon.
	 *
	 * @return string
	 */
	public function get_icon(): string {
		return 'eicon-heart';
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
		return array( 'give', 'donate', 'giving', 'donation', 'support' );
	}

	/**
	 * Register controls.
	 *
	 * @return void
	 */
	protected function register_controls(): void {
		( new GiveContentControls() )->register( $this );
		( new GiveStyleControls() )->register( $this );
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
				'quote'        => 'string',
				'verse_ref'    => 'string',
				'cta_text'     => 'string',
				'cta_url'      => 'array',
			)
		);

		$quote = $safe['quote'] ?? '';
		$verse_ref = $safe['verse_ref'] ?? '';
		$cta_text = $safe['cta_text'] ?? '';
		$cta_url = is_array( $safe['cta_url'] ?? null ) ? ( $safe['cta_url']['url'] ?? '' ) : '';
		?>
		<section class="vew-give" aria-label="give">
			<div class="vew-give__inner">
			<?php echo SectionHeading::render( $safe, array( 'block_class' => 'vew-give' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- component escapes all values. ?>

			<?php if ( '' !== $quote ) : ?>
				<div class="vew-give__note">
					<blockquote class="vew-give__quote"><?php echo esc_html( $quote ); ?></blockquote>
					<?php if ( '' !== $verse_ref ) : ?>
						<p class="vew-give__verse"><?php echo esc_html( $verse_ref ); ?></p>
					<?php endif; ?>
					<?php if ( '' !== $cta_text && '' !== $cta_url ) : ?>
						<a class="vew-give__cta" href="<?php echo esc_url( $cta_url ); ?>"><?php echo esc_html( $cta_text ); ?> <span aria-hidden="true">→</span></a>
					<?php endif; ?>
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
		return array( 'vew-section-heading', 'vew-give' );
	}
}
