<?php
// Verify every repeater row (including nested) has a unique _id.
if ( PHP_SAPI !== 'cli' ) { exit( 1 ); }
require_once dirname( __DIR__ ) . '/vendor/autoload.php';

$post_id = (int) ( getenv( 'VEW_POST_ID' ) ?: $argv[1] ?? 0 );
$data    = get_post_meta( $post_id, '_elementor_data', true );
$j       = json_decode( $data, true );

$missing = 0;
$walk    = function ( $els ) use ( &$walk, &$missing ) {
	foreach ( $els as $el ) {
		if ( ! is_array( $el ) ) { continue; }
		if ( isset( $el['settings'] ) && is_array( $el['settings'] ) ) {
			foreach ( $el['settings'] as $key => $value ) {
				if ( ! is_array( $value ) || ! array_is_list( $value ) ) { continue; }
				foreach ( $value as $row ) {
					if ( is_array( $row ) ) {
						if ( ! isset( $row['_id'] ) ) {
							echo "MISSING _id in settings[{$key}]\n";
							$missing++;
						}
						// Nested repeaters.
						foreach ( $row as $rk => $rv ) {
							if ( is_array( $rv ) && array_is_list( $rv ) ) {
								foreach ( $rv as $nested ) {
									if ( is_array( $nested ) && ! isset( $nested['_id'] ) ) {
										echo "MISSING nested _id in settings[{$key}][{$rk}]\n";
										$missing++;
									}
								}
							}
						}
					}
				}
			}
		}
		if ( ! empty( $el['elements'] ) ) { $walk( $el['elements'] ); }
	}
};

if ( is_array( $j ) ) { $walk( $j ); }
echo "post {$post_id}: " . ( $missing > 0 ? "FAIL ({$missing} missing)" : "OK — all repeater rows have _id" ) . "\n";
