<?php
/**
 * PortfolioIndex content controls.
 *
 * A heading block + a grid of factory-site portfolio cards. Per the
 * control-mapping contract, `data-copy` → TEXT via SectionHeading, plus a
 * columns selector. The card list itself is dynamic (queried from pages tagged
 * `_vew_factory_site=1`), so there is no card repeater to author.
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
 * Content controls for the PortfolioIndex widget.
 */
final class PortfolioIndexContentControls {

	/**
	 * Register the content section.
	 *
	 * @param Widget_Base $widget Widget instance.
	 *
	 * @return void
	 */
	public function register( Widget_Base $widget ): void {
		$widget->start_controls_section(
			'vew_portfolio_index_content',
			array(
				'label' => __( 'Content', 'vector-elementor-widgets' ),
			)
		);

		$widget->add_control(
			'eyebrow',
			array(
				'label'       => __( 'Eyebrow', 'vector-elementor-widgets' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'default'     => __( 'Our work', 'vector-elementor-widgets' ),
				'label_block' => true,
			)
		);

		$widget->add_control(
			'title',
			array(
				'label'       => __( 'Title', 'vector-elementor-widgets' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'default'     => __( 'Websites we have built', 'vector-elementor-widgets' ),
				'label_block' => true,
			)
		);

		SectionHeading::register_content_controls( $widget );

		$widget->add_control(
			'columns',
			array(
				'label'   => __( 'Columns', 'vector-elementor-widgets' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'default' => '3',
				'options' => array(
					'2' => __( '2', 'vector-elementor-widgets' ),
					'3' => __( '3', 'vector-elementor-widgets' ),
					'4' => __( '4', 'vector-elementor-widgets' ),
				),
			)
		);

		$widget->add_control(
			'visible',
			array(
				'label'       => __( 'Visible before "Load more"', 'vector-elementor-widgets' ),
				'type'        => \Elementor\Controls_Manager::NUMBER,
				'default'     => 6,
				'min'         => 1,
				'max'         => 24,
				'description' => __( 'Cards shown before the "Load more" button reveals the rest (keeps bandwidth bounded).', 'vector-elementor-widgets' ),
			)
		);

		$widget->end_controls_section();
	}
}
