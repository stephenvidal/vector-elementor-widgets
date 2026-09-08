<?php
/**
 * BlogPosts style controls.
 *
 * @package Vector\ElementorWidgets\Elementor\Control
 */

declare( strict_types=1 );

namespace Vector\ElementorWidgets\Elementor\Control;

use Elementor\Controls_Manager;
use Elementor\Widget_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Style controls for the BlogPosts widget.
 */
final class BlogPostsStyleControls {

	/**
	 * Register the style section.
	 *
	 * @param Widget_Base $widget Widget instance.
	 *
	 * @return void
	 */
	public function register( Widget_Base $widget ): void {
		$widget->start_controls_section(
			'vew_blog_posts_style',
			array(
				'label' => __( 'Style', 'vector-elementor-widgets' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$widget->add_control(
			'card_background',
			array(
				'label'     => __( 'Card Background', 'vector-elementor-widgets' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .vew-blog-posts__card' => 'background-color: {{VALUE}};',
				),
			)
		);

		$widget->add_control(
			'title_color',
			array(
				'label'     => __( 'Title Color', 'vector-elementor-widgets' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .vew-blog-posts__title a' => 'color: {{VALUE}};',
				),
			)
		);

		$widget->add_control(
			'meta_color',
			array(
				'label'     => __( 'Meta Color', 'vector-elementor-widgets' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .vew-blog-posts__meta' => 'color: {{VALUE}};',
				),
			)
		);

		$widget->add_control(
			'excerpt_color',
			array(
				'label'     => __( 'Excerpt Color', 'vector-elementor-widgets' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .vew-blog-posts__excerpt' => 'color: {{VALUE}};',
				),
			)
		);

		$widget->add_control(
			'read_more_color',
			array(
				'label'     => __( 'Read More Color', 'vector-elementor-widgets' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .vew-blog-posts__more' => 'color: {{VALUE}};',
				),
			)
		);

		$widget->end_controls_section();
	}
}
