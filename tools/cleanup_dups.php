<?php
// Clean up duplicate vidal-studio attachments, keeping spec-referenced originals + preview-28.
$keep = array( 532, 528, 529, 530, 531, 533, 541, 542, 674 );

$q = new WP_Query(
	array(
		'post_type'      => 'attachment',
		'post_status'    => 'inherit',
		'posts_per_page' => -1,
	)
);

$deleted = array();
foreach ( $q->posts as $p ) {
	$t   = strtolower( $p->post_title );
	$d   = substr( $p->post_date, 0, 10 );
	if ( '2026-08-26' !== $d || in_array( $p->ID, $keep, true ) ) {
		continue;
	}
	if ( strpos( $t, 'hero-' ) === 0 || strpos( $t, 'port_' ) === 0 || strpos( $t, 'preview-' ) === 0 ) {
		wp_delete_attachment( $p->ID, true );
		$deleted[] = $p->ID . ':' . $p->post_title;
	}
}
echo "DELETED:\n" . implode( "\n", $deleted ) . "\n";

// Verify kept attachments still exist.
foreach ( $keep as $id ) {
	$post = get_post( $id );
	echo "KEEP {$id}: " . ( $post ? $post->post_title . ' @ ' . wp_get_attachment_url( $id ) : 'MISSING' ) . "\n";
}
