<?php
/**
 * Repair Elementor repeater data: inject a unique `_id` into every repeater
 * item that is missing one.
 *
 * Elementor requires every repeater row to carry a unique `_id`. Rows imported
 * programmatically (compose-site/import-site) were written without one, which
 * crashes the editor (rich-text/compose ownerDocument null) and spams the
 * debug log (Undefined array key "_id" in base.php). This walks every page's
 * _elementor_data and adds `_id` where missing, then re-saves and flushes the
 * Elementor CSS cache.
 *
 * Usage: wp eval-file tools/repair-repeater-ids.php [--dry-run]
 */
if ( PHP_SAPI !== 'cli' ) { exit( 1 ); }
require_once dirname( __DIR__ ) . '/vendor/autoload.php';

$dry_run = ( getenv( 'VEW_DRY_RUN' ) === '1' );

// Elementor ids look like a 7-char lowercase hex/alnum string.
$pool    = 'abcdef0123456789';
$used    = array();
$make_id = function () use ( &$pool, &$used ) {
	do {
		$id = '';
		for ( $i = 0; $i < 7; $i++ ) {
			$id .= $pool[ wp_rand( 0, strlen( $pool ) - 1 ) ];
		}
	} while ( isset( $used[ $id ] ) );
	$used[ $id ] = true;
	return $id;
};

// Collect all ids already in use so we never collide.
$collect = function ( $els ) use ( &$collect, &$used ) {
	foreach ( $els as $el ) {
		if ( ! is_array( $el ) ) { continue; }
		if ( isset( $el['id'] ) ) { $used[ $el['id'] ] = true; }
		if ( ! empty( $el['elements'] ) && is_array( $el['elements'] ) ) {
			$collect( $el['elements'] );
		}
	}
};

// Walk and inject _id into repeater rows missing it (recursively, so nested
// repeaters like Footer columns→links also get fixed).
$inject = function ( &$els ) use ( &$inject, &$make_id ) {
	// Inject _id into any list-of-objects value, recursing into nested lists.
	$fix_value = function ( &$value ) use ( &$fix_value, &$make_id ): void {
		if ( ! is_array( $value ) ) {
			return;
		}
		if ( array_is_list( $value ) ) {
			foreach ( $value as &$row ) {
				if ( is_array( $row ) ) {
					if ( ! isset( $row['_id'] ) ) {
						$row['_id'] = $make_id();
					}
					// Recurse into the row's own fields (nested repeaters).
					foreach ( $row as &$field ) {
						$fix_value( $field );
					}
					unset( $field );
				}
			}
			unset( $row );
		} else {
			// Associative array (e.g. a controls value) — recurse into fields.
			foreach ( $value as &$field ) {
				$fix_value( $field );
			}
			unset( $field );
		}
	};

	foreach ( $els as &$el ) {
		if ( ! is_array( $el ) ) { continue; }
		if ( ! empty( $el['settings'] ) && is_array( $el['settings'] ) ) {
			foreach ( $el['settings'] as &$val ) {
				$fix_value( $val );
			}
			unset( $val );
		}
		if ( ! empty( $el['elements'] ) && is_array( $el['elements'] ) ) {
			$inject( $el['elements'] );
		}
	}
	unset( $el );
};

$posts  = get_posts( array( 'post_type' => 'page', 'posts_per_page' => -1, 'post_status' => 'any' ) );
$total_fixed = 0;

foreach ( $posts as $post ) {
	$data = get_post_meta( $post->ID, '_elementor_data', true );
	if ( empty( $data ) ) { continue; }
	$decoded = json_decode( $data, true );
	if ( ! is_array( $decoded ) ) { continue; }

	// Pre-scan: collect existing ids, count missing.
	$collect( $decoded );
	$missing = 0;
	// Count missing _id recursively (matches the inject logic).
	$count = function ( &$els ) use ( &$count, &$missing ) {
		$count_value = function ( &$value ) use ( &$count_value, &$missing ): void {
			if ( ! is_array( $value ) ) { return; }
			if ( array_is_list( $value ) ) {
				foreach ( $value as &$row ) {
					if ( is_array( $row ) ) {
						if ( ! isset( $row['_id'] ) ) { $missing++; }
						foreach ( $row as &$field ) { $count_value( $field ); }
						unset( $field );
					}
				}
				unset( $row );
			} else {
				foreach ( $value as &$field ) { $count_value( $field ); }
				unset( $field );
			}
		};
		foreach ( $els as &$el ) {
			if ( ! is_array( $el ) ) { continue; }
			if ( isset( $el['settings'] ) && is_array( $el['settings'] ) ) {
				foreach ( $el['settings'] as &$val ) { $count_value( $val ); }
				unset( $val );
			}
			if ( ! empty( $el['elements'] ) ) { $count( $el['elements'] ); }
		}
		unset( $el );
	};
	$count( $decoded );

	if ( $missing > 0 ) {
		$inject( $decoded );
		$fixed = $missing;
		echo "post {$post->ID} ({$post->post_title}): fixed {$fixed} repeater rows missing _id" . ( $dry_run ? ' [DRY RUN]' : '' ) . "\n";
		if ( ! $dry_run ) {
			update_post_meta( $post->ID, '_elementor_data', wp_slash( wp_json_encode( $decoded ) ) );
		}
		$total_fixed += $fixed;
	}
}

echo "\nTotal repeater rows repaired: {$total_fixed}\n";

if ( ! $dry_run ) {
	// Flush Elementor CSS cache so stale CSS isn't served.
	if ( function_exists( '\Elementor\Plugin' ) ) {
		\Elementor\Plugin::$instance->files_manager->clear_cache();
		echo "Elementor CSS cache flushed.\n";
	}
}
