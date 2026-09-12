<?php
/**
 * Contact content controls.
 *
 * The vidal-studio contact component: an intro block with contact details
 * (phone, email, location) + a consultation request form. Per the
 * control-mapping contract `data-copy` → TEXT.
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
 * Content controls for the Contact widget.
 */
final class ContactContentControls {

	/**
	 * Register the content section.
	 *
	 * @param Widget_Base $widget Widget instance.
	 *
	 * @return void
	 */
	public function register( Widget_Base $widget ): void {
		$widget->start_controls_section(
			'vew_contact_content',
			array(
				'label' => __( 'Content', 'vector-elementor-widgets' ),
			)
		);

		$widget->add_control(
			'eyebrow',
			array(
				'label'       => __( 'Eyebrow', 'vector-elementor-widgets' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'default'     => __( 'Let\'s talk', 'vector-elementor-widgets' ),
				'label_block' => true,
			)
		);

		$widget->add_control(
			'title',
			array(
				'label'       => __( 'Title', 'vector-elementor-widgets' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'default'     => __( 'Tell me about your business.', 'vector-elementor-widgets' ),
				'label_block' => true,
			)
		);

		$widget->add_control(
			'intro',
			array(
				'label'       => __( 'Intro', 'vector-elementor-widgets' ),
				'type'        => \Elementor\Controls_Manager::TEXTAREA,
				'default'     => __( 'Ready for a website that actually works for you? Send a few details and I\'ll get back to you with next steps — usually within a day.', 'vector-elementor-widgets' ),
				'rows'        => 3,
				'label_block' => true,
			)
		);

		SectionHeading::register_content_controls( $widget );

		$widget->add_control(
			'phone',
			array(
				'label'       => __( 'Phone', 'vector-elementor-widgets' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'default'     => '(843) 455-9604',
				'label_block' => true,
			)
		);

		$widget->add_control(
			'email',
			array(
				'label'       => __( 'Email', 'vector-elementor-widgets' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'default'     => 'vidalstephen@gmail.com',
				'label_block' => true,
			)
		);

		$widget->add_control(
			'location',
			array(
				'label'       => __( 'Location', 'vector-elementor-widgets' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'default'     => __( 'Myrtle Beach, SC', 'vector-elementor-widgets' ),
				'label_block' => true,
			)
		);

		$widget->add_control(
			'form_kicker',
			array(
				'label'       => __( 'Form Kicker', 'vector-elementor-widgets' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'default'     => __( 'Start your project', 'vector-elementor-widgets' ),
				'label_block' => true,
			)
		);

		$widget->add_control(
			'form_title',
			array(
				'label'       => __( 'Form Title', 'vector-elementor-widgets' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'default'     => __( 'Free consultation', 'vector-elementor-widgets' ),
				'label_block' => true,
			)
		);

		$widget->add_control(
			'submit_cta',
			array(
				'label'       => __( 'Submit Button Text', 'vector-elementor-widgets' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'default'     => __( 'Request my consultation', 'vector-elementor-widgets' ),
				'label_block' => true,
			)
		);

		$widget->add_control(
			'form_labels_heading',
			array(
				'label'     => __( 'Form Field Labels', 'vector-elementor-widgets' ),
				'type'      => \Elementor\Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		$widget->add_control(
			'label_name',
			array(
				'label'       => __( 'Name Label', 'vector-elementor-widgets' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'default'     => __( 'Your name', 'vector-elementor-widgets' ),
				'label_block' => true,
			)
		);

		$widget->add_control(
			'label_secondary',
			array(
				'label'       => __( 'Second Field Label', 'vector-elementor-widgets' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'default'     => __( 'Business name', 'vector-elementor-widgets' ),
				'label_block' => true,
			)
		);

		$widget->add_control(
			'label_email',
			array(
				'label'       => __( 'Email Label', 'vector-elementor-widgets' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'default'     => __( 'Email', 'vector-elementor-widgets' ),
				'label_block' => true,
			)
		);

		$widget->add_control(
			'label_phone',
			array(
				'label'       => __( 'Phone Label', 'vector-elementor-widgets' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'default'     => __( 'Phone', 'vector-elementor-widgets' ),
				'label_block' => true,
			)
		);

		$widget->add_control(
			'label_message',
			array(
				'label'       => __( 'Message Label', 'vector-elementor-widgets' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'default'     => __( 'Tell me about your project', 'vector-elementor-widgets' ),
				'label_block' => true,
			)
		);

		$widget->add_control(
			'required_note',
			array(
				'label'       => __( 'Required Fields Note', 'vector-elementor-widgets' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'default'     => __( 'Fields marked * are required', 'vector-elementor-widgets' ),
				'label_block' => true,
			)
		);

		$widget->add_control(
			'privacy_note',
			array(
				'label'       => __( 'Privacy Note', 'vector-elementor-widgets' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'default'     => __( 'No spam. Your information is used only to respond to this request.', 'vector-elementor-widgets' ),
				'label_block' => true,
			)
		);

		$widget->end_controls_section();
	}
}
