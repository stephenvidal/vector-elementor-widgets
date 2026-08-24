<?php
/**
 * Timeline style controls.
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
 * Style controls for the Timeline widget.
 */
final class TimelineStyleControls {

	/**
	 * Register the style section.
	 *
	 * @param Widget_Base $widget Widget instance.
	 *
	 * @return void
	 */
	public function register( Widget_Base $widget ): void {
		$widget->start_controls_section(
			'vew_timeline_style',
			array(
				'label' => __( 'Timeline', 'vector-elementor-widgets' ),
				'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
			)
		);

		$widget->add_control(
			'line_color',
			array(
				'label'     => __( 'Line Color', 'vector-elementor-widgets' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .vew-timeline__list' => '--vew-timeline-line: {{VALUE}};',
				),
			)
		);

		$widget->add_control(
			'dot_color',
			array(
				'label'     => __( 'Dot Color', 'vector-elementor-widgets' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .vew-timeline__dot' => 'background-color: {{VALUE}}; border-color: {{VALUE}};',
				),
			)
		);

		$widget->add_control(
			'card_bg',
			array(
				'label'     => __( 'Card Background', 'vector-elementor-widgets' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .vew-timeline__card' => 'background-color: {{VALUE}};',
				),
			)
		);

		$widget->end_controls_section();
	}
}
