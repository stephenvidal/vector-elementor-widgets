<?php
/**
 * Pricing content controls.
 *
 * The vidal-studio pricing component: a heading block + a REPEATER of pricing
 * cards. Per the control-mapping contract `data-copy` → TEXT, `data-pricing`
 * → REPEATER.
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
 * Content controls for the Pricing widget.
 */
final class PricingContentControls {

	/**
	 * Register the content section.
	 *
	 * @param Widget_Base $widget Widget instance.
	 *
	 * @return void
	 */
	public function register( Widget_Base $widget ): void {
		$widget->start_controls_section(
			'vew_pricing_content',
			array(
				'label' => __( 'Content', 'vector-elementor-widgets' ),
			)
		);

		$widget->add_control(
			'eyebrow',
			array(
				'label'       => __( 'Eyebrow', 'vector-elementor-widgets' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'default'     => __( 'Pricing', 'vector-elementor-widgets' ),
				'label_block' => true,
			)
		);

		$widget->add_control(
			'title',
			array(
				'label'       => __( 'Title', 'vector-elementor-widgets' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'default'     => __( 'Fixed packages. No hidden fees.', 'vector-elementor-widgets' ),
				'label_block' => true,
			)
		);

		SectionHeading::register_content_controls( $widget );

		$widget->add_control(
			'note',
			array(
				'label'       => __( 'Note', 'vector-elementor-widgets' ),
				'type'        => \Elementor\Controls_Manager::TEXTAREA,
				'default'     => __( 'Every package includes a custom design, mobile-first build, and a live launch. Add-ons like copywriting and ongoing care are quoted separately.', 'vector-elementor-widgets' ),
				'rows'        => 3,
				'label_block' => true,
			)
		);

		$widget->add_control(
			'plans',
			array(
				'label'       => __( 'Pricing Plans', 'vector-elementor-widgets' ),
				'type'        => \Elementor\Controls_Manager::REPEATER,
				'fields'      => array(
					array(
						'name'        => 'plan',
						'label'       => __( 'Plan Name', 'vector-elementor-widgets' ),
						'type'        => \Elementor\Controls_Manager::TEXT,
						'default'     => __( 'Starter', 'vector-elementor-widgets' ),
						'label_block' => true,
					),
					array(
						'name'        => 'price',
						'label'       => __( 'Price', 'vector-elementor-widgets' ),
						'type'        => \Elementor\Controls_Manager::TEXT,
						'default'     => '$800',
						'label_block' => true,
					),
					array(
						'name'        => 'unit',
						'label'       => __( 'Unit', 'vector-elementor-widgets' ),
						'type'        => \Elementor\Controls_Manager::TEXT,
						'default'     => __( 'one-time', 'vector-elementor-widgets' ),
						'label_block' => true,
					),
					array(
						'name'        => 'featured',
						'label'       => __( 'Featured', 'vector-elementor-widgets' ),
						'type'        => \Elementor\Controls_Manager::SWITCHER,
						'default'     => '',
						'label_on'    => __( 'Yes', 'vector-elementor-widgets' ),
						'label_off'   => __( 'No', 'vector-elementor-widgets' ),
					),
					array(
						'name'        => 'cta',
						'label'       => __( 'Button Text', 'vector-elementor-widgets' ),
						'type'        => \Elementor\Controls_Manager::TEXT,
						'default'     => __( 'Start a project', 'vector-elementor-widgets' ),
						'label_block' => true,
					),
					array(
						'name'        => 'cta_url',
						'label'       => __( 'Button Link', 'vector-elementor-widgets' ),
						'type'        => \Elementor\Controls_Manager::URL,
						'default'     => array(
							'url' => '',
						),
						'label_block' => true,
					),
					array(
						'name'        => 'features',
						'label'       => __( 'Features', 'vector-elementor-widgets' ),
						'type'        => \Elementor\Controls_Manager::REPEATER,
						'fields'      => array(
							array(
								'name'        => 'text',
								'label'       => __( 'Feature', 'vector-elementor-widgets' ),
								'type'        => \Elementor\Controls_Manager::TEXT,
								'default'     => __( 'Feature', 'vector-elementor-widgets' ),
								'label_block' => true,
							),
						),
						'title_field' => '{{{ text }}}',
					),
				),
				'default'     => array(
					array(
						'plan'     => __( 'Starter', 'vector-elementor-widgets' ),
						'price'    => '$800',
						'unit'     => __( 'one-time', 'vector-elementor-widgets' ),
						'featured' => '',
						'cta'      => __( 'Start a project', 'vector-elementor-widgets' ),
						'features' => array(
							array( 'text' => __( '1-page website', 'vector-elementor-widgets' ) ),
							array( 'text' => __( 'Custom design', 'vector-elementor-widgets' ) ),
							array( 'text' => __( 'Mobile-friendly', 'vector-elementor-widgets' ) ),
							array( 'text' => __( 'Live launch', 'vector-elementor-widgets' ) ),
							array( 'text' => __( '1 round of revisions', 'vector-elementor-widgets' ) ),
						),
					),
					array(
						'plan'     => __( 'Standard', 'vector-elementor-widgets' ),
						'price'    => '$1,600',
						'unit'     => __( 'one-time', 'vector-elementor-widgets' ),
						'featured' => 'yes',
						'cta'      => __( 'Start a project', 'vector-elementor-widgets' ),
						'features' => array(
							array( 'text' => __( 'Up to 5 pages', 'vector-elementor-widgets' ) ),
							array( 'text' => __( 'Custom design', 'vector-elementor-widgets' ) ),
							array( 'text' => __( 'Mobile-first build', 'vector-elementor-widgets' ) ),
							array( 'text' => __( 'Contact form', 'vector-elementor-widgets' ) ),
							array( 'text' => __( 'Basic SEO setup', 'vector-elementor-widgets' ) ),
							array( 'text' => __( 'Live launch', 'vector-elementor-widgets' ) ),
							array( 'text' => __( '2 rounds of revisions', 'vector-elementor-widgets' ) ),
						),
					),
					array(
						'plan'     => __( 'Pro', 'vector-elementor-widgets' ),
						'price'    => '$2,800',
						'unit'     => __( 'one-time', 'vector-elementor-widgets' ),
						'featured' => '',
						'cta'      => __( 'Start a project', 'vector-elementor-widgets' ),
						'features' => array(
							array( 'text' => __( 'Up to 10 pages', 'vector-elementor-widgets' ) ),
							array( 'text' => __( 'Custom design', 'vector-elementor-widgets' ) ),
							array( 'text' => __( 'Mobile-first build', 'vector-elementor-widgets' ) ),
							array( 'text' => __( 'Contact forms', 'vector-elementor-widgets' ) ),
							array( 'text' => __( 'Blog / gallery', 'vector-elementor-widgets' ) ),
							array( 'text' => __( 'SEO setup', 'vector-elementor-widgets' ) ),
							array( 'text' => __( 'Fast, secure launch', 'vector-elementor-widgets' ) ),
							array( 'text' => __( '3 rounds of revisions', 'vector-elementor-widgets' ) ),
							array( 'text' => __( '30 days support', 'vector-elementor-widgets' ) ),
						),
					),
				),
				'title_field' => '{{{ plan }}}',
			)
		);

		$widget->end_controls_section();
	}
}
