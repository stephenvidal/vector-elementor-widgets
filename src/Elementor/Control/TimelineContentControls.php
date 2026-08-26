<?php
/**
 * Timeline content controls.
 *
 * A vertical timeline of milestone events. Renders an optional heading block
 * plus a REPEATER of events (year/date, title, text, optional link). Per the
 * control-mapping contract `data-timeline` → REPEATER.
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
 * Content controls for the Timeline widget.
 */
final class TimelineContentControls {

	/**
	 * Register the content section.
	 *
	 * @param Widget_Base $widget Widget instance.
	 *
	 * @return void
	 */
	public function register( Widget_Base $widget ): void {
		$widget->start_controls_section(
			'vew_timeline_content',
			array(
				'label' => __( 'Timeline', 'vector-elementor-widgets' ),
			)
		);

		SectionHeading::register_content_controls( $widget );

		$widget->add_control(
			'orientation',
			array(
				'label'   => __( 'Layout', 'vector-elementor-widgets' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'default' => 'alternating',
				'options' => array(
					'alternating' => __( 'Alternating (zig-zag)', 'vector-elementor-widgets' ),
					'left'        => __( 'Left column', 'vector-elementor-widgets' ),
				),
			)
		);

		$widget->add_control(
			'events',
			array(
				'label'       => __( 'Events', 'vector-elementor-widgets' ),
				'type'        => \Elementor\Controls_Manager::REPEATER,
				'fields'      => array(
					array(
						'name'        => 'year',
						'label'       => __( 'Year / Date', 'vector-elementor-widgets' ),
						'type'        => \Elementor\Controls_Manager::TEXT,
						'default'     => '',
						'label_block' => true,
					),
					array(
						'name'        => 'title',
						'label'       => __( 'Title', 'vector-elementor-widgets' ),
						'type'        => \Elementor\Controls_Manager::TEXT,
						'default'     => '',
						'label_block' => true,
					),
					array(
						'name'        => 'text',
						'label'       => __( 'Description', 'vector-elementor-widgets' ),
						'type'        => \Elementor\Controls_Manager::TEXTAREA,
						'default'     => '',
						'rows'        => 3,
						'label_block' => true,
					),
					array(
						'name'        => 'link',
						'label'       => __( 'Link', 'vector-elementor-widgets' ),
						'type'        => \Elementor\Controls_Manager::URL,
						'default'     => array(
							'url' => '',
						),
						'label_block' => true,
					),
				),
				'default'     => array(),
				'title_field' => '{{{ title }}}',
			)
		);

		$widget->end_controls_section();
	}
}
