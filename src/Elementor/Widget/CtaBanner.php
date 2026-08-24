<?php
/**
 * CTA Banner widget.
 *
 * Phase 1 proof widget. A simple, highly reusable conversion banner that
 * validates the widget contract: content + style controls, scoped CSS,
 * selective asset loading, and the render() path.
 *
 * @package Vector\ElementorWidgets\Elementor\Widget
 */

declare( strict_types=1 );

namespace Vector\ElementorWidgets\Elementor\Widget;

use Vector\ElementorWidgets\Elementor\Control\CtaContentControls;
use Vector\ElementorWidgets\Elementor\Control\CtaStyleControls;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * CTA Banner widget.
 */
final class CtaBanner extends BaseWidget {

	/**
	 * Widget slug — stable forever (changing it breaks saved pages).
	 *
	 * @return string
	 */
	public function get_name(): string {
		return 'vew-cta-banner';
	}

	/**
	 * Widget title.
	 *
	 * @return string
	 */
	public function get_title(): string {
		return __( 'CTA Banner', 'vector-elementor-widgets' );
	}

	/**
	 * Widget icon.
	 *
	 * @return string
	 */
	public function get_icon(): string {
		return 'eicon-call-to-action';
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
		return array( 'cta', 'banner', 'call', 'action', 'conversion' );
	}

	/**
	 * Register controls.
	 *
	 * @return void
	 */
	protected function register_controls(): void {
		( new CtaContentControls() )->register( $this );
		( new CtaStyleControls() )->register( $this );
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
				'heading'           => 'string',
				'description'       => 'string',
				'primary_text'      => 'string',
				'primary_url'       => 'url',
				'secondary_text'    => 'string',
				'secondary_url'     => 'url',
			)
		);

		$heading    = $safe['heading'] ?? '';
		$desc       = $safe['description'] ?? '';
		$primary    = $safe['primary_text'] ?? '';
		$primary_ln = $safe['primary_url'] ?? '';
		$secondary  = $safe['secondary_text'] ?? '';
		$secondary_ln = $safe['secondary_url'] ?? '';

		?>
		<section class="vew-cta">
			<div class="vew-cta__inner">
			<?php if ( '' !== $heading ) : ?>
				<h2 class="vew-cta__heading"><?php echo esc_html( $heading ); ?></h2>
			<?php endif; ?>
			<?php if ( '' !== $desc ) : ?>
				<p class="vew-cta__description"><?php echo esc_html( $desc ); ?></p>
			<?php endif; ?>
			<div class="vew-cta__actions">
				<?php if ( '' !== $primary && '' !== $primary_ln ) : ?>
					<a class="vew-cta__button vew-cta__button--primary" href="<?php echo esc_url( $primary_ln ); ?>"><?php echo esc_html( $primary ); ?></a>
				<?php endif; ?>
				<?php if ( '' !== $secondary && '' !== $secondary_ln ) : ?>
					<a class="vew-cta__button vew-cta__button--secondary" href="<?php echo esc_url( $secondary_ln ); ?>"><?php echo esc_html( $secondary ); ?></a>
				<?php endif; ?>
			</div>
			</div>
		</section>
		<?php
	}

	/**
	 * Declare the stylesheet dependency so it loads only when this widget
	 * is on the page.
	 *
	 * @return array<int, string>
	 */
	public function get_style_depends(): array {
		return array( 'vew-cta-banner' );
	}
}
