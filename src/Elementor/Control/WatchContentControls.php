<?php
/**
 * Watch content controls.
 *
 * A dark watch band: intro + CTA links on one side, media image with play pin
 * on the other. Per the control-mapping contract `data-copy` → TEXT,
 * `data-image` → MEDIA.
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
 * Content controls for the Watch widget.
 */
final class WatchContentControls {

	/**
	 * Register the content section.
	 *
	 * @param Widget_Base $widget Widget instance.
	 *
	 * @return void
	 */
	public function register( Widget_Base $widget ): void {
		$widget->start_controls_section(
			'vew_watch_content',
			array(
				'label' => __( 'Content', 'vector-elementor-widgets' ),
			)
		);

		$widget->add_control(
			'eyebrow',
			array(
				'label'       => __( 'Eyebrow', 'vector-elementor-widgets' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'default'     => __( 'Watch live', 'vector-elementor-widgets' ),
				'label_block' => true,
			)
		);

		$widget->add_control(
			'title',
			array(
				'label'       => __( 'Title', 'vector-elementor-widgets' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'default'     => __( 'Worship with us,', 'vector-elementor-widgets' ),
				'label_block' => true,
			)
		);

		SectionHeading::register_content_controls( $widget );

		$widget->add_control(
			'intro',
			array(
				'label'       => __( 'Intro', 'vector-elementor-widgets' ),
				'type'        => \Elementor\Controls_Manager::TEXTAREA,
				'default'     => '',
				'rows'        => 3,
				'label_block' => true,
			)
		);

		$widget->add_control(
			'image',
			array(
				'label'   => __( 'Media Image', 'vector-elementor-widgets' ),
				'type'    => \Elementor\Controls_Manager::MEDIA,
				'default' => array(
					'url' => '',
				),
			)
		);

		$widget->add_control(
			'cta_text',
			array(
				'label'       => __( 'Primary CTA Text', 'vector-elementor-widgets' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'default'     => __( 'Watch on YouTube', 'vector-elementor-widgets' ),
				'label_block' => true,
			)
		);

		$widget->add_control(
			'cta_url',
			array(
				'label'   => __( 'Primary CTA Link', 'vector-elementor-widgets' ),
				'type'    => \Elementor\Controls_Manager::URL,
				'default' => array(
					'url' => '',
				),
			)
		);

		$widget->add_control(
			'secondary_text',
			array(
				'label'       => __( 'Secondary CTA Text', 'vector-elementor-widgets' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'default'     => '',
				'label_block' => true,
			)
		);

		$widget->add_control(
			'secondary_url',
			array(
				'label'   => __( 'Secondary CTA Link', 'vector-elementor-widgets' ),
				'type'    => \Elementor\Controls_Manager::URL,
				'default' => array(
					'url' => '',
				),
			)
		);

		$widget->end_controls_section();
	}
}
