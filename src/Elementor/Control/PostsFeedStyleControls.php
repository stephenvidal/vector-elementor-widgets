<?php
/**
 * PostsFeed style controls.
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
 * Style controls for the PostsFeed widget.
 */
final class PostsFeedStyleControls {

	/**
	 * Register the style section.
	 *
	 * @param Widget_Base $widget Widget instance.
	 *
	 * @return void
	 */
	public function register( Widget_Base $widget ): void {
		$widget->start_controls_section(
			'vew_posts_feed_style',
			array(
				'label' => __( 'Style', 'vector-elementor-widgets' ),
				'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
			)
		);

		$widget->add_control(
			'title_color',
			array(
				'label'     => __( 'Post title color', 'vector-elementor-widgets' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .vew-posts-feed__title a' => 'color: {{VALUE}};',
				),
			)
		);

		$widget->add_control(
			'card_border_radius',
			array(
				'label'     => __( 'Card border radius', 'vector-elementor-widgets' ),
				'type'      => \Elementor\Controls_Manager::SLIDER,
				'range'     => array(
					'px' => array(
						'min' => 0,
						'max' => 40,
					),
				),
				'default'   => array(
					'size' => 8,
					'unit' => 'px',
				),
				'selectors' => array(
					'{{WRAPPER}} .vew-posts-feed__card' => 'border-radius: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$widget->end_controls_section();
	}
}
