<?php
/**
 * Guard anchor-jump offsets for sticky headers.
 *
 * A sticky header covers the top of the viewport, so a plain `#section` jump
 * lands the target's heading *underneath* the bar. `scroll-margin-top` is the
 * fix — but only if it applies to the element that actually carries the id.
 *
 * The kit compiler previously matched a hardcoded allow-list of ids
 * (#top, #home, #services, …). Any section id an editor invented — #about,
 * #ministries, #watch, #give — silently received no offset, so those links
 * scrolled to the wrong place with no error anywhere. The generic
 * section[id] selector removes that whole failure class.
 *
 * @package Vector\ElementorWidgets\Tests\Unit\Kit
 */

declare( strict_types=1 );

namespace Vector\ElementorWidgets\Tests\Unit\Kit;

use Brain\Monkey\Functions;
use Vector\ElementorWidgets\Kit\Kit;
use Vector\ElementorWidgets\Kit\KitCssCompiler;
use Vector\ElementorWidgets\Tests\Unit\TestCase;

final class KitCssCompilerAnchorOffsetTest extends TestCase {

	/**
	 * Stub WP sanitize functions.
	 *
	 * @return void
	 */
	protected function setUp(): void {
		parent::setUp();
		Functions\when( 'sanitize_key' )->returnArg();
		Functions\when( 'sanitize_text_field' )->returnArg();
	}

	/**
	 * Compile a minimal kit to CSS.
	 *
	 * @return string
	 */
	private function compile(): string {
		$kit = Kit::from_array(
			array(
				'slug'   => 'anchor-test',
				'label'  => 'Anchor Test',
				'tokens' => array( 'brand' => '#1c628f' ),
			)
		);
		return ( new KitCssCompiler() )->compile( $kit );
	}

	/**
	 * The offset must target any identified section, not a fixed id allow-list.
	 *
	 * @return void
	 */
	public function test_anchor_offset_is_not_a_hardcoded_id_list(): void {
		$css = $this->compile();

		$this->assertStringContainsString( 'scroll-margin-top', $css );
		$this->assertMatchesRegularExpression(
			'/section\[id\]/',
			$css,
			'Anchor offsets must target any section[id]; a hardcoded id list misses editor-invented ids.'
		);
	}

	/**
	 * Elementor legacy markup puts the id on .elementor-section, so that shape
	 * must be covered too or legacy-structure pages get no offset.
	 *
	 * @return void
	 */
	public function test_anchor_offset_covers_elementor_section_markup(): void {
		$css = $this->compile();

		$this->assertStringContainsString( '.elementor-section[id]', $css );
	}

	/**
	 * Elementor's Menu Anchor widget is a bare <div>, not a <section>, so it
	 * needs its own entry or those jumps land behind the sticky bar.
	 *
	 * @return void
	 */
	public function test_anchor_offset_covers_menu_anchor_widget(): void {
		$css = $this->compile();

		$this->assertStringContainsString(
			'.elementor-menu-anchor[id]',
			$css,
			'Menu Anchor targets are <div> elements and must receive the scroll offset.'
		);
	}

	/**
	 * A mobile override must exist: the header is shorter on small screens.
	 *
	 * @return void
	 */
	public function test_anchor_offset_has_mobile_override(): void {
		$css = $this->compile();

		$this->assertMatchesRegularExpression(
			'/@media \(max-width: 820px\)[\s\S]*?scroll-margin-top/',
			$css,
			'Anchor offsets need a mobile override so the offset matches the shorter mobile header.'
		);
	}
}
