<?php
/**
 * Contact style controls.
 *
 * Themed via the site's global kit (--brand, --brand-dark, --ink, --muted,
 * --line, --surface). Exposes the heading color, the detail link color, and
 * the submit button background, per-instance.
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
 * Style controls for the Contact widget.
 */
final class ContactStyleControls {

	/**
	 * Register the style section.
	 *
	 * @param Widget_Base $widget Widget instance.
	 *
	 * @return void
	 */
	public function register( Widget_Base $widget ): void {
		$widget->start_controls_section(
			'vew_contact_style',
			array(
				'label' => __( 'Style', 'vector-elementor-widgets' ),
				'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
			)
		);

		$widget->add_control(
			'contact_heading_color',
			array(
				'label'     => __( 'Heading Color', 'vector-elementor-widgets' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .vew-contact__title' => 'color: {{VALUE}};',
				),
			)
		);

		$widget->add_control(
			'contact_detail_color',
			array(
				'label'     => __( 'Detail Link Color', 'vector-elementor-widgets' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .vew-contact__details a' => 'color: {{VALUE}};',
				),
			)
		);

		$widget->add_control(
			'contact_submit_bg',
			array(
				'label'     => __( 'Submit Button Background', 'vector-elementor-widgets' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .vew-contact__submit' => 'background-color: {{VALUE}};',
				),
			)
		);

		SectionHeading::register_style_controls( $widget );

		$widget->end_controls_section();
	}
}
