<?php
/**
 * GoogleMap content controls.
 *
 * An embeddable Google Maps block: an optional heading block + a location
 * (address / query) + zoom, rendered as an `<iframe>` via Google's no-key
 * embed endpoint. Per the control-mapping contract `data-location` →
 * `location`, `data-zoom` → `zoom`.
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
 * Content controls for the GoogleMap widget.
 */
final class GoogleMapContentControls {

	/**
	 * Register the content section.
	 *
	 * @param Widget_Base $widget Widget instance.
	 *
	 * @return void
	 */
	public function register( Widget_Base $widget ): void {
		SectionHeading::register_content_controls( $widget );

		$widget->start_controls_section(
			'vew_google_map_content',
			array(
				'label' => __( 'Map', 'vector-elementor-widgets' ),
			)
		);

		$widget->add_control(
			'location',
			array(
				'label'       => __( 'Location / Query', 'vector-elementor-widgets' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'default'     => 'New York, NY',
				'description' => __( 'Address, city, or place name to centre the map.', 'vector-elementor-widgets' ),
				'label_block' => true,
			)
		);

		$widget->add_control(
			'zoom',
			array(
				'label'   => __( 'Zoom Level', 'vector-elementor-widgets' ),
				'type'    => \Elementor\Controls_Manager::SLIDER,
				'range'   => array(
					'px' => array(
						'min' => 1,
						'max' => 20,
					),
				),
				'default' => array(
					'size' => 14,
				),
			)
		);

		$widget->add_control(
			'height',
			array(
				'label'   => __( 'Map Height', 'vector-elementor-widgets' ),
				'type'    => \Elementor\Controls_Manager::SLIDER,
				'range'   => array(
					'px' => array(
						'min' => 200,
						'max' => 800,
					),
				),
				'default' => array(
					'size' => 420,
				),
			)
		);

		$widget->end_controls_section();
	}
}
