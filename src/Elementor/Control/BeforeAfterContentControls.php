<?php
/**
 * BeforeAfter content controls.
 *
 * A draggable before/after image comparison slider. Renders an optional
 * section heading plus a "before" and an "after" image with optional labels,
 * driven by a pointer-drag divider in vew-before-after.js.
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
 * Content controls for the BeforeAfter widget.
 */
final class BeforeAfterContentControls {

	/**
	 * Register the content section.
	 *
	 * @param Widget_Base $widget Widget instance.
	 *
	 * @return void
	 */
	public function register( Widget_Base $widget ): void {
		$widget->start_controls_section(
			'vew_before_after_content',
			array(
				'label' => __( 'Images', 'vector-elementor-widgets' ),
			)
		);

		SectionHeading::register_content_controls( $widget );

		$widget->add_control(
			'before_image',
			array(
				'label'   => __( 'Before Image', 'vector-elementor-widgets' ),
				'type'    => \Elementor\Controls_Manager::MEDIA,
				'default' => array(
					'url' => '',
				),
			)
		);

		$widget->add_control(
			'before_label',
			array(
				'label'       => __( 'Before Label', 'vector-elementor-widgets' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'default'     => __( 'Before', 'vector-elementor-widgets' ),
				'label_block' => true,
			)
		);

		$widget->add_control(
			'after_image',
			array(
				'label'   => __( 'After Image', 'vector-elementor-widgets' ),
				'type'    => \Elementor\Controls_Manager::MEDIA,
				'default' => array(
					'url' => '',
				),
			)
		);

		$widget->add_control(
			'after_label',
			array(
				'label'       => __( 'After Label', 'vector-elementor-widgets' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'default'     => __( 'After', 'vector-elementor-widgets' ),
				'label_block' => true,
			)
		);

		$widget->add_control(
			'orientation',
			array(
				'label'   => __( 'Orientation', 'vector-elementor-widgets' ),
				'type'    => \Elementor\Controls_Manager::CHOOSE,
				'options' => array(
					'horizontal' => array(
						'title' => __( 'Horizontal', 'vector-elementor-widgets' ),
						'icon'  => 'eicon-h-align-stretch',
					),
					'vertical'   => array(
						'title' => __( 'Vertical', 'vector-elementor-widgets' ),
						'icon'  => 'eicon-v-align-stretch',
					),
				),
				'default' => 'horizontal',
			)
		);

		$widget->end_controls_section();
	}
}
