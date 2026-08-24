<?php
/**
 * Pricing style controls.
 *
 * Themed via the site's global kit (--brand, --brand-dark, --ink, --muted,
 * --line, --surface). Exposes the heading color, the featured card border,
 * and the CTA background, per-instance.
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
 * Style controls for the Pricing widget.
 */
final class PricingStyleControls {

	/**
	 * Register the style section.
	 *
	 * @param Widget_Base $widget Widget instance.
	 *
	 * @return void
	 */
	public function register( Widget_Base $widget ): void {
		$widget->start_controls_section(
			'vew_pricing_style',
			array(
				'label' => __( 'Style', 'vector-elementor-widgets' ),
				'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
			)
		);

		$widget->add_control(
			'pricing_heading_color',
			array(
				'label'     => __( 'Heading Color', 'vector-elementor-widgets' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .vew-pricing__title' => 'color: {{VALUE}};',
				),
			)
		);

		$widget->add_control(
			'pricing_featured_border',
			array(
				'label'     => __( 'Featured Card Border', 'vector-elementor-widgets' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .vew-pricing__card--featured' => 'border-color: {{VALUE}};',
				),
			)
		);

		$widget->add_control(
			'pricing_cta_bg',
			array(
				'label'     => __( 'Button Background', 'vector-elementor-widgets' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .vew-pricing__cta' => 'background-color: {{VALUE}};',
				),
			)
		);

		SectionHeading::register_style_controls( $widget );

		$widget->end_controls_section();
	}
}
