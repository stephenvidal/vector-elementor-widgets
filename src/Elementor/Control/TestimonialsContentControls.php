<?php
/**
 * Testimonials content controls.
 *
 * The Derby reviews component: heading + a REPEATER of review cards. Each
 * card has a quote (blockquote), author, role, and a star rating. Per the
 * control-mapping contract `data-copy` → TEXT, `data-reviews` → REPEATER.
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
 * Content controls for the Testimonials widget.
 */
final class TestimonialsContentControls {

	/**
	 * Register the content section.
	 *
	 * @param Widget_Base $widget Widget instance.
	 *
	 * @return void
	 */
	public function register( Widget_Base $widget ): void {
		$widget->start_controls_section(
			'vew_testimonials_content',
			array(
				'label' => __( 'Content', 'vector-elementor-widgets' ),
			)
		);

		$widget->add_control(
			'eyebrow',
			array(
				'label'       => __( 'Eyebrow', 'vector-elementor-widgets' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'default'     => __( 'Testimonials', 'vector-elementor-widgets' ),
				'label_block' => true,
			)
		);

		$widget->add_control(
			'title',
			array(
				'label'       => __( 'Title', 'vector-elementor-widgets' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'default'     => __( 'Trusted by our neighbors', 'vector-elementor-widgets' ),
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
			'reviews',
			array(
				'label'       => __( 'Reviews', 'vector-elementor-widgets' ),
				'type'        => \Elementor\Controls_Manager::REPEATER,
				'fields'      => array(
					array(
						'name'        => 'quote',
						'label'       => __( 'Quote', 'vector-elementor-widgets' ),
						'type'        => \Elementor\Controls_Manager::TEXTAREA,
						'default'     => __( 'Outstanding service from start to finish.', 'vector-elementor-widgets' ),
						'label_block' => true,
					),
					array(
						'name'    => 'avatar',
						'label'   => __( 'Author photo (optional)', 'vector-elementor-widgets' ),
						'type'    => \Elementor\Controls_Manager::MEDIA,
						'default' => array(
							'url' => '',
						),
					),
					array(
						'name'        => 'author',
						'label'       => __( 'Author', 'vector-elementor-widgets' ),
						'type'        => \Elementor\Controls_Manager::TEXT,
						'default'     => __( 'Jamie R.', 'vector-elementor-widgets' ),
						'label_block' => true,
					),
					array(
						'name'        => 'role',
						'label'       => __( 'Role / Context', 'vector-elementor-widgets' ),
						'type'        => \Elementor\Controls_Manager::TEXT,
						'default'     => __( 'Homeowner', 'vector-elementor-widgets' ),
						'label_block' => true,
					),
					array(
						'name'        => 'stars',
						'label'       => __( 'Rating (1-5)', 'vector-elementor-widgets' ),
						'type'        => \Elementor\Controls_Manager::NUMBER,
						'default'     => 5,
						'min'         => 1,
						'max'         => 5,
					),
				),
				'default'     => array(
					array(
						'quote'  => __( 'Outstanding service from start to finish. Our lawn has never looked better.', 'vector-elementor-widgets' ),
						'author' => __( 'Jamie R.', 'vector-elementor-widgets' ),
						'role'   => __( 'Homeowner', 'vector-elementor-widgets' ),
						'stars'  => 5,
					),
					array(
						'quote'  => __( 'Reliable, professional, and always on time. Highly recommended.', 'vector-elementor-widgets' ),
						'author' => __( 'Pat M.', 'vector-elementor-widgets' ),
						'role'   => __( 'Property Manager', 'vector-elementor-widgets' ),
						'stars'  => 5,
					),
					array(
						'quote'  => __( 'They transformed our outdoor space. We could not be happier.', 'vector-elementor-widgets' ),
						'author' => __( 'Taylor S.', 'vector-elementor-widgets' ),
						'role'   => __( 'Business Owner', 'vector-elementor-widgets' ),
						'stars'  => 5,
					),
				),
				'title_field' => '{{{ author }}}',
			)
		);

		$widget->end_controls_section();
	}
}
