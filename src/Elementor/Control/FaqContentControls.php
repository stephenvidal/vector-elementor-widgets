<?php
/**
 * FAQ content controls.
 *
 * The Derby FAQ accordion component: an intro block + a REPEATER of Q/A
 * items. Per the control-mapping contract `data-copy` → TEXT, `data-faq` →
 * REPEATER. Each item is an accessible accordion (button + aria-expanded +
 * hidden panel).
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
 * Content controls for the FAQ widget.
 */
final class FaqContentControls {

	/**
	 * Register the content section.
	 *
	 * @param Widget_Base $widget Widget instance.
	 *
	 * @return void
	 */
	public function register( Widget_Base $widget ): void {
		$widget->start_controls_section(
			'vew_faq_content',
			array(
				'label' => __( 'Content', 'vector-elementor-widgets' ),
			)
		);

		$widget->add_control(
			'eyebrow',
			array(
				'label'       => __( 'Eyebrow', 'vector-elementor-widgets' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'default'     => __( 'FAQ', 'vector-elementor-widgets' ),
				'label_block' => true,
			)
		);

		$widget->add_control(
			'title',
			array(
				'label'       => __( 'Title', 'vector-elementor-widgets' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'default'     => __( 'Frequently asked questions', 'vector-elementor-widgets' ),
				'label_block' => true,
			)
		);

		$widget->add_control(
			'intro',
			array(
				'label'       => __( 'Intro', 'vector-elementor-widgets' ),
				'type'        => \Elementor\Controls_Manager::TEXTAREA,
				'default'     => __( 'Have a question? Reach out and we will be happy to help.', 'vector-elementor-widgets' ),
				'rows'        => 3,
				'label_block' => true,
			)
		);

		SectionHeading::register_content_controls( $widget );

		$widget->add_control(
			'items',
			array(
				'label'       => __( 'Questions', 'vector-elementor-widgets' ),
				'type'        => \Elementor\Controls_Manager::REPEATER,
				'fields'      => array(
					array(
						'name'        => 'question',
						'label'       => __( 'Question', 'vector-elementor-widgets' ),
						'type'        => \Elementor\Controls_Manager::TEXT,
						'default'     => __( 'Question?', 'vector-elementor-widgets' ),
						'label_block' => true,
					),
					array(
						'name'        => 'answer',
						'label'       => __( 'Answer', 'vector-elementor-widgets' ),
						'type'        => \Elementor\Controls_Manager::TEXTAREA,
						'default'     => __( 'Answer goes here.', 'vector-elementor-widgets' ),
						'label_block' => true,
					),
				),
				'default'     => array(
					array(
						'question' => __( 'Do you offer a free estimate?', 'vector-elementor-widgets' ),
						'answer'   => __( 'Yes — every project starts with a free, no-obligation estimate.', 'vector-elementor-widgets' ),
					),
					array(
						'question' => __( 'How soon can you start?', 'vector-elementor-widgets' ),
						'answer'   => __( 'Most projects can be scheduled within a week of the estimate.', 'vector-elementor-widgets' ),
					),
					array(
						'question' => __( 'Are you insured?', 'vector-elementor-widgets' ),
						'answer'   => __( 'Yes, we are fully insured and licensed for all services we provide.', 'vector-elementor-widgets' ),
					),
				),
				'title_field' => '{{{ question }}}',
			)
		);

		$widget->end_controls_section();
	}
}
