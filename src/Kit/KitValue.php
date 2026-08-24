<?php
/**
 * Kit value sanitization helpers.
 *
 * Sanitizes the individual color / font / layout values that make up a
 * Site Kit. Everything is whitelisted by token key in KitValue::TOKENS —
 * unknown keys are dropped, not passed through.
 *
 * @package Vector\ElementorWidgets\Kit
 */

declare( strict_types=1 );

namespace Vector\ElementorWidgets\Kit;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Static value sanitizers for Kit config.
 */
final class KitValue {

	/**
	 * Known color tokens. The value is a CSS color: a hex code, rgb()/rgba(),
	 * hsl()/hsla(), a named color, or a linear-gradient() for hero-overlay.
	 *
	 * @var array<string, string>
	 */
	public const TOKENS = array(
		'ink'          => 'color',
		'muted'        => 'color',
		'paper'        => 'color',
		'surface'      => 'color',
		'soft'         => 'color',
		'brand'        => 'color',
		'brand-dark'   => 'color',
		'accent'       => 'color',
		'on-brand'     => 'color',
		'on-brand-dark' => 'color',
		'on-accent'    => 'color',
		'line'         => 'color',
		'shadow'       => 'shadow',
		'hero-overlay'        => 'gradient',
		'hero-overlay-mobile' => 'gradient',
	);
	/**
	 * Known font keys.
	 *
	 * @var array<string, true>
	 */
	public const FONTS = array(
		'display' => true,
		'sans'    => true,
	);

	/**
	 * Known layout keys and their types.
	 *
	 * @var array<string, string>
	 */
	public const LAYOUT = array(
		'container_width'         => 'int',
		'gutter'                  => 'int',
		'gutter_mobile'           => 'int',
		'section_space_y'         => 'size',
		'section_space_y_mobile'  => 'size',
	);

	/**
	 * Whitelist of token keys, for iteration.
	 *
	 * @return array<int, string>
	 */
	public static function token_keys(): array {
		return array_keys( self::TOKENS );
	}

	/**
	 * Sanitize a color value (hex, rgb/rgba/hsl/hsla, or a color name).
	 *
	 * @param string $value Raw value.
	 *
	 * @return string Sanitized value, or '' if invalid.
	 */
	public static function color( string $value ): string {
		$value = trim( $value );
		if ( '' === $value ) {
			return '';
		}
		// Named colors / hex / rgb() / rgba() / hsl() / hsla() / transparent / currentColor.
		if ( preg_match( '/^(#[0-9a-fA-F]{3,8}|transparent|currentColor|[a-zA-Z]+)$/', $value ) ) {
			return $value;
		}
		if ( preg_match( '/^(rgb|rgba|hsl|hsla)\(\s*[\d.\s,%]+\s*\)$/', $value ) ) {
			return $value;
		}
		return '';
	}

	/**
	 * Sanitize a shadow value (a CSS box-shadow with rgba/hex colors).
	 *
	 * @param string $value Raw value.
	 *
	 * @return string Sanitized value, or '' if invalid.
	 */
	public static function shadow( string $value ): string {
		$value = trim( $value );
		if ( '' === $value || str_contains( $value, ';' ) || str_contains( $value, '{' ) || str_contains( $value, '}' ) ) {
			return '';
		}
		// Only allow shadow-shaped values: length + color combos.
		if ( ! preg_match( '/^([\d.]+(px|rem|em|vw|vh)\s*)+(\#[0-9a-fA-F]{3,8}|rgba?\([\d.\s,%]+\)|hsla?\([\d.\s,%]+\)|[a-zA-Z]+)$/', $value ) ) {
			return '';
		}
		return $value;
	}

	/**
	 * Sanitize a gradient value (linear-gradient(...)).
	 *
	 * @param string $value Raw value.
	 *
	 * @return string Sanitized value, or '' if invalid.
	 */
	public static function gradient( string $value ): string {
		$value = trim( $value );
		if ( '' === $value || ! str_starts_with( $value, 'linear-gradient(' ) || ! str_ends_with( $value, ')' ) ) {
			return '';
		}
		if ( str_contains( $value, ';' ) || str_contains( $value, '{' ) || str_contains( $value, '}' ) ) {
			return '';
		}
		// Inside must be direction + color stops (colors, optional % stops).
		// Accepts: `90deg, #color 0%, rgba(...) 45%, ...`. Allow spaces, %, commas.
		$inner = substr( $value, strlen( 'linear-gradient(' ), -1 );
		if ( preg_match( '/^[\d.]+deg\s*,\s*.+$/', $inner ) && ! preg_match( '/[;{}]/', $inner ) ) {
			return $value;
		}
		return '';
	}

	/**
	 * Sanitize a font-family value (CSS font stack).
	 *
	 * @param string $value Raw value.
	 *
	 * @return string Sanitized value, or '' if invalid.
	 */
	public static function font( string $value ): string {
		$value = trim( $value );
		if ( '' === $value || str_contains( $value, ';' ) || str_contains( $value, '{' ) || str_contains( $value, '}' ) ) {
			return '';
		}
		// Font stacks: quoted names, generic families, hyphen/space words, commas.
		// Deliberately excludes characters that could break out of a CSS value.
		if ( preg_match( '/^[A-Za-z0-9 _\-,"\']+$/', $value ) ) {
			return $value;
		}
		return '';
	}

	/**
	 * Sanitize an integer layout value.
	 *
	 * @param mixed $value Raw value.
	 *
	 * @return int Non-negative integer, or 0.
	 */
	public static function layout_int( $value ): int {
		return max( 0, (int) $value );
	}

	/**
	 * Sanitize a size layout value (px number, clamp(), or min()).
	 *
	 * @param string $value Raw value.
	 *
	 * @return string Sanitized value, or '' if invalid.
	 */
	public static function size( string $value ): string {
		$value = trim( $value );
		if ( '' === $value || str_contains( $value, ';' ) || str_contains( $value, '{' ) || str_contains( $value, '}' ) ) {
			return '';
		}
		// Bare length or clamp()/min()/max().
		if ( preg_match( '/^[\d.]+(px|rem|em|vw|vh|%)$/', $value ) ) {
			return $value;
		}
		if ( preg_match( '/^(clamp|min|max)\([\d.,\s\w%()]+\)$/', $value ) ) {
			return $value;
		}
		return '';
	}
}
