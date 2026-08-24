<?php
/**
 * BeforeAfter style controls.
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
 * Style controls for the BeforeAfter widget.
 */
final class BeforeAfterStyleControls {

	/**
	 * Register the style section.
	 *
	 * @param Widget_Base $widget Widget instance.
	 *
	 * @return void
	 */
	public function register( Widget_Base $widget ): void {
		$widget->start_controls_section(
			'vew_before_after_style',
			array(
				'label' => __( 'Comparison', 'vector-elementor-widgets' ),
				'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
			)
		);

		$widget->add_control(
			'border_radius',
			array(
				'label'     => __( 'Image Border Radius', 'vector-elementor-widgets' ),
				'type'      => \Elementor\Controls_Manager::SLIDER,
				'range'     => array(
					'px' => array(
						'min' => 0,
						'max' => 40,
					),
				),
				'default'   => array( 'size' => 4 ),
				'selectors' => array(
					'{{WRAPPER}} .vew-before-after__stage' => 'border-radius: {{SIZE}}{{UNIT}}; overflow: hidden;',
				),
			)
		);

		$widget->add_control(
			'handle_color',
			array(
				'label'     => __( 'Handle Color', 'vector-elementor-widgets' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .vew-before-after__handle' => 'background-color: {{VALUE}}; border-color: {{VALUE}};',
				),
			)
		);

		$widget->add_control(
			'tag_bg',
			array(
				'label'     => __( 'Label Background', 'vector-elementor-widgets' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .vew-before-after__tag' => 'background-color: {{VALUE}};',
				),
			)
		);

		$widget->end_controls_section();
	}
}
