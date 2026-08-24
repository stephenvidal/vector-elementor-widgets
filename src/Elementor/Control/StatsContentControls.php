<?php
/**
 * Stats content controls.
 *
 * A dark stats band: a centered heading block + a REPEATER of value/label
 * statistics. Per the control-mapping contract `data-stats` → REPEATER.
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
 * Content controls for the Stats widget.
 */
final class StatsContentControls {

	/**
	 * Register the content section.
	 *
	 * @param Widget_Base $widget Widget instance.
	 *
	 * @return void
	 */
	public function register( Widget_Base $widget ): void {
		$widget->start_controls_section(
			'vew_stats_content',
			array(
				'label' => __( 'Content', 'vector-elementor-widgets' ),
			)
		);

		$widget->add_control(
			'eyebrow',
			array(
				'label'       => __( 'Eyebrow', 'vector-elementor-widgets' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'default'     => __( 'A track record of trust', 'vector-elementor-widgets' ),
				'label_block' => true,
			)
		);

		$widget->add_control(
			'title',
			array(
				'label'       => __( 'Title', 'vector-elementor-widgets' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'default'     => __( 'Numbers that', 'vector-elementor-widgets' ),
				'label_block' => true,
			)
		);

		SectionHeading::register_content_controls( $widget );

		$widget->add_control(
			'stats',
			array(
				'label'       => __( 'Statistics', 'vector-elementor-widgets' ),
				'type'        => \Elementor\Controls_Manager::REPEATER,
				'fields'      => array(
					array(
						'name'        => 'value',
						'label'       => __( 'Value', 'vector-elementor-widgets' ),
						'type'        => \Elementor\Controls_Manager::TEXT,
						'default'     => __( '15+', 'vector-elementor-widgets' ),
						'label_block' => true,
					),
					array(
						'name'        => 'label',
						'label'       => __( 'Label', 'vector-elementor-widgets' ),
						'type'        => \Elementor\Controls_Manager::TEXT,
						'default'     => __( 'Years of practice', 'vector-elementor-widgets' ),
						'label_block' => true,
					),
				),
				'default'     => array(
					array(
						'value' => '15+',
						'label' => __( 'Years of practice', 'vector-elementor-widgets' ),
					),
					array(
						'value' => '500+',
						'label' => __( 'Matters resolved', 'vector-elementor-widgets' ),
					),
					array(
						'value' => '98%',
						'label' => __( 'Client satisfaction', 'vector-elementor-widgets' ),
					),
					array(
						'value' => '24h',
						'label' => __( 'Response time', 'vector-elementor-widgets' ),
					),
				),
				'title_field' => '{{{ value }}}',
			)
		);

		$widget->end_controls_section();
	}
}
