<?php
/**
 * Services style controls.
 *
 * Themed via the site's global kit (--brand, --brand-dark, --ink, --muted,
 * --line, --surface, --soft). Exposes the heading color, the active tab
 * background, and the card title color, per-instance.
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
 * Style controls for the Services widget.
 */
final class ServicesStyleControls {

	/**
	 * Register the style section.
	 *
	 * @param Widget_Base $widget Widget instance.
	 *
	 * @return void
	 */
	public function register( Widget_Base $widget ): void {
		$widget->start_controls_section(
			'vew_services_style',
			array(
				'label' => __( 'Style', 'vector-elementor-widgets' ),
				'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
			)
		);

		$widget->add_control(
			'services_heading_color',
			array(
				'label'     => __( 'Heading Color', 'vector-elementor-widgets' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .vew-services__title' => 'color: {{VALUE}};',
				),
			)
		);

		$widget->add_control(
			'services_tab_bg',
			array(
				'label'     => __( 'Active Tab Background', 'vector-elementor-widgets' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .vew-services__indicator' => 'background-color: {{VALUE}};',
				),
			)
		);

		$widget->add_control(
			'services_card_title_color',
			array(
				'label'     => __( 'Card Title Color', 'vector-elementor-widgets' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .vew-services__card-title' => 'color: {{VALUE}};',
				),
			)
		);

		SectionHeading::register_style_controls( $widget );

		$widget->end_controls_section();
	}
}
