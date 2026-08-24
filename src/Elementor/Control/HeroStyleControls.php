<?php
/**
 * Hero style controls.
 *
 * The Derby hero uses a full-bleed background with a dark overlay. Colors are
 * themed via the site's global kit (--brand, --accent, etc.) — the controls
 * here expose the hero-specific surface + text colors that a site can set
 * per-instance. Defaults are deliberately light-on-dark.
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
 * Style controls for the Hero widget.
 */
final class HeroStyleControls {

	/**
	 * Register the style section.
	 *
	 * @param Widget_Base $widget Widget instance.
	 *
	 * @return void
	 */
	public function register( Widget_Base $widget ): void {
		$widget->start_controls_section(
			'vew_hero_style',
			array(
				'label' => __( 'Style', 'vector-elementor-widgets' ),
				'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
			)
		);

		$widget->add_control(
			'hero_background_image',
			array(
				'label'   => __( 'Background Image', 'vector-elementor-widgets' ),
				'type'    => \Elementor\Controls_Manager::MEDIA,
				'default' => array(
					'url' => '',
				),
			)
		);

		$widget->add_control(
			'hero_background_color',
			array(
				'label'     => __( 'Background Color', 'vector-elementor-widgets' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				// Intentionally NO default: leave empty so the widget stylesheet's
				// var(--brand-dark, ...) resolves the kit token. A hardcoded default
				// here gets baked into post CSS and overrides the kit on every page.
				'selectors' => array(
					'{{WRAPPER}} .vew-hero' => 'background-color: {{VALUE}};',
				),
			)
		);

		$widget->add_control(
			'hero_overlay_opacity',
			array(
				'label'     => __( 'Overlay Opacity', 'vector-elementor-widgets' ),
				'type'      => \Elementor\Controls_Manager::SLIDER,
				'range'     => array(
					'px' => array(
						'min'  => 0,
						'max'  => 100,
						'step' => 1,
					),
				),
				'default'   => array(
					'size' => 100,
				),
				'selectors' => array(
					'{{WRAPPER}} .vew-hero__overlay' => 'opacity: {{SIZE}}%;',
				),
			)
		);

		$widget->add_control(
			'hero_heading_color',
			array(
				'label'     => __( 'Heading Color', 'vector-elementor-widgets' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				// Intentionally NO default: let the widget stylesheet's
				// var(--ink, ...) resolve the kit token. A hardcoded default
				// here gets baked into post CSS and overrides the kit on every page.
				'selectors' => array(
					'{{WRAPPER}} .vew-hero__headline' => 'color: {{VALUE}};',
				),
			)
		);

		$widget->add_control(
			'hero_eyebrow_color',
			array(
				'label'     => __( 'Eyebrow Color', 'vector-elementor-widgets' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				// Intentionally NO default: let the widget stylesheet's
				// var(--brand, ...) resolve the kit token.
				'selectors' => array(
					'{{WRAPPER}} .vew-hero__eyebrow' => 'color: {{VALUE}};',
				),
			)
		);

		$widget->add_control(
			'hero_copy_color',
			array(
				'label'     => __( 'Body Copy Color', 'vector-elementor-widgets' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				// Intentionally NO default: let the widget stylesheet's
				// var(--muted, ...) resolve the kit token.
				'selectors' => array(
					'{{WRAPPER}} .vew-hero__copy' => 'color: {{VALUE}};',
				),
			)
		);

		$widget->end_controls_section();
	}
}
