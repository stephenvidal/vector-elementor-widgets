<?php
/**
 * Feature Grid content controls.
 *
 * The Derby featured-services component: a heading + eyebrow + intro, and a
 * REPEATER of feature cards. Per the control-mapping contract `data-copy` →
 * TEXT, `data-featured-services` → REPEATER. The first card renders large
 * (spans 2 rows) to preserve the Derby mosaic.
 *
 * @package Vector\ElementorWidgets\Elementor\Control
 */

declare( strict_types=1 );

namespace Vector\ElementorWidgets\Elementor\Control;

use Elementor\Widget_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Content controls for the Feature Grid widget.
 */
final class FeatureGridContentControls {

	/**
	 * Register the content section.
	 *
	 * @param Widget_Base $widget Widget instance.
	 *
	 * @return void
	 */
	public function register( Widget_Base $widget ): void {
		$widget->start_controls_section(
			'vew_feature_grid_content',
			array(
				'label' => __( 'Content', 'vector-elementor-widgets' ),
			)
		);

		$widget->add_control(
			'eyebrow',
			array(
				'label'       => __( 'Eyebrow', 'vector-elementor-widgets' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'default'     => __( 'What we do', 'vector-elementor-widgets' ),
				'label_block' => true,
			)
		);

		$widget->add_control(
			'title',
			array(
				'label'       => __( 'Title', 'vector-elementor-widgets' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'default'     => __( 'Services designed around you', 'vector-elementor-widgets' ),
				'label_block' => true,
			)
		);

		$widget->add_control(
			'title_accent',
			array(
				'label'       => __( 'Emphasized Title Text', 'vector-elementor-widgets' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'default'     => '',
				'label_block' => true,
			)
		);

		$widget->add_control(
			'intro',
			array(
				'label'       => __( 'Intro', 'vector-elementor-widgets' ),
				'type'        => \Elementor\Controls_Manager::TEXTAREA,
				'default'     => __( 'Everything you need to keep your property looking its best, delivered with care and attention to detail.', 'vector-elementor-widgets' ),
				'rows'        => 3,
				'label_block' => true,
			)
		);

		$widget->add_control(
			'features',
			array(
				'label'       => __( 'Feature Cards', 'vector-elementor-widgets' ),
				'type'        => \Elementor\Controls_Manager::REPEATER,
				'fields'      => array(
					array(
						'name'        => 'title',
						'label'       => __( 'Title', 'vector-elementor-widgets' ),
						'type'        => \Elementor\Controls_Manager::TEXT,
						'default'     => __( 'Feature', 'vector-elementor-widgets' ),
						'label_block' => true,
					),
					array(
						'name'        => 'text',
						'label'       => __( 'Description', 'vector-elementor-widgets' ),
						'type'        => \Elementor\Controls_Manager::TEXTAREA,
						'default'     => __( 'Describe the feature here.', 'vector-elementor-widgets' ),
						'label_block' => true,
					),
					array(
						'name'        => 'detail',
						'label'       => __( 'Detail Label', 'vector-elementor-widgets' ),
						'type'        => \Elementor\Controls_Manager::TEXT,
						'default'     => __( 'Learn more', 'vector-elementor-widgets' ),
						'label_block' => true,
					),
					array(
						'name'        => 'number',
						'label'       => __( 'Number', 'vector-elementor-widgets' ),
						'type'        => \Elementor\Controls_Manager::TEXT,
						'default'     => '01',
						'label_block' => true,
					),
				),
				'default'     => array(
					array(
						'title'  => __( 'Lawn Maintenance', 'vector-elementor-widgets' ),
						'text'   => __( 'Regular mowing, edging, and care that keeps your lawn lush and healthy all season.', 'vector-elementor-widgets' ),
						'detail' => __( 'Learn more', 'vector-elementor-widgets' ),
						'number' => '01',
					),
					array(
						'title'  => __( 'Seasonal Cleanup', 'vector-elementor-widgets' ),
						'text'   => __( 'Spring and fall cleanup to reset your property for the changing season.', 'vector-elementor-widgets' ),
						'detail' => __( 'Learn more', 'vector-elementor-widgets' ),
						'number' => '02',
					),
					array(
						'title'  => __( 'Landscaping', 'vector-elementor-widgets' ),
						'text'   => __( 'Design and installation that elevate your outdoor space.', 'vector-elementor-widgets' ),
						'detail' => __( 'Learn more', 'vector-elementor-widgets' ),
						'number' => '03',
					),
				),
				'title_field' => '{{{ title }}}',
			)
		);

		$widget->end_controls_section();
	}
}
