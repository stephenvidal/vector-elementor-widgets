<?php
/**
 * Footer content controls.
 *
 * The vidal-studio site footer: a brand block + a REPEATER of link columns
 * (each with a heading + REPEATER of links) + a bottom bar. Per the
 * control-mapping contract `data-copy` → TEXT, `data-menu` → REPEATER.
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
 * Content controls for the Footer widget.
 */
final class FooterContentControls {

	/**
	 * Register the content section.
	 *
	 * @param Widget_Base $widget Widget instance.
	 *
	 * @return void
	 */
	public function register( Widget_Base $widget ): void {
		$widget->start_controls_section(
			'vew_footer_content',
			array(
				'label' => __( 'Content', 'vector-elementor-widgets' ),
			)
		);

		$widget->add_control(
			'brand_name',
			array(
				'label'       => __( 'Brand Name', 'vector-elementor-widgets' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'default'     => __( 'VIDAL STUDIO', 'vector-elementor-widgets' ),
				'label_block' => true,
			)
		);

		$widget->add_control(
			'brand_tag',
			array(
				'label'       => __( 'Brand Tagline', 'vector-elementor-widgets' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'default'     => __( 'WEB DESIGN & BUILD', 'vector-elementor-widgets' ),
				'label_block' => true,
			)
		);

		$widget->add_control(
			'blurb',
			array(
				'label'       => __( 'Blurb', 'vector-elementor-widgets' ),
				'type'        => \Elementor\Controls_Manager::TEXTAREA,
				'default'     => __( 'Hand-built websites for small business. Based in Myrtle Beach, SC — working for clients everywhere.', 'vector-elementor-widgets' ),
				'rows'        => 3,
				'label_block' => true,
			)
		);

		$widget->add_control(
			'columns',
			array(
				'label'       => __( 'Link Columns', 'vector-elementor-widgets' ),
				'type'        => \Elementor\Controls_Manager::REPEATER,
				'fields'      => array(
					array(
						'name'        => 'heading',
						'label'       => __( 'Column Heading', 'vector-elementor-widgets' ),
						'type'        => \Elementor\Controls_Manager::TEXT,
						'default'     => __( 'Column', 'vector-elementor-widgets' ),
						'label_block' => true,
					),
					array(
						'name'        => 'links',
						'label'       => __( 'Links', 'vector-elementor-widgets' ),
						'type'        => \Elementor\Controls_Manager::REPEATER,
						'fields'      => array(
							array(
								'name'        => 'text',
								'label'       => __( 'Label', 'vector-elementor-widgets' ),
								'type'        => \Elementor\Controls_Manager::TEXT,
								'default'     => __( 'Link', 'vector-elementor-widgets' ),
								'label_block' => true,
							),
							array(
								'name'        => 'url',
								'label'       => __( 'URL', 'vector-elementor-widgets' ),
								'type'        => \Elementor\Controls_Manager::URL,
								'default'     => array(
									'url' => '#',
								),
							),
						),
						'title_field' => '{{{ text }}}',
					),
				),
				'default'     => array(
					array(
						'heading' => __( 'Navigate', 'vector-elementor-widgets' ),
						'links'   => array(
							array(
								'text' => __( 'Services', 'vector-elementor-widgets' ),
								'url' => array( 'url' => '#services' ),
							),
							array(
								'text' => __( 'Portfolio', 'vector-elementor-widgets' ),
								'url' => array( 'url' => '#portfolio' ),
							),
							array(
								'text' => __( 'Process', 'vector-elementor-widgets' ),
								'url' => array( 'url' => '#process' ),
							),
							array(
								'text' => __( 'FAQ', 'vector-elementor-widgets' ),
								'url' => array( 'url' => '#faq' ),
							),
						),
					),
					array(
						'heading' => __( 'Work with me', 'vector-elementor-widgets' ),
						'links'   => array(
							array(
								'text' => __( 'Pricing', 'vector-elementor-widgets' ),
								'url' => array( 'url' => '#pricing' ),
							),
							array(
								'text' => __( 'Start a project', 'vector-elementor-widgets' ),
								'url' => array( 'url' => '#contact' ),
							),
							array(
								'text' => __( 'Free consultation', 'vector-elementor-widgets' ),
								'url' => array( 'url' => '#contact' ),
							),
						),
					),
					array(
						'heading' => __( 'Get in touch', 'vector-elementor-widgets' ),
						'links'   => array(
							array(
								'text' => __( '(843) 455-9604', 'vector-elementor-widgets' ),
								'url' => array( 'url' => 'tel:+18434559604' ),
							),
							array(
								'text' => __( 'vidalstephen@gmail.com', 'vector-elementor-widgets' ),
								'url' => array( 'url' => 'mailto:vidalstephen@gmail.com' ),
							),
						),
					),
				),
				'title_field' => '{{{ heading }}}',
			)
		);

		$widget->add_control(
			'copyright',
			array(
				'label'       => __( 'Copyright', 'vector-elementor-widgets' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'default'     => __( '© Vidal Studio', 'vector-elementor-widgets' ),
				'label_block' => true,
			)
		);

		$widget->end_controls_section();
	}
}
