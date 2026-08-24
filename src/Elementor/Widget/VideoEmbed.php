<?php
/**
 * VideoEmbed widget.
 *
 * A responsive, privacy-aware video embed. Accepts a YouTube / Vimeo URL
 * (rendered as a lazy `<iframe>`) or a direct .mp4/.webm file (rendered as a
 * native `<video>`). All URLs are sanitized; YouTube/Vimeo embed URLs are
 * rebuilt from a parsed ID so no user string reaches the iframe src verbatim.
 *
 * @package Vector\ElementorWidgets\Elementor\Widget
 */

declare( strict_types=1 );

namespace Vector\ElementorWidgets\Elementor\Widget;

use Vector\ElementorWidgets\Elementor\Control\VideoEmbedContentControls;
use Vector\ElementorWidgets\Elementor\Control\VideoEmbedStyleControls;
use Vector\ElementorWidgets\Elementor\Component\SectionHeading;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * VideoEmbed widget.
 */
final class VideoEmbed extends BaseWidget {

	/**
	 * Widget slug — stable forever.
	 *
	 * @return string
	 */
	public function get_name(): string {
		return 'vew-video-embed';
	}

	/**
	 * Widget title.
	 *
	 * @return string
	 */
	public function get_title(): string {
		return __( 'Video / Embed', 'vector-elementor-widgets' );
	}

	/**
	 * Widget icon.
	 *
	 * @return string
	 */
	public function get_icon(): string {
		return 'eicon-video-playlist';
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
		return array( 'video', 'embed', 'youtube', 'vimeo', 'media', 'player' );
	}

	/**
	 * Register controls.
	 *
	 * @return void
	 */
	protected function register_controls(): void {
		( new VideoEmbedContentControls() )->register( $this );
		( new VideoEmbedStyleControls() )->register( $this );
	}

	/**
	 * Extract a YouTube video ID from a URL.
	 *
	 * @param string $url YouTube URL.
	 *
	 * @return string
	 */
	private function youtube_id( string $url ): string {
		if ( preg_match( '/(?:youtube\.com\/(?:watch\?v=|embed\/|shorts\/)|youtu\.be\/)([\w-]{6,20})/', $url, $m ) ) {
			return $m[1];
		}
		return '';
	}

	/**
	 * Extract a Vimeo video ID from a URL.
	 *
	 * @param string $url Vimeo URL.
	 *
	 * @return string
	 */
	private function vimeo_id( string $url ): string {
		if ( preg_match( '/(?:vimeo\.com\/(?:video\/)?|player\.vimeo\.com\/video\/)(\d+)/', $url, $m ) ) {
			return $m[1];
		}
		return '';
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
					'video_url' => 'array',
					'autoplay'  => 'string',
					'controls'  => 'string',
				)
			)
		);

		$video_url = isset( $safe['video_url']['url'] ) ? (string) $safe['video_url']['url'] : '';
		$video_url = trim( esc_url_raw( $video_url ) );
		if ( '' === $video_url ) {
			return;
		}

		$autoplay = ( isset( $safe['autoplay'] ) && 'yes' === $safe['autoplay'] );
		$controls = ! ( isset( $safe['controls'] ) && 'yes' !== $safe['controls'] );

		$heading = SectionHeading::render(
			$safe,
			array( 'block_class' => 'vew-video-embed' )
		);

		$embed = '';

		// YouTube → privacy-enhanced iframe (youtube-nocookie), rebuilt from ID.
		$yt = $this->youtube_id( $video_url );
		if ( '' !== $yt ) {
			$src = 'https://www.youtube-nocookie.com/embed/' . $yt;
			if ( $autoplay ) {
				$src .= '?autoplay=1';
			}
			$embed = '<iframe src="' . esc_url( $src ) . '" title="' . esc_attr__( 'YouTube video', 'vector-elementor-widgets' ) . '" loading="lazy" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>';
		} elseif ( '' !== $this->vimeo_id( $video_url ) ) {
			$src   = 'https://player.vimeo.com/video/' . $this->vimeo_id( $video_url );
			$embed = '<iframe src="' . esc_url( $src ) . '" title="' . esc_attr__( 'Vimeo video', 'vector-elementor-widgets' ) . '" loading="lazy" allow="autoplay; fullscreen; picture-in-picture" allowfullscreen></iframe>';
		} elseif ( preg_match( '/\.(mp4|webm)(\?|$)/i', $video_url ) ) {
			// Direct video file → native <video>.
			$attrs = ' src="' . esc_url( $video_url ) . '"';
			if ( $autoplay ) {
				$attrs .= ' autoplay muted playsinline';
			}
			if ( $controls ) {
				$attrs .= ' controls';
			}
			$embed = '<video' . $attrs . '></video>';
		}

		// If nothing matched, fall back to an iframe of the (sanitized) URL.
		if ( '' === $embed ) {
			$embed = '<iframe src="' . esc_url( $video_url ) . '" title="' . esc_attr__( 'Embedded video', 'vector-elementor-widgets' ) . '" loading="lazy" allowfullscreen></iframe>';
		}
		?>
		<section class="vew-video-embed" aria-label="<?php echo esc_attr__( 'Video', 'vector-elementor-widgets' ); ?>">
			<div class="vew-video-embed__inner">
				<?php
				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- component escapes all values.
				echo $heading;
				?>
				<div class="vew-video-embed__frame">
					<?php
					// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- embed is built with escaped values above.
					echo $embed;
					?>
				</div>
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
		return array( 'vew-section-heading', 'vew-video-embed' );
	}
}
