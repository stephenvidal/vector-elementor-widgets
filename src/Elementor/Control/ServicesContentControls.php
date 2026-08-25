<?php
/**
 * Services content controls.
 *
 * The vidal-studio services component: a heading block + a variant toggle
 * (Design / Build / Launch). Per the control-mapping contract, the N-state
 * toggle = a SELECT `variant` control plus one REPEATER per variant, each
 * gated by `condition: { variant: '<value>' }`.
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
 * Content controls for the Services widget.
 */
final class ServicesContentControls {

	/**
	 * Register the content section.
	 *
	 * @param Widget_Base $widget Widget instance.
	 *
	 * @return void
	 */
	public function register( Widget_Base $widget ): void {
		$widget->start_controls_section(
			'vew_services_content',
			array(
				'label' => __( 'Content', 'vector-elementor-widgets' ),
			)
		);

		$widget->add_control(
			'eyebrow',
			array(
				'label'       => __( 'Eyebrow', 'vector-elementor-widgets' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'default'     => __( 'What I do', 'vector-elementor-widgets' ),
				'label_block' => true,
			)
		);

		$widget->add_control(
			'title',
			array(
				'label'       => __( 'Title', 'vector-elementor-widgets' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'default'     => __( 'Design & build. Nothing you don\'t need.', 'vector-elementor-widgets' ),
				'label_block' => true,
			)
		);

		$widget->add_control(
			'intro',
			array(
				'label'       => __( 'Intro', 'vector-elementor-widgets' ),
				'type'        => \Elementor\Controls_Manager::TEXTAREA,
				'default'     => __( 'Every site is designed and built from scratch to fit your business — and launched on a fast, secure, always-on connection.', 'vector-elementor-widgets' ),
				'rows'        => 3,
				'label_block' => true,
			)
		);

		SectionHeading::register_content_controls( $widget );

		$widget->add_control(
			'variant',
			array(
				'label'   => __( 'Default Variant', 'vector-elementor-widgets' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'default' => 'design',
				'options' => array(
					'design' => __( 'Design', 'vector-elementor-widgets' ),
					'build'  => __( 'Build', 'vector-elementor-widgets' ),
					'launch' => __( 'Launch', 'vector-elementor-widgets' ),
				),
			)
		);

		$labels = array(
			'design' => __( 'Design Cards', 'vector-elementor-widgets' ),
			'build'  => __( 'Build Cards', 'vector-elementor-widgets' ),
			'launch' => __( 'Launch Cards', 'vector-elementor-widgets' ),
		);

		// Configurable tab labels — the variant names stay Design/Build/Launch
		// internally, but the rendered tab text is user-defined. Defaults to the
		// classic Design / Build / Launch labels for backward compatibility.
		$widget->add_control(
			'tab_labels',
			array(
				'label'       => __( 'Tab Labels', 'vector-elementor-widgets' ),
				'type'        => \Elementor\Controls_Manager::REPEATER,
				'fields'      => array(
					array(
						'name'        => 'key',
						'label'       => __( 'Variant Key', 'vector-elementor-widgets' ),
						'type'        => \Elementor\Controls_Manager::SELECT,
						'options'     => array(
							'design' => __( 'Design', 'vector-elementor-widgets' ),
							'build'  => __( 'Build', 'vector-elementor-widgets' ),
							'launch' => __( 'Launch', 'vector-elementor-widgets' ),
						),
						'default'     => 'design',
						'label_block' => true,
					),
					array(
						'name'        => 'label',
						'label'       => __( 'Label', 'vector-elementor-widgets' ),
						'type'        => \Elementor\Controls_Manager::TEXT,
						'default'     => '',
						'label_block' => true,
					),
				),
				'default'     => array(
					array(
						'key'   => 'design',
						'label' => __( 'Design', 'vector-elementor-widgets' ),
					),
					array(
						'key'   => 'build',
						'label' => __( 'Build', 'vector-elementor-widgets' ),
					),
					array(
						'key'   => 'launch',
						'label' => __( 'Launch', 'vector-elementor-widgets' ),
					),
				),
				'title_field' => '{{{ key }}}',
			)
		);

		foreach ( array( 'design', 'build', 'launch' ) as $key ) {
			$widget->add_control(
				$key,
				array(
					'label'       => $labels[ $key ],
					'type'        => \Elementor\Controls_Manager::REPEATER,
					'fields'      => array(
						array(
							'name'        => 'icon',
							'label'       => __( 'Icon', 'vector-elementor-widgets' ),
							'type'        => \Elementor\Controls_Manager::TEXT,
							'default'     => '✦',
							'label_block' => true,
						),
						array(
							'name'        => 'title',
							'label'       => __( 'Title', 'vector-elementor-widgets' ),
							'type'        => \Elementor\Controls_Manager::TEXT,
							'default'     => __( 'Service', 'vector-elementor-widgets' ),
							'label_block' => true,
						),
						array(
							'name'        => 'text',
							'label'       => __( 'Description', 'vector-elementor-widgets' ),
							'type'        => \Elementor\Controls_Manager::TEXTAREA,
							'default'     => __( 'Describe the service here.', 'vector-elementor-widgets' ),
							'label_block' => true,
						),
					),
					'title_field' => '{{{ title }}}',
				)
			);
		}

		$widget->end_controls_section();
	}
}
