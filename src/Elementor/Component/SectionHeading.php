<?php
/**
 * Shared art-directed section heading capability.
 *
 * @package Vector\ElementorWidgets\Elementor\Component
 */

declare( strict_types=1 );

namespace Vector\ElementorWidgets\Elementor\Component;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Widget_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Additive controls and renderer for eyebrow/title/accent/intro compositions.
 */
final class SectionHeading {

	/**
	 * Register additive structured-title controls inside the current section.
	 *
	 * @param Widget_Base $widget Widget receiving the controls.
	 */
	public static function register_content_controls( Widget_Base $widget ): void {
		$widget->add_control(
			'title_accent',
			array(
				'label'       => __( 'Emphasized Title Text', 'vector-elementor-widgets' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => '',
				'label_block' => true,
			)
		);
		$widget->add_control(
			'title_after',
			array(
				'label'       => __( 'Title Text After Emphasis', 'vector-elementor-widgets' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => '',
				'label_block' => true,
				'condition'   => array( 'title_accent!' => '' ),
			)
		);
		$widget->add_responsive_control(
			'title_accent_display',
			array(
				'label'     => __( 'Accent Placement', 'vector-elementor-widgets' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'inline',
				'options'   => array(
					'inline' => __( 'Inline / Natural Wrap', 'vector-elementor-widgets' ),
					'block'  => __( 'New Line', 'vector-elementor-widgets' ),
				),
				'selectors' => array(
					'{{WRAPPER}} .vew-section-heading__accent' => 'display: {{VALUE}};',
				),
				'condition' => array( 'title_accent!' => '' ),
			)
		);
	}

	/**
	 * Register accent appearance controls inside the current style section.
	 *
	 * @param Widget_Base $widget Widget receiving the controls.
	 */
	public static function register_style_controls( Widget_Base $widget ): void {
		$widget->add_control(
			'title_accent_color',
			array(
				'label'     => __( 'Heading Accent Color', 'vector-elementor-widgets' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .vew-section-heading__accent' => 'color: {{VALUE}};',
				),
				'condition' => array( 'title_accent!' => '' ),
			)
		);
		$widget->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'      => 'title_accent_typography',
				'selector'  => '{{WRAPPER}} .vew-section-heading__accent',
				'condition' => array( 'title_accent!' => '' ),
			)
		);
	}

	/**
	 * Settings whitelist fragment consumed by section widgets.
	 *
	 * @return array<string, string>
	 */
	public static function setting_types(): array {
		return array(
			'eyebrow'     => 'string',
			'title'       => 'string',
			'title_accent' => 'string',
			'title_after' => 'string',
			'intro'       => 'string',
		);
	}

	/**
	 * Render a section heading. Legacy title-only settings retain plain markup.
	 *
	 * @param array<string, mixed> $settings Sanitized settings.
	 * @param array<string, mixed> $config   Rendering configuration.
	 */
	public static function render( array $settings, array $config ): string {
		$block   = isset( $config['block_class'] ) ? (string) $config['block_class'] : 'vew-section';
		$wrap    = ! isset( $config['wrap'] ) || (bool) $config['wrap'];
		$eyebrow = isset( $settings['eyebrow'] ) ? (string) $settings['eyebrow'] : '';
		$title   = isset( $settings['title'] ) ? (string) $settings['title'] : '';
		$accent  = isset( $settings['title_accent'] ) ? (string) $settings['title_accent'] : '';
		$after   = isset( $settings['title_after'] ) ? (string) $settings['title_after'] : '';
		$intro   = isset( $settings['intro'] ) ? (string) $settings['intro'] : '';

		if ( '' === $eyebrow && '' === $title && '' === $accent && '' === $after && '' === $intro ) {
			return '';
		}

		$html = $wrap ? '<header class="' . esc_attr( $block . '__heading' ) . ' vew-section-heading">' : '';
		if ( '' !== $eyebrow ) {
			$html .= '<p class="' . esc_attr( $block . '__eyebrow' ) . ' vew-section-heading__eyebrow">' . esc_html( $eyebrow ) . '</p>';
		}
		if ( '' !== $title || '' !== $accent || '' !== $after ) {
			$title_class = esc_attr( $block . '__title' );
			if ( '' === $accent && '' === $after ) {
				$html .= '<h2 class="' . $title_class . ' vew-section-heading__title">' . esc_html( $title ) . '</h2>';
			} else {
				$html .= '<h2 class="' . $title_class . ' vew-section-heading__title">';
				if ( '' !== $title ) {
					$html .= '<span class="vew-section-heading__before">' . esc_html( $title ) . '</span> ';
				}
				if ( '' !== $accent ) {
					$html .= '<em class="vew-section-heading__accent">' . esc_html( $accent ) . '</em>';
				}
				if ( '' !== $after ) {
					$html .= ' <span class="vew-section-heading__after">' . esc_html( $after ) . '</span>';
				}
				$html .= '</h2>';
			}
		}
		if ( '' !== $intro ) {
			$intro_suffix = isset( $config['intro_suffix'] ) ? (string) $config['intro_suffix'] : 'intro';
			$html        .= '<p class="' . esc_attr( $block . '__' . $intro_suffix ) . ' vew-section-heading__intro">' . esc_html( $intro ) . '</p>';
		}
		$html .= $wrap ? '</header>' : '';
		return $html;
	}
}
