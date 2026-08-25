<?php
// Deep-scan Elementor data including REPEATER items for missing _id /
// null elements (the source of the base.php:847 "Undefined array key _id"
// warnings and a possible editor crash).
if ( PHP_SAPI !== 'cli' ) { exit( 1 ); }
require_once dirname( __DIR__ ) . '/vendor/autoload.php';

$posts = get_posts( array( 'post_type' => 'page', 'posts_per_page' => -1, 'post_status' => 'any' ) );

foreach ( $posts as $post ) {
	$data = get_post_meta( $post->ID, '_elementor_data', true );
	if ( empty( $data ) ) { continue; }
	$decoded = json_decode( $data, true );
	if ( ! is_array( $decoded ) ) { continue; }

	$missing_id = 0;
	$null_el    = 0;
	$problems   = array();
	$walk       = function ( $els ) use ( &$walk, &$missing_id, &$null_el, &$problems, $post ) {
		foreach ( $els as $el ) {
			if ( ! is_array( $el ) ) { continue; }
			if ( ! isset( $el['id'] ) && ! isset( $el['_id'] ) ) {
				$missing_id++;
				$problems[] = ( $el['elType'] ?? 'unknown' ) . ( isset( $el['widgetType'] ) ? ':' . $el['widgetType'] : '' );
			}
			if ( array_key_exists( 'elements', $el ) && ! is_array( $el['elements'] ) ) {
				$null_el++;
			}
			// Repeaters: settings contain arrays keyed by repeater name.
			if ( isset( $el['settings'] ) && is_array( $el['settings'] ) ) {
				foreach ( $el['settings'] as $key => $val ) {
					if ( is_array( $val ) && count( $val ) > 0 && isset( $val[0] ) && is_array( $val[0] ) && ! isset( $val[0]['_id'] ) ) {
						$problems[] = 'repeater:' . $key . ' (no _id)';
					}
				}
			}
			if ( ! empty( $el['elements'] ) && is_array( $el['elements'] ) ) {
				$walk( $el['elements'] );
			}
		}
	};
	$walk( $decoded );

	if ( $missing_id > 0 || $null_el > 0 || ! empty( $problems ) ) {
		echo "post {$post->ID} ({$post->post_title}): missing_id={$missing_id} null_elements={$null_el}\n";
		foreach ( array_count_values( $problems ) as $p => $c ) {
			echo "    - {$p} x{$c}\n";
		}
	}
}
echo "scan complete\n";
