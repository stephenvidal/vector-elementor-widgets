<?php
/**
 * Kit store.
 *
 * Persists {@see Kit} objects as `vew_site_kit` posts. The post name is the
 * kit slug; the config array is serialized in the `_vew_site_kit_config`
 * meta. Provides CRUD and lookup by slug.
 *
 * @package Vector\ElementorWidgets\Kit
 */

declare( strict_types=1 );

namespace Vector\ElementorWidgets\Kit;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Persistence layer for site kits.
 *
 * Deliberately non-final so tests can double it when injecting into higher
 * services (e.g. SiteKit).
 */
class KitStore {

	/**
	 * Register the CPT + hooks.
	 *
	 * @return void
	 */
	public function register_hooks(): void {
		( new KitPostType() )->register_hooks();
	}

	/**
	 * Save a kit (create or update by slug).
	 *
	 * @param Kit $kit Kit to persist.
	 *
	 * @return int Post ID.
	 */
	public function save( Kit $kit ): int {
		$existing = $this->find_post_by_slug( $kit->slug() );

		$args = array(
			'post_title'  => $kit->label(),
			'post_status' => 'publish',
			'post_type'   => KitPostType::POST_TYPE,
		);

		$post_id = $existing ? (int) $existing->ID : wp_insert_post( $args );
		if ( $existing ) {
			$args['ID'] = $post_id;
			wp_update_post( $args );
		}

		// Store as a native array so update_post_meta/get_post_meta serialize it
		// with correct slash escaping. Storing a JSON *string* is unsafe: WP's
		// slash-roundtrip mangles the `\"` escapes inside the JSON, corrupting it.
		update_post_meta( $post_id, KitPostType::META_KEY, $kit->to_array() );

		return $post_id;
	}

	/**
	 * Get a kit by slug.
	 *
	 * @param string $slug Kit slug.
	 *
	 * @return Kit|null The kit, or null if not found.
	 */
	public function get( string $slug ): ?Kit {
		$post = $this->find_post_by_slug( $slug );
		if ( null === $post ) {
			return null;
		}
		return $this->kit_from_post( $post );
	}

	/**
	 * Check whether a kit slug exists.
	 *
	 * @param string $slug Kit slug.
	 *
	 * @return bool
	 */
	public function has( string $slug ): bool {
		return null !== $this->find_post_by_slug( $slug );
	}

	/**
	 * Delete a kit by slug.
	 *
	 * @param string $slug Kit slug.
	 *
	 * @return bool True if a kit was deleted.
	 */
	public function delete( string $slug ): bool {
		$post = $this->find_post_by_slug( $slug );
		if ( null === $post ) {
			return false;
		}
		return (bool) wp_delete_post( (int) $post->ID, true );
	}

	/**
	 * List all kit slugs.
	 *
	 * @return array<int, string>
	 */
	public function all_slugs(): array {
		$posts = $this->query_posts( -1 );
		$slugs = array();
		foreach ( $posts as $post ) {
			$slugs[] = (string) $post->post_name;
		}
		sort( $slugs );
		return $slugs;
	}

	/**
	 * List all kits.
	 *
	 * @return array<string, Kit> Map of slug => Kit.
	 */
	public function all(): array {
		$kits = array();
		foreach ( $this->query_posts( -1 ) as $post ) {
			$kit = $this->kit_from_post( $post );
			if ( null !== $kit ) {
				$kits[ $kit->slug() ] = $kit;
			}
		}
		ksort( $kits );
		return $kits;
	}

	/**
	 * Find a kit post by its name (slug).
	 *
	 * @param string $slug Kit slug.
	 *
	 * @return \WP_Post|null
	 */
	private function find_post_by_slug( string $slug ): ?\WP_Post {
		if ( '' === $slug ) {
			return null;
		}
		$query = $this->query_posts( 1, $slug );
		return $query[0] ?? null;
	}

	/**
	 * Query kit posts, optionally filtered by name.
	 *
	 * @param int    $per_page Number of posts (or -1 for all).
	 * @param string $slug     Optional slug filter.
	 *
	 * @return array<int, \WP_Post>
	 */
	private function query_posts( int $per_page, string $slug = '' ): array {
		$args = array(
			'post_type'      => KitPostType::POST_TYPE,
			'post_status'    => 'publish',
			'posts_per_page' => $per_page,
			'orderby'        => 'name',
			'order'          => 'ASC',
			'no_found_rows'  => true,
		);
		if ( '' !== $slug ) {
			$args['name'] = $slug;
		}
		$query = new \WP_Query( $args );
		return $query->posts;
	}

	/**
	 * Hydrate a Kit from a kit post.
	 *
	 * @param \WP_Post $post Kit post.
	 *
	 * @return Kit|null
	 */
	private function kit_from_post( \WP_Post $post ): ?Kit {
		$data = get_post_meta( (int) $post->ID, KitPostType::META_KEY, true );
		if ( ! is_array( $data ) ) {
			return null;
		}
		$data['slug'] = (string) $post->post_name;

		try {
			return Kit::from_array( $data );
		} catch ( \InvalidArgumentException $e ) {
			return null;
		}
	}
}
