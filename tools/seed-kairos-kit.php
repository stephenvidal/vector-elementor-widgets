<?php
/**
 * Seed the Kairos kit into the DB kit store.
 *
 * Usage: wp eval-file tools/seed-kairos-kit.php
 * Idempotent — only creates the kit if it does not already exist.
 */

if ( ! defined( 'WP_CLI' ) || ! WP_CLI ) {
	exit;
}

$store = new \Vector\ElementorWidgets\Kit\KitStore();
if ( $store->has( 'kairos' ) ) {
	WP_CLI::success( 'Kairos kit already exists.' );
	exit;
}

$kit = \Vector\ElementorWidgets\Kit\Kit::from_array(
	array(
		'slug'        => 'kairos',
		'label'       => 'Kairos',
		'description' => 'Kairos Agent theme — deep charcoal-navy + warm gold.',
		'tokens'      => array(
			'ink'                 => '#e8ebf0',
			'muted'               => '#9aa4b3',
			'paper'               => '#0e1319',
			'surface'             => '#131a22',
			'soft'                => '#1a222c',
			'brand'               => '#c9a84c',
			'brand-dark'          => '#0e1319',
			'accent'              => '#c9a84c',
			'on-brand'            => '#0e1319',
			'on-brand-dark'       => '#ffffff',
			'on-accent'           => '#0e1319',
			'line'                => 'rgba(233, 236, 240, 0.12)',
			'shadow'              => '0 18px 44px rgba(0, 0, 0, 0.4)',
			'hero-overlay'        => 'linear-gradient(90deg, rgba(8, 11, 16, 0.94) 0%, rgba(8, 11, 16, 0.72) 45%, rgba(8, 11, 16, 0.3) 74%)',
			'hero-overlay-mobile' => 'linear-gradient(180deg, rgba(8, 11, 16, 0.45), rgba(8, 11, 16, 0.86) 56%, rgba(8, 11, 16, 0.96))',
		),
		'fonts'       => array(
			'display' => '"Inter", -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif',
			'sans'    => '"Inter", -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif',
		),
		'layout'      => array(
			'section_space_y'        => 'clamp(72px, 9vw, 104px)',
			'section_space_y_mobile' => 'clamp(52px, 10vw, 68px)',
		),
	)
);

$store->save( $kit );
WP_CLI::success( 'Seeded Kairos kit.' );
