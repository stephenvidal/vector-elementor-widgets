<?php
/**
 * Kit custom post type registration.
 *
 * Registers the `vew_site_kit` CPT that stores each site kit's config as
 * post meta (`_vew_site_kit_config`). The post name is the kit slug.
 *
 * @package Vector\ElementorWidgets\Kit
 */

declare( strict_types=1 );

namespace Vector\ElementorWidgets\Kit;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers the kit CPT.
 */
final class KitPostType {

	/**
	 * Custom post type slug.
	 */
	public const POST_TYPE = 'vew_site_kit';

	/**
	 * Post meta key holding the serialized config array.
	 */
	public const META_KEY = '_vew_site_kit_config';

	/**
	 * Register hooks.
	 *
	 * @return void
	 */
	public function register_hooks(): void {
		add_action( 'init', $this->register( ... ) );
	}

	/**
	 * Register the CPT.
	 *
	 * @return void
	 */
	public function register(): void {
		\register_post_type(
			self::POST_TYPE,
			array(
				'label'               => __( 'Site Kits', 'vector-elementor-widgets' ),
				'public'             => false,
				'publicly_queryable' => false,
				'show_ui'            => false,
				'show_in_menu'       => false,
				'query_var'          => false,
				'rewrite'            => false,
				'capability_type'    => 'post',
				'has_archive'        => false,
				'hierarchical'       => false,
				'supports'           => array( 'title' ),
			)
		);
	}
}
