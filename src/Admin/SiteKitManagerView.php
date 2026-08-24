<?php
/**
 * Site Kit Manager view.
 *
 * A static HTML emitter for the Site Kit admin screen: lists kits, offers a
 * global-kit selector, an export/delete action per kit, and a create/edit
 * form for a kit's tokens, fonts, and layout.
 *
 * Pure emitter: no DI, no constructor state, no `$this`. Takes typed inputs
 * and returns HTML. The caller (SiteKitManagerPage) handles actions + nonce.
 *
 * @package Vector\ElementorWidgets\Admin
 */

declare( strict_types=1 );

namespace Vector\ElementorWidgets\Admin;

use Vector\ElementorWidgets\Kit\Kit;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Site Kit Manager view — static HTML emitter.
 */
final class SiteKitManagerView {

	/**
	 * Render the kit manager screen.
	 *
	 * @param array<string, Kit> $kits         Map of slug => Kit.
	 * @param string             $global_slug  Currently selected global kit slug ('' = none).
	 * @param string             $nonce        Form nonce.
	 * @param string             $action       Form action name.
	 * @param Kit|null           $editing      Kit being edited (null = show create form).
	 * @param string             $list_url     URL back to the list.
	 *
	 * @return string
	 */
	public static function render(
		array $kits,
		string $global_slug,
		string $nonce,
		string $action,
		?Kit $editing = null,
		string $list_url = ''
	): string {
		$list = self::render_list( $kits, $global_slug, $nonce, $action, $list_url );
		$form = self::form( $editing, $nonce, $action, $list_url );
		$import = self::import_form( $nonce, $action );

		return '<div class="vew-site-kits">'
			. '<h1>' . esc_html__( 'Site Kits', 'vector-elementor-widgets' ) . '</h1>'
			. '<p>' . esc_html__( 'Manage the design tokens (colors, fonts, spacing) that theme every Vector widget. Assign a kit globally or per page.', 'vector-elementor-widgets' ) . '</p>'
			. $list
			. '<hr />'
			. $import
			. '<hr />'
			. $form
			. '</div>';
	}

	/**
	 * Render the JSON import form.
	 *
	 * @param string $nonce  Nonce.
	 * @param string $action Action.
	 *
	 * @return string
	 */
	private static function import_form( string $nonce, string $action ): string {
		return sprintf(
			'<h2>%1$s</h2>
			<form method="post" action="%2$s" class="vew-site-kits__import" enctype="multipart/form-data">
				%3$s
				<input type="hidden" name="vew_site_kit_action" value="import" />
				<label for="vew_site_kit_import">%4$s</label>
				<input type="file" id="vew_site_kit_import" name="vew_site_kit_import" accept="application/json" />
				<button type="submit" class="button">%5$s</button>
			</form>',
			esc_html__( 'Import a kit', 'vector-elementor-widgets' ),
			esc_url( admin_url( 'admin-post.php' ) ),
			wp_nonce_field( $action, '_vew_site_kit_nonce', true, false ),
			esc_html__( 'JSON file', 'vector-elementor-widgets' ),
			esc_html__( 'Import kit', 'vector-elementor-widgets' )
		);
	}

	/**
	 * Render the kit list + global selector + per-kit actions.
	 *
	 * @param array<string, Kit> $kits        Map of slug => Kit.
	 * @param string             $global_slug Global kit slug.
	 * @param string             $nonce       Nonce.
	 * @param string             $action      Action.
	 * @param string             $list_url    Manager URL.
	 *
	 * @return string
	 */
	private static function render_list(
		array $kits,
		string $global_slug,
		string $nonce,
		string $action,
		string $list_url
	): string {
		$rows = '';
		foreach ( $kits as $slug => $kit ) {
			$rows .= self::row( $slug, $kit, $nonce, $action, $list_url );
		}

		if ( '' === $rows ) {
			$rows = '<p class="vew-site-kits__empty">' . esc_html__( 'No site kits yet. Create one below.', 'vector-elementor-widgets' ) . '</p>';
		}

		$global_options = '<option value="">' . esc_html__( '— None (use built-in default) —', 'vector-elementor-widgets' ) . '</option>';
		foreach ( $kits as $slug => $kit ) {
			$selected = ( $slug === $global_slug ) ? ' selected="selected"' : '';
			$global_options .= '<option value="' . esc_attr( $slug ) . '"' . $selected . '>' . esc_html( $kit->label() ) . ' (' . esc_html( $slug ) . ')</option>';
		}

		return sprintf(
			'<h2>%1$s</h2>
			<form method="post" action="%2$s" class="vew-site-kits__global">
				%3$s
				<input type="hidden" name="vew_site_kit_action" value="set_global" />
				<label for="vew_global_site_kit">%4$s</label>
				<select id="vew_global_site_kit" name="vew_global_site_kit">%5$s</select>
				<button type="submit" class="button">%6$s</button>
			</form>
			<table class="widefat striped vew-site-kits__table">
				<thead><tr><th>%7$s</th><th>%8$s</th><th>%9$s</th></tr></thead>
				<tbody>%10$s</tbody>
			</table>',
			esc_html__( 'Existing kits', 'vector-elementor-widgets' ),
			esc_url( admin_url( 'admin-post.php' ) ),
			wp_nonce_field( $action, '_vew_site_kit_nonce', true, false ),
			esc_html__( 'Global kit', 'vector-elementor-widgets' ),
			$global_options, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- each option escaped above.
			esc_html__( 'Set global', 'vector-elementor-widgets' ),
			esc_html__( 'Slug', 'vector-elementor-widgets' ),
			esc_html__( 'Label', 'vector-elementor-widgets' ),
			esc_html__( 'Actions', 'vector-elementor-widgets' ),
			$rows // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- each row escaped in self::row().
		);
	}

	/**
	 * Render a single kit row with edit/export/delete actions.
	 *
	 * @param string $slug      Kit slug.
	 * @param Kit    $kit       Kit.
	 * @param string $nonce     Nonce.
	 * @param string $action    Action.
	 * @param string $list_url  Manager URL.
	 *
	 * @return string
	 */
	private static function row( string $slug, Kit $kit, string $nonce, string $action, string $list_url ): string {
		$edit   = esc_url( add_query_arg( array( 'vew_site_kit_edit' => $slug ), $list_url ) );
		$export = sprintf(
			'<form method="post" action="%1$s" style="display:inline">
				%2$s
				<input type="hidden" name="vew_site_kit_action" value="export" />
				<input type="hidden" name="vew_site_kit_slug" value="%3$s" />
				<button type="submit" class="button">%4$s</button>
			</form>',
			esc_url( admin_url( 'admin-post.php' ) ),
			wp_nonce_field( $action, '_vew_site_kit_nonce', true, false ),
			esc_attr( $slug ),
			esc_html__( 'Export', 'vector-elementor-widgets' )
		);
		$delete = sprintf(
			'<form method="post" action="%1$s" style="display:inline" onsubmit="return confirm(\'%2$s\')">
				%3$s
				<input type="hidden" name="vew_site_kit_action" value="delete" />
				<input type="hidden" name="vew_site_kit_slug" value="%4$s" />
				<button type="submit" class="button">%5$s</button>
			</form>',
			esc_url( admin_url( 'admin-post.php' ) ),
			esc_js( __( 'Delete this site kit?', 'vector-elementor-widgets' ) ),
			wp_nonce_field( $action, '_vew_site_kit_nonce', true, false ),
			esc_attr( $slug ),
			esc_html__( 'Delete', 'vector-elementor-widgets' )
		);

		return sprintf(
			'<tr>
				<td><code>%1$s</code></td>
				<td>%2$s</td>
				<td><a class="button" href="%3$s">%4$s</a> %5$s %6$s</td>
			</tr>',
			esc_html( $slug ),
			esc_html( $kit->label() ),
			$edit,
			esc_html__( 'Edit', 'vector-elementor-widgets' ),
			$export,
			$delete
		);
	}

	/**
	 * Render the create/edit form for a kit.
	 *
	 * @param Kit|null $editing   Kit being edited (null = create).
	 * @param string   $nonce     Nonce.
	 * @param string   $action    Action.
	 * @param string   $list_url  Manager URL.
	 *
	 * @return string
	 */
	private static function form( ?Kit $editing, string $nonce, string $action, string $list_url ): string {
		$heading = $editing
			// translators: %s: kit label.
			? sprintf( __( 'Edit kit: %s', 'vector-elementor-widgets' ), $editing->label() )
			: __( 'Create a new site kit', 'vector-elementor-widgets' );

		$slug_input = '';
		if ( null === $editing ) {
			$slug_input = '<p><label for="vew_kit_slug">' . esc_html__( 'Slug (unique id)', 'vector-elementor-widgets' ) . '</label><br />'
				. '<input type="text" id="vew_kit_slug" name="vew_kit[slug]" class="regular-text" /></p>';
		} else {
			$slug_input = '<p><label>' . esc_html__( 'Slug', 'vector-elementor-widgets' ) . '</label>: <code>' . esc_html( $editing->slug() ) . '</code>'
				. '<input type="hidden" name="vew_kit[slug]" value="' . esc_attr( $editing->slug() ) . '" /></p>';
		}

		$tokens = self::token_inputs( $editing );
		$fonts  = self::font_inputs( $editing );
		$layout = self::layout_inputs( $editing );

		return sprintf(
			'<h2>%1$s</h2>
			<form method="post" action="%2$s" class="vew-site-kits__form">
				%3$s
				<input type="hidden" name="vew_site_kit_action" value="save" />
				<p><label for="vew_kit_label">%4$s</label><br />
					<input type="text" id="vew_kit_label" name="vew_kit[label]" class="regular-text" value="%5$s" /></p>
				<p><label for="vew_kit_description">%6$s</label><br />
					<textarea id="vew_kit_description" name="vew_kit[description]" class="large-text" rows="2">%7$s</textarea></p>
				%8$s
				%9$s
				%10$s
				%11$s
				<p class="submit"><button type="submit" class="button button-primary">%12$s</button> %13$s</p>
			</form>',
			esc_html( $heading ),
			esc_url( admin_url( 'admin-post.php' ) ),
			wp_nonce_field( $action, '_vew_site_kit_nonce', true, false ),
			esc_html__( 'Label', 'vector-elementor-widgets' ),
			esc_attr( $editing ? $editing->label() : '' ),
			esc_html__( 'Description', 'vector-elementor-widgets' ),
			esc_textarea( $editing ? $editing->description() : '' ),
			$slug_input,
			$tokens,
			$fonts,
			$layout,
			esc_html__( 'Save Kit', 'vector-elementor-widgets' ),
			'' !== $list_url ? '<a class="button" href="' . esc_url( $list_url ) . '">' . esc_html__( 'Cancel', 'vector-elementor-widgets' ) . '</a>' : ''
		);
	}

	/**
	 * Render the token inputs.
	 *
	 * @param Kit|null $editing Kit being edited.
	 *
	 * @return string
	 */
	private static function token_inputs( ?Kit $editing ): string {
		$out = '<h3>' . esc_html__( 'Colors', 'vector-elementor-widgets' ) . '</h3><table class="form-table">';
		foreach ( \Vector\ElementorWidgets\Kit\KitValue::TOKENS as $key => $type ) {
			$value = $editing ? $editing->token( $key ) : '';
			$out  .= '<tr><th><label for="vew_tokens_' . esc_attr( $key ) . '">' . esc_html( '--' . $key ) . '</label></th>'
				. '<td><input type="' . ( 'gradient' === $type ? 'text' : 'text' ) . '" id="vew_tokens_' . esc_attr( $key ) . '" name="vew_kit[tokens][' . esc_attr( $key ) . ']" class="regular-text" value="' . esc_attr( $value ) . '" /></td></tr>';
		}
		$out .= '</table>';
		return $out;
	}

	/**
	 * Render the font inputs.
	 *
	 * @param Kit|null $editing Kit being edited.
	 *
	 * @return string
	 */
	private static function font_inputs( ?Kit $editing ): string {
		$out = '<h3>' . esc_html__( 'Fonts', 'vector-elementor-widgets' ) . '</h3><table class="form-table">';
		foreach ( array_keys( \Vector\ElementorWidgets\Kit\KitValue::FONTS ) as $key ) {
			$value = $editing ? $editing->font( $key ) : '';
			$out  .= '<tr><th><label for="vew_fonts_' . esc_attr( $key ) . '">' . esc_html( $key ) . '</label></th>'
				. '<td><input type="text" id="vew_fonts_' . esc_attr( $key ) . '" name="vew_kit[fonts][' . esc_attr( $key ) . ']" class="regular-text" value="' . esc_attr( $value ) . '" /></td></tr>';
		}
		$out .= '</table>';
		return $out;
	}

	/**
	 * Render the layout inputs.
	 *
	 * @param Kit|null $editing Kit being edited.
	 *
	 * @return string
	 */
	private static function layout_inputs( ?Kit $editing ): string {
		$out = '<h3>' . esc_html__( 'Layout & spacing', 'vector-elementor-widgets' ) . '</h3><table class="form-table">';
		foreach ( \Vector\ElementorWidgets\Kit\KitValue::LAYOUT as $key => $type ) {
			$value = $editing ? $editing->layout_value( $key ) : '';
			$attr  = 'int' === $type ? 'type="number" min="0"' : 'type="text"';
			$out  .= '<tr><th><label for="vew_layout_' . esc_attr( $key ) . '">' . esc_html( $key ) . '</label></th>'
				. '<td><input ' . $attr . ' id="vew_layout_' . esc_attr( $key ) . '" name="vew_kit[layout][' . esc_attr( $key ) . ']" class="regular-text" value="' . esc_attr( (string) $value ) . '" /></td></tr>';
		}
		$out .= '</table>';
		return $out;
	}
}
