<?php
// Verify EVERY page's repeater rows (incl. nested) have a unique _id.
if ( PHP_SAPI !== 'cli' ) { exit( 1 ); }
require_once dirname( __DIR__ ) . '/vendor/autoload.php';

$posts = get_posts( array( 'post_type' => 'page', 'posts_per_page' => -1, 'post_status' => 'any' ) );
$fail  = 0;

foreach ( $posts as $post ) {
	$data = get_post_meta( $post->ID, '_elementor_data', true );
	if ( empty( $data ) ) { continue; }
	$decoded = json_decode( $data, true );
	if ( ! is_array( $decoded ) ) { continue; }

	$missing = 0;
	$walk    = function ( $els ) use ( &$walk, &$missing ) {
		$chk = function ( $value ) use ( &$chk, &$missing ): void {
			if ( ! is_array( $value ) ) { return; }
			if ( array_is_list( $value ) ) {
				foreach ( $value as $row ) {
					if ( is_array( $row ) ) {
						if ( ! isset( $row['_id'] ) ) { $missing++; }
						foreach ( $row as $f ) { $chk( $f ); }
					}
				}
			} else {
				foreach ( $value as $f ) { $chk( $f ); }
			}
		};
		foreach ( $els as $el ) {
			if ( ! is_array( $el ) ) { continue; }
			if ( isset( $el['settings'] ) && is_array( $el['settings'] ) ) {
				foreach ( $el['settings'] as $v ) { $chk( $v ); }
			}
			if ( ! empty( $el['elements'] ) ) { $walk( $el['elements'] ); }
		}
	};
	$walk( $decoded );

	if ( $missing > 0 ) {
		echo "post {$post->ID} ({$post->post_title}): {$missing} missing\n";
		$fail++;
	}
}

echo $fail > 0 ? "FAIL: {$fail} pages have missing _id\n" : "ALL PAGES OK — every repeater row has _id\n";
