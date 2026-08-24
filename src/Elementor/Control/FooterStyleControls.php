<?php
/**
 * Footer style controls.
 *
 * Themed via the site's global kit (--brand-dark, --accent, --ink). Exposes
 * the brand color, the link color, and the heading color, per-instance.
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
 * Style controls for the Footer widget.
 */
final class FooterStyleControls {

	/**
	 * Register the style section.
	 *
	 * @param Widget_Base $widget Widget instance.
	 *
	 * @return void
	 */
	public function register( Widget_Base $widget ): void {
		$widget->start_controls_section(
			'vew_footer_style',
			array(
				'label' => __( 'Style', 'vector-elementor-widgets' ),
				'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
			)
		);

		$widget->add_control(
			'footer_brand_color',
			array(
				'label'     => __( 'Brand Color', 'vector-elementor-widgets' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .vew-footer__mark' => 'color: {{VALUE}};',
				),
			)
		);

		$widget->add_control(
			'footer_heading_color',
			array(
				'label'     => __( 'Column Heading Color', 'vector-elementor-widgets' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .vew-footer__col h3' => 'color: {{VALUE}};',
				),
			)
		);

		$widget->add_control(
			'footer_link_color',
			array(
				'label'     => __( 'Link Color', 'vector-elementor-widgets' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .vew-footer__col a' => 'color: {{VALUE}};',
				),
			)
		);

		$widget->end_controls_section();
	}
}
