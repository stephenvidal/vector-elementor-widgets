<?php
/**
 * Abstract widget base.
 *
 * Establishes the shared conventions every Vector widget follows:
 *  - constructor matches `Widget_Base::__construct( array $data, ?array $args )`
 *  - no `_content_template()` override (all render paths go through render())
 *  - settings are whitelisted + type-coerced before use
 *  - `is_editor_preview()` helper
 *
 * @package Vector\ElementorWidgets\Elementor\Widget
 */

declare( strict_types=1 );

namespace Vector\ElementorWidgets\Elementor\Widget;

use Elementor\Widget_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Abstract base for all Vector widgets.
 */
abstract class BaseWidget extends Widget_Base {

	/**
	 * Constructor.
	 *
	 * Per D-505, the signature MUST match `Widget_Base::__construct`.
	 * Elementor instantiates the widget with its own (data, args) shape
	 * when it loads a saved page document. Domain services are resolved
	 * lazily inside render() — never via constructor injection.
	 *
	 * @param array<string, mixed> $data Widget data.
	 * @param array<string, mixed> $args Widget args.
	 */
	public function __construct( array $data = array(), ?array $args = null ) {
		parent::__construct( $data, $args );
	}

	/**
	 * Whether we are rendering inside the Elementor editor preview.
	 *
	 * @return bool
	 */
	protected function is_editor_preview(): bool {
		return \Elementor\Plugin::$instance->editor->is_edit_mode();
	}

	/**
	 * Sanitize a settings array against a whitelist of key => type.
	 *
	 * Unknown keys are stripped. Types: 'string', 'int', 'array', 'bool',
	 * 'url', 'html'.
	 *
	 * @param array<string, mixed>  $settings Raw settings from get_settings_for_display().
	 * @param array<string, string> $allowed  Whitelist of key => type.
	 *
	 * @return array<string, mixed>
	 */
	protected function sanitize_settings( array $settings, array $allowed ): array {
		$safe = array();
		foreach ( $allowed as $key => $type ) {
			if ( ! array_key_exists( $key, $settings ) ) {
				continue;
			}
			$value = $settings[ $key ];
			switch ( $type ) {
				case 'string':
					$safe[ $key ] = sanitize_text_field( (string) $value );
					break;
				case 'int':
					$safe[ $key ] = absint( $value );
					break;
				case 'bool':
					$safe[ $key ] = (bool) $value;
					break;
				case 'url':
					// Elementor URL controls return an array: array( 'url' => '...', 'is_external' => ..., 'nofollow' => ... ).
					$url = is_array( $value ) ? ( $value['url'] ?? '' ) : $value;
					$safe[ $key ] = esc_url_raw( (string) $url );
					break;
				case 'html':
					$safe[ $key ] = wp_kses_post( (string) $value );
					break;
				case 'array':
					$safe[ $key ] = is_array( $value ) ? $value : array();
					break;
				default:
					$safe[ $key ] = $value;
			}
		}
		return $safe;
	}
}
