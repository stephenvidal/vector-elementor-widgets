<?php
/**
 * Hero content controls.
 *
 * The Derby hero: eyebrow, headline (WYSIWYG), body copy, primary + secondary
 * CTAs (label + URL), and a trust-list repeater. Per the control-mapping
 * contract: `data-copy` → TEXT/WYSIWYG, `data-copy-href` → TEXT+URL,
 * `data-list` → REPEATER.
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
 * Content controls for the Hero widget.
 */
final class HeroContentControls {

	/**
	 * Register the content section.
	 *
	 * @param Widget_Base $widget Widget instance.
	 *
	 * @return void
	 */
	public function register( Widget_Base $widget ): void {
		$widget->start_controls_section(
			'vew_hero_content',
			array(
				'label' => __( 'Content', 'vector-elementor-widgets' ),
			)
		);

		$widget->add_control(
			'eyebrow',
			array(
				'label'       => __( 'Eyebrow', 'vector-elementor-widgets' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'default'     => __( 'Dependable local property care', 'vector-elementor-widgets' ),
				'label_block' => true,
			)
		);

		$widget->add_control(
			'headline',
			array(
				'label'       => __( 'Headline', 'vector-elementor-widgets' ),
				'type'        => \Elementor\Controls_Manager::WYSIWYG,
				'default'     => __( 'Beautiful Lawns.<br>Professional Care.', 'vector-elementor-widgets' ),
				'label_block' => true,
			)
		);

		$widget->add_control(
			'copy',
			array(
				'label'       => __( 'Body Copy', 'vector-elementor-widgets' ),
				'type'        => \Elementor\Controls_Manager::TEXTAREA,
				'default'     => __( 'Keep your property looking its absolute best with dependable, professional care delivered with attention to detail.', 'vector-elementor-widgets' ),
				'rows'        => 4,
				'label_block' => true,
			)
		);

		$widget->add_control(
			'primary_cta_text',
			array(
				'label'       => __( 'Primary Button Text', 'vector-elementor-widgets' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'default'     => __( 'Request an Estimate', 'vector-elementor-widgets' ),
				'label_block' => true,
			)
		);

		$widget->add_control(
			'primary_cta_url',
			array(
				'label'       => __( 'Primary Button Link', 'vector-elementor-widgets' ),
				'type'        => \Elementor\Controls_Manager::URL,
				'placeholder' => '#contact',
				'default'     => array(
					'url' => '#contact',
				),
			)
		);

		$widget->add_control(
			'secondary_cta_text',
			array(
				'label'       => __( 'Secondary Button Text', 'vector-elementor-widgets' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'default'     => __( 'Explore Our Services', 'vector-elementor-widgets' ),
				'label_block' => true,
			)
		);

		$widget->add_control(
			'secondary_cta_url',
			array(
				'label'       => __( 'Secondary Button Link', 'vector-elementor-widgets' ),
				'type'        => \Elementor\Controls_Manager::URL,
				'placeholder' => '#services',
				'default'     => array(
					'url' => '#services',
				),
			)
		);

		$widget->add_control(
			'trust_list',
			array(
				'label'       => __( 'Trust Points', 'vector-elementor-widgets' ),
				'type'        => \Elementor\Controls_Manager::REPEATER,
				'fields'      => array(
					array(
						'name'        => 'text',
						'label'       => __( 'Point', 'vector-elementor-widgets' ),
						'type'        => \Elementor\Controls_Manager::TEXT,
						'default'     => __( 'Dependable', 'vector-elementor-widgets' ),
						'label_block' => true,
					),
				),
				'default'     => array(
					array( 'text' => __( 'Dependable', 'vector-elementor-widgets' ) ),
					array( 'text' => __( 'Insured', 'vector-elementor-widgets' ) ),
					array( 'text' => __( 'Local', 'vector-elementor-widgets' ) ),
				),
				'title_field' => '{{{ text }}}',
			)
		);

		$widget->end_controls_section();
	}
}
