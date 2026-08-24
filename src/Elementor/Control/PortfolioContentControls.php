<?php
/**
 * Portfolio content controls.
 *
 * The vidal-studio portfolio component: a heading block + a REPEATER of
 * portfolio cards. Per the control-mapping contract `data-copy` → TEXT,
 * `data-image` → MEDIA, `data-portfolio` → REPEATER.
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
 * Content controls for the Portfolio widget.
 */
final class PortfolioContentControls {

	/**
	 * Register the content section.
	 *
	 * @param Widget_Base $widget Widget instance.
	 *
	 * @return void
	 */
	public function register( Widget_Base $widget ): void {
		$widget->start_controls_section(
			'vew_portfolio_content',
			array(
				'label' => __( 'Content', 'vector-elementor-widgets' ),
			)
		);

		$widget->add_control(
			'eyebrow',
			array(
				'label'       => __( 'Eyebrow', 'vector-elementor-widgets' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'default'     => __( 'Recent work', 'vector-elementor-widgets' ),
				'label_block' => true,
			)
		);

		$widget->add_control(
			'title',
			array(
				'label'       => __( 'Title', 'vector-elementor-widgets' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'default'     => __( 'Real businesses. Live websites.', 'vector-elementor-widgets' ),
				'label_block' => true,
			)
		);

		$widget->add_control(
			'intro',
			array(
				'label'       => __( 'Intro', 'vector-elementor-widgets' ),
				'type'        => \Elementor\Controls_Manager::TEXTAREA,
				'default'     => __( 'Each of these is a live site I designed and built — a different niche, a distinctive look, and a clear job to do. Click through to see them in the wild.', 'vector-elementor-widgets' ),
				'rows'        => 3,
				'label_block' => true,
			)
		);

		SectionHeading::register_content_controls( $widget );

		$widget->add_control(
			'items',
			array(
				'label'       => __( 'Portfolio Items', 'vector-elementor-widgets' ),
				'type'        => \Elementor\Controls_Manager::REPEATER,
				'fields'      => array(
					array(
						'name'        => 'title',
						'label'       => __( 'Title', 'vector-elementor-widgets' ),
						'type'        => \Elementor\Controls_Manager::TEXT,
						'default'     => __( 'Project', 'vector-elementor-widgets' ),
						'label_block' => true,
					),
					array(
						'name'        => 'tag',
						'label'       => __( 'Tag', 'vector-elementor-widgets' ),
						'type'        => \Elementor\Controls_Manager::TEXT,
						'default'     => __( 'Category', 'vector-elementor-widgets' ),
						'label_block' => true,
					),
					array(
						'name'        => 'text',
						'label'       => __( 'Description', 'vector-elementor-widgets' ),
						'type'        => \Elementor\Controls_Manager::TEXTAREA,
						'default'     => __( 'Describe the project here.', 'vector-elementor-widgets' ),
						'label_block' => true,
					),
					array(
						'name'        => 'url',
						'label'       => __( 'Link', 'vector-elementor-widgets' ),
						'type'        => \Elementor\Controls_Manager::URL,
						'default'     => array(
							'url' => '',
						),
					),
					array(
						'name'        => 'link_label',
						'label'       => __( 'Link label', 'vector-elementor-widgets' ),
						'type'        => \Elementor\Controls_Manager::TEXT,
						'default'     => '',
						'label_block' => true,
					),
					array(
						'name'        => 'image',
						'label'       => __( 'Image', 'vector-elementor-widgets' ),
						'type'        => \Elementor\Controls_Manager::MEDIA,
						'default'     => array(
							'url' => '',
						),
					),
					array(
						'name'        => 'alt',
						'label'       => __( 'Image Alt Text', 'vector-elementor-widgets' ),
						'type'        => \Elementor\Controls_Manager::TEXT,
						'default'     => '',
						'label_block' => true,
					),
				),
				'title_field' => '{{{ title }}}',
			)
		);

		$widget->end_controls_section();
	}
}
