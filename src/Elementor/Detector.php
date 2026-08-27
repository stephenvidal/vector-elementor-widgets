<?php
/**
 * Elementor detector.
 *
 * Phase 1: non-fatal detection. Surfaces a single dismissable admin notice
 * when Elementor is missing or below the minimum version. The notice is
 * per-user (dismissed via a user meta flag) and survives theme/plugin
 * switches.
 *
 * The widget registration itself lives in the Elementor integration; this
 * class is purely the detector + notice surface.
 *
 * @package Vector\ElementorWidgets\Elementor
 */

declare( strict_types=1 );

namespace Vector\ElementorWidgets\Elementor;

use Vector\ElementorWidgets\Support\Compatibility;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Elementor detector + notice surface.
 */
final class Detector {

	/**
	 * User meta key used to persist the "dismissed" state.
	 */
	public const DISMISS_META = 'vew_elementor_notice_dismissed';

	/**
	 * Register admin notice hooks.
	 *
	 * @return void
	 */
	public function register_hooks(): void {
		add_action( 'admin_notices', array( $this, 'maybe_render_notice' ) );
		add_action( 'admin_post_vew_dismiss_elementor_notice', array( $this, 'handle_dismiss' ) );
	}

	/**
	 * Render the admin notice when Elementor is missing or unsupported.
	 *
	 * @return void
	 */
	public function maybe_render_notice(): void {
		if ( ! $this->should_show() ) {
			return;
		}
		$compat = ( new Compatibility() )->check();

		// Elementor installed and at/above the minimum — nothing to warn about.
		if ( null !== $compat->elementor_version && $compat->elementor_meets_min ) {
			return;
		}

		if ( null === $compat->elementor_version ) {
			$message = __( 'Vector Elementor Widgets: Elementor is not installed. The Elementor widgets are disabled.', 'vector-elementor-widgets' );
		} else {
			$message = sprintf(
				/* translators: 1: required Elementor version, 2: actual Elementor version */
				__( 'Vector Elementor Widgets recommends Elementor %1$s or higher. The site is running Elementor %2$s — the Elementor widgets are disabled but the plugin still functions.', 'vector-elementor-widgets' ),
				VEW_MIN_ELEMENTOR,
				$compat->elementor_version
			);
		}

		$dismiss_url = wp_nonce_url(
			add_query_arg( 'action', 'vew_dismiss_elementor_notice', admin_url( 'admin-post.php' ) ),
			'vew_dismiss_elementor_notice'
		);

		printf(
			'<div class="notice notice-warning is-dismissible vew-elementor-notice"><p>%s</p><p><a class="button" href="%s">%s</a></p></div>',
			esc_html( $message ),
			esc_url( $dismiss_url ),
			esc_html__( 'Dismiss', 'vector-elementor-widgets' )
		);
	}

	/**
	 * Handle the dismiss action.
	 *
	 * @return void
	 */
	public function handle_dismiss(): void {
		if ( ! function_exists( 'current_user_can' ) || ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have permission to do that.', 'vector-elementor-widgets' ) );
		}
		check_admin_referer( 'vew_dismiss_elementor_notice' );
		$user_id = get_current_user_id();
		if ( $user_id > 0 ) {
			update_user_meta( $user_id, self::DISMISS_META, '1' );
		}
		wp_safe_redirect( admin_url() );
		exit;
	}

	/**
	 * Whether the notice should render for the current user.
	 *
	 * @return bool
	 */
	private function should_show(): bool {
		if ( ! function_exists( 'current_user_can' ) || ! current_user_can( 'manage_options' ) ) {
			return false;
		}
		$user_id = get_current_user_id();
		if ( $user_id <= 0 ) {
			return false;
		}
		return '1' !== get_user_meta( $user_id, self::DISMISS_META, true );
	}
}
