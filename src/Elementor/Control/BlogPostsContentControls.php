<?php
/**
 * BlogPosts content controls.
 *
 * A full-featured dynamic blog grid: optional heading block + shared query
 * configuration + card content toggles. Renders a responsive card grid via
 * WP_Query.
 *
 * @package Vector\ElementorWidgets\Elementor\Control
 */

declare( strict_types=1 );

namespace Vector\ElementorWidgets\Elementor\Control;

use Elementor\Controls_Manager;
use Elementor\Widget_Base;
use Vector\ElementorWidgets\Elementor\Component\QueryControls;
use Vector\ElementorWidgets\Elementor\Component\SectionHeading;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Content controls for the BlogPosts widget.
 */
final class BlogPostsContentControls {

	/**
	 * Register the content section.
	 *
	 * @param Widget_Base $widget Widget instance.
	 *
	 * @return void
	 */
	public function register( Widget_Base $widget ): void {
		$widget->start_controls_section(
			'vew_blog_posts_content',
			array(
				'label' => __( 'Content', 'vector-elementor-widgets' ),
			)
		);

		$widget->add_control(
			'eyebrow',
			array(
				'label'       => __( 'Eyebrow', 'vector-elementor-widgets' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => __( 'Latest', 'vector-elementor-widgets' ),
				'label_block' => true,
			)
		);

		$widget->add_control(
			'title',
			array(
				'label'       => __( 'Title', 'vector-elementor-widgets' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => __( 'From the blog', 'vector-elementor-widgets' ),
				'label_block' => true,
			)
		);

		SectionHeading::register_content_controls( $widget );

		$widget->add_control(
			'columns',
			array(
				'label'   => __( 'Columns', 'vector-elementor-widgets' ),
				'type'    => Controls_Manager::SELECT,
				'default' => '3',
				'options' => array(
					'2' => __( '2', 'vector-elementor-widgets' ),
					'3' => __( '3', 'vector-elementor-widgets' ),
					'4' => __( '4', 'vector-elementor-widgets' ),
				),
			)
		);

		$widget->add_control(
			'visible',
			array(
				'label'   => __( 'Posts shown initially', 'vector-elementor-widgets' ),
				'type'    => Controls_Manager::NUMBER,
				'min'     => 1,
				'max'     => 24,
				'default' => 6,
			)
		);

		$widget->add_control(
			'per_click',
			array(
				'label'   => __( 'Posts per "Load more" click', 'vector-elementor-widgets' ),
				'type'    => Controls_Manager::NUMBER,
				'min'     => 1,
				'max'     => 12,
				'default' => 3,
			)
		);

		$widget->add_control(
			'show_excerpt',
			array(
				'label'        => __( 'Show excerpt', 'vector-elementor-widgets' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => __( 'On', 'vector-elementor-widgets' ),
				'label_off'    => __( 'Off', 'vector-elementor-widgets' ),
				'return_value' => 'yes',
				'default'      => 'yes',
			)
		);

		$widget->add_control(
			'excerpt_length',
			array(
				'label'     => __( 'Excerpt length (words)', 'vector-elementor-widgets' ),
				'type'      => Controls_Manager::NUMBER,
				'min'       => 10,
				'max'       => 60,
				'default'   => 22,
				'condition' => array( 'show_excerpt' => 'yes' ),
			)
		);

		$widget->add_control(
			'show_date',
			array(
				'label'        => __( 'Show date', 'vector-elementor-widgets' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => __( 'On', 'vector-elementor-widgets' ),
				'label_off'    => __( 'Off', 'vector-elementor-widgets' ),
				'return_value' => 'yes',
				'default'      => 'yes',
			)
		);

		$widget->add_control(
			'show_author',
			array(
				'label'        => __( 'Show author', 'vector-elementor-widgets' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => __( 'On', 'vector-elementor-widgets' ),
				'label_off'    => __( 'Off', 'vector-elementor-widgets' ),
				'return_value' => 'yes',
				'default'      => 'yes',
			)
		);

		$widget->add_control(
			'show_category',
			array(
				'label'        => __( 'Show category', 'vector-elementor-widgets' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => __( 'On', 'vector-elementor-widgets' ),
				'label_off'    => __( 'Off', 'vector-elementor-widgets' ),
				'return_value' => 'yes',
				'default'      => 'yes',
			)
		);

		$widget->add_control(
			'show_read_more',
			array(
				'label'        => __( 'Show read-more link', 'vector-elementor-widgets' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => __( 'On', 'vector-elementor-widgets' ),
				'label_off'    => __( 'Off', 'vector-elementor-widgets' ),
				'return_value' => 'yes',
				'default'      => 'yes',
			)
		);

		$widget->end_controls_section();

		// Query section — shared component.
		$widget->start_controls_section(
			'vew_blog_posts_query',
			array(
				'label' => __( 'Query', 'vector-elementor-widgets' ),
			)
		);

		QueryControls::register_content_controls( $widget );

		$widget->end_controls_section();
	}
}
