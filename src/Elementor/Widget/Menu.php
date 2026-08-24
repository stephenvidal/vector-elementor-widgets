<?php
/**
 * Menu widget.
 *
 * Port of the vidal/ember-oak/hearth-crumb menu list: a heading block + a
 * REPEATER of menu items (name, description, price, optional tag). Per the
 * control-mapping contract `data-copy` → TEXT, `data-menu` → REPEATER.
 *
 * @package Vector\ElementorWidgets\Elementor\Widget
 */

declare( strict_types=1 );

namespace Vector\ElementorWidgets\Elementor\Widget;

use Vector\ElementorWidgets\Elementor\Control\MenuContentControls;
use Vector\ElementorWidgets\Elementor\Control\MenuStyleControls;
use Vector\ElementorWidgets\Elementor\Component\SectionHeading;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Menu widget.
 */
final class Menu extends BaseWidget {

	/**
	 * Widget slug — stable forever.
	 *
	 * @return string
	 */
	public function get_name(): string {
		return 'vew-menu';
	}

	/**
	 * Widget title.
	 *
	 * @return string
	 */
	public function get_title(): string {
		return __( 'Menu', 'vector-elementor-widgets' );
	}

	/**
	 * Widget icon.
	 *
	 * @return string
	 */
	public function get_icon(): string {
		return 'eicon-menu-bar';
	}

	/**
	 * Widget category.
	 *
	 * @return array<int, string>
	 */
	public function get_categories(): array {
		return array( 'vector-widgets' );
	}

	/**
	 * Widget keywords.
	 *
	 * @return array<int, string>
	 */
	public function get_keywords(): array {
		return array( 'menu', 'list', 'price', 'items', 'roast' );
	}

	/**
	 * Register controls.
	 *
	 * @return void
	 */
	protected function register_controls(): void {
		( new MenuContentControls() )->register( $this );
		( new MenuStyleControls() )->register( $this );
	}

	/**
	 * Render the widget.
	 *
	 * The ONLY render path. No `_content_template()` override.
	 *
	 * @return void
	 */
	protected function render(): void {
		$settings = $this->get_settings_for_display();
		$safe     = $this->sanitize_settings(
			$settings,
			array(
				'eyebrow'      => 'string',
				'title'        => 'string',
				'title_accent' => 'string',
				'title_after'  => 'string',
				'intro'        => 'string',
				'items'        => 'array',
			)
		);

		$items = $safe['items'] ?? array();
		?>
		<section class="vew-menu" aria-label="menu">
			<div class="vew-menu__inner">
			<?php echo SectionHeading::render( $safe, array( 'block_class' => 'vew-menu' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- component escapes all values. ?>

			<?php if ( is_array( $items ) && count( $items ) > 0 ) : ?>
				<div class="vew-menu__list">
					<?php foreach ( $items as $item ) : ?>
						<?php
						$i_name  = isset( $item['name'] ) ? sanitize_text_field( (string) $item['name'] ) : '';
						$i_desc  = isset( $item['description'] ) ? sanitize_text_field( (string) $item['description'] ) : '';
						$i_price = isset( $item['price'] ) ? sanitize_text_field( (string) $item['price'] ) : '';
						$i_tag   = isset( $item['tag'] ) ? sanitize_text_field( (string) $item['tag'] ) : '';
						if ( '' === $i_name ) {
							continue;
						}
						?>
						<div class="vew-menu__item">
							<div class="vew-menu__name">
								<h3 class="vew-menu__item-title"><?php echo esc_html( $i_name ); ?></h3>
								<?php if ( '' !== $i_desc ) : ?>
									<p class="vew-menu__item-desc"><?php echo esc_html( $i_desc ); ?></p>
								<?php endif; ?>
							</div>
							<?php if ( '' !== $i_price ) : ?>
								<span class="vew-menu__price"><?php echo esc_html( $i_price ); ?></span>
							<?php endif; ?>
							<?php if ( '' !== $i_tag ) : ?>
								<span class="vew-menu__tag"><?php echo esc_html( $i_tag ); ?></span>
							<?php endif; ?>
						</div>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
			</div>
		</section>
		<?php
	}

	/**
	 * Declare the stylesheet dependency.
	 *
	 * @return array<int, string>
	 */
	public function get_style_depends(): array {
		return array( 'vew-section-heading', 'vew-menu' );
	}
}
