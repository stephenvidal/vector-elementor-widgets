<?php
/**
 * PageHero style controls.
 *
 * Themed via the site's global kit (--brand, --ink, --muted). Exposes the
 * breadcrumb link color and title color, per-instance.
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
 * Style controls for the PageHero widget.
 */
final class PageHeroStyleControls {

	/**
	 * Register the style section.
	 *
	 * @param Widget_Base $widget Widget instance.
	 *
	 * @return void
	 */
	public function register( Widget_Base $widget ): void {
		$widget->start_controls_section(
			'vew_page_hero_style',
			array(
				'label' => __( 'Style', 'vector-elementor-widgets' ),
				'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
			)
		);

		$widget->add_control(
			'page_hero_breadcrumb_color',
			array(
				'label'     => __( 'Breadcrumb Color', 'vector-elementor-widgets' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .vew-page-hero__breadcrumb a' => 'color: {{VALUE}};',
				),
			)
		);

		$widget->add_control(
			'page_hero_title_color',
			array(
				'label'     => __( 'Title Color', 'vector-elementor-widgets' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .vew-page-hero__title' => 'color: {{VALUE}};',
				),
			)
		);

		$widget->end_controls_section();
	}
}
