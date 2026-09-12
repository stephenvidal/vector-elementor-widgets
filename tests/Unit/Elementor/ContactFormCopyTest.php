<?php
/**
 * Guard the Contact widget's editable form-copy contract.
 *
 * The Contact widget's field labels, required note, and privacy note used to be
 * hardcoded in `render()`. They are now editor-settable controls. That
 * introduces the exact silent-failure class this suite already guards
 * elsewhere: `sanitize_settings()` strips any key not on its whitelist, so a
 * control that exists but is missing from the whitelist renders nothing and
 * never errors — the editor changes the label and the page ignores it.
 *
 * These tests pin all three layers together, lexically, because building a real
 * control stack needs `Elementor\Widget_Base`, which the unit suite does not
 * have:
 *   1. the control is declared in ContactContentControls,
 *   2. the key is on Contact::sanitize_settings()'s whitelist,
 *   3. render() actually reads the sanitized variable.
 *
 * @package Vector\ElementorWidgets\Tests\Unit\Elementor
 */

declare( strict_types=1 );

namespace Vector\ElementorWidgets\Tests\Unit\Elementor;

use Vector\ElementorWidgets\Tests\Unit\TestCase;

final class ContactFormCopyTest extends TestCase {

	/**
	 * Setting keys that must be editable, whitelisted, and rendered.
	 *
	 * @return array<int, array<int, string>>
	 */
	public static function editable_copy_keys(): array {
		return array(
			array( 'label_name' ),
			array( 'label_secondary' ),
			array( 'label_email' ),
			array( 'label_phone' ),
			array( 'label_message' ),
			array( 'required_note' ),
			array( 'privacy_note' ),
		);
	}

	/**
	 * Read a source file from the plugin.
	 *
	 * @param string $relative Path relative to the plugin root.
	 *
	 * @return string
	 */
	private function source( string $relative ): string {
		$path = dirname( __DIR__, 3 ) . '/' . $relative;
		$this->assertFileExists( $path, "Missing source file: {$relative}" );
		$contents = file_get_contents( $path );
		$this->assertIsString( $contents );
		return $contents;
	}

	/**
	 * Every editable copy key has a declared control.
	 *
	 * @dataProvider editable_copy_keys
	 *
	 * @param string $key Setting key.
	 *
	 * @return void
	 */
	public function test_control_is_declared( string $key ): void {
		$controls = $this->source( 'src/Elementor/Control/ContactContentControls.php' );
		$this->assertStringContainsString(
			"'" . $key . "'",
			$controls,
			"ContactContentControls must declare a control for '{$key}'."
		);
	}

	/**
	 * Every editable copy key is on the sanitize whitelist, else it is silently
	 * dropped before render and the editor's change never appears.
	 *
	 * @dataProvider editable_copy_keys
	 *
	 * @param string $key Setting key.
	 *
	 * @return void
	 */
	public function test_key_is_whitelisted( string $key ): void {
		$widget = $this->source( 'src/Elementor/Widget/Contact.php' );
		$this->assertStringContainsString(
			"'" . $key . "'",
			$widget,
			"Contact::sanitize_settings() must whitelist '{$key}' or the control's value is stripped."
		);
	}

	/**
	 * Every editable copy key is read into a variable that render() echoes.
	 *
	 * @dataProvider editable_copy_keys
	 *
	 * @param string $key Setting key.
	 *
	 * @return void
	 */
	public function test_key_is_read_from_sanitized_settings( string $key ): void {
		$widget = $this->source( 'src/Elementor/Widget/Contact.php' );
		$this->assertStringContainsString(
			"\$safe['" . $key . "']",
			$widget,
			"Contact::render() must read \$safe['{$key}']."
		);
	}

	/**
	 * The previously hardcoded labels are no longer literal in the markup.
	 *
	 * Guards the regression directly: if someone restores the hardcoded string,
	 * the label stops being editable even though the control still exists.
	 *
	 * @return void
	 */
	public function test_labels_are_no_longer_hardcoded(): void {
		$widget = $this->source( 'src/Elementor/Widget/Contact.php' );

		$this->assertStringNotContainsString(
			"esc_html__( 'Your name', 'vector-elementor-widgets' ); ?> *</label>",
			$widget,
			'The name label must render the editable value, not a hardcoded string.'
		);
		$this->assertStringNotContainsString(
			"esc_html__( 'Fields marked * are required', 'vector-elementor-widgets' ); ?></small>",
			$widget,
			'The required-fields note must render the editable value.'
		);
	}
}
