<?php
/**
 * TeamGrid widget.
 *
 * A responsive grid of team/staff cards: photo, name, role, short bio, and
 * optional social links. Renders a shared section heading above the grid.
 *
 * @package Vector\ElementorWidgets\Elementor\Widget
 */

declare( strict_types=1 );

namespace Vector\ElementorWidgets\Elementor\Widget;

use Vector\ElementorWidgets\Elementor\Control\TeamGridContentControls;
use Vector\ElementorWidgets\Elementor\Control\TeamGridStyleControls;
use Vector\ElementorWidgets\Elementor\Component\SectionHeading;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * TeamGrid widget.
 */
final class TeamGrid extends BaseWidget {

	/**
	 * Widget slug — stable forever.
	 *
	 * @return string
	 */
	public function get_name(): string {
		return 'vew-team-grid';
	}

	/**
	 * Widget title.
	 *
	 * @return string
	 */
	public function get_title(): string {
		return __( 'Team Grid', 'vector-elementor-widgets' );
	}

	/**
	 * Widget icon.
	 *
	 * @return string
	 */
	public function get_icon(): string {
		return 'eicon-person';
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
		return array( 'team', 'staff', 'people', 'member', 'about' );
	}

	/**
	 * Register controls.
	 *
	 * @return void
	 */
	protected function register_controls(): void {
		( new TeamGridContentControls() )->register( $this );
		( new TeamGridStyleControls() )->register( $this );
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
			array_merge(
				SectionHeading::setting_types(),
				array(
					'columns' => 'string',
					'members' => 'array',
				)
			)
		);

		$columns = isset( $safe['columns'] ) ? sanitize_key( (string) $safe['columns'] ) : '3';
		$columns = in_array( $columns, array( '2', '3', '4' ), true ) ? $columns : '3';
		$members = $safe['members'] ?? array();

		$heading = SectionHeading::render(
			$safe,
			array( 'block_class' => 'vew-team-grid' )
		);

		$cards = array();
		if ( is_array( $members ) ) {
			foreach ( $members as $member ) {
				$member = is_array( $member ) ? $member : array();
				$photo  = isset( $member['photo']['url'] ) ? (string) $member['photo']['url'] : '';
				$name   = isset( $member['name'] ) ? sanitize_text_field( (string) $member['name'] ) : '';
				$role   = isset( $member['role'] ) ? sanitize_text_field( (string) $member['role'] ) : '';
				$bio    = isset( $member['bio'] ) ? sanitize_text_field( (string) $member['bio'] ) : '';

				if ( '' === $name ) {
					continue;
				}

				// Build the social links.
				$social = '';
				if ( ! empty( $member['twitter']['url'] ) ) {
					$social .= '<a class="vew-team-grid__social" href="' . esc_url( $member['twitter']['url'] ) . '" target="_blank" rel="noopener" aria-label="' . esc_attr( sprintf( /* translators: %s: member name. */ __( '%s on X', 'vector-elementor-widgets' ), $name ) ) . '">X</a>';
				}
				if ( ! empty( $member['linkedin']['url'] ) ) {
					$social .= '<a class="vew-team-grid__social" href="' . esc_url( $member['linkedin']['url'] ) . '" target="_blank" rel="noopener" aria-label="' . esc_attr( sprintf( /* translators: %s: member name. */ __( '%s on LinkedIn', 'vector-elementor-widgets' ), $name ) ) . '">in</a>';
				}

				$card  = '<article class="vew-team-grid__card">';
				if ( '' !== $photo ) {
					$card .= '<div class="vew-team-grid__photo"><img src="' . esc_url( $photo ) . '" alt="' . esc_attr( $name ) . '" loading="lazy"></div>';
				}
				$card .= '<div class="vew-team-grid__body">';
				$card .= '<h3 class="vew-team-grid__name">' . esc_html( $name ) . '</h3>';
				if ( '' !== $role ) {
					$card .= '<p class="vew-team-grid__role">' . esc_html( $role ) . '</p>';
				}
				if ( '' !== $bio ) {
					$card .= '<p class="vew-team-grid__bio">' . esc_html( $bio ) . '</p>';
				}
				if ( '' !== $social ) {
					$card .= '<div class="vew-team-grid__socials">' . $social . '</div>';
				}
				$card .= '</div></article>';

				$cards[] = $card;
			}
		}

		?>
		<section class="vew-team-grid vew-team-grid--<?php echo esc_attr( $columns ); ?>" aria-label="<?php echo esc_attr__( 'Team', 'vector-elementor-widgets' ); ?>">
			<div class="vew-team-grid__inner">
				<?php
				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- component escapes all values.
				echo $heading;
				?>
				<?php if ( count( $cards ) > 0 ) : ?>
					<div class="vew-team-grid__grid">
						<?php
						// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- cards are escaped above.
						echo implode( '', $cards );
						?>
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
		return array( 'vew-section-heading', 'vew-team-grid' );
	}
}
