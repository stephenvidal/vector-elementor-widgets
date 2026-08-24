<?php
/**
 * PageHero content controls.
 *
 * Port of the gslg page-hero component. Maps the breadcrumb nav, eyebrow,
 * H1 title, and lead paragraph (data-copy → TEXT, data-copy-href → URL).
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
 * Content controls for the PageHero widget.
 */
final class PageHeroContentControls {

	/**
	 * Register the content section.
	 *
	 * @param Widget_Base $widget Widget instance.
	 *
	 * @return void
	 */
	public function register( Widget_Base $widget ): void {
		$widget->start_controls_section(
			'vew_pagehero_content',
			array(
				'label' => __( 'Content', 'vector-elementor-widgets' ),
			)
		);

		$widget->add_control(
			'eyebrow',
			array(
				'label'       => __( 'Eyebrow', 'vector-elementor-widgets' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'default'     => '',
				'label_block' => true,
			)
		);

		$widget->add_control(
			'title',
			array(
				'label'       => __( 'Title (H1)', 'vector-elementor-widgets' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'default'     => '',
				'label_block' => true,
			)
		);

		$widget->add_control(
			'lead',
			array(
				'label'       => __( 'Lead Paragraph', 'vector-elementor-widgets' ),
				'type'        => \Elementor\Controls_Manager::TEXTAREA,
				'default'     => '',
				'rows'        => 3,
				'label_block' => true,
			)
		);

		$widget->add_control(
			'breadcrumb_home_text',
			array(
				'label'       => __( 'Breadcrumb: Home Text', 'vector-elementor-widgets' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'default'     => __( 'Home', 'vector-elementor-widgets' ),
				'label_block' => true,
			)
		);

		$widget->add_control(
			'breadcrumb_home_url',
			array(
				'label'       => __( 'Breadcrumb: Home Link', 'vector-elementor-widgets' ),
				'type'        => \Elementor\Controls_Manager::URL,
				'placeholder' => 'https://example.com',
				'default'     => array(
					'url' => '#',
				),
			)
		);

		$widget->add_control(
			'breadcrumb_mid_text',
			array(
				'label'       => __( 'Breadcrumb: Middle Text', 'vector-elementor-widgets' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'default'     => '',
				'label_block' => true,
			)
		);

		$widget->add_control(
			'breadcrumb_mid_url',
			array(
				'label'       => __( 'Breadcrumb: Middle Link', 'vector-elementor-widgets' ),
				'type'        => \Elementor\Controls_Manager::URL,
				'placeholder' => 'https://example.com',
				'default'     => array(
					'url' => '#',
				),
			)
		);

		$widget->add_control(
			'breadcrumb_current',
			array(
				'label'       => __( 'Breadcrumb: Current Page', 'vector-elementor-widgets' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'default'     => '',
				'label_block' => true,
			)
		);

		$widget->end_controls_section();
	}
}
