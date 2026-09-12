<?php
/**
 * Header content controls.
 *
 * The vidal-studio site header: a brand, a REPEATER of nav links, and a CTA
 * button. Per the control-mapping contract `data-copy` → TEXT, `data-menu` →
 * REPEATER.
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
 * Content controls for the Header widget.
 */
final class HeaderContentControls {

	/**
	 * Register the content section.
	 *
	 * @param Widget_Base $widget Widget instance.
	 *
	 * @return void
	 */
	public function register( Widget_Base $widget ): void {
		$widget->start_controls_section(
			'vew_header_content',
			array(
				'label' => __( 'Content', 'vector-elementor-widgets' ),
			)
		);

		$widget->add_control(
			'logo',
			array(
				'label'   => __( 'Brand Logo', 'vector-elementor-widgets' ),
				'type'    => \Elementor\Controls_Manager::MEDIA,
				'default' => array(
					'url' => '',
				),
				'description' => __( 'Optional brand logo image. Leave empty to use the built-in shield mark.', 'vector-elementor-widgets' ),
			)
		);

		$widget->add_control(
			'announcement',
			array(
				'label'       => __( 'Announcement Text', 'vector-elementor-widgets' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'default'     => '',
				'label_block' => true,
			)
		);

		$widget->add_control(
			'announcement_url',
			array(
				'label'   => __( 'Announcement Link', 'vector-elementor-widgets' ),
				'type'    => \Elementor\Controls_Manager::URL,
				'default' => array(
					'url' => '',
				),
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
			'cta_text',
			array(
				'label'       => __( 'CTA Text', 'vector-elementor-widgets' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'default'     => __( 'Start a project', 'vector-elementor-widgets' ),
				'label_block' => true,
			)
		);

		$widget->add_control(
			'cta_url',
			array(
				'label'       => __( 'CTA Link', 'vector-elementor-widgets' ),
				'type'        => \Elementor\Controls_Manager::URL,
				'default'     => array(
					'url' => '#contact',
				),
			)
		);

		$widget->add_control(
			'links',
			array(
				'label'       => __( 'Navigation Links', 'vector-elementor-widgets' ),
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
				'default'     => array(
					array(
						'text' => __( 'Services', 'vector-elementor-widgets' ),
						'url'  => array( 'url' => '#services' ),
					),
					array(
						'text' => __( 'Portfolio', 'vector-elementor-widgets' ),
						'url'  => array( 'url' => '#portfolio' ),
					),
					array(
						'text' => __( 'Process', 'vector-elementor-widgets' ),
						'url'  => array( 'url' => '#process' ),
					),
					array(
						'text' => __( 'Pricing', 'vector-elementor-widgets' ),
						'url'  => array( 'url' => '#pricing' ),
					),
					array(
						'text' => __( 'FAQ', 'vector-elementor-widgets' ),
						'url'  => array( 'url' => '#faq' ),
					),
					array(
						'text' => __( 'Contact', 'vector-elementor-widgets' ),
						'url'  => array( 'url' => '#contact' ),
					),
				),
				'title_field' => '{{{ text }}}',
			)
		);

		$widget->add_control(
			'mobile_links',
			array(
				'label'       => __( 'Mobile Navigation Links (optional, overrides desktop links in mobile menu)', 'vector-elementor-widgets' ),
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
			)
		);

		$widget->end_controls_section();
	}
}
