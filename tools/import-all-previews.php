<?php
/**
 * One-shot: import every factory page's preview-N.png and set _portfolio_preview.
 * Run once from dev-env:
 *   docker compose run --rm -T -v ~/agent-lab/websites:/var/www/html/sites:ro \
 *     wp-cli -c "wp eval-file /var/www/html/wp-content/plugins/vector-elementor-widgets/tools/import-all-previews.php --allow-root"
 */
if ( PHP_SAPI !== 'cli' ) { exit( 1 ); }

add_filter( 'upload_mimes', static function ( array $mimes ): array {
    $mimes['png'] = 'image/png';
    return $mimes;
} );

$sites_root = '/var/www/html/sites';
$tmp_dir    = sys_get_temp_dir() . '/vew-preview-import';
if ( ! is_dir( $tmp_dir ) ) { mkdir( $tmp_dir, 0755, true ); }

$q = new WP_Query( array(
    'post_type'      => 'page',
    'post_status'    => 'publish',
    'posts_per_page' => -1,
    'meta_key'       => '_vew_factory_site',
    'meta_value'     => '1',
    'meta_compare'   => '=',
) );

$done = array();
foreach ( $q->posts as $post ) {
    $slug = $post->post_name;
    $assets = $sites_root . '/' . $slug . '/assets';
    if ( ! is_dir( $assets ) ) { continue; }

    // Find the preview-N.png (prefer the highest N, i.e. the newest capture).
    $candidates = glob( $assets . '/preview-*.png' );
    if ( empty( $candidates ) ) { continue; }
    usort( $candidates, static function ( $a, $b ) {
        preg_match( '/preview-(\d+)\.png$/', $a, $ma );
        preg_match( '/preview-(\d+)\.png$/', $b, $mb );
        return (int) $mb[1] <=> (int) $ma[1];
    } );
    $src = $candidates[0];
    $fname = basename( $src );

    // Skip if already imported (dedup by filename).
    $existing = get_page_by_title( $fname, OBJECT, 'attachment' );
    if ( $existing instanceof WP_Post ) {
        update_post_meta( $post->ID, '_portfolio_preview', (int) $existing->ID );
        $done[] = "{$slug}: reused {$existing->ID} ({$fname})";
        continue;
    }

    $tmp_path = $tmp_dir . '/' . $fname;
    if ( ! copy( $src, $tmp_path ) ) {
        $done[] = "{$slug}: COPY FAILED";
        continue;
    }
    $aid = media_handle_sideload( array( 'name' => $fname, 'tmp_name' => $tmp_path ), 0 );
    if ( is_wp_error( $aid ) ) {
        $done[] = "{$slug}: IMPORT FAILED " . $aid->get_error_message();
        continue;
    }
    update_post_meta( $post->ID, '_portfolio_preview', (int) $aid );
    $done[] = "{$slug}: imported {$aid} ({$fname})";
}

echo implode( "\n", $done ) . "\n";
echo "TOTAL: " . count( $done ) . "\n";
