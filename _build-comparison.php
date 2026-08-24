<?php
/**
 * Build the VEW comparison page with real Derby content, composed as proper
 * Elementor 4.x CONTAINERS (Flexbox) with Boxed content width so the
 * horizontal gutter comes from the container layer — not baked into widgets.
 * Run via: wp eval-file /var/www/html/wp-content/plugins/vector-elementor-widgets/_build-comparison.php
 */

function vew_el( $id, $type, $settings ) {
	return array(
		'id'                       => $id,
		'elType'                   => 'widget',
		'isInner'                  => false,
		'isLocked'                 => false,
		'widgetType'               => $type,
		'settings'                 => $settings,
		'elements'                 => array(),
		'editSettings'             => array( 'defaultEditRoute' => 'content' ),
		'defaultEditSettings'      => array( 'defaultEditRoute' => 'content' ),
		'interactions'             => array(),
	);
}

/**
 * Wrap a widget in a full-width Elementor 4.x CONTAINER with Boxed content
 * (the gutter) + vertical padding.
 */
function vew_container( $id, $widget, $settings = array() ) {
	$defaults = array(
		'content_width' => 'boxed',
		'flex_direction' => 'column',
		'padding'       => array(
			'unit' => 'px',
			'top'  => 80,
			'right' => 0,
			'bottom' => 80,
			'left'  => 0,
			'isLinked' => false,
		),
		'padding_mobile' => array(
			'unit' => 'px',
			'top'  => 60,
			'right' => 17,
			'bottom' => 60,
			'left'  => 17,
			'isLinked' => false,
		),
	);
	$settings = array_merge( $defaults, $settings );

	return array(
		'id'                       => $id,
		'elType'                   => 'container',
		'isInner'                  => false,
		'isLocked'                 => false,
		'settings'                 => $settings,
		'elements'                 => array( $widget ),
		'editSettings'             => array( 'defaultEditRoute' => 'content' ),
		'defaultEditSettings'      => array( 'defaultEditRoute' => 'content' ),
		'interactions'             => array(),
	);
}

$base = wp_upload_dir()['baseurl'] . '/2026/08';
$hero = $base . '/lawn-hero.png';

// --- Hero (full-bleed container, no boxed gutter — hero has its own inner container) ---
$hero_widget = vew_el( 'vphr0001', 'vew-hero', array(
	'eyebrow'            => 'Dependable local property care',
	'headline'           => 'Beautiful Lawns.<br>Professional Care.<br><em>Reliable Service.</em>',
	'copy'               => 'Keep your property looking its absolute best with dependable lawn maintenance, seasonal cleanup, trimming, and landscaping services delivered with care and attention to detail.',
	'primary_cta_text'   => 'Request a Free Lawn Estimate',
	'primary_cta_url'    => array( 'url' => '#contact' ),
	'secondary_cta_text' => 'Explore Lawn Services',
	'secondary_cta_url'  => array( 'url' => '#services' ),
	'trust_list'         => array(
		array( 'text' => 'Locally Owned' ),
		array( 'text' => 'Reliable Scheduling' ),
		array( 'text' => 'Fair, Upfront Pricing' ),
		array( 'text' => 'Homes & Small Businesses' ),
	),
	'hero_background_image' => array( 'url' => $hero, 'id' => 119 ),
) );
$hero_container = vew_container( 'vphrsec', $hero_widget, array(
	'content_width' => 'full',
	'padding'       => array( 'unit' => 'px', 'top' => 0, 'right' => 0, 'bottom' => 0, 'left' => 0, 'isLinked' => true ),
	'padding_mobile' => array( 'unit' => 'px', 'top' => 0, 'right' => 0, 'bottom' => 0, 'left' => 0, 'isLinked' => true ),
) );

// --- Feature Grid (boxed container) ---
$fg_widget = vew_el( 'vpfg0001', 'vew-feature-grid', array(
	'eyebrow' => 'Property care, handled',
	'title'   => 'The details that make a property feel cared for.',
	'intro'   => 'From the weekly rhythm of mowing to seasonal reset work, every visit is built around clean results, reliable communication, and respect for your property.',
	'features' => array(
		array( 'title' => 'Routine Lawn Care', 'text' => 'Consistent mowing, edging, and trimming built around your property and growing conditions.', 'detail' => 'Weekly · Biweekly · By estimate', 'number' => '01' ),
		array( 'title' => 'Crisp Edging & Trimming', 'text' => 'Clean borders around walks, drives, beds, fences, and hard-to-reach areas.', 'detail' => 'Detail-focused finishing', 'number' => '02' ),
		array( 'title' => 'Seasonal Cleanups', 'text' => 'A practical reset for leaves, overgrowth, storm debris, and changing seasons.', 'detail' => 'One-time or recurring', 'number' => '03' ),
	),
) );
$fg_container = vew_container( 'vpfgsec', $fg_widget );

// --- Testimonials (boxed container) ---
$ts_widget = vew_el( 'vpts0001', 'vew-testimonials', array(
	'eyebrow' => 'Good work is remembered.',
	'title'   => 'Trusted by our neighbors',
	'reviews' => array(
		array( 'quote' => 'The kind of consistency we were looking for — clean edges, a tidy driveway, and good communication about timing.', 'author' => 'A.M.', 'role' => 'Local Area · Routine care', 'stars' => 5 ),
		array( 'quote' => 'The overgrown areas looked completely different by the end of the visit. The cleanup was as careful as the cutting.', 'author' => 'J.R.', 'role' => 'Local Area · Yard cleanup', 'stars' => 5 ),
		array( 'quote' => 'Straightforward to schedule, clear about the work, and dependable when the weather changed our original day.', 'author' => 'D.K.', 'role' => 'Local Area · Seasonal service', 'stars' => 5 ),
	),
) );
$ts_container = vew_container( 'vptssec', $ts_widget );

// --- FAQ (boxed container) ---
$faq_widget = vew_el( 'vpfq0001', 'vew-faq', array(
	'eyebrow' => 'FAQ',
	'title'   => 'Frequently asked questions',
	'intro'   => 'A few helpful details before requesting lawn service. If your question is not here, just get in touch.',
	'items'   => array(
		array( 'question' => 'Do you offer weekly and biweekly lawn service?', 'answer' => 'Recurring schedules may be available based on route capacity, property location, and seasonal demand. Share your preferred frequency and we will confirm current options.' ),
		array( 'question' => 'Are estimates free?', 'answer' => 'The site is prepared for free estimate requests, but the final estimate policy should be confirmed by the owner before launch.' ),
		array( 'question' => 'What happens when it rains?', 'answer' => 'Weather can affect safe, quality lawn work. If a visit needs to move, updated timing will be communicated as soon as practical.' ),
		array( 'question' => 'Do you remove lawn debris?', 'answer' => 'Cleanup and debris handling depend on the requested service and local disposal options. Include your preference in the estimate request.' ),
	),
) );
$faq_container = vew_container( 'vpfqsec', $faq_widget );

// --- CTA Banner (boxed container) ---
$cta_widget = vew_el( 'vpct0001', 'vew-cta-banner', array(
	'heading'        => 'Ready for a better-kept yard?',
	'description'    => 'Tell us about your property and we will put together a clear, no-pressure plan for the work it needs.',
	'primary_text'   => 'Request a Free Lawn Estimate',
	'primary_url'    => array( 'url' => '#contact' ),
	'secondary_text' => 'View All Lawn Services',
	'secondary_url'  => array( 'url' => '#services' ),
) );
$cta_container = vew_container( 'vpctsec', $cta_widget );

$content = array( $hero_container, $fg_container, $ts_container, $faq_container, $cta_container );

$pid = get_page_by_path( 'vew-comparison' ) ? get_page_by_path( 'vew-comparison' )->ID : 0;
if ( ! $pid ) {
	$pid = wp_insert_post( array(
		'post_title'   => 'VEW Comparison — Derby Prototype vs Elementor Widgets',
		'post_name'    => 'vew-comparison',
		'post_status'  => 'publish',
		'post_type'    => 'page',
	) );
}

// Store JUST the content elements array (Elementor expects this directly).
update_post_meta( $pid, '_elementor_data', wp_slash( wp_json_encode( $content ) ) );
update_post_meta( $pid, '_elementor_edit_mode', 'builder' );
update_post_meta( $pid, '_elementor_template_type', 'wp-page' );
update_post_meta( $pid, '_wp_page_template', 'elementor_canvas' );
delete_post_meta( $pid, '_elementor_render_cache' );
delete_post_meta( $pid, '_elementor_css' );
delete_post_meta( $pid, '_elementor_page_assets' );

echo "PAGE ID: " . $pid . "\n";
echo "URL: https://wppt.nsystems.live/?p=" . $pid . "\n";
echo "Containers: " . count( $content ) . "\n";
