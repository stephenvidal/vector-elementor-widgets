<?php
/**
 * Gallery style controls.
 *
 * Themed via the site's global kit. Exposes the feature-item span toggle and
 * the grid gap, per-instance.
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
 * Style controls for the Gallery widget.
 */
final class GalleryStyleControls {

	/**
	 * Register the style section.
	 *
	 * @param Widget_Base $widget Widget instance.
	 *
	 * @return void
	 */
	public function register( Widget_Base $widget ): void {
		$widget->start_controls_section(
			'vew_gallery_style',
			array(
				'label' => __( 'Style', 'vector-elementor-widgets' ),
				'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
			)
		);

		$widget->add_control(
			'gallery_style',
			array(
				'label'   => __( 'Layout Style', 'vector-elementor-widgets' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'default' => 'mosaic',
				'options' => array(
					'mosaic'    => __( 'Mosaic (feature first)', 'vector-elementor-widgets' ),
					'editorial' => __( 'Editorial (labels + lightbox)', 'vector-elementor-widgets' ),
					'grid'      => __( 'Uniform Grid', 'vector-elementor-widgets' ),
					'carousel'  => __( 'Carousel (scroll strip)', 'vector-elementor-widgets' ),
				),
			)
		);

		$widget->add_control(
			'gallery_feature_span',
			array(
				'label'     => __( 'Feature First Image', 'vector-elementor-widgets' ),
				'type'      => \Elementor\Controls_Manager::SWITCHER,
				'default'   => 'yes',
				'label_on'  => __( 'Yes', 'vector-elementor-widgets' ),
				'label_off' => __( 'No', 'vector-elementor-widgets' ),
				'condition' => array( 'gallery_style' => 'mosaic' ),
				'selectors' => array(
					'{{WRAPPER}} .vew-gallery__item--feature' => 'grid-column: 1 / 3; grid-row: 1 / 3;',
				),
			)
		);

		$widget->add_control(
			'gallery_gap',
			array(
				'label'     => __( 'Gap', 'vector-elementor-widgets' ),
				'type'      => \Elementor\Controls_Manager::SLIDER,
				'range'     => array(
					'px' => array(
						'min'  => 0,
						'max'  => 60,
						'step' => 1,
					),
				),
				'default'   => array(
					'size' => 14,
				),
				'selectors' => array(
					'{{WRAPPER}} .vew-gallery__grid' => 'gap: {{SIZE}}px;',
				),
			)
		);

		SectionHeading::register_style_controls( $widget );

		$widget->end_controls_section();
	}
}
