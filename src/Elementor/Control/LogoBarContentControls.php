<?php
/**
 * LogoBar content controls.
 *
 * A "trusted by" client/partner logo row or marquee: an optional heading block
 * + a REPEATER of logos (MEDIA) with optional link + name. Per the
 * control-mapping contract `data-copy` → TEXT, `data-logo` → REPEATER.
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
 * Content controls for the LogoBar widget.
 */
final class LogoBarContentControls {

	/**
	 * Register the content section.
	 *
	 * @param Widget_Base $widget Widget instance.
	 *
	 * @return void
	 */
	public function register( Widget_Base $widget ): void {
		$widget->start_controls_section(
			'vew_logo_bar_content',
			array(
				'label' => __( 'Content', 'vector-elementor-widgets' ),
			)
		);

		$widget->add_control(
			'eyebrow',
			array(
				'label'       => __( 'Eyebrow', 'vector-elementor-widgets' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'default'     => __( 'Trusted by', 'vector-elementor-widgets' ),
				'label_block' => true,
			)
		);

		$widget->add_control(
			'title',
			array(
				'label'       => __( 'Title', 'vector-elementor-widgets' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'default'     => '',
				'label_block' => true,
			)
		);

		SectionHeading::register_content_controls( $widget );

		$widget->add_control(
			'layout',
			array(
				'label'   => __( 'Layout', 'vector-elementor-widgets' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'default' => 'grid',
				'options' => array(
					'grid'    => __( 'Static grid', 'vector-elementor-widgets' ),
					'marquee' => __( 'Auto-scrolling marquee', 'vector-elementor-widgets' ),
				),
			)
		);

		$widget->add_control(
			'grayscale',
			array(
				'label'        => __( 'Grayscale until hover', 'vector-elementor-widgets' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'label_on'     => __( 'On', 'vector-elementor-widgets' ),
				'label_off'    => __( 'Off', 'vector-elementor-widgets' ),
				'return_value' => 'yes',
				'default'      => 'yes',
			)
		);

		$widget->add_control(
			'logos',
			array(
				'label'       => __( 'Logos', 'vector-elementor-widgets' ),
				'type'        => \Elementor\Controls_Manager::REPEATER,
				'fields'      => array(
					array(
						'name'    => 'logo',
						'label'   => __( 'Logo Image', 'vector-elementor-widgets' ),
						'type'    => \Elementor\Controls_Manager::MEDIA,
						'default' => array(
							'url' => '',
						),
					),
					array(
						'name'        => 'name',
						'label'       => __( 'Name / Alt text', 'vector-elementor-widgets' ),
						'type'        => \Elementor\Controls_Manager::TEXT,
						'default'     => '',
						'label_block' => true,
					),
					array(
						'name'        => 'url',
						'label'       => __( 'Link (optional)', 'vector-elementor-widgets' ),
						'type'        => \Elementor\Controls_Manager::URL,
						'default'     => array(
							'url' => '',
						),
						'label_block' => true,
					),
				),
				'default'     => array(),
				'title_field' => '{{{ name }}}',
			)
		);

		$widget->end_controls_section();
	}
}
