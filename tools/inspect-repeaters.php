<?php
// Inspect raw repeater data on a page to confirm whether repeater items lack _id.
if ( PHP_SAPI !== 'cli' ) { exit( 1 ); }
require_once dirname( __DIR__ ) . '/vendor/autoload.php';
$post_id = isset( $argv[1] ) ? (int) $argv[1] : 0;
$data    = get_post_meta( $post_id, '_elementor_data', true );
$decoded = json_decode( $data, true );

$walk = function ( $els ) use ( &$walk ) {
	foreach ( $els as $el ) {
		if ( ! is_array( $el ) ) { continue; }
		if ( isset( $el['settings'] ) && is_array( $el['settings'] ) ) {
			foreach ( $el['settings'] as $key => $val ) {
				if ( is_array( $val ) && isset( $val[0] ) && is_array( $val[0] ) ) {
					// A repeater: each item should have _id.
					$no_id = 0;
					foreach ( $val as $item ) {
						if ( is_array( $item ) && ! isset( $item['_id'] ) ) { $no_id++; }
					}
					if ( $no_id > 0 ) {
						echo "widget " . ( $el['widgetType'] ?? '?' ) . " -> settings[{$key}]: {$no_id}/" . count( $val ) . " items missing _id\n";
					}
				}
			}
		}
		if ( ! empty( $el['elements'] ) ) { $walk( $el['elements'] ); }
	}
};
if ( is_array( $decoded ) ) { $walk( $decoded ); }
echo "inspect complete (post {$post_id})\n";
