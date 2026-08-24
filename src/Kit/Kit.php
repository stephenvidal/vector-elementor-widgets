<?php
/**
 * Site Kit value object.
 *
 * Immutable, validated representation of one site kit's configuration:
 * color tokens, font families, and layout/padding values. Constructed via
 * {@see Kit::from_array()} which sanitizes every field against the
 * whitelist in KitValue and drops anything unknown/invalid.
 *
 * @package Vector\ElementorWidgets\Kit
 */

declare( strict_types=1 );

namespace Vector\ElementorWidgets\Kit;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Immutable Site Kit configuration.
 */
final class Kit {

	/**
	 * Kit slug (post name). Lowercase letters, numbers, hyphen, underscore.
	 *
	 * @var string
	 */
	private string $slug;

	/**
	 * Human label.
	 *
	 * @var string
	 */
	private string $label;

	/**
	 * Description.
	 *
	 * @var string
	 */
	private string $description;

	/**
	 * Sanitized color tokens.
	 *
	 * @var array<string, string>
	 */
	private array $tokens;

	/**
	 * Sanitized font families.
	 *
	 * @var array<string, string>
	 */
	private array $fonts;

	/**
	 * Sanitized layout values.
	 *
	 * @var array<string, int|string>
	 */
	private array $layout;

	/**
	 * Reserved slugs that cannot be used for a user kit.
	 *
	 * @var array<string, true>
	 */
	private const RESERVED = array(
		'default' => true,
		'global'  => true,
	);

	/**
	 * Constructor.
	 *
	 * @param string                    $slug        Slug.
	 * @param string                    $label       Label.
	 * @param string                    $description Description.
	 * @param array<string, string>     $tokens Tokens.
	 * @param array<string, string>     $fonts  Fonts.
	 * @param array<string, int|string> $layout Layout.
	 */
	private function __construct(
		string $slug,
		string $label,
		string $description,
		array $tokens,
		array $fonts,
		array $layout
	) {
		$this->slug        = $slug;
		$this->label       = $label;
		$this->description = $description;
		$this->tokens      = $tokens;
		$this->fonts       = $fonts;
		$this->layout      = $layout;
	}

	/**
	 * Build a Kit from a raw (possibly untrusted) config array.
	 *
	 * @param array<string, mixed> $data Raw config.
	 *
	 * @return self
	 *
	 * @throws \InvalidArgumentException If the slug is empty, invalid, or reserved.
	 */
	public static function from_array( array $data ): self {
		$slug = isset( $data['slug'] ) ? (string) $data['slug'] : '';
		$slug = sanitize_key( $slug );

		// Enforce the slug pattern directly (do not rely on sanitize_key alone).
		if ( '' === $slug || ! preg_match( '/^[a-z0-9-_]+$/', $slug ) || isset( self::RESERVED[ $slug ] ) ) {
			throw new \InvalidArgumentException( 'Invalid kit slug.' ); // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped
		}

		$label       = isset( $data['label'] ) ? sanitize_text_field( (string) $data['label'] ) : $slug;
		$description = isset( $data['description'] ) ? sanitize_text_field( (string) $data['description'] ) : '';

		$tokens = array();
		if ( isset( $data['tokens'] ) && is_array( $data['tokens'] ) ) {
			foreach ( KitValue::TOKENS as $key => $type ) {
				if ( ! isset( $data['tokens'][ $key ] ) ) {
					continue;
				}
				$raw   = (string) $data['tokens'][ $key ];
				$value = self::sanitize_token( $key, $type, $raw );
				if ( '' !== $value ) {
					$tokens[ $key ] = $value;
				}
			}
		}

		$fonts = array();
		if ( isset( $data['fonts'] ) && is_array( $data['fonts'] ) ) {
			foreach ( array_keys( KitValue::FONTS ) as $key ) {
				if ( ! isset( $data['fonts'][ $key ] ) ) {
					continue;
				}
				$value = KitValue::font( (string) $data['fonts'][ $key ] );
				if ( '' !== $value ) {
					$fonts[ $key ] = $value;
				}
			}
		}

		$layout = array();
		if ( isset( $data['layout'] ) && is_array( $data['layout'] ) ) {
			foreach ( KitValue::LAYOUT as $key => $type ) {
				if ( ! isset( $data['layout'][ $key ] ) ) {
					continue;
				}
				if ( 'int' === $type ) {
					$layout[ $key ] = KitValue::layout_int( $data['layout'][ $key ] );
				} else {
					$value = KitValue::size( (string) $data['layout'][ $key ] );
					if ( '' !== $value ) {
						$layout[ $key ] = $value;
					}
				}
			}
		}

		return new self( $slug, $label, $description, $tokens, $fonts, $layout );
	}

	/**
	 * Sanitize a single token by its declared type.
	 *
	 * @param string $key  Token key.
	 * @param string $type Token type (color|shadow|gradient).
	 * @param string $raw  Raw value.
	 *
	 * @return string Sanitized value.
	 */
	private static function sanitize_token( string $key, string $type, string $raw ): string {
		switch ( $type ) {
			case 'color':
				return KitValue::color( $raw );
			case 'shadow':
				return KitValue::shadow( $raw );
			case 'gradient':
				return KitValue::gradient( $raw );
			default:
				return '';
		}
	}

	/**
	 * Get the slug.
	 *
	 * @return string
	 */
	public function slug(): string {
		return $this->slug;
	}

	/**
	 * Get the label.
	 *
	 * @return string
	 */
	public function label(): string {
		return $this->label;
	}

	/**
	 * Get the description.
	 *
	 * @return string
	 */
	public function description(): string {
		return $this->description;
	}

	/**
	 * Get color tokens.
	 *
	 * @return array<string, string>
	 */
	public function tokens(): array {
		return $this->tokens;
	}

	/**
	 * Get fonts.
	 *
	 * @return array<string, string>
	 */
	public function fonts(): array {
		return $this->fonts;
	}

	/**
	 * Get layout.
	 *
	 * @return array<string, int|string>
	 */
	public function layout(): array {
		return $this->layout;
	}

	/**
	 * Get one token value, or '' if absent.
	 *
	 * @param string $key Token key.
	 *
	 * @return string
	 */
	public function token( string $key ): string {
		return $this->tokens[ $key ] ?? '';
	}

	/**
	 * Get one font value, or '' if absent.
	 *
	 * @param string $key Font key.
	 *
	 * @return string
	 */
	public function font( string $key ): string {
		return $this->fonts[ $key ] ?? '';
	}

	/**
	 * Get one layout value, or null if absent.
	 *
	 * @param string $key Layout key.
	 *
	 * @return int|string|null
	 */
	public function layout_value( string $key ) {
		return $this->layout[ $key ] ?? null;
	}

	/**
	 * Export the kit as a plain config array (for persistence + JSON).
	 *
	 * @return array<string, mixed>
	 */
	public function to_array(): array {
		return array(
			'slug'        => $this->slug,
			'label'       => $this->label,
			'description' => $this->description,
			'tokens'      => $this->tokens,
			'fonts'       => $this->fonts,
			'layout'      => $this->layout,
		);
	}
}
