<?php
/**
 * Shared dynamic-query capability.
 *
 * Additive controls and a sanitized WP_Query args builder for widgets that
 * render a dynamic list of posts (blog feed, post gallery, related posts).
 * Recurrence is now demonstrated across PostsFeed, PortfolioIndex, and the
 * new dynamic widgets, so the query contract is extracted here per the
 * framework-refinement abstraction threshold.
 *
 * @package Vector\ElementorWidgets\Elementor\Component
 */

declare( strict_types=1 );

namespace Vector\ElementorWidgets\Elementor\Component;

use Elementor\Controls_Manager;
use Elementor\Widget_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Additive controls and query-args builder for dynamic post widgets.
 */
final class QueryControls {

	/**
	 * Register additive query controls inside the current content section.
	 *
	 * Control IDs are prefixed `query_` so they never collide with a widget's
	 * own controls in the shared flat stack (ControlSectionIdsTest).
	 *
	 * @param Widget_Base $widget Widget receiving the controls.
	 */
	public static function register_content_controls( Widget_Base $widget ): void {
		$widget->add_control(
			'query_post_type',
			array(
				'label'   => __( 'Post type', 'vector-elementor-widgets' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'post',
				'options' => self::post_type_options(),
			)
		);

		$widget->add_control(
			'query_count',
			array(
				'label'   => __( 'Number of items', 'vector-elementor-widgets' ),
				'type'    => Controls_Manager::NUMBER,
				'min'     => 1,
				'max'     => 24,
				'default' => 6,
			)
		);

		$widget->add_control(
			'query_orderby',
			array(
				'label'   => __( 'Order by', 'vector-elementor-widgets' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'date',
				'options' => array(
					'date'     => __( 'Date', 'vector-elementor-widgets' ),
					'title'    => __( 'Title', 'vector-elementor-widgets' ),
					'modified' => __( 'Last modified', 'vector-elementor-widgets' ),
					'rand'     => __( 'Random', 'vector-elementor-widgets' ),
				),
			)
		);

		$widget->add_control(
			'query_order',
			array(
				'label'   => __( 'Order', 'vector-elementor-widgets' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'desc',
				'options' => array(
					'desc' => __( 'Newest first', 'vector-elementor-widgets' ),
					'asc'  => __( 'Oldest first', 'vector-elementor-widgets' ),
				),
			)
		);

		$widget->add_control(
			'query_ignore_sticky',
			array(
				'label'        => __( 'Ignore sticky posts', 'vector-elementor-widgets' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => __( 'On', 'vector-elementor-widgets' ),
				'label_off'    => __( 'Off', 'vector-elementor-widgets' ),
				'return_value' => 'yes',
				'default'      => 'yes',
			)
		);

		$widget->add_control(
			'query_category',
			array(
				'label'       => __( 'Category (optional)', 'vector-elementor-widgets' ),
				'type'        => Controls_Manager::SELECT2,
				'options'     => self::category_options(),
				'multiple'    => true,
				'label_block' => true,
				'description' => __( 'Leave empty to show all categories.', 'vector-elementor-widgets' ),
			)
		);

		$widget->add_control(
			'query_author',
			array(
				'label'       => __( 'Author (optional)', 'vector-elementor-widgets' ),
				'type'        => Controls_Manager::SELECT2,
				'options'     => self::author_options(),
				'multiple'    => true,
				'label_block' => true,
				'description' => __( 'Leave empty to show all authors.', 'vector-elementor-widgets' ),
			)
		);
	}

	/**
	 * Settings whitelist fragment consumed by dynamic widgets.
	 *
	 * @return array<string, string>
	 */
	public static function setting_types(): array {
		return array(
			'query_post_type'     => 'string',
			'query_count'         => 'int',
			'query_orderby'       => 'string',
			'query_order'         => 'string',
			'query_ignore_sticky' => 'string',
			'query_category'      => 'array',
			'query_author'        => 'array',
		);
	}

	/**
	 * Build a sanitized WP_Query args array from sanitized settings.
	 *
	 * @param array<string, mixed> $safe Sanitized settings (whitelisted + coerced).
	 *
	 * @return array<string, mixed> WP_Query args.
	 */
	public static function query_args( array $safe ): array {
		$post_type = isset( $safe['query_post_type'] ) ? sanitize_key( (string) $safe['query_post_type'] ) : 'post';
		$post_type = post_type_exists( $post_type ) ? $post_type : 'post';

		$count = isset( $safe['query_count'] ) ? (int) $safe['query_count'] : 6;
		$count = max( 1, min( 24, $count ) );

		$orderby = isset( $safe['query_orderby'] ) ? sanitize_key( (string) $safe['query_orderby'] ) : 'date';
		$orderby = in_array( $orderby, array( 'date', 'title', 'modified', 'rand' ), true ) ? $orderby : 'date';

		$order = isset( $safe['query_order'] ) && 'asc' === $safe['query_order'] ? 'ASC' : 'DESC';

		$ignore_sticky = ( isset( $safe['query_ignore_sticky'] ) && 'yes' === $safe['query_ignore_sticky'] );

		$args = array(
			'post_type'      => $post_type,
			'posts_per_page' => $count,
			'orderby'        => $orderby,
			'order'          => $order,
			'ignore_sticky_posts' => $ignore_sticky,
		);

		// Category filter (term include) via tax_query.
		$categories = isset( $safe['query_category'] ) && is_array( $safe['query_category'] )
			? array_filter( array_map( 'absint', $safe['query_category'] ) )
			: array();
		if ( ! empty( $categories ) ) {
			$args['tax_query'] = array( // phpcs:ignore WordPress.DB.SlowDBQuery -- exact-term, low cardinality.
				array(
					'taxonomy' => 'category',
					'field'    => 'term_id',
					'terms'    => array_values( $categories ),
				),
			);
		}

		// Author filter.
		$authors = isset( $safe['query_author'] ) && is_array( $safe['query_author'] )
			? array_filter( array_map( 'absint', $safe['query_author'] ) )
			: array();
		if ( ! empty( $authors ) ) {
			$args['author__in'] = array_values( $authors );
		}

		return $args;
	}

	/**
	 * Public post types (excluding 'page', which is not a feed source).
	 *
	 * @return array<string, string>
	 */
	private static function post_type_options(): array {
		$options = array( 'post' => 'post' );

		$public = get_post_types( array( 'public' => true ), 'objects' );
		foreach ( $public as $type ) {
			if ( in_array( $type->name, array( 'post', 'page' ), true ) ) {
				continue;
			}
			$options[ $type->name ] = $type->name;
		}

		return $options;
	}

	/**
	 * Category terms for the filter selector.
	 *
	 * @return array<string, string>
	 */
	private static function category_options(): array {
		$options = array();

		$terms = get_terms(
			array(
				'taxonomy'   => 'category',
				'hide_empty' => false,
			)
		);

		if ( is_wp_error( $terms ) || ! is_array( $terms ) ) {
			return $options;
		}

		foreach ( $terms as $term ) {
			$options[ (string) $term->term_id ] = (string) $term->name;
		}

		return $options;
	}

	/**
	 * Authors for the filter selector.
	 *
	 * @return array<string, string>
	 */
	private static function author_options(): array {
		$options = array();

		$users = get_users(
			array(
				'who' => 'authors',
			)
		);

		foreach ( $users as $user ) {
			$options[ (string) $user->ID ] = (string) $user->display_name;
		}

		return $options;
	}
}
