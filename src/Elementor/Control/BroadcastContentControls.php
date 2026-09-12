<?php
/**
 * Broadcast content controls.
 *
 * One repeater row per recurring time segment (e.g. "Saturday Worship",
 * Sat 11:00, 90 min, its own thumbnail + stream URL). Per the control-mapping
 * contract `data-copy` → TEXT, `data-image` → MEDIA, `data-menu` → REPEATER.
 *
 * All times are wall-clock in the SITE timezone, never a fixed instant, so a
 * configured 11:00 stays 11:00 local across DST.
 *
 * @package Vector\ElementorWidgets\Elementor\Control
 */

declare( strict_types=1 );

namespace Vector\ElementorWidgets\Elementor\Control;

use Elementor\Widget_Base;
use Vector\ElementorWidgets\Elementor\Component\SectionHeading;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Content controls for the Broadcast widget.
 */
final class BroadcastContentControls {

	/**
	 * Weekday options, ISO-8601 numbers keyed by label.
	 *
	 * @return array<int, string>
	 */
	public static function weekday_options(): array {
		return array(
			1 => __( 'Monday', 'vector-elementor-widgets' ),
			2 => __( 'Tuesday', 'vector-elementor-widgets' ),
			3 => __( 'Wednesday', 'vector-elementor-widgets' ),
			4 => __( 'Thursday', 'vector-elementor-widgets' ),
			5 => __( 'Friday', 'vector-elementor-widgets' ),
			6 => __( 'Saturday', 'vector-elementor-widgets' ),
			7 => __( 'Sunday', 'vector-elementor-widgets' ),
		);
	}

	/**
	 * Register the content section.
	 *
	 * @param Widget_Base $widget Widget instance.
	 *
	 * @return void
	 */
	public function register( Widget_Base $widget ): void {
		$widget->start_controls_section(
			'vew_broadcast_content',
			array(
				'label' => __( 'Broadcast', 'vector-elementor-widgets' ),
			)
		);

		SectionHeading::register_content_controls( $widget );

		$widget->add_control(
			'timezone_note',
			array(
				'label'       => __( 'Timezone Note', 'vector-elementor-widgets' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'default'     => __( 'Times shown in Eastern Time', 'vector-elementor-widgets' ),
				'description' => __( 'Shown beneath the schedule so remote viewers know which clock the times refer to.', 'vector-elementor-widgets' ),
				'label_block' => true,
			)
		);

		$widget->add_control(
			'segments',
			array(
				'label'       => __( 'Time Segments', 'vector-elementor-widgets' ),
				'type'        => \Elementor\Controls_Manager::REPEATER,
				'fields'      => array(
					array(
						'name'        => 'label',
						'label'       => __( 'Segment Name', 'vector-elementor-widgets' ),
						'type'        => \Elementor\Controls_Manager::TEXT,
						'default'     => __( 'Saturday Worship', 'vector-elementor-widgets' ),
						'label_block' => true,
					),
					array(
						'name'        => 'days',
						'label'       => __( 'Days', 'vector-elementor-widgets' ),
						'type'        => \Elementor\Controls_Manager::SELECT2,
						'multiple'    => true,
						'label_block' => true,
						'options'     => self::weekday_options(),
						'default'     => array( 6 ),
						'description' => __( 'Select every day this slot recurs on.', 'vector-elementor-widgets' ),
					),
					array(
						'name'        => 'time',
						'label'       => __( 'Start Time', 'vector-elementor-widgets' ),
						'type'        => \Elementor\Controls_Manager::TEXT,
						'default'     => '11:00',
						'placeholder' => '11:00',
						'description' => __( '24-hour HH:MM in the site timezone.', 'vector-elementor-widgets' ),
					),
					array(
						'name'        => 'duration',
						'label'       => __( 'Duration (minutes)', 'vector-elementor-widgets' ),
						'type'        => \Elementor\Controls_Manager::NUMBER,
						'default'     => 90,
						'min'         => 1,
						'max'         => 1440,
						'description' => __( 'How long the segment stays live before rolling to the next one.', 'vector-elementor-widgets' ),
					),
					array(
						'name'        => 'thumbnail',
						'label'       => __( 'Thumbnail', 'vector-elementor-widgets' ),
						'type'        => \Elementor\Controls_Manager::MEDIA,
						'default'     => array( 'url' => '' ),
						'description' => __( 'Image shown while this segment is the next one up.', 'vector-elementor-widgets' ),
					),
					array(
						'name'        => 'stream_url',
						'label'       => __( 'Video / Stream URL', 'vector-elementor-widgets' ),
						'type'        => \Elementor\Controls_Manager::URL,
						'default'     => array( 'url' => '' ),
						'description' => __( 'Where the play button sends viewers once the segment starts.', 'vector-elementor-widgets' ),
					),
					array(
						'name'        => 'secondary_url',
						'label'       => __( 'Second Stream URL', 'vector-elementor-widgets' ),
						'type'        => \Elementor\Controls_Manager::URL,
						'default'     => array( 'url' => '' ),
						'description' => __( 'Optional simulcast — e.g. a Facebook stream.', 'vector-elementor-widgets' ),
					),
				),
				'title_field' => '{{{ label }}}',
				'default'     => array(
					array(
						'label'     => __( 'Saturday Worship', 'vector-elementor-widgets' ),
						'days'      => array( 6 ),
						'time'      => '11:00',
						'duration'  => 90,
					),
					array(
						'label'     => __( 'Tuesday Bible Study', 'vector-elementor-widgets' ),
						'days'      => array( 2 ),
						'time'      => '19:30',
						'duration'  => 60,
					),
				),
			)
		);

		$widget->add_control(
			'starting_soon_heading',
			array(
				'label'     => __( 'Countdown Behaviour', 'vector-elementor-widgets' ),
				'type'      => \Elementor\Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		$widget->add_control(
			'starting_soon_minutes',
			array(
				'label'       => __( 'Starting Soon Window (minutes)', 'vector-elementor-widgets' ),
				'type'        => \Elementor\Controls_Manager::NUMBER,
				'default'     => 10,
				'min'         => 0,
				'max'         => 120,
				'description' => __( 'How long before the start the "starting soon" state begins. 0 disables it.', 'vector-elementor-widgets' ),
			)
		);

		$widget->add_control(
			'visitor_time',
			array(
				'label'        => __( 'Show Visitor Local Time', 'vector-elementor-widgets' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'description'  => __( 'Adds the start time converted into each viewer\'s own timezone.', 'vector-elementor-widgets' ),
			)
		);

		$widget->add_control(
			'calendar_links',
			array(
				'label'        => __( 'Add to Calendar', 'vector-elementor-widgets' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'description'  => __( 'Offers an .ics download and a Google Calendar link for the next segment.', 'vector-elementor-widgets' ),
			)
		);

		$widget->add_control(
			'live_label',
			array(
				'label'       => __( 'Live Label', 'vector-elementor-widgets' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'default'     => __( 'Live now', 'vector-elementor-widgets' ),
				'label_block' => true,
			)
		);

		$widget->add_control(
			'upcoming_label',
			array(
				'label'       => __( 'Countdown Label', 'vector-elementor-widgets' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'default'     => __( 'Begins in', 'vector-elementor-widgets' ),
				'label_block' => true,
			)
		);

		$widget->add_control(
			'starting_soon_label',
			array(
				'label'       => __( 'Starting Soon Label', 'vector-elementor-widgets' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'default'     => __( 'Starting soon', 'vector-elementor-widgets' ),
				'label_block' => true,
			)
		);

		$widget->add_control(
			'watch_label',
			array(
				'label'       => __( 'Watch Button Label', 'vector-elementor-widgets' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'default'     => __( 'Watch now', 'vector-elementor-widgets' ),
				'label_block' => true,
			)
		);

		$widget->add_control(
			'secondary_label',
			array(
				'label'       => __( 'Second Stream Label', 'vector-elementor-widgets' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'default'     => __( 'Also on Facebook', 'vector-elementor-widgets' ),
				'description' => __( 'Shown for the optional second destination when live.', 'vector-elementor-widgets' ),
				'label_block' => true,
			)
		);

		$widget->add_control(
			'calendar_label',
			array(
				'label'       => __( 'Calendar Link Label', 'vector-elementor-widgets' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'default'     => __( 'Add to calendar', 'vector-elementor-widgets' ),
				'label_block' => true,
			)
		);

		$widget->add_control(
			'empty_text',
			array(
				'label'       => __( 'No Schedule Text', 'vector-elementor-widgets' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'default'     => __( 'Our next gathering time will be posted soon.', 'vector-elementor-widgets' ),
				'description' => __( 'Shown when no segments are configured, so the block never renders empty.', 'vector-elementor-widgets' ),
				'label_block' => true,
			)
		);

		$widget->add_control(
			'preview_offset',
			array(
				'label'       => __( 'Preview State (editors only)', 'vector-elementor-widgets' ),
				'type'        => \Elementor\Controls_Manager::SELECT,
				'default'     => 'off',
				'options'     => array(
					'off'      => __( '— Off (real time) —', 'vector-elementor-widgets' ),
					'-1 day'   => __( 'Countdown — 1 day before', 'vector-elementor-widgets' ),
					'-1 hour'  => __( 'Countdown — 1 hour before', 'vector-elementor-widgets' ),
					'-5 min'   => __( 'Starting soon — 5 min before', 'vector-elementor-widgets' ),
					'+5 min'   => __( 'LIVE — 5 min after start', 'vector-elementor-widgets' ),
					'+2 hours' => __( 'ENDED — rolls to the next segment', 'vector-elementor-widgets' ),
				),
				'description' => __( 'Moves the clock relative to the NEXT service start, so you can verify each state without waiting. Only editors and admins see this.', 'vector-elementor-widgets' ),
			)
		);

		$widget->end_controls_section();
	}
}
