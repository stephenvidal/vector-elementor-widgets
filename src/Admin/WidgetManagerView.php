<?php
/**
 * Widget Manager view.
 *
 * A static HTML emitter for the Widget Manager admin screen. Renders the
 * list of registered widgets with an enable/disable toggle per widget.
 *
 * Pure emitter: no DI, no constructor state, no `$this`. Takes typed inputs
 * (the widget catalog + the set of enabled classes) and returns HTML. The
 * caller (WidgetManagerPage) handles the form action, nonce, and save.
 *
 * @package Vector\ElementorWidgets\Admin
 */

declare( strict_types=1 );

namespace Vector\ElementorWidgets\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Widget Manager view — static HTML emitter.
 */
final class WidgetManagerView {

	/**
	 * Render the full widget manager form.
	 *
	 * @param array<int, array{slug: string, class: string, title: string, description: string, icon: string}> $widgets Widget catalog entries.
	 * @param array<int, string>                                                                               $enabled_slugs Enabled widget slugs.
	 * @param string                                                                                           $nonce           Form nonce value.
	 * @param string                                                                                           $action          Form action name.
	 *
	 * @return string
	 */
	public static function render( array $widgets, array $enabled_slugs, string $nonce, string $action ): string {
		$rows = '';
		foreach ( $widgets as $widget ) {
			$rows .= self::row( $widget, $enabled_slugs );
		}

		return sprintf(
			'<div class="vew-widget-manager" data-vew-widget-manager>
				<form method="post" action="%1$s">
					%2$s
					<input type="hidden" name="vew_widget_manager_action" value="%3$s" />
					<div class="vew-widget-manager__list" role="list">
						%4$s
					</div>
					<p class="submit">
						<button type="submit" class="button button-primary">%5$s</button>
					</p>
				</form>
			</div>',
			esc_url( admin_url( 'admin-post.php' ) ),
			wp_nonce_field( $action, '_vew_widget_manager_nonce', true, false ),
			esc_attr( $action ),
			$rows, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- each row is fully escaped by self::row().
			esc_html__( 'Save Widget Settings', 'vector-elementor-widgets' )
		);
	}

	/**
	 * Render a single widget row with its enable toggle.
	 *
	 * @param array{slug: string, class: string, title: string, description: string, icon: string} $widget Widget definition.
	 * @param array<int, string>                                                                   $enabled_slugs Enabled widget slugs.
	 *
	 * @return string
	 */
	private static function row( array $widget, array $enabled_slugs ): string {
		$enabled = in_array( $widget['slug'], $enabled_slugs, true );
		$checked = $enabled ? ' checked="checked"' : '';

		return sprintf(
			'<div class="vew-widget-manager__row" role="listitem">
				<label class="vew-widget-manager__toggle">
					<input type="checkbox" name="vew_widgets[]" value="%1$s"%2$s />
					<span class="vew-widget-manager__title">%3$s</span>
				</label>
				<p class="vew-widget-manager__desc">%4$s</p>
				<code class="vew-widget-manager__slug">%5$s</code>
			</div>',
			esc_attr( $widget['slug'] ),
			$checked, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static literal.
			esc_html( $widget['title'] ),
			esc_html( $widget['description'] ),
			esc_html( $widget['slug'] )
		);
	}
}
