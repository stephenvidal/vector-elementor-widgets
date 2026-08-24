<?php
/**
 * Portfolio style controls.
 *
 * Themed via the site's global kit (--brand, --brand-dark, --ink, --muted,
 * --line, --surface). Exposes the heading color, the card title color, and
 * the tag background, per-instance.
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
 * Style controls for the Portfolio widget.
 */
final class PortfolioStyleControls {

	/**
	 * Register the style section.
	 *
	 * @param Widget_Base $widget Widget instance.
	 *
	 * @return void
	 */
	public function register( Widget_Base $widget ): void {
		$widget->start_controls_section(
			'vew_portfolio_style',
			array(
				'label' => __( 'Style', 'vector-elementor-widgets' ),
				'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
			)
		);

		$widget->add_control(
			'portfolio_heading_color',
			array(
				'label'     => __( 'Heading Color', 'vector-elementor-widgets' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .vew-portfolio__title' => 'color: {{VALUE}};',
				),
			)
		);

		$widget->add_control(
			'portfolio_card_title_color',
			array(
				'label'     => __( 'Card Title Color', 'vector-elementor-widgets' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .vew-portfolio__card-title' => 'color: {{VALUE}};',
				),
			)
		);

		$widget->add_control(
			'portfolio_tag_bg',
			array(
				'label'     => __( 'Tag Background', 'vector-elementor-widgets' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .vew-portfolio__tag' => 'background-color: {{VALUE}};',
				),
			)
		);

		SectionHeading::register_style_controls( $widget );

		$widget->end_controls_section();
	}
}
