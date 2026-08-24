<?php
/**
 * LogoBar style controls.
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
 * Style controls for the LogoBar widget.
 */
final class LogoBarStyleControls {

	/**
	 * Register the style section.
	 *
	 * @param Widget_Base $widget Widget instance.
	 *
	 * @return void
	 */
	public function register( Widget_Base $widget ): void {
		$widget->start_controls_section(
			'vew_logo_bar_style',
			array(
				'label' => __( 'Style', 'vector-elementor-widgets' ),
				'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
			)
		);

		$widget->add_responsive_control(
			'columns',
			array(
				'label'     => __( 'Logos per row', 'vector-elementor-widgets' ),
				'type'      => \Elementor\Controls_Manager::NUMBER,
				'min'       => 2,
				'max'       => 8,
				'default'   => 4,
				'selectors' => array(
					'{{WRAPPER}} .vew-logo-bar__grid' => 'grid-template-columns: repeat({{VALUE}}, 1fr);',
				),
			)
		);

		$widget->add_control(
			'logo_max_height',
			array(
				'label'     => __( 'Logo max height', 'vector-elementor-widgets' ),
				'type'      => \Elementor\Controls_Manager::SLIDER,
				'range'     => array(
					'px' => array(
						'min' => 16,
						'max' => 160,
					),
				),
				'default'   => array(
					'size' => 40,
					'unit' => 'px',
				),
				'selectors' => array(
					'{{WRAPPER}} .vew-logo-bar__item img' => 'max-height: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$widget->add_control(
			'logo_opacity',
			array(
				'label'     => __( 'Logo opacity', 'vector-elementor-widgets' ),
				'type'      => \Elementor\Controls_Manager::SLIDER,
				'range'     => array(
					'px' => array(
						'min'  => 0.1,
						'max'  => 1,
						'step' => 0.05,
					),
				),
				'default'   => array(
					'size' => 0.6,
				),
				'selectors' => array(
					'{{WRAPPER}} .vew-logo-bar__item' => 'opacity: {{SIZE}};',
					'{{WRAPPER}} .vew-logo-bar__item:hover' => 'opacity: 1;',
				),
			)
		);

		$widget->end_controls_section();
	}
}
