<?php
/**
 * Header style controls.
 *
 * Themed via the site's global kit (--brand, --brand-dark, --ink, --muted,
 * --line). Exposes the brand color, the nav link color, and the CTA
 * background, per-instance.
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
 * Style controls for the Header widget.
 */
final class HeaderStyleControls {

	/**
	 * Register the style section.
	 *
	 * @param Widget_Base $widget Widget instance.
	 *
	 * @return void
	 */
	public function register( Widget_Base $widget ): void {
		$widget->start_controls_section(
			'vew_header_style',
			array(
				'label' => __( 'Style', 'vector-elementor-widgets' ),
				'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
			)
		);

		$widget->add_control(
			'header_brand_color',
			array(
				'label'     => __( 'Brand Color', 'vector-elementor-widgets' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .vew-header__mark' => 'color: {{VALUE}};',
				),
			)
		);

		$widget->add_control(
			'header_nav_color',
			array(
				'label'     => __( 'Nav Link Color', 'vector-elementor-widgets' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .vew-header__nav a' => 'color: {{VALUE}};',
				),
			)
		);

		$widget->add_control(
			'header_cta_bg',
			array(
				'label'     => __( 'CTA Background', 'vector-elementor-widgets' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .vew-header__cta' => 'background-color: {{VALUE}};',
				),
			)
		);

		$widget->end_controls_section();
	}
}
