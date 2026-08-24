<?php
/**
 * TeamGrid content controls.
 *
 * A team/staff grid: an optional heading block + a REPEATER of people cards
 * (photo, name, role, bio, socials). Per the control-mapping contract
 * `data-copy` → TEXT, `data-team` → REPEATER.
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
 * Content controls for the TeamGrid widget.
 */
final class TeamGridContentControls {

	/**
	 * Register the content section.
	 *
	 * @param Widget_Base $widget Widget instance.
	 *
	 * @return void
	 */
	public function register( Widget_Base $widget ): void {
		$widget->start_controls_section(
			'vew_team_grid_content',
			array(
				'label' => __( 'Content', 'vector-elementor-widgets' ),
			)
		);

		$widget->add_control(
			'eyebrow',
			array(
				'label'       => __( 'Eyebrow', 'vector-elementor-widgets' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'default'     => __( 'Our team', 'vector-elementor-widgets' ),
				'label_block' => true,
			)
		);

		$widget->add_control(
			'title',
			array(
				'label'       => __( 'Title', 'vector-elementor-widgets' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'default'     => __( 'Meet the people behind the work', 'vector-elementor-widgets' ),
				'label_block' => true,
			)
		);

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

		SectionHeading::register_content_controls( $widget );

		$widget->add_control(
			'columns',
			array(
				'label'   => __( 'Columns', 'vector-elementor-widgets' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'default' => '3',
				'options' => array(
					'2' => __( '2', 'vector-elementor-widgets' ),
					'3' => __( '3', 'vector-elementor-widgets' ),
					'4' => __( '4', 'vector-elementor-widgets' ),
				),
			)
		);

		$widget->add_control(
			'members',
			array(
				'label'       => __( 'Team members', 'vector-elementor-widgets' ),
				'type'        => \Elementor\Controls_Manager::REPEATER,
				'fields'      => array(
					array(
						'name'    => 'photo',
						'label'   => __( 'Photo', 'vector-elementor-widgets' ),
						'type'    => \Elementor\Controls_Manager::MEDIA,
						'default' => array(
							'url' => '',
						),
					),
					array(
						'name'        => 'name',
						'label'       => __( 'Name', 'vector-elementor-widgets' ),
						'type'        => \Elementor\Controls_Manager::TEXT,
						'default'     => '',
						'label_block' => true,
					),
					array(
						'name'        => 'role',
						'label'       => __( 'Role', 'vector-elementor-widgets' ),
						'type'        => \Elementor\Controls_Manager::TEXT,
						'default'     => '',
						'label_block' => true,
					),
					array(
						'name'        => 'bio',
						'label'       => __( 'Bio', 'vector-elementor-widgets' ),
						'type'        => \Elementor\Controls_Manager::TEXTAREA,
						'default'     => '',
						'rows'        => 3,
						'label_block' => true,
					),
					array(
						'name'        => 'twitter',
						'label'       => __( 'X / Twitter URL', 'vector-elementor-widgets' ),
						'type'        => \Elementor\Controls_Manager::URL,
						'default'     => array(
							'url' => '',
						),
						'label_block' => true,
					),
					array(
						'name'        => 'linkedin',
						'label'       => __( 'LinkedIn URL', 'vector-elementor-widgets' ),
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
