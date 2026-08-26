<?php
/**
 * VideoEmbed content controls.
 *
 * A responsive video embed block: an optional heading block + a URL (YouTube,
 * Vimeo, or direct MP4) that is sanitized and turned into a privacy-aware
 * `<iframe>` / `<video>` in render(). Per the control-mapping contract
 * `data-video_url` → `video_url`, `data-autoplay` → `autoplay`.
 *
 * @package Vector\ElementorWidgets\Elementor\Control
 */

declare( strict_types=1 );

namespace Vector\ElementorWidgets\Elementor\Control;

use Elementor\Widget_Base;
use Vector\ElementorWidgets\Elementor\Component\SectionHeading;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Content controls for the VideoEmbed widget.
 */
final class VideoEmbedContentControls {

	/**
	 * Register the content section.
	 *
	 * @param Widget_Base $widget Widget instance.
	 *
	 * @return void
	 */
	public function register( Widget_Base $widget ): void {
		$widget->start_controls_section(
			'vew_video_embed_content',
			array(
				'label' => __( 'Video', 'vector-elementor-widgets' ),
			)
		);

		SectionHeading::register_content_controls( $widget );

		$widget->add_control(
			'video_url',
			array(
				'label'       => __( 'Video URL', 'vector-elementor-widgets' ),
				'type'        => \Elementor\Controls_Manager::URL,
				'placeholder' => 'https://www.youtube.com/watch?v=…',
				'description' => __( 'YouTube, Vimeo, or a direct .mp4/.webm file.', 'vector-elementor-widgets' ),
				'label_block' => true,
			)
		);

		$widget->add_control(
			'autoplay',
			array(
				'label'        => __( 'Autoplay', 'vector-elementor-widgets' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'label_on'     => __( 'On', 'vector-elementor-widgets' ),
				'label_off'    => __( 'Off', 'vector-elementor-widgets' ),
				'return_value' => 'yes',
				'default'      => '',
			)
		);

		$widget->add_control(
			'controls',
			array(
				'label'        => __( 'Show Player Controls', 'vector-elementor-widgets' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'label_on'     => __( 'On', 'vector-elementor-widgets' ),
				'label_off'    => __( 'Off', 'vector-elementor-widgets' ),
				'return_value' => 'yes',
				'default'      => 'yes',
			)
		);

		$widget->end_controls_section();
	}
}
