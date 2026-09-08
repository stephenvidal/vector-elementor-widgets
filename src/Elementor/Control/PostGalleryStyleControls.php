<?php
/**
 * PostGallery style controls.
 *
 * @package Vector\ElementorWidgets\Elementor\Control
 */

declare( strict_types=1 );

namespace Vector\ElementorWidgets\Elementor\Control;

use Elementor\Controls_Manager;
use Elementor\Widget_Base;
use Vector\ElementorWidgets\Elementor\Component\SectionHeading;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Style controls for the PostGallery widget.
 */
final class PostGalleryStyleControls {

	/**
	 * Register the style section.
	 *
	 * @param Widget_Base $widget Widget instance.
	 *
	 * @return void
	 */
	public function register( Widget_Base $widget ): void {
		$widget->start_controls_section(
			'vew_post_gallery_style',
			array(
				'label' => __( 'Style', 'vector-elementor-widgets' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$widget->add_control(
			'caption_color',
			array(
				'label'     => __( 'Caption Color', 'vector-elementor-widgets' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .vew-post-gallery__caption' => 'color: {{VALUE}};',
				),
			)
		);

		$widget->add_control(
			'item_radius',
			array(
				'label'     => __( 'Item Border Radius', 'vector-elementor-widgets' ),
				'type'      => Controls_Manager::SLIDER,
				'default'   => array(
					'size' => 3,
				),
				'range'     => array(
					'px' => array(
						'min' => 0,
						'max' => 24,
					),
				),
				'selectors' => array(
					'{{WRAPPER}} .vew-post-gallery__item' => 'border-radius: {{SIZE}}px;',
				),
			)
		);

		SectionHeading::register_style_controls( $widget );

		$widget->end_controls_section();
	}
}
