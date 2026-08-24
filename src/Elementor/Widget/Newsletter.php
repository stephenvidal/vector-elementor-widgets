<?php
/**
 * Newsletter widget.
 *
 * A compact email-capture block: heading/subtext + a single-field subscribe
 * form that posts to the newsletter handler (nonce + sanitize + persist).
 * Surfaces a success/error notice via a query-status flag.
 *
 * @package Vector\ElementorWidgets\Elementor\Widget
 */

declare( strict_types=1 );

namespace Vector\ElementorWidgets\Elementor\Widget;

use Vector\ElementorWidgets\Elementor\Control\NewsletterContentControls;
use Vector\ElementorWidgets\Elementor\Control\NewsletterStyleControls;
use Vector\ElementorWidgets\Elementor\Component\SectionHeading;
use Vector\ElementorWidgets\Support\NewsletterFormHandler;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Newsletter widget.
 */
final class Newsletter extends BaseWidget {

	/**
	 * Widget slug — stable forever.
	 *
	 * @return string
	 */
	public function get_name(): string {
		return 'vew-newsletter';
	}

	/**
	 * Widget title.
	 *
	 * @return string
	 */
	public function get_title(): string {
		return __( 'Newsletter Signup', 'vector-elementor-widgets' );
	}

	/**
	 * Widget icon.
	 *
	 * @return string
	 */
	public function get_icon(): string {
		return 'eicon-mail';
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
		return array( 'newsletter', 'subscribe', 'email', 'signup', 'mailing' );
	}

	/**
	 * Register controls.
	 *
	 * @return void
	 */
	protected function register_controls(): void {
		( new NewsletterContentControls() )->register( $this );
		( new NewsletterStyleControls() )->register( $this );
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
					'placeholder' => 'string',
					'button_text' => 'string',
				)
			)
		);

		$placeholder = $safe['placeholder'] ?? __( 'you@example.com', 'vector-elementor-widgets' );
		$button_text = $safe['button_text'] ?? __( 'Subscribe', 'vector-elementor-widgets' );

		$heading = SectionHeading::render(
			$safe,
			array( 'block_class' => 'vew-newsletter' )
		);

		$uid = wp_unique_id( 'vew-newsletter-' );

		$status = isset( $_GET['vew_newsletter_status'] ) ? sanitize_key( wp_unslash( $_GET['vew_newsletter_status'] ) ) : '';
		?>
		<section class="vew-newsletter" aria-label="<?php echo esc_attr__( 'Newsletter signup', 'vector-elementor-widgets' ); ?>">
			<div class="vew-newsletter__inner">
				<?php
				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- component escapes all values.
				echo $heading;
				?>
				<?php if ( 'vew_newsletter_sent' === $status ) : ?>
					<div class="vew-newsletter__notice vew-newsletter__notice--success" role="status"><?php echo esc_html__( 'Thanks for subscribing!', 'vector-elementor-widgets' ); ?></div>
				<?php elseif ( 'vew_newsletter_failed' === $status || 'vew_newsletter_error' === $status ) : ?>
					<div class="vew-newsletter__notice vew-newsletter__notice--error" role="alert"><?php echo esc_html__( 'Sorry, that email could not be subscribed. Please try again.', 'vector-elementor-widgets' ); ?></div>
				<?php endif; ?>

				<form class="vew-newsletter__form" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="post">
					<input type="hidden" name="action" value="<?php echo esc_attr( NewsletterFormHandler::ACTION ); ?>">
					<?php NewsletterFormHandler::nonce_field(); ?>
					<label class="vew-newsletter__sr-only" for="<?php echo esc_attr( $uid ); ?>email"><?php echo esc_html__( 'Email address', 'vector-elementor-widgets' ); ?></label>
					<div class="vew-newsletter__row">
						<input class="vew-newsletter__input" type="email" id="<?php echo esc_attr( $uid ); ?>email" name="email" placeholder="<?php echo esc_attr( $placeholder ); ?>" required autocomplete="email">
						<button class="vew-newsletter__button" type="submit"><?php echo esc_html( $button_text ); ?></button>
					</div>
					<p class="vew-newsletter__privacy"><?php echo esc_html__( 'No spam. Unsubscribe anytime.', 'vector-elementor-widgets' ); ?></p>
				</form>
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
		return array( 'vew-section-heading', 'vew-newsletter' );
	}
}
