<?php
/**
 * PostGallery content controls.
 *
 * A post-sourced gallery: optional heading block + shared query configuration
 * + display style + caption toggle. Renders featured images from a query.
 *
 * @package Vector\ElementorWidgets\Elementor\Control
 */

declare( strict_types=1 );

namespace Vector\ElementorWidgets\Elementor\Control;

use Elementor\Controls_Manager;
use Elementor\Widget_Base;
use Vector\ElementorWidgets\Elementor\Component\QueryControls;
use Vector\ElementorWidgets\Elementor\Component\SectionHeading;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Content controls for the PostGallery widget.
 */
final class PostGalleryContentControls {

	/**
	 * Register the content section.
	 *
	 * @param Widget_Base $widget Widget instance.
	 *
	 * @return void
	 */
	public function register( Widget_Base $widget ): void {
		$widget->start_controls_section(
			'vew_post_gallery_content',
			array(
				'label' => __( 'Content', 'vector-elementor-widgets' ),
			)
		);

		$widget->add_control(
			'eyebrow',
			array(
				'label'       => __( 'Eyebrow', 'vector-elementor-widgets' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => __( 'Gallery', 'vector-elementor-widgets' ),
				'label_block' => true,
			)
		);

		$widget->add_control(
			'title',
			array(
				'label'       => __( 'Title', 'vector-elementor-widgets' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => __( 'From the posts', 'vector-elementor-widgets' ),
				'label_block' => true,
			)
		);

		SectionHeading::register_content_controls( $widget );

		$widget->add_control(
			'gallery_style',
			array(
				'label'   => __( 'Display style', 'vector-elementor-widgets' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'mosaic',
				'options' => array(
					'mosaic'   => __( 'Mosaic', 'vector-elementor-widgets' ),
					'grid'     => __( 'Grid', 'vector-elementor-widgets' ),
					'carousel' => __( 'Carousel', 'vector-elementor-widgets' ),
				),
			)
		);

		$widget->add_control(
			'columns',
			array(
				'label'     => __( 'Columns', 'vector-elementor-widgets' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => '3',
				'options'   => array(
					'2' => __( '2', 'vector-elementor-widgets' ),
					'3' => __( '3', 'vector-elementor-widgets' ),
					'4' => __( '4', 'vector-elementor-widgets' ),
				),
				'condition' => array( 'gallery_style!' => 'carousel' ),
			)
		);

		$widget->add_control(
			'show_caption',
			array(
				'label'        => __( 'Show post title caption', 'vector-elementor-widgets' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => __( 'On', 'vector-elementor-widgets' ),
				'label_off'    => __( 'Off', 'vector-elementor-widgets' ),
				'return_value' => 'yes',
				'default'      => 'yes',
			)
		);

		$widget->end_controls_section();

		// Query section — shared component.
		$widget->start_controls_section(
			'vew_post_gallery_query',
			array(
				'label' => __( 'Query', 'vector-elementor-widgets' ),
			)
		);

		QueryControls::register_content_controls( $widget );

		$widget->end_controls_section();
	}
}
