<?php
/**
 * Menu content controls.
 *
 * A menu list: a heading block + a REPEATER of menu items (name, description,
 * price, optional tag). Per the control-mapping contract `data-copy` → TEXT,
 * `data-menu` → REPEATER.
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
 * Content controls for the Menu widget.
 */
final class MenuContentControls {

	/**
	 * Register the content section.
	 *
	 * @param Widget_Base $widget Widget instance.
	 *
	 * @return void
	 */
	public function register( Widget_Base $widget ): void {
		$widget->start_controls_section(
			'vew_menu_content',
			array(
				'label' => __( 'Content', 'vector-elementor-widgets' ),
			)
		);

		$widget->add_control(
			'eyebrow',
			array(
				'label'       => __( 'Eyebrow', 'vector-elementor-widgets' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'default'     => __( "This week's roast", 'vector-elementor-widgets' ),
				'label_block' => true,
			)
		);

		$widget->add_control(
			'title',
			array(
				'label'       => __( 'Title', 'vector-elementor-widgets' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'default'     => __( 'Fresh out', 'vector-elementor-widgets' ),
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
			'items',
			array(
				'label'       => __( 'Menu Items', 'vector-elementor-widgets' ),
				'type'        => \Elementor\Controls_Manager::REPEATER,
				'fields'      => array(
					array(
						'name'        => 'name',
						'label'       => __( 'Name', 'vector-elementor-widgets' ),
						'type'        => \Elementor\Controls_Manager::TEXT,
						'default'     => __( 'Item', 'vector-elementor-widgets' ),
						'label_block' => true,
					),
					array(
						'name'        => 'description',
						'label'       => __( 'Description', 'vector-elementor-widgets' ),
						'type'        => \Elementor\Controls_Manager::TEXTAREA,
						'default'     => '',
						'label_block' => true,
					),
					array(
						'name'        => 'price',
						'label'       => __( 'Price', 'vector-elementor-widgets' ),
						'type'        => \Elementor\Controls_Manager::TEXT,
						'default'     => '',
						'label_block' => true,
					),
					array(
						'name'        => 'tag',
						'label'       => __( 'Tag', 'vector-elementor-widgets' ),
						'type'        => \Elementor\Controls_Manager::TEXT,
						'default'     => '',
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
