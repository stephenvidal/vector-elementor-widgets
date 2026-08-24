<?php
/**
 * Guard Elementor control/section id uniqueness WITHIN each widget.
 *
 * Elementor keys every control and section by id in one flat stack PER
 * WIDGET. Declaring the same id twice inside one widget does not raise an
 * exception — it logs `_doing_it_wrong( 'Cannot redeclare control with same
 * name' )` and drops the second declaration. On a production site with
 * `WP_DEBUG` off that is completely silent, and the controls that should
 * have lived in the dropped section land somewhere unpredictable.
 *
 * IMPORTANT: Elementor scopes controls per-widget. Two DIFFERENT widgets may
 * freely reuse the same id (e.g. `eyebrow`, `title`) — that is not a bug.
 * So this guard checks for duplicates WITHIN a widget's own control set
 * (the Content + Style files sharing the same widget prefix), not globally.
 *
 * The control classes are read as source because building a real control
 * stack needs `Elementor\Widget_Base`, which the unit suite does not have.
 * A lexical scan still catches the exact mistake that ships: the same id
 * literal passed to `start_controls_section()` or `add_control()` from two
 * different places within one widget.
 *
 * @package Vector\ElementorWidgets\Tests\Unit\Elementor
 */

declare( strict_types=1 );

namespace Vector\ElementorWidgets\Tests\Unit\Elementor;

use Vector\ElementorWidgets\Tests\Unit\TestCase;

final class ControlSectionIdsTest extends TestCase {

	/**
	 * Absolute path to the control-class directory.
	 *
	 * @return string
	 */
	private function control_dir(): string {
		return dirname( __DIR__, 3 ) . '/src/Elementor/Control';
	}

	/**
	 * Group control files by widget prefix.
	 *
	 * A widget's control files share the widget name prefix, e.g.
	 * `CtaContentControls.php` + `CtaStyleControls.php` → prefix `Cta`.
	 * Returns map of prefix => list of files.
	 *
	 * @return array<string, array<int, string>>
	 */
	private function control_files_by_widget(): array {
		$grouped = array();

		foreach ( (array) glob( $this->control_dir() . '/*.php' ) as $path ) {
			$file = basename( (string) $path );
			// Strip the trailing ContentControls / StyleControls suffix.
			$prefix = preg_replace( '/(Content|Style)Controls\.php$/', '', $file );
			if ( '' === $prefix ) {
				continue;
			}
			$grouped[ $prefix ][] = $file;
		}

		return $grouped;
	}

	/**
	 * Collect ids declared via a given Elementor call, grouped by widget.
	 *
	 * @param string $method Either 'start_controls_section' or 'add_control'.
	 * @return array<string, array<string, array<int, string>>> widget => id => files.
	 */
	private function declared_ids_by_widget( string $method ): array {
		$found = array();

		foreach ( $this->control_files_by_widget() as $prefix => $files ) {
			foreach ( $files as $file ) {
				$source = file_get_contents( $this->control_dir() . '/' . $file );
				$this->assertIsString( $source );

				// `$widget->start_controls_section(\n\t'the_id',` — the id is
				// always the first argument and always a single-quoted literal.
				$pattern = '/->' . preg_quote( $method, '/' ) . '\(\s*\n?\s*\'([a-z0-9_]+)\'/';
				if ( ! preg_match_all( $pattern, $source, $matches ) ) {
					continue;
				}

				foreach ( $matches[1] as $id ) {
					$found[ $prefix ][ $id ][] = $file;
				}
			}
		}

		return $found;
	}

	/**
	 * No section id may be declared twice within a single widget.
	 *
	 * @return void
	 */
	public function test_no_section_id_is_declared_twice_within_a_widget(): void {
		$duplicates = array();

		foreach ( $this->declared_ids_by_widget( 'start_controls_section' ) as $prefix => $ids ) {
			foreach ( $ids as $id => $files ) {
				if ( count( $files ) > 1 ) {
					$duplicates[ $prefix ][ $id ] = $files;
				}
			}
		}

		$this->assertSame(
			array(),
			$duplicates,
			"Elementor silently drops a redeclared section (within a widget):\n" . print_r( $duplicates, true )
		);
	}

	/**
	 * No control id may be declared twice within a single widget.
	 *
	 * @return void
	 */
	public function test_no_control_id_is_declared_twice_within_a_widget(): void {
		$duplicates = array();

		foreach ( $this->declared_ids_by_widget( 'add_control' ) as $prefix => $ids ) {
			foreach ( $ids as $id => $files ) {
				if ( count( $files ) > 1 ) {
					$duplicates[ $prefix ][ $id ] = $files;
				}
			}
		}

		$this->assertSame(
			array(),
			$duplicates,
			"Elementor silently drops a redeclared control (within a widget):\n" . print_r( $duplicates, true )
		);
	}

	/**
	 * A control id must not collide with a section id within the same widget
	 * (they share one flat stack).
	 *
	 * @return void
	 */
	public function test_no_control_shares_an_id_with_a_section_within_a_widget(): void {
		$collisions = array();
		$sections   = $this->declared_ids_by_widget( 'start_controls_section' );
		$controls   = $this->declared_ids_by_widget( 'add_control' );

		foreach ( $controls as $prefix => $ids ) {
			$section_ids = isset( $sections[ $prefix ] ) ? array_keys( $sections[ $prefix ] ) : array();
			$intersect   = array_intersect( array_keys( $ids ), $section_ids );
			if ( count( $intersect ) > 0 ) {
				$collisions[ $prefix ] = array_values( $intersect );
			}
		}

		$this->assertSame( array(), $collisions );
	}

	/**
	 * Sanity check on the scanner itself: if the regex stops matching,
	 * the tests above would pass vacuously. Verify known sections per widget.
	 *
	 * @return void
	 */
	public function test_scanner_finds_the_known_sections(): void {
		$sections = $this->declared_ids_by_widget( 'start_controls_section' );

		$this->assertArrayHasKey( 'vew_cta_content', $sections['Cta'] ?? array(), 'Cta owns vew_cta_content.' );
		$this->assertArrayHasKey( 'vew_hero_content', $sections['Hero'] ?? array(), 'Hero owns vew_hero_content.' );
		$this->assertArrayHasKey( 'vew_feature_grid_content', $sections['FeatureGrid'] ?? array(), 'FeatureGrid owns vew_feature_grid_content.' );
		$this->assertArrayHasKey( 'vew_testimonials_content', $sections['Testimonials'] ?? array(), 'Testimonials owns vew_testimonials_content.' );
		$this->assertArrayHasKey( 'vew_faq_content', $sections['Faq'] ?? array(), 'Faq owns vew_faq_content.' );
	}
}
