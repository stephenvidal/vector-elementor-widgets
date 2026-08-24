<?php
/**
 * CTA Banner style controls.
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
 * Style controls for the CTA Banner widget.
 */
final class CtaStyleControls {

	/**
	 * Register the style section.
	 *
	 * @param Widget_Base $widget Widget instance.
	 *
	 * @return void
	 */
	public function register( Widget_Base $widget ): void {
		$widget->start_controls_section(
			'vew_cta_style',
			array(
				'label' => __( 'Style', 'vector-elementor-widgets' ),
				'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
			)
		);

		$widget->add_control(
			'background',
			array(
				'label'     => __( 'Background Color', 'vector-elementor-widgets' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				// Intentionally NO default: leave empty so the widget stylesheet's
				// var(--brand-dark, ...) resolves the kit token. A hardcoded default
				// here gets baked into post CSS and overrides the kit on every page.
				'selectors' => array(
					'{{WRAPPER}} .vew-cta' => 'background-color: {{VALUE}};',
				),
			)
		);

		$widget->add_control(
			'heading_color',
			array(
				'label'     => __( 'Heading Color', 'vector-elementor-widgets' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				// Intentionally NO default: let the widget stylesheet's
				// var(--ink, ...) resolve the kit token. A hardcoded default
				// here gets baked into post CSS and overrides the kit on every page.
				'selectors' => array(
					'{{WRAPPER}} .vew-cta__heading' => 'color: {{VALUE}};',
				),
			)
		);

		$widget->add_control(
			'description_color',
			array(
				'label'     => __( 'Description Color', 'vector-elementor-widgets' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				// Intentionally NO default: let the widget stylesheet's
				// var(--muted, ...) resolve the kit token.
				'selectors' => array(
					'{{WRAPPER}} .vew-cta__description' => 'color: {{VALUE}};',
				),
			)
		);

		$widget->add_control(
			'primary_button_color',
			array(
				'label'     => __( 'Primary Button Color', 'vector-elementor-widgets' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				// Intentionally NO default: the widget stylesheet's var(--accent)
				// resolves the kit token. A hardcoded default would bake into post
				// CSS and override the kit.
				'selectors' => array(
					'{{WRAPPER}} .vew-cta__button--primary' => 'background-color: {{VALUE}};',
				),
			)
		);

		$widget->end_controls_section();
	}
}
