/**
 * Vector Elementor Widgets — Broadcast.
 *
 * Vanilla JS, no dependencies. Scope all selectors to .vew-broadcast.
 *
 * Responsibilities:
 *  1. Re-resolve the active segment client-side from `data-segments`, so a page
 *     served from the full-page cache still rolls forward correctly.
 *  2. Tick the countdown to the segment start and swap state at zero —
 *     without a reload.
 *  3. Append each viewer's local time for the start.
 *  4. Announce only at meaningful thresholds.
 *
 * All instants in the payload are ISO-8601 with an explicit offset, so they
 * need no timezone guessing here.
 */
( function () {
	'use strict';

	var THRESHOLDS = [ 86400, 3600, 600 ]; // 1 day, 1 hour, 10 minutes (seconds)

	/**
	 * Parse the JSON payload off an element attribute.
	 *
	 * @param {Element} root Widget root.
	 * @param {string}  attr Attribute name.
	 * @return {Array} Parsed value, or an empty array.
	 */
	function readJSON( root, attr ) {
		var raw = root.getAttribute( attr );
		if ( ! raw ) {
			return [];
		}
		try {
			var parsed = JSON.parse( raw );
			return Array.isArray( parsed ) ? parsed : [];
		} catch ( e ) {
			return [];
		}
	}

	/**
	 * Parse the labels attribute.
	 *
	 * @param {Element} root Widget root.
	 * @return {Object} Label map.
	 */
	function readLabels( root ) {
		var raw = root.getAttribute( 'data-labels' );
		if ( ! raw ) {
			return {};
		}
		try {
			return JSON.parse( raw ) || {};
		} catch ( e ) {
			return {};
		}
	}

	/**
	 * Milliseconds until an instant, floored at zero.
	 *
	 * @param {string} iso ISO-8601 instant.
	 * @return {number} Milliseconds remaining.
	 */
	function msUntil( iso ) {
		var target = Date.parse( iso );
		if ( isNaN( target ) ) {
			return 0;
		}
		return Math.max( 0, target - Date.now() );
	}

	/**
	 * Humanise a millisecond duration, at most two units.
	 *
	 * @param {number} ms Milliseconds.
	 * @return {string} e.g. "2d 04h", "11m 09s".
	 */
	function humanise( ms ) {
		var secs = Math.floor( ms / 1000 );
		var days = Math.floor( secs / 86400 );
		var hours = Math.floor( ( secs % 86400 ) / 3600 );
		var mins = Math.floor( ( secs % 3600 ) / 60 );
		var rem = secs % 60;

		function pad( n ) {
			return String( n ).padStart( 2, '0' );
		}

		if ( days > 0 ) {
			return days + 'd ' + pad( hours ) + 'h';
		}
		if ( hours > 0 ) {
			return hours + 'h ' + pad( mins ) + 'm';
		}
		return mins + 'm ' + pad( rem ) + 's';
	}

	/**
	 * Boot the embedded live player.
	 *
	 * hls.js is injected lazily (only when an HLS live player is present) so
	 * the ~600KB lib never loads on pages that just render the schedule. The
	 * lib URL is on the widget root (data-hls-lib). Native playback is used
	 * where the browser supports it; otherwise the lib attaches to the video.
	 *
	 * @param {Element} video   The <video data-broadcast-player>.
	 * @param {string}  hlsUrl  The .m3u8 endpoint.
	 * @param {string}  libSrc  Self-hosted hls.js URL ('' when absent).
	 * @return {void}
	 */
	function initHlsPlayer( video, hlsUrl, libSrc ) {
		var canPlayNative = video.canPlayType( 'application/vnd.apple.mpegurl' ) !== '';

		if ( canPlayNative ) {
			video.src = hlsUrl;
			video.play();
			return;
		}

		if ( typeof window.VewHls !== 'undefined' ) {
			attachHls( video, hlsUrl );
			return;
		}

		if ( ! libSrc ) {
			return; // no lib available and no native support — leave the fallback link
		}

		var script = document.createElement( 'script' );
		script.src = libSrc;
		script.onload = function () {
			attachHls( video, hlsUrl );
		};
		document.head.appendChild( script );
	}

	/**
	 * Attach an hls.js instance to the video and start playback.
	 *
	 * @param {Element} video  The <video> element.
	 * @param {string}  url    The .m3u8 endpoint.
	 * @return {void}
	 */
	function attachHls( video, url ) {
		if ( typeof window.Hls === 'undefined' || ! window.Hls.isSupported() ) {
			video.controls = true;
			return;
		}
		var hls = new window.Hls();
		hls.loadSource( url );
		hls.attachMedia( video );
		hls.on( window.Hls.Events.ERROR, function ( event, data ) {
			if ( data && data.fatal ) {
				hls.destroy();
				video.controls = true; // surface native controls on fatal error
			}
		} );
		window.VewHls = hls;
	}

	/**
	 * Pick the active segment from the payload at a given moment.
	 *
	 * Live first, then starting-soon, then the soonest upcoming. This mirrors
	 * the server's precedence so a cached page and a fresh page agree.
	 *
	 * @param {Array}  segments      Payload rows.
	 * @param {number} soonWindowMs  Starting-soon window.
	 * @param {number} now           Current epoch ms.
	 * @return {Object|null} The active row.
	 */
	function pickActive( segments, soonWindowMs, now ) {
		var live = null;
		var soon = null;
		var upcoming = null;

		segments.forEach( function ( row ) {
			var start = Date.parse( row.starts_at );
			var end = Date.parse( row.ends_at );
			if ( isNaN( start ) ) {
				return;
			}

			if ( now >= start && now < end ) {
				if ( ! live || start > Date.parse( live.starts_at ) ) {
					live = row;
				}
				return;
			}
			if ( soonWindowMs > 0 && now >= start - soonWindowMs && now < start ) {
				if ( ! soon || start < Date.parse( soon.starts_at ) ) {
					soon = row;
				}
				return;
			}
			if ( now < start ) {
				if ( ! upcoming || start < Date.parse( upcoming.starts_at ) ) {
					upcoming = row;
				}
			}
		} );

		return live || soon || upcoming;
	}

	/**
	 * State key for a row at a moment.
	 *
	 * @param {Object} row          Payload row.
	 * @param {number} soonWindowMs Starting-soon window.
	 * @param {number} now          Current epoch ms.
	 * @return {string} State key.
	 */
	function stateFor( row, soonWindowMs, now ) {
		var start = Date.parse( row.starts_at );
		var end = Date.parse( row.ends_at );

		if ( now >= start && now < end ) {
			return 'live';
		}
		if ( soonWindowMs > 0 && now >= start - soonWindowMs && now < start ) {
			return 'starting_soon';
		}
		return 'upcoming';
	}

	/**
	 * Render the visitor's local equivalent of the start time.
	 *
	 * Complements the site-time line rather than replacing it: the widget
	 * documents times in site time, so a remote viewer gets both.
	 *
	 * @param {Element} node     Target node.
	 * @param {string}  iso      ISO-8601 start.
	 * @param {string}  siteZone IANA zone of the site.
	 * @return {void}
	 */
	function renderLocalTime( node, iso, siteZone ) {
		if ( ! node ) {
			return;
		}
		var date = new Date( iso );
		if ( isNaN( date.getTime() ) ) {
			return;
		}

		var visitorZone = '';
		try {
			visitorZone = Intl.DateTimeFormat().resolvedOptions().timeZone || '';
		} catch ( e ) {
			visitorZone = '';
		}

		// Nothing to add when the viewer is already in the site's zone.
		if ( visitorZone && siteZone && visitorZone === siteZone ) {
			node.hidden = true;
			return;
		}

		var formatted = '';
		try {
			formatted = new Intl.DateTimeFormat( undefined, {
				weekday: 'short',
				hour: 'numeric',
				minute: '2-digit',
				timeZoneName: 'short'
			} ).format( date );
		} catch ( e ) {
			return;
		}

		// Skip when the local rendering already matches the site clock (e.g. an
		// unnamed zone at the same offset), so we never print a duplicate.
		if ( siteZone ) {
			try {
				var siteClock = new Intl.DateTimeFormat( 'en-US', {
					weekday: 'short',
					hour: 'numeric',
					minute: '2-digit',
					timeZone: siteZone
				} ).format( date );
				var localClock = new Intl.DateTimeFormat( 'en-US', {
					weekday: 'short',
					hour: 'numeric',
					minute: '2-digit'
				} ).format( date );
				if ( siteClock === localClock ) {
					node.hidden = true;
					return;
				}
			} catch ( e ) {
				// Fall through and show the line.
			}
		}

		var prefix = node.getAttribute( 'data-local-prefix' );
		if ( ! prefix ) {
			prefix = 'Your time:';
			node.setAttribute( 'data-local-prefix', prefix );
		}

		node.textContent = prefix + ' ' + formatted;
		node.hidden = false;
	}

	document.querySelectorAll( '.vew-broadcast[data-vew-broadcast]' ).forEach( function ( root ) {
		var segments = readJSON( root, 'data-segments' );
		var labels = readLabels( root );
		var soonWindowMs = ( parseInt( root.getAttribute( 'data-starting-soon-minutes' ), 10 ) || 0 ) * 60000;
		var wantVisitorTime = root.getAttribute( 'data-visitor-time' ) === '1';
		var useVisitorTime = wantVisitorTime;
		var siteZone = root.getAttribute( 'data-site-timezone' ) || '';
		// In editor preview the server renders a deliberately shifted state. The
		// client must NOT recompute from the real clock, or previewing a state
		// would be impossible.
		var previewActive = root.getAttribute( 'data-preview' ) === '1';

		var badge = root.querySelector( '[data-broadcast-badge]' );
		var title = root.querySelector( '[data-broadcast-label]' );
		var when = root.querySelector( '[data-broadcast-when]' );
		var clock = root.querySelector( '[data-broadcast-clock]' );
		var clockLabel = root.querySelector( '[data-broadcast-clock-label]' );
		var clockValue = root.querySelector( '[data-broadcast-clock-value]' );
		var watch = root.querySelector( '[data-broadcast-watch]' );
		var secondary = root.querySelector( '[data-broadcast-secondary]' );
		var thumb = root.querySelector( '[data-broadcast-thumb]' );
		var announce = root.querySelector( '[data-broadcast-announce]' );
		var player = root.querySelector( '[data-broadcast-player]' );
		var localNode = root.querySelector( '[data-broadcast-local]' );

		if ( ! segments.length || ! clockValue ) {
			return;
		}

		var active = null;
		var announcedFor = '';
		var timer = null;

		/**
		 * Apply a payload row to the DOM.
		 *
		 * @param {Object} row   Payload row.
		 * @param {string} state State key.
		 * @return {void}
		 */
		function applyRow( row, state ) {
			root.setAttribute( 'data-state', state );

			if ( title && row.label ) {
				title.textContent = row.label;
			}

			if ( badge ) {
				badge.className = 'vew-broadcast__badge vew-broadcast__badge--' + state;
				badge.textContent = labels[ state === 'starting_soon' ? 'starting_soon' : state ] || labels.upcoming || '';
			}

			if ( when ) {
				var startDate = new Date( row.starts_at );
				if ( ! isNaN( startDate.getTime() ) ) {
					// Format in the SITE timezone, not the visitor's. The widget
					// states the times are in site time, so rendering them in the
					// visitor's zone here would contradict that note (and the
					// countdown, which targets the site-time instant).
					var whenOpts = {
						weekday: 'long',
						month: 'long',
						day: 'numeric',
						hour: 'numeric',
						minute: '2-digit'
					};
					if ( siteZone ) {
						whenOpts.timeZone = siteZone;
					}
					try {
						when.textContent = new Intl.DateTimeFormat( undefined, whenOpts ).format( startDate );
					} catch ( e ) {
						when.textContent = startDate.toString();
					}
				}
			}

			if ( thumb && row.thumbnail ) {
				thumb.src = row.thumbnail;
				thumb.alt = row.thumbnailAlt || '';
			}

			if ( watch ) {
				if ( row.stream ) {
					watch.href = row.stream;
				}
				watch.hidden = state !== 'live';
			}

			if ( secondary ) {
				if ( row.secondary ) {
					secondary.href = row.secondary;
					secondary.hidden = state !== 'live';
				} else {
					secondary.hidden = true;
				}
			}

			if ( useVisitorTime && localNode ) {
				renderLocalTime( localNode, row.starts_at, siteZone );
			}
		}

		/**
		 * Recompute the active row and repaint.
		 *
		 * @return {void}
		 */
		function refresh() {
			var now = Date.now();
			var next = pickActive( segments, soonWindowMs, now );

			if ( next && next !== active ) {
				active = next;
				applyRow( active, stateFor( active, soonWindowMs, now ) );
			} else if ( active ) {
				root.setAttribute( 'data-state', stateFor( active, soonWindowMs, now ) );
			}

			if ( ! active ) {
				return;
			}

			var state = stateFor( active, soonWindowMs, Date.now() );

			if ( state === 'live' ) {
				if ( clockLabel ) {
					clockLabel.textContent = '';
				}
				clockValue.textContent = '';
			} else if ( state === 'starting_soon' ) {
				if ( clockLabel ) {
					clockLabel.textContent = labels.starting_soon || '';
				}
				clockValue.textContent = '';
			} else {
				if ( clockLabel ) {
					clockLabel.textContent = labels.upcoming || '';
				}
				var remaining = msUntil( active.starts_at );
				clockValue.textContent = humanise( remaining );

				// Announce only at meaningful thresholds, never per second.
				var secs = Math.floor( remaining / 1000 );
				var hit = null;
				for ( var i = 0; i < THRESHOLDS.length; i++ ) {
					if ( secs <= THRESHOLDS[ i ] && secs > THRESHOLDS[ i ] - 2 ) {
						hit = THRESHOLDS[ i ];
						break;
					}
				}
				if ( announce && hit !== null ) {
					var key = active.starts_at + ':' + hit;
					if ( announcedFor !== key ) {
						announcedFor = key;
						announce.textContent = humanise( remaining ) + ' until ' + ( active.label || 'the broadcast' );
					}
				}
			}

			// Announce the live transition once.
			if ( state === 'live' && announce && announcedFor !== 'live:' + active.starts_at ) {
				announcedFor = 'live:' + active.starts_at;
				announce.textContent = ( active.label || 'The broadcast' ) + ' ' + ( labels.live || 'is live now' );
			}
		}

		if ( previewActive ) {
			// Leave the server-rendered state exactly as-is and do not tick.
			// Still fill the countdown once so a preview does not show a
			// placeholder dash where a reviewer expects a duration.
			active = pickActive( segments, soonWindowMs, Date.now() );
			if ( active && clockValue ) {
				var previewState = root.getAttribute( 'data-state' );
				if ( 'upcoming' === previewState ) {
					clockValue.textContent = humanise( msUntil( active.starts_at ) );
				}
			}
			return;
		}

		active = pickActive( segments, soonWindowMs, Date.now() );
		if ( active ) {
			applyRow( active, stateFor( active, soonWindowMs, Date.now() ) );
		}
		refresh();

		// Bootstrap the embedded HLS live player if one is present. The
		// <video> carries data-hls-url when it is an HLS endpoint; hls.js is
		// lazily injected (only when a live embedded player exists) so the
		// ~600KB lib is not downloaded on pages that just show the schedule.
		if ( player ) {
			var hlsUrl = player.getAttribute( 'data-hls-url' );
			var libSrc = root.getAttribute( 'data-hls-lib' );
			if ( hlsUrl ) {
				initHlsPlayer( player, hlsUrl, libSrc );
			} else {
				// Direct .mp4/.webm — native <video> handles it once it's live
				// and unmuted by user gesture.
				player.controls = true;
			}
		}

		timer = window.setInterval( refresh, 1000 );

		// Stop ticking when the tab is hidden; resume on return. A background
		// tab does not need a per-second timer, and the first refresh on return
		// corrects any drift.
		document.addEventListener( 'visibilitychange', function () {
			if ( document.hidden ) {
				window.clearInterval( timer );
				timer = null;
			} else if ( ! timer ) {
				refresh();
				timer = window.setInterval( refresh, 1000 );
			}
		} );
	} );
} )();
