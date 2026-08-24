<?php
/**
 * CTA Banner content controls.
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
 * Content controls for the CTA Banner widget.
 */
final class CtaContentControls {

	/**
	 * Register the content section.
	 *
	 * @param Widget_Base $widget Widget instance.
	 *
	 * @return void
	 */
	public function register( Widget_Base $widget ): void {
		$widget->start_controls_section(
			'vew_cta_content',
			array(
				'label' => __( 'Content', 'vector-elementor-widgets' ),
			)
		);

		$widget->add_control(
			'heading',
			array(
				'label'       => __( 'Heading', 'vector-elementor-widgets' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'default'     => __( 'Ready to get started?', 'vector-elementor-widgets' ),
				'label_block' => true,
			)
		);

		$widget->add_control(
			'description',
			array(
				'label'       => __( 'Description', 'vector-elementor-widgets' ),
				'type'        => \Elementor\Controls_Manager::TEXTAREA,
				'default'     => __( 'Tell your visitors what happens next and why they should act now.', 'vector-elementor-widgets' ),
				'rows'        => 3,
				'label_block' => true,
			)
		);

		$widget->add_control(
			'primary_text',
			array(
				'label'       => __( 'Primary Button Text', 'vector-elementor-widgets' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'default'     => __( 'Get Started', 'vector-elementor-widgets' ),
				'label_block' => true,
			)
		);

		$widget->add_control(
			'primary_url',
			array(
				'label'       => __( 'Primary Button Link', 'vector-elementor-widgets' ),
				'type'        => \Elementor\Controls_Manager::URL,
				'placeholder' => 'https://example.com',
				'default'     => array(
					'url' => '#',
				),
			)
		);

		$widget->add_control(
			'secondary_text',
			array(
				'label'       => __( 'Secondary Button Text', 'vector-elementor-widgets' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'default'     => __( 'Learn More', 'vector-elementor-widgets' ),
				'label_block' => true,
			)
		);

		$widget->add_control(
			'secondary_url',
			array(
				'label'       => __( 'Secondary Button Link', 'vector-elementor-widgets' ),
				'type'        => \Elementor\Controls_Manager::URL,
				'placeholder' => 'https://example.com',
				'default'     => array(
					'url' => '#',
				),
			)
		);

		$widget->end_controls_section();
	}
}
