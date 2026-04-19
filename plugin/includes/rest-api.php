<?php
/**
 * REST API endpoints for Take Action Toolkit.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'rest_api_init', 'tat_register_rest_routes' );

function tat_register_rest_routes() {
	register_rest_route( 'take-action/v1', '/events', array(
		'methods'             => 'GET',
		'callback'            => 'tat_rest_get_events',
		'permission_callback' => '__return_true',
		'args'                => array(
			'type' => array(
				'type'              => 'string',
				'enum'              => array( 'all', 'in-person', 'virtual', 'hybrid' ),
				'default'           => 'all',
				'sanitize_callback' => 'sanitize_text_field',
			),
			'limit' => array(
				'type'              => 'integer',
				'default'           => 50,
				'minimum'           => 1,
				'maximum'           => 100,
				'sanitize_callback' => 'absint',
			),
		),
	) );

	register_rest_route( 'take-action/v1', '/settings/public', array(
		'methods'             => 'GET',
		'callback'            => 'tat_rest_get_public_settings',
		'permission_callback' => '__return_true',
	) );

	register_rest_route( 'take-action/v1', '/calendar/test', array(
		'methods'             => 'POST',
		'callback'            => 'tat_rest_test_calendar',
		'permission_callback' => function () {
			return current_user_can( 'manage_options' );
		},
	) );

	register_rest_route( 'take-action/v1', '/import/organizations', array(
		'methods'             => 'POST',
		'callback'            => 'tat_rest_import_organizations',
		'permission_callback' => function () {
			return current_user_can( 'edit_posts' );
		},
	) );
}

function tat_rest_get_events( $request ) {
	$events = tat_get_calendar_events();

	if ( is_wp_error( $events ) ) {
		return $events;
	}

	$type = $request->get_param( 'type' );
	if ( 'all' !== $type ) {
		$events = array_values( array_filter( $events, function ( $event ) use ( $type ) {
			return $event['type'] === $type;
		} ) );
	}

	$limit  = $request->get_param( 'limit' );
	$events = array_slice( $events, 0, $limit );

	return rest_ensure_response( $events );
}

function tat_rest_get_public_settings() {
	$settings = get_option( 'tat_settings', tat_get_default_settings() );

	return rest_ensure_response( array(
		'location_name'    => $settings['location_name'],
		'location_type'    => $settings['location_type'],
		'tagline'          => $settings['tagline'],
		'contact_email'    => $settings['contact_email'],
		'volunteer_url'    => $settings['volunteer_url'],
		'donate_url'       => $settings['donate_url'],
		'newsletter_url'   => $settings['newsletter_url'],
		'primary_color'    => $settings['primary_color'],
		'secondary_color'  => $settings['secondary_color'],
		'social_instagram' => $settings['social_instagram'],
		'social_facebook'  => $settings['social_facebook'],
		'social_bluesky'   => $settings['social_bluesky'],
		'social_tiktok'    => $settings['social_tiktok'],
		'social_twitter'   => $settings['social_twitter'],
		'social_youtube'   => $settings['social_youtube'],
		'social_threads'   => $settings['social_threads'],
		'subscribe_urls'   => tat_get_calendar_subscribe_urls(),
	) );
}

function tat_rest_test_calendar() {
	delete_transient( 'tat_calendar_events' );
	$events = tat_fetch_calendar_events();

	if ( is_wp_error( $events ) ) {
		return new WP_Error( 'calendar_error', $events->get_error_message(), array( 'status' => 400 ) );
	}

	return rest_ensure_response( array(
		'success' => true,
		'count'   => count( $events ),
		'preview' => array_slice( $events, 0, 3 ),
	) );
}

function tat_rest_import_organizations( $request ) {
	$body = $request->get_json_params();
	$orgs = $body['organizations'] ?? array();

	if ( empty( $orgs ) || ! is_array( $orgs ) ) {
		return new WP_Error( 'invalid_data', __( 'No organizations provided.', 'take-action-toolkit' ), array( 'status' => 400 ) );
	}

	$imported = 0;
	$errors   = array();

	foreach ( $orgs as $org ) {
		$name = sanitize_text_field( $org['name'] ?? '' );
		if ( empty( $name ) ) {
			$errors[] = __( 'Skipped entry with no name.', 'take-action-toolkit' );
			continue;
		}

		$post_id = wp_insert_post( array(
			'post_type'    => 'organization',
			'post_title'   => $name,
			'post_content' => wp_kses_post( $org['description'] ?? '' ),
			'post_status'  => 'publish',
		) );

		if ( is_wp_error( $post_id ) ) {
			$errors[] = sprintf(
				/* translators: 1: org name, 2: error message */
				__( 'Failed to import "%1$s": %2$s', 'take-action-toolkit' ),
				$name,
				$post_id->get_error_message()
			);
			continue;
		}

		$meta_map = array(
			'website'   => 'tat_website',
			'email'     => 'tat_email',
			'phone'     => 'tat_phone',
			'instagram' => 'tat_instagram',
			'facebook'  => 'tat_facebook',
		);

		foreach ( $meta_map as $input_key => $meta_key ) {
			if ( ! empty( $org[ $input_key ] ) ) {
				update_post_meta( $post_id, $meta_key, sanitize_text_field( $org[ $input_key ] ) );
			}
		}

		if ( ! empty( $org['categories'] ) ) {
			$cats = is_array( $org['categories'] )
				? $org['categories']
				: array_map( 'trim', explode( ',', $org['categories'] ) );
			wp_set_object_terms( $post_id, $cats, 'org_category' );
		}

		$imported++;
	}

	return rest_ensure_response( array(
		'imported' => $imported,
		'errors'   => $errors,
	) );
}
