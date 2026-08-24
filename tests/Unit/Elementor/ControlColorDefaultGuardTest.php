<?php
/**
 * Guard against the kit-theming token trap in the controls layer.
 *
 * A COLOR control that declares BOTH a hardcoded `default` AND a `selectors`
 * entry is a bug: Elementor bakes the default into each post's generated CSS
 * at save time, and that baked rule then overrides the widget stylesheet's
 * `var(--token, ...)` on every page load. The result is a color that can never
 * be re-themed by a site kit (e.g. white text on a light kit = invisible).
 *
 * The correct pattern (modeled by `hero_background_color` / `background` /
 * `primary_button_color`) is to OMIT the `default` so the widget stylesheet's
 * kit token resolves. This guard fails the build if anyone reintroduces a
 * baked color default on a selector-driven control.
 *
 * The control classes are read as source because building a real control
 * stack needs `Elementor\Widget_Base`, which the unit suite does not have.
 * A lexical scan still catches the exact mistake that ships.
 *
 * @package Vector\ElementorWidgets\Tests\Unit\Elementor
 */

declare( strict_types=1 );

namespace Vector\ElementorWidgets\Tests\Unit\Elementor;

use Vector\ElementorWidgets\Tests\Unit\TestCase;

final class ControlColorDefaultGuardTest extends TestCase {

	/**
	 * Absolute path to the control-class directory.
	 *
	 * @return string
	 */
	private function control_dir(): string {
		return dirname( __DIR__, 3 ) . '/src/Elementor/Control';
	}

	/**
	 * Every *StyleControls.php file in the control directory.
	 *
	 * @return array<int, string> basenames.
	 */
	private function style_control_files(): array {
		$files = array();
		foreach ( (array) glob( $this->control_dir() . '/*StyleControls.php' ) as $path ) {
			$files[] = basename( (string) $path );
		}
		sort( $files );
		return $files;
	}

	/**
	 * No COLOR control may combine a hardcoded `default` with a `selectors` entry.
	 *
	 * @return void
	 */
	public function test_no_color_control_combines_default_with_selectors(): void {
		$violations = array();

		foreach ( $this->style_control_files() as $file ) {
			$source = file_get_contents( $this->control_dir() . '/' . $file );
			$this->assertIsString( $source );

			// Split the file into per-control blocks on each add_control call.
			$blocks = preg_split( '/\$widget->add_control\(/', $source );
			$this->assertIsArray( $blocks );
			array_shift( $blocks ); // Drop everything before the first add_control.

			foreach ( $blocks as $i => $block ) {
				$is_color = (bool) preg_match( '/Controls_Manager::COLOR/', $block );
				if ( ! $is_color ) {
					continue;
				}

				$has_default  = (bool) preg_match( '/\'default\'\s*=>\s*\'[^\']+\'/', $block );
				$has_selectors = (bool) preg_match( '/\'selectors\'\s*=>/', $block );

				if ( $has_default && $has_selectors ) {
					$violations[] = sprintf( '%s (add_control #%d)', $file, $i + 1 );
				}
			}
		}

		$this->assertSame(
			array(),
			$violations,
			"COLOR controls must NOT combine a hardcoded 'default' with 'selectors' — "
			. "the default gets baked into post CSS and overrides the kit token on every page:\n"
			. print_r( $violations, true )
		);
	}

	/**
	 * Sanity check on the scanner itself: it must actually see the COLOR type
	 * and the selectors key, otherwise the guard above passes vacuously.
	 *
	 * @return void
	 */
	public function test_scanner_detects_color_and_selectors(): void {
		$source = file_get_contents( $this->control_dir() . '/HeroStyleControls.php' );
		$this->assertIsString( $source );

		$this->assertStringContainsString( 'Controls_Manager::COLOR', $source );
		$this->assertStringContainsString( "'selectors' =>", $source );
		// The known-good background control intentionally has NO default.
		$this->assertStringContainsString( 'hero_background_color', $source );
	}
}
