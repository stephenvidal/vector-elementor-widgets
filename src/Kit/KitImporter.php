<?php
/**
 * Kit importer.
 *
 * Validates + imports a kit from a JSON payload (the same shape produced by
 * export). Used by the admin import action. Returns the created {@see Kit},
 * or null with an error reason if the payload is invalid.
 *
 * @package Vector\ElementorWidgets\Kit
 */

declare( strict_types=1 );

namespace Vector\ElementorWidgets\Kit;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Kit importer.
 */
final class KitImporter {

	/**
	 * Import a kit from a JSON string.
	 *
	 * @param string $json Raw JSON payload.
	 *
	 * @return array{kit: Kit|null, error: string}
	 */
	public function from_json( string $json ): array {
		$data = json_decode( $json, true );
		if ( ! is_array( $data ) ) {
			return array(
				'kit' => null,
				'error' => __( 'Invalid JSON payload.', 'vector-elementor-widgets' ),
			);
		}
		unset( $data['_vew_site_kit_version'] );

		if ( ! isset( $data['slug'] ) || ! is_string( $data['slug'] ) ) {
			return array(
				'kit' => null,
				'error' => __( 'Kit payload is missing a slug.', 'vector-elementor-widgets' ),
			);
		}

		try {
			$kit = Kit::from_array( $data );
		} catch ( \InvalidArgumentException $e ) {
			return array(
				'kit' => null,
				'error' => __( 'Invalid kit slug.', 'vector-elementor-widgets' ),
			);
		}

		return array(
			'kit' => $kit,
			'error' => '',
		);
	}
}
