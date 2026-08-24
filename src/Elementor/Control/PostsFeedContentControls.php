<?php
/**
 * PostsFeed content controls.
 *
 * A dynamic blog/posts feed: an optional heading block + a query configuration
 * (post type, count, columns, order, which meta to show). Renders a grid of
 * the latest posts via WP_Query.
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
 * Content controls for the PostsFeed widget.
 */
final class PostsFeedContentControls {

	/**
	 * Register the content section.
	 *
	 * @param Widget_Base $widget Widget instance.
	 *
	 * @return void
	 */
	public function register( Widget_Base $widget ): void {
		$widget->start_controls_section(
			'vew_posts_feed_content',
			array(
				'label' => __( 'Content', 'vector-elementor-widgets' ),
			)
		);

		$widget->add_control(
			'eyebrow',
			array(
				'label'       => __( 'Eyebrow', 'vector-elementor-widgets' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'default'     => __( 'Latest', 'vector-elementor-widgets' ),
				'label_block' => true,
			)
		);

		$widget->add_control(
			'title',
			array(
				'label'       => __( 'Title', 'vector-elementor-widgets' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'default'     => __( 'From the blog', 'vector-elementor-widgets' ),
				'label_block' => true,
			)
		);

		SectionHeading::register_content_controls( $widget );

		$widget->add_control(
			'post_type',
			array(
				'label'   => __( 'Post type', 'vector-elementor-widgets' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'default' => 'post',
				'options' => $this->post_type_options(),
			)
		);

		$widget->add_control(
			'count',
			array(
				'label'   => __( 'Number of posts', 'vector-elementor-widgets' ),
				'type'    => \Elementor\Controls_Manager::NUMBER,
				'min'     => 1,
				'max'     => 12,
				'default' => 3,
			)
		);

		$widget->add_control(
			'columns',
			array(
				'label'   => __( 'Columns', 'vector-elementor-widgets' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'default' => '3',
				'options' => array(
					'2' => __( '2', 'vector-elementor-widgets' ),
					'3' => __( '3', 'vector-elementor-widgets' ),
					'4' => __( '4', 'vector-elementor-widgets' ),
				),
			)
		);

		$widget->add_control(
			'order',
			array(
				'label'   => __( 'Order', 'vector-elementor-widgets' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'default' => 'desc',
				'options' => array(
					'desc' => __( 'Newest first', 'vector-elementor-widgets' ),
					'asc'  => __( 'Oldest first', 'vector-elementor-widgets' ),
				),
			)
		);

		$widget->add_control(
			'show_excerpt',
			array(
				'label'        => __( 'Show excerpt', 'vector-elementor-widgets' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'label_on'     => __( 'On', 'vector-elementor-widgets' ),
				'label_off'    => __( 'Off', 'vector-elementor-widgets' ),
				'return_value' => 'yes',
				'default'      => 'yes',
			)
		);

		$widget->add_control(
			'show_date',
			array(
				'label'        => __( 'Show date', 'vector-elementor-widgets' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'label_on'     => __( 'On', 'vector-elementor-widgets' ),
				'label_off'    => __( 'Off', 'vector-elementor-widgets' ),
				'return_value' => 'yes',
				'default'      => 'yes',
			)
		);

		$widget->end_controls_section();
	}

	/**
	 * Public post types (excluding 'page', which is not a feed source) for the
	 * selector.
	 *
	 * @return array<string, string>
	 */
	private function post_type_options(): array {
		$options = array( 'post' => 'post' );

		$public = get_post_types( array( 'public' => true ), 'objects' );
		foreach ( $public as $type ) {
			if ( in_array( $type->name, array( 'post', 'page' ), true ) ) {
				continue;
			}
			$options[ $type->name ] = $type->name;
		}

		return $options;
	}
}
