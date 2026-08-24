<?php
/**
 * Split content controls.
 *
 * A two-column split: image + caption on one side, eyebrow/title/paragraphs/
 * quote/signature on the other. Per the control-mapping contract `data-image`
 * → MEDIA, `data-copy` → TEXT.
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
 * Content controls for the Split widget.
 */
final class SplitContentControls {

	/**
	 * Register the content section.
	 *
	 * @param Widget_Base $widget Widget instance.
	 *
	 * @return void
	 */
	public function register( Widget_Base $widget ): void {
		$widget->start_controls_section(
			'vew_split_content',
			array(
				'label' => __( 'Content', 'vector-elementor-widgets' ),
			)
		);

		$widget->add_control(
			'image',
			array(
				'label'   => __( 'Image', 'vector-elementor-widgets' ),
				'type'    => \Elementor\Controls_Manager::MEDIA,
				'default' => array(
					'url' => '',
				),
			)
		);

		$widget->add_control(
			'image_caption',
			array(
				'label'       => __( 'Image Caption', 'vector-elementor-widgets' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'default'     => '',
				'label_block' => true,
			)
		);

		$widget->add_control(
			'eyebrow',
			array(
				'label'       => __( 'Eyebrow', 'vector-elementor-widgets' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'default'     => __( 'Our story', 'vector-elementor-widgets' ),
				'label_block' => true,
			)
		);

		$widget->add_control(
			'title',
			array(
				'label'       => __( 'Title', 'vector-elementor-widgets' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'default'     => __( 'From a home roaster', 'vector-elementor-widgets' ),
				'label_block' => true,
			)
		);

		SectionHeading::register_content_controls( $widget );

		$widget->add_control(
			'paragraphs',
			array(
				'label'       => __( 'Paragraphs', 'vector-elementor-widgets' ),
				'type'        => \Elementor\Controls_Manager::REPEATER,
				'fields'      => array(
					array(
						'name'        => 'text',
						'label'       => __( 'Paragraph', 'vector-elementor-widgets' ),
						'type'        => \Elementor\Controls_Manager::TEXTAREA,
						'default'     => __( 'Describe the story here.', 'vector-elementor-widgets' ),
						'label_block' => true,
					),
				),
				'default'     => array(
					array( 'text' => __( 'A short paragraph about the story.', 'vector-elementor-widgets' ) ),
				),
				'title_field' => '{{{ text }}}',
			)
		);

		$widget->add_control(
			'quote',
			array(
				'label'       => __( 'Quote', 'vector-elementor-widgets' ),
				'type'        => \Elementor\Controls_Manager::TEXTAREA,
				'default'     => '',
				'rows'        => 2,
				'label_block' => true,
			)
		);

		$widget->add_control(
			'signature',
			array(
				'label'       => __( 'Signature Name', 'vector-elementor-widgets' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'default'     => '',
				'label_block' => true,
			)
		);

		$widget->add_control(
			'signature_role',
			array(
				'label'       => __( 'Signature Role', 'vector-elementor-widgets' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'default'     => '',
				'label_block' => true,
			)
		);

		$widget->end_controls_section();
	}
}
