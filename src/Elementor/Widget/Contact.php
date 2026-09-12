<?php
/**
 * Contact widget.
 *
 * Port of the vidal-studio contact component: an intro block with contact
 * details (phone, email, location) + a consultation request form. The form
 * is a static, accessible HTML form (no backend — it opens the visitor's
 * mail client with the details pre-filled). Per the control-mapping contract
 * `data-copy` → TEXT.
 *
 * @package Vector\ElementorWidgets\Elementor\Widget
 */

declare( strict_types=1 );

namespace Vector\ElementorWidgets\Elementor\Widget;

use Vector\ElementorWidgets\Elementor\Control\ContactContentControls;
use Vector\ElementorWidgets\Elementor\Control\ContactStyleControls;
use Vector\ElementorWidgets\Elementor\Component\SectionHeading;
use Vector\ElementorWidgets\Support\ContactFormHandler;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Contact widget.
 */
final class Contact extends BaseWidget {

	/**
	 * Widget slug — stable forever.
	 *
	 * @return string
	 */
	public function get_name(): string {
		return 'vew-contact';
	}

	/**
	 * Widget title.
	 *
	 * @return string
	 */
	public function get_title(): string {
		return __( 'Contact', 'vector-elementor-widgets' );
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
		return array( 'contact', 'form', 'email', 'phone', 'get in touch' );
	}

	/**
	 * Register controls.
	 *
	 * @return void
	 */
	protected function register_controls(): void {
		( new ContactContentControls() )->register( $this );
		( new ContactStyleControls() )->register( $this );
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
				'phone'   => 'string',
				'email'   => 'string',
				'location' => 'string',
				'form_kicker' => 'string',
				'form_title'  => 'string',
				'submit_cta'  => 'string',
				'label_name'      => 'string',
				'label_secondary' => 'string',
				'label_email'     => 'string',
				'label_phone'     => 'string',
				'label_message'   => 'string',
				'required_note'   => 'string',
				'privacy_note'    => 'string',
			)
		);

		$eyebrow     = $safe['eyebrow'] ?? '';
		$title       = $safe['title'] ?? '';
		$intro       = $safe['intro'] ?? '';
		$phone       = $safe['phone'] ?? '';
		$email       = $safe['email'] ?? '';
		$location    = $safe['location'] ?? '';
		$form_kicker = $safe['form_kicker'] ?? '';
		$form_title  = $safe['form_title'] ?? '';
		$submit_cta  = $safe['submit_cta'] ?? '';
		$submit_cta  = '' !== $submit_cta ? $submit_cta : __( 'Send message', 'vector-elementor-widgets' );

		// Editable form copy. Each falls back to the previous hardcoded string so
		// existing pages render identically until an editor customizes them.
		$label_name      = $safe['label_name'] ?? '';
		$label_name      = '' !== $label_name ? $label_name : __( 'Your name', 'vector-elementor-widgets' );
		$label_secondary = $safe['label_secondary'] ?? '';
		$label_secondary = '' !== $label_secondary ? $label_secondary : __( 'Business name', 'vector-elementor-widgets' );
		$label_email     = $safe['label_email'] ?? '';
		$label_email     = '' !== $label_email ? $label_email : __( 'Email', 'vector-elementor-widgets' );
		$label_phone     = $safe['label_phone'] ?? '';
		$label_phone     = '' !== $label_phone ? $label_phone : __( 'Phone', 'vector-elementor-widgets' );
		$label_message   = $safe['label_message'] ?? '';
		$label_message   = '' !== $label_message ? $label_message : __( 'Tell me about your project', 'vector-elementor-widgets' );
		$required_note   = $safe['required_note'] ?? '';
		$required_note   = '' !== $required_note ? $required_note : __( 'Fields marked * are required', 'vector-elementor-widgets' );
		$privacy_note    = $safe['privacy_note'] ?? '';
		$privacy_note    = '' !== $privacy_note ? $privacy_note : __( 'No spam. Your information is used only to respond to this request.', 'vector-elementor-widgets' );

		// Unique per-instance prefix so multiple Contact widgets never collide
		// on id/for attributes.
		$uid = wp_unique_id( 'vew-contact-' );

		// Read a submission status flag (set by the admin-post handler).
		$status = isset( $_GET['vew_contact_status'] ) ? sanitize_key( wp_unslash( $_GET['vew_contact_status'] ) ) : '';
		?>
		<section class="vew-contact" aria-label="contact">
			<div class="vew-contact__inner">
			<div class="vew-contact__intro">
				<?php
				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- component escapes all values.
				echo SectionHeading::render(
					$safe,
					array(
						'block_class' => 'vew-contact',
						'intro_suffix' => 'intro-copy',
						'wrap' => false,
					)
				);
				?>

				<?php if ( '' !== $phone || '' !== $email || '' !== $location ) : ?>
					<div class="vew-contact__details">
						<?php if ( '' !== $phone ) : ?>
							<a href="tel:<?php echo esc_attr( preg_replace( '/[^0-9+]/', '', $phone ) ); ?>"><span><?php echo esc_html__( 'Call', 'vector-elementor-widgets' ); ?></span><?php echo esc_html( $phone ); ?></a>
						<?php endif; ?>
						<?php if ( '' !== $email ) : ?>
							<a href="mailto:<?php echo esc_attr( $email ); ?>"><span><?php echo esc_html__( 'Email', 'vector-elementor-widgets' ); ?></span><?php echo esc_html( $email ); ?></a>
						<?php endif; ?>
						<?php if ( '' !== $location ) : ?>
							<div><span><?php echo esc_html__( 'Location', 'vector-elementor-widgets' ); ?></span><?php echo esc_html( $location ); ?></div>
						<?php endif; ?>
					</div>
				<?php endif; ?>
			</div>

			<?php if ( 'vew_contact_sent' === $status ) : ?>
				<div class="vew-contact__notice vew-contact__notice--success" role="status"><?php echo esc_html__( 'Thanks! Your message has been sent.', 'vector-elementor-widgets' ); ?></div>
			<?php elseif ( 'vew_contact_failed' === $status || 'vew_contact_error' === $status ) : ?>
				<div class="vew-contact__notice vew-contact__notice--error" role="alert"><?php echo esc_html__( 'Sorry, your message could not be sent. Please try again.', 'vector-elementor-widgets' ); ?></div>
			<?php endif; ?>

			<form class="vew-contact__form" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="post">
				<input type="hidden" name="action" value="<?php echo esc_attr( ContactFormHandler::ACTION ); ?>">
				<input type="hidden" name="vew_contact_recipient" value="<?php echo esc_attr( $email ); ?>">
				<?php ContactFormHandler::nonce_field(); ?>
				<div class="vew-contact__form-heading">
					<?php if ( '' !== $form_kicker ) : ?>
						<span><?php echo esc_html( $form_kicker ); ?></span>
					<?php endif; ?>
					<?php if ( '' !== $form_title ) : ?>
						<strong><?php echo esc_html( $form_title ); ?></strong>
					<?php endif; ?>
					<small><?php echo esc_html( $required_note ); ?></small>
				</div>
				<div class="vew-contact__form-grid">
					<div class="vew-contact__field">
						<label for="<?php echo esc_attr( $uid ); ?>name"><?php echo esc_html( $label_name ); ?> *</label>
						<input type="text" id="<?php echo esc_attr( $uid ); ?>name" name="name" required>
					</div>
					<div class="vew-contact__field">
						<label for="<?php echo esc_attr( $uid ); ?>business"><?php echo esc_html( $label_secondary ); ?></label>
						<input type="text" id="<?php echo esc_attr( $uid ); ?>business" name="business">
					</div>
					<div class="vew-contact__field">
						<label for="<?php echo esc_attr( $uid ); ?>email"><?php echo esc_html( $label_email ); ?> *</label>
						<input type="email" id="<?php echo esc_attr( $uid ); ?>email" name="email" required>
					</div>
					<div class="vew-contact__field">
						<label for="<?php echo esc_attr( $uid ); ?>phone"><?php echo esc_html( $label_phone ); ?></label>
						<input type="tel" id="<?php echo esc_attr( $uid ); ?>phone" name="phone">
					</div>
					<div class="vew-contact__field vew-contact__field--full">
						<label for="<?php echo esc_attr( $uid ); ?>message"><?php echo esc_html( $label_message ); ?> *</label>
						<textarea id="<?php echo esc_attr( $uid ); ?>message" name="message" required></textarea>
					</div>
				</div>
				<button class="vew-contact__submit" type="submit"><?php echo esc_html( $submit_cta ); ?> <span aria-hidden="true">→</span></button>
				<p class="vew-contact__privacy"><?php echo esc_html( $privacy_note ); ?></p>
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
		return array( 'vew-section-heading', 'vew-contact' );
	}
}
