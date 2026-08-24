<?php
/**
 * Process content controls.
 *
 * The vidal-studio process component: a heading block + a REPEATER of steps.
 * Per the control-mapping contract `data-copy` → TEXT, `data-process` →
 * REPEATER.
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
 * Content controls for the Process widget.
 */
final class ProcessContentControls {

	/**
	 * Register the content section.
	 *
	 * @param Widget_Base $widget Widget instance.
	 *
	 * @return void
	 */
	public function register( Widget_Base $widget ): void {
		$widget->start_controls_section(
			'vew_process_content',
			array(
				'label' => __( 'Content', 'vector-elementor-widgets' ),
			)
		);

		$widget->add_control(
			'eyebrow',
			array(
				'label'       => __( 'Eyebrow', 'vector-elementor-widgets' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'default'     => __( 'A straightforward process', 'vector-elementor-widgets' ),
				'label_block' => true,
			)
		);

		$widget->add_control(
			'title',
			array(
				'label'       => __( 'Title', 'vector-elementor-widgets' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'default'     => __( 'From first call to launched site.', 'vector-elementor-widgets' ),
				'label_block' => true,
			)
		);

		SectionHeading::register_content_controls( $widget );

		$widget->add_control(
			'note',
			array(
				'label'       => __( 'Note', 'vector-elementor-widgets' ),
				'type'        => \Elementor\Controls_Manager::TEXTAREA,
				'default'     => __( 'No jargon, no surprise invoices. You\'ll know what\'s happening at every step, and you\'ll own your site when it\'s done.', 'vector-elementor-widgets' ),
				'rows'        => 3,
				'label_block' => true,
			)
		);

		$widget->add_control(
			'steps',
			array(
				'label'       => __( 'Steps', 'vector-elementor-widgets' ),
				'type'        => \Elementor\Controls_Manager::REPEATER,
				'fields'      => array(
					array(
						'name'        => 'title',
						'label'       => __( 'Step Title', 'vector-elementor-widgets' ),
						'type'        => \Elementor\Controls_Manager::TEXT,
						'default'     => __( 'Step', 'vector-elementor-widgets' ),
						'label_block' => true,
					),
					array(
						'name'        => 'text',
						'label'       => __( 'Description', 'vector-elementor-widgets' ),
						'type'        => \Elementor\Controls_Manager::TEXTAREA,
						'default'     => __( 'Describe the step here.', 'vector-elementor-widgets' ),
						'label_block' => true,
					),
				),
				'default'     => array(
					array(
						'title' => __( 'Discover', 'vector-elementor-widgets' ),
						'text'  => __( 'A quick call to understand your business, your goals, and what you need from the site.', 'vector-elementor-widgets' ),
					),
					array(
						'title' => __( 'Design', 'vector-elementor-widgets' ),
						'text'  => __( 'I design a custom layout and palette tailored to your niche — and we refine it together.', 'vector-elementor-widgets' ),
					),
					array(
						'title' => __( 'Build', 'vector-elementor-widgets' ),
						'text'  => __( 'I hand-code the site, mobile-first, fast and accessible, keeping you updated as it comes together.', 'vector-elementor-widgets' ),
					),
					array(
						'title' => __( 'Launch', 'vector-elementor-widgets' ),
						'text'  => __( 'We go live on a fast, secure connection and you start getting found. I make sure everything works.', 'vector-elementor-widgets' ),
					),
				),
				'title_field' => '{{{ title }}}',
			)
		);

		$widget->end_controls_section();
	}
}
