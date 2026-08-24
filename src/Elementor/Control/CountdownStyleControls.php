<?php
/**
 * Countdown style controls.
 *
 * @package Vector\ElementorWidgets\Elementor\Control
 */

declare( strict_types=1 );

namespace Vector\ElementorWidgets\Elementor\Control;

use Elementor\Widget_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Style controls for the Countdown widget.
 */
final class CountdownStyleControls {

	/**
	 * Register the style section.
	 *
	 * @param Widget_Base $widget Widget instance.
	 *
	 * @return void
	 */
	public function register( Widget_Base $widget ): void {
		$widget->start_controls_section(
			'vew_countdown_style',
			array(
				'label' => __( 'Countdown', 'vector-elementor-widgets' ),
				'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
			)
		);

		$widget->add_control(
			'number_color',
			array(
				'label'     => __( 'Number Color', 'vector-elementor-widgets' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .vew-countdown__number' => 'color: {{VALUE}};',
				),
			)
		);

		$widget->add_control(
			'label_color',
			array(
				'label'     => __( 'Label Color', 'vector-elementor-widgets' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .vew-countdown__label' => 'color: {{VALUE}};',
				),
			)
		);

		$widget->add_control(
			'unit_bg',
			array(
				'label'     => __( 'Unit Background', 'vector-elementor-widgets' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .vew-countdown__unit' => 'background-color: {{VALUE}};',
				),
			)
		);

		$widget->end_controls_section();
	}
}
