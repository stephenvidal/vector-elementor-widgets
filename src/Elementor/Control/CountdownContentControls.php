<?php
/**
 * Countdown content controls.
 *
 * A countdown timer to a target date/time, with an optional section heading.
 * Per the control-mapping contract `data-end` → `end` (ISO 8601 datetime),
 * `data-labels` → `labels` (string).
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
 * Content controls for the Countdown widget.
 */
final class CountdownContentControls {

	/**
	 * Register the content section.
	 *
	 * @param Widget_Base $widget Widget instance.
	 *
	 * @return void
	 */
	public function register( Widget_Base $widget ): void {
		$widget->start_controls_section(
			'vew_countdown_content',
			array(
				'label' => __( 'Countdown', 'vector-elementor-widgets' ),
			)
		);

		SectionHeading::register_content_controls( $widget );

		$widget->add_control(
			'end',
			array(
				'label'       => __( 'Target Date & Time', 'vector-elementor-widgets' ),
				'type'        => \Elementor\Controls_Manager::DATE_TIME,
				'picker_options' => array(
					'enableTime' => true,
					'dateFormat' => 'Y-m-d H:i',
				),
				'default'     => gmdate( 'Y-m-d H:i', time() + DAY_IN_SECONDS * 30 ),
				'description' => __( 'The moment the countdown reaches zero.', 'vector-elementor-widgets' ),
				'label_block' => true,
			)
		);

		$widget->add_control(
			'expired_text',
			array(
				'label'       => __( 'Text After Countdown', 'vector-elementor-widgets' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'default'     => __( 'The countdown has ended.', 'vector-elementor-widgets' ),
				'label_block' => true,
			)
		);

		$widget->end_controls_section();
	}
}
