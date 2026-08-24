<?php
/**
 * Kit CSS compiler.
 *
 * Turns a {@see Kit} config into a CSS string of `:root` design tokens plus
 * a `body` baseline, mirroring the structure of the legacy `assets/site-kit.css`.
 * The compiled CSS is enqueued inline by SiteKit so the theme re-colors every
 * widget (which references `var(--token, fallback)`).
 *
 * @package Vector\ElementorWidgets\Kit
 */

declare( strict_types=1 );

namespace Vector\ElementorWidgets\Kit;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Compiles Kit config → CSS.
 */
final class KitCssCompiler {

	/**
	 * Default font stacks used when a kit omits them.
	 */
	private const FALLBACK_DISPLAY = 'Georgia, "Times New Roman", serif';
	private const FALLBACK_SANS    = '"Inter", -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif';

	/**
	 * Compile a kit to CSS.
	 *
	 * @param Kit $kit Kit to compile.
	 *
	 * @return string Compiled CSS (no trailing newline).
	 */
	public function compile( Kit $kit ): string {
		$tokens = $kit->tokens();
		$fonts  = $kit->fonts();
		$layout = $kit->layout();

		$sans    = $fonts['sans'] ?? self::FALLBACK_SANS;
		$display = $fonts['display'] ?? self::FALLBACK_DISPLAY;
		$ink     = $tokens['ink'] ?? '#1a1b26';
		$paper   = $tokens['paper'] ?? '#ffffff';

		$root_lines = array();
		foreach ( KitValue::TOKENS as $key => $_type ) {
			if ( isset( $tokens[ $key ] ) && '' !== $tokens[ $key ] ) {
				$root_lines[] = sprintf( "\t--%s: %s;", $key, $tokens[ $key ] );
			}
		}
		$root_lines[] = sprintf( "\t--display: %s;", $display );
		$root_lines[] = sprintf( "\t--sans: %s;", $sans );

		foreach ( KitValue::LAYOUT as $key => $_type ) {
			if ( isset( $layout[ $key ] ) ) {
				$css_key       = str_replace( '_', '-', $key );
				$root_lines[]  = sprintf( '	--%s: %s;', $css_key, is_int( $layout[ $key ] ) ? $layout[ $key ] . 'px' : $layout[ $key ] );
			}
		}

		$css  = ":root {\n" . implode( "\n", $root_lines ) . "\n}\n\n";
		$css .= "body {\n";
		$css .= sprintf( "\tbackground-color: var(--paper, %s);\n", $paper );
		$css .= sprintf( "\tcolor: var(--ink, %s);\n", $ink );
		$css .= sprintf( "\tfont-family: var(--sans, %s);\n", $sans );
		$css .= "\tline-height: 1.6;\n";
		$css .= "\t-webkit-font-smoothing: antialiased;\n";
		$css .= "}\n\n";
		$css .= ":where(#top, #home, #services, #portfolio, #process, #pricing, #faq, #contact, #footer) {\n";
		$css .= "\tscroll-margin-top: 88px;\n";
		$css .= "}\n\n";
		$css .= "@media (max-width: 820px) {\n";
		$css .= "\t:where(#top, #home, #services, #portfolio, #process, #pricing, #faq, #contact, #footer) {\n";
		$css .= "\t\tscroll-margin-top: 72px;\n";
		$css .= "\t}\n";
		$css .= "}\n";

		return rtrim( $css, "\n" );
	}
}
