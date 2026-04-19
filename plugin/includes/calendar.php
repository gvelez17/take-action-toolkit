<?php
/**
 * Google Calendar integration for Take Action Toolkit.
 *
 * Fetches events from a public Google Calendar and caches them in a transient.
 * Supports both Google Calendar API (with API key) and iCal/ICS feeds.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'tat_refresh_calendar', 'tat_fetch_calendar_events' );

function tat_schedule_calendar_refresh() {
	if ( ! wp_next_scheduled( 'tat_refresh_calendar' ) ) {
		wp_schedule_event( time(), 'tat_fifteen_minutes', 'tat_refresh_calendar' );
	}
}

add_filter( 'cron_schedules', 'tat_add_cron_interval' );

function tat_add_cron_interval( $schedules ) {
	$schedules['tat_fifteen_minutes'] = array(
		'interval' => 900,
		'display'  => __( 'Every 15 minutes', 'take-action-toolkit' ),
	);
	return $schedules;
}

add_action( 'init', 'tat_schedule_calendar_refresh' );

function tat_get_calendar_events() {
	$cached = get_transient( 'tat_calendar_events' );
	if ( false !== $cached ) {
		return $cached;
	}

	return tat_fetch_calendar_events();
}

function tat_fetch_calendar_events() {
	$settings = get_option( 'tat_settings', array() );
	$source   = $settings['calendar_source'] ?? 'google_api';

	if ( 'ics' === $source ) {
		$events = tat_fetch_ics_events( $settings );
	} else {
		$events = tat_fetch_google_api_events( $settings );
	}

	if ( is_wp_error( $events ) ) {
		$stale = get_transient( 'tat_calendar_events' );
		if ( false !== $stale ) {
			return $stale;
		}
		return $events;
	}

	set_transient( 'tat_calendar_events', $events, 20 * MINUTE_IN_SECONDS );
	return $events;
}

function tat_fetch_google_api_events( $settings ) {
	$calendar_id = $settings['calendar_id'] ?? '';
	$api_key     = defined( 'TAT_GOOGLE_API_KEY' ) ? TAT_GOOGLE_API_KEY : ( $settings['google_api_key'] ?? '' );

	if ( empty( $calendar_id ) || empty( $api_key ) ) {
		return new WP_Error( 'tat_calendar_not_configured', __( 'Calendar is not configured.', 'take-action-toolkit' ) );
	}

	$url = sprintf(
		'https://www.googleapis.com/calendar/v3/calendars/%s/events?key=%s&timeMin=%s&maxResults=100&singleEvents=true&orderBy=startTime',
		rawurlencode( $calendar_id ),
		rawurlencode( $api_key ),
		rawurlencode( gmdate( 'c' ) )
	);

	$response = wp_remote_get( $url, array( 'timeout' => 15 ) );

	if ( is_wp_error( $response ) ) {
		return $response;
	}

	$code = wp_remote_retrieve_response_code( $response );
	if ( 200 !== $code ) {
		return new WP_Error( 'tat_calendar_api_error', sprintf(
			/* translators: %d: HTTP status code */
			__( 'Google Calendar API returned status %d.', 'take-action-toolkit' ),
			$code
		) );
	}

	$body = json_decode( wp_remote_retrieve_body( $response ), true );
	if ( empty( $body['items'] ) ) {
		return array();
	}

	return array_map( 'tat_normalize_google_event', $body['items'] );
}

function tat_normalize_google_event( $item ) {
	$start = $item['start']['dateTime'] ?? $item['start']['date'] ?? '';
	$end   = $item['end']['dateTime'] ?? $item['end']['date'] ?? '';

	$is_all_day = isset( $item['start']['date'] ) && ! isset( $item['start']['dateTime'] );

	$event = array(
		'id'          => $item['id'] ?? '',
		'title'       => $item['summary'] ?? __( '(No title)', 'take-action-toolkit' ),
		'description' => $item['description'] ?? '',
		'location'    => $item['location'] ?? '',
		'start'       => $start,
		'end'         => $end,
		'is_all_day'  => $is_all_day,
		'url'         => $item['htmlLink'] ?? '',
		'organizer'   => $item['organizer']['displayName'] ?? '',
		'status'      => $item['status'] ?? 'confirmed',
	);

	if ( ! empty( $item['location'] ) ) {
		$encoded_location          = rawurlencode( $item['location'] );
		$event['map_google']       = 'https://www.google.com/maps/search/?api=1&query=' . $encoded_location;
		$event['map_apple']        = 'https://maps.apple.com/?q=' . $encoded_location;
		$event['map_openstreetmap'] = 'https://www.openstreetmap.org/search?query=' . $encoded_location;
	}

	$event['type'] = tat_detect_event_type( $item );

	return $event;
}

function tat_detect_event_type( $item ) {
	$location    = strtolower( $item['location'] ?? '' );
	$description = strtolower( $item['description'] ?? '' );
	$summary     = strtolower( $item['summary'] ?? '' );

	$virtual_indicators = array( 'zoom', 'meet.google', 'teams.microsoft', 'webex', 'virtual', 'online', 'http' );
	foreach ( $virtual_indicators as $indicator ) {
		if ( str_contains( $location, $indicator ) || str_contains( $description, $indicator ) ) {
			if ( ! empty( $item['location'] ) && ! str_starts_with( trim( $item['location'] ), 'http' ) ) {
				return 'hybrid';
			}
			return 'virtual';
		}
	}

	return 'in-person';
}

function tat_fetch_ics_events( $settings ) {
	$ics_url = $settings['ics_url'] ?? '';

	if ( empty( $ics_url ) ) {
		return new WP_Error( 'tat_ics_not_configured', __( 'ICS feed URL is not configured.', 'take-action-toolkit' ) );
	}

	$response = wp_remote_get( $ics_url, array( 'timeout' => 15 ) );

	if ( is_wp_error( $response ) ) {
		return $response;
	}

	$body = wp_remote_retrieve_body( $response );
	return tat_parse_ics( $body );
}

function tat_parse_ics( $ics_content ) {
	$events = array();
	$now    = time();

	preg_match_all( '/BEGIN:VEVENT(.+?)END:VEVENT/s', $ics_content, $matches );

	foreach ( $matches[1] as $event_data ) {
		$event = array(
			'id'          => '',
			'title'       => '',
			'description' => '',
			'location'    => '',
			'start'       => '',
			'end'         => '',
			'is_all_day'  => false,
			'url'         => '',
			'organizer'   => '',
			'status'      => 'confirmed',
			'type'        => 'in-person',
		);

		if ( preg_match( '/UID:(.+)/i', $event_data, $m ) ) {
			$event['id'] = trim( $m[1] );
		}
		if ( preg_match( '/SUMMARY:(.+)/i', $event_data, $m ) ) {
			$event['title'] = trim( tat_unescape_ics_text( $m[1] ) );
		}
		if ( preg_match( '/DESCRIPTION:(.+)/i', $event_data, $m ) ) {
			$event['description'] = trim( tat_unescape_ics_text( $m[1] ) );
		}
		if ( preg_match( '/LOCATION:(.+)/i', $event_data, $m ) ) {
			$event['location'] = trim( tat_unescape_ics_text( $m[1] ) );
		}
		if ( preg_match( '/URL:(.+)/i', $event_data, $m ) ) {
			$event['url'] = trim( $m[1] );
		}

		if ( preg_match( '/DTSTART(?:;VALUE=DATE)?[^:]*:(\d{4})(\d{2})(\d{2})(?:T(\d{2})(\d{2})(\d{2}))?/i', $event_data, $m ) ) {
			if ( empty( $m[4] ) ) {
				$event['start']      = $m[1] . '-' . $m[2] . '-' . $m[3];
				$event['is_all_day'] = true;
			} else {
				$event['start'] = $m[1] . '-' . $m[2] . '-' . $m[3] . 'T' . $m[4] . ':' . $m[5] . ':' . $m[6];
			}
		}

		if ( preg_match( '/DTEND(?:;VALUE=DATE)?[^:]*:(\d{4})(\d{2})(\d{2})(?:T(\d{2})(\d{2})(\d{2}))?/i', $event_data, $m ) ) {
			if ( empty( $m[4] ) ) {
				$event['end'] = $m[1] . '-' . $m[2] . '-' . $m[3];
			} else {
				$event['end'] = $m[1] . '-' . $m[2] . '-' . $m[3] . 'T' . $m[4] . ':' . $m[5] . ':' . $m[6];
			}
		}

		$event_time = strtotime( $event['start'] );
		if ( false !== $event_time && $event_time < $now ) {
			continue;
		}

		if ( ! empty( $event['location'] ) ) {
			$encoded                    = rawurlencode( $event['location'] );
			$event['map_google']        = 'https://www.google.com/maps/search/?api=1&query=' . $encoded;
			$event['map_apple']         = 'https://maps.apple.com/?q=' . $encoded;
			$event['map_openstreetmap'] = 'https://www.openstreetmap.org/search?query=' . $encoded;
		}

		$events[] = $event;
	}

	usort( $events, function ( $a, $b ) {
		return strcmp( $a['start'], $b['start'] );
	} );

	return $events;
}

function tat_unescape_ics_text( $text ) {
	$text = str_replace( array( '\\n', '\\N' ), "\n", $text );
	$text = str_replace( array( '\\,', '\\;', '\\\\' ), array( ',', ';', '\\' ), $text );
	return $text;
}

function tat_get_calendar_subscribe_urls() {
	$settings    = get_option( 'tat_settings', array() );
	$calendar_id = $settings['calendar_id'] ?? '';
	$ics_url     = $settings['ics_url'] ?? '';

	$urls = array();

	if ( ! empty( $calendar_id ) ) {
		$urls['google']  = 'https://calendar.google.com/calendar/render?cid=' . rawurlencode( $calendar_id );
		$urls['ical']    = 'https://calendar.google.com/calendar/ical/' . rawurlencode( $calendar_id ) . '/public/basic.ics';
		$urls['outlook'] = $urls['ical'];
	} elseif ( ! empty( $ics_url ) ) {
		$urls['ical']    = $ics_url;
		$urls['outlook'] = $ics_url;
	}

	return $urls;
}
