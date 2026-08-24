<?php
/**
 * Feature Grid style controls.
 *
 * Themed via the site's global kit (--brand, --brand-dark, --line, --muted,
 * --display). Exposes the headline + eyebrow accent colors and the card
 * surface, per-instance.
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
 * Style controls for the Feature Grid widget.
 */
final class FeatureGridStyleControls {

	/**
	 * Register the style section.
	 *
	 * @param Widget_Base $widget Widget instance.
	 *
	 * @return void
	 */
	public function register( Widget_Base $widget ): void {
		$widget->start_controls_section(
			'vew_feature_grid_style',
			array(
				'label' => __( 'Style', 'vector-elementor-widgets' ),
				'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
			)
		);

		$widget->add_control(
			'fg_heading_color',
			array(
				'label'     => __( 'Heading Color', 'vector-elementor-widgets' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .vew-feature-grid__title' => 'color: {{VALUE}};',
				),
			)
		);

		$widget->add_control(
			'fg_card_bg',
			array(
				'label'     => __( 'Card Background', 'vector-elementor-widgets' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .vew-feature-card' => 'background-color: {{VALUE}};',
				),
			)
		);

		$widget->add_control(
			'fg_card_title_color',
			array(
				'label'     => __( 'Card Title Color', 'vector-elementor-widgets' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .vew-feature-card__title' => 'color: {{VALUE}};',
				),
			)
		);

		$widget->add_control(
			'fg_card_detail_color',
			array(
				'label'     => __( 'Detail Label Color', 'vector-elementor-widgets' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .vew-feature-card__detail' => 'color: {{VALUE}};',
				),
			)
		);

		$widget->end_controls_section();
	}
}
