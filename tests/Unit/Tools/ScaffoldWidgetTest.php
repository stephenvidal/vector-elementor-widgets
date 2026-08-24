<?php
/**
 * Scaffold tool output tests.
 *
 * Runs `tools/scaffold-widget.php` against a temporary sandbox copy of the
 * plugin (so the real tree is never mutated), then asserts the generated
 * files match the established widget pattern and the two registration
 * points (Plugin.php + WidgetCatalog.php) are patched correctly.
 *
 * @package Vector\ElementorWidgets\Tests\Unit\Tools
 */

declare( strict_types=1 );

namespace Vector\ElementorWidgets\Tests\Unit\Tools;

use Vector\ElementorWidgets\Tests\Unit\TestCase;

/**
 * Scaffold tool lexical tests.
 */
final class ScaffoldWidgetTest extends TestCase {

	/**
	 * Plugin root (real tree).
	 *
	 * @var string
	 */
	private string $root;

	/**
	 * Temporary sandbox root.
	 *
	 * @var string
	 */
	private string $sandbox;

	/**
	 * Set up the sandbox copy.
	 *
	 * @return void
	 */
	protected function setUp(): void {
		parent::setUp();
		$this->root    = dirname( __DIR__, 3 );
		$this->sandbox = sys_get_temp_dir() . '/vew-scaffold-' . uniqid( '', true );

		// Copy the tool + the two files it patches into the sandbox.
		$this->copy( 'tools/scaffold-widget.php' );
		$this->copy( 'src/Plugin.php' );
		$this->copy( 'src/Elementor/WidgetCatalog.php' );
	}

	/**
	 * Tear down the sandbox.
	 *
	 * @return void
	 */
	protected function tearDown(): void {
		$this->rm( $this->sandbox );
		parent::tearDown();
	}

	/**
	 * Copy a file from the real tree into the sandbox, preserving dirs.
	 *
	 * @param string $rel Relative path.
	 *
	 * @return void
	 */
	private function copy( string $rel ): void {
		$src = $this->root . '/' . $rel;
		$dst = $this->sandbox . '/' . $rel;
		if ( ! is_dir( dirname( $dst ) ) ) {
			mkdir( dirname( $dst ), 0755, true );
		}
		copy( $src, $dst );
	}

	/**
	 * Recursively remove a directory.
	 *
	 * @param string $dir Directory path.
	 *
	 * @return void
	 */
	private function rm( string $dir ): void {
		if ( ! is_dir( $dir ) ) {
			return;
		}
		$items = scandir( $dir );
		foreach ( $items as $item ) {
			if ( '.' === $item || '..' === $item ) {
				continue;
			}
			$path = $dir . '/' . $item;
			if ( is_dir( $path ) ) {
				$this->rm( $path );
			} else {
				unlink( $path );
			}
		}
		rmdir( $dir );
	}

	/**
	 * Run the scaffold tool in the sandbox.
	 *
	 * @param string $name Widget name.
	 * @param string $slug Widget slug.
	 * @param bool   $js   Whether to pass --js.
	 *
	 * @return array{code: int, out: string}
	 */
	private function run_scaffold( string $name, string $slug, bool $js = false ): array {
		$cmd = 'php ' . escapeshellarg( $this->sandbox . '/tools/scaffold-widget.php' )
			. ' ' . escapeshellarg( $name )
			. ' --slug=' . escapeshellarg( $slug );
		if ( $js ) {
			$cmd .= ' --js';
		}
		$out = array();
		$code = 0;
		exec( $cmd . ' 2>&1', $out, $code );
		return array( 'code' => $code, 'out' => implode( "\n", $out ) );
	}

	/**
	 * The scaffold writes all expected files.
	 *
	 * @return void
	 */
	public function test_generates_all_expected_files(): void {
		$result = $this->run_scaffold( 'Lexical', 'vew-lexical' );
		$this->assertSame( 0, $result['code'], $result['out'] );

		$expected = array(
			'src/Elementor/Widget/Lexical.php',
			'src/Elementor/Control/LexicalContentControls.php',
			'src/Elementor/Control/LexicalStyleControls.php',
			'widgets/Lexical/vew-lexical.css',
		);
		foreach ( $expected as $rel ) {
			$this->assertFileExists( $this->sandbox . '/' . $rel, "Missing {$rel}" );
		}
		// No JS unless requested.
		$this->assertFileDoesNotExist( $this->sandbox . '/widgets/Lexical/vew-lexical.js' );
	}

	/**
	 * The widget file matches the established pattern.
	 *
	 * @return void
	 */
	public function test_widget_file_matches_pattern(): void {
		$this->run_scaffold( 'Lexical', 'vew-lexical' );
		$src = file_get_contents( $this->sandbox . '/src/Elementor/Widget/Lexical.php' );

		$this->assertStringContainsString( 'final class Lexical extends BaseWidget', $src );
		$this->assertStringContainsString( "return 'vew-lexical';", $src );
		$this->assertStringContainsString( 'return array( \'vector-widgets\' );', $src );
		$this->assertStringContainsString( '( new LexicalContentControls() )->register( $this );', $src );
		$this->assertStringContainsString( '( new LexicalStyleControls() )->register( $this );', $src );
		$this->assertStringContainsString( 'protected function render(): void', $src );
		$this->assertStringContainsString( 'return array( \'vew-lexical\' );', $src );
		// No script dependency when --js is absent.
		$this->assertStringNotContainsString( 'get_script_depends', $src );
		// Class closes cleanly (no dangling brace).
		$this->assertStringEndsWith( "}\n", $src );
	}

	/**
	 * The --js flag adds a script dependency and a JS asset.
	 *
	 * @return void
	 */
	public function test_js_flag_adds_script_dependency_and_asset(): void {
		$this->run_scaffold( 'Lexical', 'vew-lexical', true );
		$src = file_get_contents( $this->sandbox . '/src/Elementor/Widget/Lexical.php' );

		$this->assertStringContainsString( 'get_script_depends', $src );
		$this->assertStringContainsString( "return array( 'vew-lexical' );", $src );
		$this->assertFileExists( $this->sandbox . '/widgets/Lexical/vew-lexical.js' );
	}

	/**
	 * The content and style control files match the pattern.
	 *
	 * @return void
	 */
	public function test_control_files_match_pattern(): void {
		$this->run_scaffold( 'Lexical', 'vew-lexical' );

		$content = file_get_contents( $this->sandbox . '/src/Elementor/Control/LexicalContentControls.php' );
		$this->assertStringContainsString( 'final class LexicalContentControls', $content );
		$this->assertStringContainsString( 'public function register( Widget_Base $widget ): void', $content );
		$this->assertStringContainsString( "'vew_lexical_content'", $content );

		$style = file_get_contents( $this->sandbox . '/src/Elementor/Control/LexicalStyleControls.php' );
		$this->assertStringContainsString( 'final class LexicalStyleControls', $style );
		$this->assertStringContainsString( 'TAB_STYLE', $style );
		$this->assertStringContainsString( "'vew_lexical_style'", $style );
	}

	/**
	 * Plugin.php is patched with use + register + asset.
	 *
	 * @return void
	 */
	public function test_plugin_is_patched(): void {
		$this->run_scaffold( 'Lexical', 'vew-lexical' );
		$src = file_get_contents( $this->sandbox . '/src/Plugin.php' );

		$this->assertStringContainsString( 'use Vector\\ElementorWidgets\\Elementor\\Widget\\Lexical;', $src );
		$this->assertStringContainsString( "->register( Lexical::class );", $src );
		$this->assertStringContainsString( "register_style( 'vew-lexical', 'widgets/Lexical/vew-lexical.css' );", $src );
	}

	/**
	 * WidgetCatalog.php is patched with use + catalog entry.
	 *
	 * @return void
	 */
	public function test_catalog_is_patched(): void {
		$this->run_scaffold( 'Lexical', 'vew-lexical' );
		$src = file_get_contents( $this->sandbox . '/src/Elementor/WidgetCatalog.php' );

		$this->assertStringContainsString( 'use Vector\\ElementorWidgets\\Elementor\\Widget\\Lexical;', $src );
		$this->assertStringContainsString( "'slug'        => 'vew-lexical',", $src );
		$this->assertStringContainsString( 'Lexical::class', $src );
	}

	/**
	 * The catalog entry is added exactly once (no duplicate corruption).
	 *
	 * @return void
	 */
	public function test_catalog_entry_added_once(): void {
		$this->run_scaffold( 'Lexical', 'vew-lexical' );
		$src = file_get_contents( $this->sandbox . '/src/Elementor/WidgetCatalog.php' );

		$this->assertSame( 1, substr_count( $src, "'slug'        => 'vew-lexical'," ) );
		$this->assertSame( 1, substr_count( $src, 'use Vector\\ElementorWidgets\\Elementor\\Widget\\Lexical;' ) );
	}

	/**
	 * Running twice aborts (idempotency guard).
	 *
	 * @return void
	 */
	public function test_second_run_aborts(): void {
		$this->run_scaffold( 'Lexical', 'vew-lexical' );
		$second = $this->run_scaffold( 'Lexical', 'vew-lexical' );

		$this->assertNotSame( 0, $second['code'] );
		$this->assertStringContainsString( 'already exists', $second['out'] );
	}
}
