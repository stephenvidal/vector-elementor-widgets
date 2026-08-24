<?php
/**
 * Gallery content controls.
 *
 * A mosaic gallery: a heading block + a REPEATER of images. Per the
 * control-mapping contract `data-copy` → TEXT, `data-gallery` → REPEATER.
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
 * Content controls for the Gallery widget.
 */
final class GalleryContentControls {

	/**
	 * Register the content section.
	 *
	 * @param Widget_Base $widget Widget instance.
	 *
	 * @return void
	 */
	public function register( Widget_Base $widget ): void {
		$widget->start_controls_section(
			'vew_gallery_content',
			array(
				'label' => __( 'Content', 'vector-elementor-widgets' ),
			)
		);

		$widget->add_control(
			'eyebrow',
			array(
				'label'       => __( 'Eyebrow', 'vector-elementor-widgets' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'default'     => __( 'From bean to cup', 'vector-elementor-widgets' ),
				'label_block' => true,
			)
		);

		$widget->add_control(
			'title',
			array(
				'label'       => __( 'Title', 'vector-elementor-widgets' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'default'     => __( 'Inside', 'vector-elementor-widgets' ),
				'label_block' => true,
			)
		);

		SectionHeading::register_content_controls( $widget );

		$widget->add_control(
			'images',
			array(
				'label'       => __( 'Images', 'vector-elementor-widgets' ),
				'type'        => \Elementor\Controls_Manager::REPEATER,
				'fields'      => array(
					array(
						'name'    => 'url',
						'label'   => __( 'Image', 'vector-elementor-widgets' ),
						'type'    => \Elementor\Controls_Manager::MEDIA,
						'default' => array(
							'url' => '',
						),
					),
					array(
						'name'        => 'label',
						'label'       => __( 'Label (Editorial style)', 'vector-elementor-widgets' ),
						'type'        => \Elementor\Controls_Manager::TEXT,
						'default'     => '',
						'label_block' => true,
					),
					array(
						'name'        => 'alt',
						'label'       => __( 'Alt Text', 'vector-elementor-widgets' ),
						'type'        => \Elementor\Controls_Manager::TEXT,
						'default'     => '',
						'label_block' => true,
					),
				),
				'default'     => array(),
				'title_field' => '{{{ alt }}}',
			)
		);

		$widget->end_controls_section();
	}
}
