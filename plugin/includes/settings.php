<?php
/**
 * Plugin settings for Take Action Toolkit.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'admin_menu', 'tat_add_settings_page' );

function tat_add_settings_page() {
	add_menu_page(
		__( 'Take Action Settings', 'take-action-toolkit' ),
		__( 'Take Action', 'take-action-toolkit' ),
		'manage_options',
		'take-action-settings',
		'tat_render_settings_page',
		'dashicons-megaphone',
		3
	);
}

add_action( 'admin_init', 'tat_register_settings' );

function tat_register_settings() {
	register_setting( 'tat_settings_group', 'tat_settings', array(
		'type'              => 'object',
		'sanitize_callback' => 'tat_sanitize_settings',
		'default'           => tat_get_default_settings(),
	) );
}

function tat_get_default_settings() {
	return array(
		'location_name'    => '',
		'location_type'    => 'city',
		'tagline'          => '',
		'calendar_source'  => 'google_api',
		'calendar_id'      => '',
		'google_api_key'   => '',
		'ics_url'          => '',
		'volunteer_url'    => '',
		'donate_url'       => '',
		'newsletter_url'   => '',
		'newsletter_embed' => '',
		'contact_email'    => '',
		'social_instagram' => '',
		'social_facebook'  => '',
		'social_bluesky'   => '',
		'social_tiktok'    => '',
		'social_twitter'   => '',
		'social_youtube'   => '',
		'social_threads'   => '',
		'primary_color'    => '#dc2626',
		'secondary_color'  => '#1e3a5f',
	);
}

function tat_sanitize_settings( $input ) {
	$defaults  = tat_get_default_settings();
	$sanitized = array();

	foreach ( $defaults as $key => $default ) {
		if ( ! isset( $input[ $key ] ) ) {
			$sanitized[ $key ] = $default;
			continue;
		}

		$url_fields = array(
			'volunteer_url', 'donate_url', 'newsletter_url', 'ics_url',
			'social_instagram', 'social_facebook', 'social_bluesky',
			'social_tiktok', 'social_twitter', 'social_youtube', 'social_threads',
		);

		if ( in_array( $key, $url_fields, true ) ) {
			$sanitized[ $key ] = esc_url_raw( $input[ $key ] );
		} elseif ( 'newsletter_embed' === $key ) {
			$sanitized[ $key ] = wp_kses(
				$input[ $key ],
				array(
					'iframe' => array(
						'src'             => true,
						'width'           => true,
						'height'          => true,
						'frameborder'     => true,
						'allowfullscreen' => true,
						'title'           => true,
					),
					'script' => array(
						'src'   => true,
						'async' => true,
						'defer' => true,
					),
					'div'   => array(
						'class' => true,
						'id'    => true,
						'style' => true,
					),
					'form'  => array(
						'action' => true,
						'method' => true,
						'class'  => true,
						'id'     => true,
					),
					'input' => array(
						'type'        => true,
						'name'        => true,
						'value'       => true,
						'placeholder' => true,
						'class'       => true,
						'required'    => true,
					),
					'button' => array(
						'type'  => true,
						'class' => true,
					),
					'label' => array(
						'for'   => true,
						'class' => true,
					),
				)
			);
		} elseif ( 'contact_email' === $key ) {
			$sanitized[ $key ] = sanitize_email( $input[ $key ] );
		} elseif ( in_array( $key, array( 'primary_color', 'secondary_color' ), true ) ) {
			$sanitized[ $key ] = sanitize_hex_color( $input[ $key ] ) ?: $default;
		} else {
			$sanitized[ $key ] = sanitize_text_field( $input[ $key ] );
		}
	}

	delete_transient( 'tat_calendar_events' );

	return $sanitized;
}

function tat_get_setting( $key ) {
	$settings = get_option( 'tat_settings', tat_get_default_settings() );
	$defaults = tat_get_default_settings();
	return $settings[ $key ] ?? $defaults[ $key ] ?? '';
}

function tat_render_settings_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$settings = get_option( 'tat_settings', tat_get_default_settings() );
	?>
	<div class="wrap">
		<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

		<form method="post" action="options.php">
			<?php settings_fields( 'tat_settings_group' ); ?>

			<h2 class="title"><?php esc_html_e( 'Location', 'take-action-toolkit' ); ?></h2>
			<table class="form-table">
				<tr>
					<th><label for="tat_location_name"><?php esc_html_e( 'Location Name', 'take-action-toolkit' ); ?></label></th>
					<td>
						<input type="text" id="tat_location_name" name="tat_settings[location_name]"
							value="<?php echo esc_attr( $settings['location_name'] ); ?>" class="regular-text"
							placeholder="<?php esc_attr_e( 'e.g., Tucson, Pennsylvania, Western Mass', 'take-action-toolkit' ); ?>">
					</td>
				</tr>
				<tr>
					<th><label for="tat_location_type"><?php esc_html_e( 'Location Type', 'take-action-toolkit' ); ?></label></th>
					<td>
						<select id="tat_location_type" name="tat_settings[location_type]">
							<?php
							$types = array(
								'city'         => __( 'City', 'take-action-toolkit' ),
								'state'        => __( 'State', 'take-action-toolkit' ),
								'county'       => __( 'County', 'take-action-toolkit' ),
								'region'       => __( 'Region', 'take-action-toolkit' ),
								'neighborhood' => __( 'Neighborhood', 'take-action-toolkit' ),
								'district'     => __( 'District', 'take-action-toolkit' ),
								'other'        => __( 'Other', 'take-action-toolkit' ),
							);
							foreach ( $types as $value => $label ) {
								printf(
									'<option value="%s" %s>%s</option>',
									esc_attr( $value ),
									selected( $settings['location_type'], $value, false ),
									esc_html( $label )
								);
							}
							?>
						</select>
					</td>
				</tr>
				<tr>
					<th><label for="tat_tagline"><?php esc_html_e( 'Tagline', 'take-action-toolkit' ); ?></label></th>
					<td>
						<input type="text" id="tat_tagline" name="tat_settings[tagline]"
							value="<?php echo esc_attr( $settings['tagline'] ); ?>" class="large-text"
							placeholder="<?php
								echo esc_attr( sprintf(
									/* translators: %s: location name */
									__( "Your central hub for %s's pro-democracy activism", 'take-action-toolkit' ),
									$settings['location_name'] ?: __( 'your community', 'take-action-toolkit' )
								) );
							?>">
					</td>
				</tr>
			</table>

			<h2 class="title"><?php esc_html_e( 'Calendar', 'take-action-toolkit' ); ?></h2>
			<table class="form-table">
				<tr>
					<th><label for="tat_calendar_source"><?php esc_html_e( 'Calendar Source', 'take-action-toolkit' ); ?></label></th>
					<td>
						<select id="tat_calendar_source" name="tat_settings[calendar_source]">
							<option value="google_api" <?php selected( $settings['calendar_source'], 'google_api' ); ?>>
								<?php esc_html_e( 'Google Calendar (API)', 'take-action-toolkit' ); ?>
							</option>
							<option value="ics" <?php selected( $settings['calendar_source'], 'ics' ); ?>>
								<?php esc_html_e( 'iCal/ICS Feed', 'take-action-toolkit' ); ?>
							</option>
						</select>
					</td>
				</tr>
				<tr class="tat-google-api-field">
					<th><label for="tat_calendar_id"><?php esc_html_e( 'Google Calendar ID', 'take-action-toolkit' ); ?></label></th>
					<td>
						<input type="text" id="tat_calendar_id" name="tat_settings[calendar_id]"
							value="<?php echo esc_attr( $settings['calendar_id'] ); ?>" class="large-text"
							placeholder="your-calendar-id@group.calendar.google.com">
						<p class="description">
							<?php esc_html_e( 'Found in Google Calendar → Settings → Integrate calendar.', 'take-action-toolkit' ); ?>
						</p>
					</td>
				</tr>
				<tr class="tat-google-api-field">
					<th><label for="tat_google_api_key"><?php esc_html_e( 'Google API Key', 'take-action-toolkit' ); ?></label></th>
					<td>
						<input type="text" id="tat_google_api_key" name="tat_settings[google_api_key]"
							value="<?php echo esc_attr( $settings['google_api_key'] ); ?>" class="large-text">
						<p class="description">
							<?php
							printf(
								/* translators: %s: wp-config.php */
								esc_html__( 'For security, you can also define TAT_GOOGLE_API_KEY in %s instead.', 'take-action-toolkit' ),
								'<code>wp-config.php</code>'
							);
							?>
						</p>
					</td>
				</tr>
				<tr class="tat-ics-field">
					<th><label for="tat_ics_url"><?php esc_html_e( 'ICS Feed URL', 'take-action-toolkit' ); ?></label></th>
					<td>
						<input type="url" id="tat_ics_url" name="tat_settings[ics_url]"
							value="<?php echo esc_attr( $settings['ics_url'] ); ?>" class="large-text"
							placeholder="https://calendar.google.com/calendar/ical/.../basic.ics">
					</td>
				</tr>
			</table>

			<h2 class="title"><?php esc_html_e( 'Action Links', 'take-action-toolkit' ); ?></h2>
			<table class="form-table">
				<tr>
					<th><label for="tat_volunteer_url"><?php esc_html_e( 'Volunteer Signup URL', 'take-action-toolkit' ); ?></label></th>
					<td><input type="url" id="tat_volunteer_url" name="tat_settings[volunteer_url]"
						value="<?php echo esc_attr( $settings['volunteer_url'] ); ?>" class="large-text"></td>
				</tr>
				<tr>
					<th><label for="tat_donate_url"><?php esc_html_e( 'Donation URL', 'take-action-toolkit' ); ?></label></th>
					<td><input type="url" id="tat_donate_url" name="tat_settings[donate_url]"
						value="<?php echo esc_attr( $settings['donate_url'] ); ?>" class="large-text"></td>
				</tr>
				<tr>
					<th><label for="tat_newsletter_url"><?php esc_html_e( 'Newsletter Signup URL', 'take-action-toolkit' ); ?></label></th>
					<td><input type="url" id="tat_newsletter_url" name="tat_settings[newsletter_url]"
						value="<?php echo esc_attr( $settings['newsletter_url'] ); ?>" class="large-text"></td>
				</tr>
				<tr>
					<th><label for="tat_newsletter_embed"><?php esc_html_e( 'Newsletter Embed Code', 'take-action-toolkit' ); ?></label></th>
					<td>
						<textarea id="tat_newsletter_embed" name="tat_settings[newsletter_embed]"
							class="large-text" rows="4"><?php echo esc_textarea( $settings['newsletter_embed'] ); ?></textarea>
						<p class="description">
							<?php esc_html_e( 'Paste embed code from Mailchimp, HubSpot, etc. Used instead of the URL if provided.', 'take-action-toolkit' ); ?>
						</p>
					</td>
				</tr>
				<tr>
					<th><label for="tat_contact_email"><?php esc_html_e( 'Contact Email', 'take-action-toolkit' ); ?></label></th>
					<td><input type="email" id="tat_contact_email" name="tat_settings[contact_email]"
						value="<?php echo esc_attr( $settings['contact_email'] ); ?>" class="regular-text"></td>
				</tr>
			</table>

			<h2 class="title"><?php esc_html_e( 'Social Media', 'take-action-toolkit' ); ?></h2>
			<table class="form-table">
				<?php
				$social_fields = array(
					'social_instagram' => __( 'Instagram URL', 'take-action-toolkit' ),
					'social_facebook'  => __( 'Facebook URL', 'take-action-toolkit' ),
					'social_bluesky'   => __( 'Bluesky URL', 'take-action-toolkit' ),
					'social_tiktok'    => __( 'TikTok URL', 'take-action-toolkit' ),
					'social_twitter'   => __( 'X / Twitter URL', 'take-action-toolkit' ),
					'social_youtube'   => __( 'YouTube URL', 'take-action-toolkit' ),
					'social_threads'   => __( 'Threads URL', 'take-action-toolkit' ),
				);
				foreach ( $social_fields as $key => $label ) {
					printf(
						'<tr><th><label for="tat_%1$s">%2$s</label></th><td><input type="url" id="tat_%1$s" name="tat_settings[%1$s]" value="%3$s" class="large-text"></td></tr>',
						esc_attr( $key ),
						esc_html( $label ),
						esc_attr( $settings[ $key ] )
					);
				}
				?>
			</table>

			<h2 class="title"><?php esc_html_e( 'Branding', 'take-action-toolkit' ); ?></h2>
			<table class="form-table">
				<tr>
					<th><label for="tat_primary_color"><?php esc_html_e( 'Primary Color', 'take-action-toolkit' ); ?></label></th>
					<td><input type="color" id="tat_primary_color" name="tat_settings[primary_color]"
						value="<?php echo esc_attr( $settings['primary_color'] ); ?>"></td>
				</tr>
				<tr>
					<th><label for="tat_secondary_color"><?php esc_html_e( 'Secondary Color', 'take-action-toolkit' ); ?></label></th>
					<td><input type="color" id="tat_secondary_color" name="tat_settings[secondary_color]"
						value="<?php echo esc_attr( $settings['secondary_color'] ); ?>"></td>
				</tr>
			</table>

			<?php submit_button(); ?>
		</form>

		<hr>
		<h2><?php esc_html_e( 'Calendar Connection Test', 'take-action-toolkit' ); ?></h2>
		<p>
			<button type="button" class="button" id="tat-test-calendar">
				<?php esc_html_e( 'Test Calendar Connection', 'take-action-toolkit' ); ?>
			</button>
			<span id="tat-test-result"></span>
		</p>
	</div>

	<script>
	document.addEventListener('DOMContentLoaded', function() {
		const sourceSelect = document.getElementById('tat_calendar_source');
		function toggleCalendarFields() {
			const source = sourceSelect.value;
			document.querySelectorAll('.tat-google-api-field').forEach(function(el) {
				el.style.display = source === 'google_api' ? '' : 'none';
			});
			document.querySelectorAll('.tat-ics-field').forEach(function(el) {
				el.style.display = source === 'ics' ? '' : 'none';
			});
		}
		sourceSelect.addEventListener('change', toggleCalendarFields);
		toggleCalendarFields();

		document.getElementById('tat-test-calendar').addEventListener('click', function() {
			const result = document.getElementById('tat-test-result');
			result.textContent = '<?php echo esc_js( __( 'Testing...', 'take-action-toolkit' ) ); ?>';
			fetch(ajaxurl + '?action=tat_test_calendar&_wpnonce=<?php echo esc_js( wp_create_nonce( 'tat_test_calendar' ) ); ?>')
				.then(function(r) { return r.json(); })
				.then(function(data) {
					if (data.success) {
						result.textContent = data.data.message;
						result.style.color = 'green';
					} else {
						result.textContent = data.data.message || '<?php echo esc_js( __( 'Connection failed.', 'take-action-toolkit' ) ); ?>';
						result.style.color = 'red';
					}
				});
		});
	});
	</script>
	<?php
}

add_action( 'wp_ajax_tat_test_calendar', 'tat_ajax_test_calendar' );

function tat_ajax_test_calendar() {
	check_ajax_referer( 'tat_test_calendar' );

	if ( ! current_user_can( 'manage_options' ) ) {
		wp_send_json_error( array( 'message' => __( 'Permission denied.', 'take-action-toolkit' ) ) );
	}

	delete_transient( 'tat_calendar_events' );
	$events = tat_fetch_calendar_events();

	if ( is_wp_error( $events ) ) {
		wp_send_json_error( array( 'message' => $events->get_error_message() ) );
	}

	$count = count( $events );
	wp_send_json_success( array(
		'message' => sprintf(
			/* translators: %d: number of events found */
			_n( 'Connected! Found %d upcoming event.', 'Connected! Found %d upcoming events.', $count, 'take-action-toolkit' ),
			$count
		),
		'count'   => $count,
	) );
}
