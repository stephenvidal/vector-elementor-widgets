<?php
/**
 * Process style controls.
 *
 * Themed via the site's global kit (--brand-dark, --accent, --ink). Exposes
 * the eyebrow accent color and the step title color, per-instance.
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
 * Style controls for the Process widget.
 */
final class ProcessStyleControls {

	/**
	 * Register the style section.
	 *
	 * @param Widget_Base $widget Widget instance.
	 *
	 * @return void
	 */
	public function register( Widget_Base $widget ): void {
		$widget->start_controls_section(
			'vew_process_style',
			array(
				'label' => __( 'Style', 'vector-elementor-widgets' ),
				'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
			)
		);

		$widget->add_control(
			'process_eyebrow_color',
			array(
				'label'     => __( 'Eyebrow Color', 'vector-elementor-widgets' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .vew-process__eyebrow' => 'color: {{VALUE}};',
				),
			)
		);

		$widget->add_control(
			'process_title_color',
			array(
				'label'     => __( 'Title Color', 'vector-elementor-widgets' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .vew-process__title' => 'color: {{VALUE}};',
				),
			)
		);

		$widget->add_control(
			'process_step_number_color',
			array(
				'label'     => __( 'Step Number Color', 'vector-elementor-widgets' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .vew-process__step::before' => 'color: {{VALUE}};',
				),
			)
		);

		SectionHeading::register_style_controls( $widget );

		$widget->end_controls_section();
	}
}
