<?php
/**
 * Seed a site kit into the DB kit store from a JSON file.
 *
 * Usage (from dev-env dir):
 *   docker compose run --rm -T -v /home/msn0624c/agent-lab/websites:/var/www/html/sites:ro \
 *     wp-cli -c "wp eval-file /var/www/html/wp-content/plugins/vector-elementor-widgets/tools/seed-site-kit.php <slug> --allow-root"
 *
 * Reads /var/www/html/sites/<slug>/kit.json. Idempotent — updates if exists, creates if not.
 * kit.json shape:
 *   { "label": "...", "description": "...", "tokens": { ... }, "fonts": {...}, "layout": {...} }
 *
 * @package Vector\ElementorWidgets\Tools
 */

if ( ! defined( 'WP_CLI' ) || ! WP_CLI ) {
	exit;
}

$slug = $args[0] ?? '';
if ( '' === $slug ) {
	WP_CLI::error( 'Usage: wp eval-file seed-site-kit.php <slug>' );
}

$path = '/var/www/html/sites/' . $slug . '/kit.json';
if ( ! file_exists( $path ) ) {
	WP_CLI::error( "kit.json not found: {$path}" );
}

$cfg = json_decode( (string) file_get_contents( $path ), true );
if ( ! is_array( $cfg ) || empty( $cfg['tokens'] ) ) {
	WP_CLI::error( "Invalid kit.json for {$slug}" );
}

$store = new \Vector\ElementorWidgets\Kit\KitStore();

$kit = \Vector\ElementorWidgets\Kit\Kit::from_array(
	array(
		'slug'        => $slug,
		'label'       => $cfg['label'] ?? $slug,
		'description' => $cfg['description'] ?? '',
		'tokens'      => $cfg['tokens'],
		'fonts'       => $cfg['fonts'] ?? array(
			'display' => '"Inter", -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif',
			'sans'    => '"Inter", -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif',
		),
		'layout'      => $cfg['layout'] ?? array(
			'section_space_y'        => 'clamp(72px, 9vw, 104px)',
			'section_space_y_mobile' => 'clamp(52px, 10vw, 68px)',
		),
	)
);

$store->save( $kit );
WP_CLI::success( "Seeded/updated kit: {$slug}" );
