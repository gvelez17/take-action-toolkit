<?php
/**
 * First-run setup wizard for Take Action Toolkit.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'admin_notices', 'tat_setup_wizard_notice' );

function tat_setup_wizard_notice() {
	if ( ! get_option( 'tat_needs_setup' ) ) {
		return;
	}

	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	if ( isset( $_GET['page'] ) && 'take-action-setup' === $_GET['page'] ) {
		return;
	}

	?>
	<div class="notice notice-info is-dismissible">
		<p>
			<strong><?php esc_html_e( 'Welcome to Take Action Toolkit!', 'take-action-toolkit' ); ?></strong>
			<?php esc_html_e( "Let's set up your activism hub.", 'take-action-toolkit' ); ?>
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=take-action-setup' ) ); ?>" class="button button-primary" style="margin-left: 10px;">
				<?php esc_html_e( 'Run Setup Wizard', 'take-action-toolkit' ); ?>
			</a>
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=take-action-settings' ) ); ?>" style="margin-left: 10px;">
				<?php esc_html_e( 'Skip — configure manually', 'take-action-toolkit' ); ?>
			</a>
		</p>
	</div>
	<?php
}

add_action( 'admin_menu', 'tat_add_setup_wizard_page' );

function tat_add_setup_wizard_page() {
	add_submenu_page(
		null,
		__( 'Take Action Setup', 'take-action-toolkit' ),
		__( 'Setup', 'take-action-toolkit' ),
		'manage_options',
		'take-action-setup',
		'tat_render_setup_wizard'
	);
}

function tat_render_setup_wizard() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$settings = get_option( 'tat_settings', tat_get_default_settings() );
	$step     = isset( $_GET['step'] ) ? absint( $_GET['step'] ) : 1;
	$steps    = array(
		1 => __( 'Your Location', 'take-action-toolkit' ),
		2 => __( 'Your Look', 'take-action-toolkit' ),
		3 => __( 'Your Calendar', 'take-action-toolkit' ),
		4 => __( 'Action Links', 'take-action-toolkit' ),
		5 => __( 'Launch', 'take-action-toolkit' ),
	);

	if ( isset( $_POST['tat_wizard_nonce'] ) && wp_verify_nonce( $_POST['tat_wizard_nonce'], 'tat_wizard' ) ) {
		$settings = tat_process_wizard_step( $step, $settings );
		update_option( 'tat_settings', tat_sanitize_settings( $settings ) );

		if ( $step >= 5 ) {
			delete_option( 'tat_needs_setup' );
			wp_safe_redirect( admin_url( 'admin.php?page=take-action-settings&setup=complete' ) );
			exit;
		}

		wp_safe_redirect( admin_url( 'admin.php?page=take-action-setup&step=' . ( $step + 1 ) ) );
		exit;
	}

	?>
	<div class="wrap" style="max-width: 700px;">
		<h1><?php esc_html_e( 'Take Action Toolkit Setup', 'take-action-toolkit' ); ?></h1>

		<div class="tat-wizard-progress" style="display: flex; gap: 4px; margin: 20px 0;">
			<?php foreach ( $steps as $num => $label ) : ?>
				<div style="flex: 1; text-align: center; padding: 8px; background: <?php echo $num <= $step ? '#2271b1' : '#ddd'; ?>; color: <?php echo $num <= $step ? '#fff' : '#666'; ?>; border-radius: 3px; font-size: 13px;">
					<?php echo esc_html( $label ); ?>
				</div>
			<?php endforeach; ?>
		</div>

		<form method="post" style="background: #fff; padding: 24px; border: 1px solid #c3c4c7; border-radius: 4px;">
			<?php wp_nonce_field( 'tat_wizard', 'tat_wizard_nonce' ); ?>

			<?php
			switch ( $step ) {
				case 1:
					tat_wizard_step_location( $settings );
					break;
				case 2:
					tat_wizard_step_branding( $settings );
					break;
				case 3:
					tat_wizard_step_calendar( $settings );
					break;
				case 4:
					tat_wizard_step_actions( $settings );
					break;
				case 5:
					tat_wizard_step_launch( $settings );
					break;
			}
			?>

			<p style="margin-top: 24px; display: flex; justify-content: space-between; align-items: center;">
				<?php if ( $step > 1 ) : ?>
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=take-action-setup&step=' . ( $step - 1 ) ) ); ?>"
						class="button"><?php esc_html_e( '← Back', 'take-action-toolkit' ); ?></a>
				<?php else : ?>
					<span></span>
				<?php endif; ?>

				<?php if ( $step < 5 ) : ?>
					<button type="submit" class="button button-primary button-hero">
						<?php esc_html_e( 'Continue →', 'take-action-toolkit' ); ?>
					</button>
				<?php else : ?>
					<button type="submit" class="button button-primary button-hero">
						<?php esc_html_e( 'Launch My Site!', 'take-action-toolkit' ); ?>
					</button>
				<?php endif; ?>
			</p>
		</form>

		<p style="text-align: center; margin-top: 16px;">
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=take-action-settings' ) ); ?>">
				<?php esc_html_e( 'Skip wizard — I\'ll configure everything manually', 'take-action-toolkit' ); ?>
			</a>
		</p>
	</div>
	<?php
}

function tat_wizard_step_location( $settings ) {
	?>
	<h2><?php esc_html_e( 'Tell us about your location', 'take-action-toolkit' ); ?></h2>
	<p><?php esc_html_e( 'Your site will be called "Take Action [Location]". This could be a city, state, county, region, or neighborhood.', 'take-action-toolkit' ); ?></p>

	<table class="form-table">
		<tr>
			<th><label for="location_name"><?php esc_html_e( 'Location Name', 'take-action-toolkit' ); ?></label></th>
			<td>
				<input type="text" id="location_name" name="location_name"
					value="<?php echo esc_attr( $settings['location_name'] ); ?>" class="regular-text" required
					placeholder="<?php esc_attr_e( 'e.g., Tucson, Pennsylvania, Western Mass', 'take-action-toolkit' ); ?>">
			</td>
		</tr>
		<tr>
			<th><label for="location_type"><?php esc_html_e( 'Location Type', 'take-action-toolkit' ); ?></label></th>
			<td>
				<select id="location_type" name="location_type">
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
			<th><label for="tagline"><?php esc_html_e( 'Tagline', 'take-action-toolkit' ); ?></label></th>
			<td>
				<input type="text" id="tagline" name="tagline"
					value="<?php echo esc_attr( $settings['tagline'] ); ?>" class="large-text"
					placeholder="<?php echo esc_attr( sprintf(
						__( "Your central hub for %s's pro-democracy activism", 'take-action-toolkit' ),
						$settings['location_name'] ?: __( 'your community', 'take-action-toolkit' )
					) ); ?>">
				<p class="description"><?php esc_html_e( 'Leave blank to use the default.', 'take-action-toolkit' ); ?></p>
			</td>
		</tr>
		<tr>
			<th><label for="contact_email"><?php esc_html_e( 'Contact Email', 'take-action-toolkit' ); ?></label></th>
			<td>
				<input type="email" id="contact_email" name="contact_email"
					value="<?php echo esc_attr( $settings['contact_email'] ); ?>" class="regular-text"
					placeholder="info@takeaction<?php echo esc_attr( strtolower( $settings['location_name'] ?: 'yourcity' ) ); ?>.org">
			</td>
		</tr>
	</table>
	<?php
}

function tat_wizard_step_branding( $settings ) {
	?>
	<h2><?php esc_html_e( 'Choose your look', 'take-action-toolkit' ); ?></h2>
	<p><?php esc_html_e( 'Pick colors that represent your movement. You can upload a logo now or add one later.', 'take-action-toolkit' ); ?></p>

	<table class="form-table">
		<tr>
			<th><label for="primary_color"><?php esc_html_e( 'Primary Color', 'take-action-toolkit' ); ?></label></th>
			<td>
				<input type="color" id="primary_color" name="primary_color"
					value="<?php echo esc_attr( $settings['primary_color'] ); ?>">
				<div style="display: flex; gap: 8px; margin-top: 8px;">
					<?php
					$presets = array(
						'#dc2626' => __( 'Bold Red', 'take-action-toolkit' ),
						'#2563eb' => __( 'Community Blue', 'take-action-toolkit' ),
						'#d97706' => __( 'Desert Gold', 'take-action-toolkit' ),
						'#16a34a' => __( 'Forest Green', 'take-action-toolkit' ),
						'#7c3aed' => __( 'Purple Power', 'take-action-toolkit' ),
						'#0891b2' => __( 'Ocean Teal', 'take-action-toolkit' ),
					);
					foreach ( $presets as $hex => $name ) {
						printf(
							'<button type="button" onclick="document.getElementById(\'primary_color\').value=\'%1$s\'" title="%2$s" style="width:36px;height:36px;background:%1$s;border:2px solid #ccc;border-radius:4px;cursor:pointer;"></button>',
							esc_attr( $hex ),
							esc_attr( $name )
						);
					}
					?>
				</div>
			</td>
		</tr>
		<tr>
			<th><label for="secondary_color"><?php esc_html_e( 'Secondary Color', 'take-action-toolkit' ); ?></label></th>
			<td>
				<input type="color" id="secondary_color" name="secondary_color"
					value="<?php echo esc_attr( $settings['secondary_color'] ); ?>">
				<p class="description"><?php esc_html_e( 'Used for headings and accents.', 'take-action-toolkit' ); ?></p>
			</td>
		</tr>
	</table>

	<p class="description" style="margin-top: 16px;">
		<?php esc_html_e( 'You can upload a logo later through the WordPress Customizer (Appearance → Customize → Site Identity).', 'take-action-toolkit' ); ?>
	</p>
	<?php
}

function tat_wizard_step_calendar( $settings ) {
	?>
	<h2><?php esc_html_e( 'Connect your calendar', 'take-action-toolkit' ); ?></h2>
	<p><?php esc_html_e( 'Your event calendar is the heart of your site. Connect a Google Calendar or any iCal feed.', 'take-action-toolkit' ); ?></p>

	<table class="form-table">
		<tr>
			<th><label for="calendar_source"><?php esc_html_e( 'Calendar Type', 'take-action-toolkit' ); ?></label></th>
			<td>
				<select id="calendar_source" name="calendar_source">
					<option value="google_api" <?php selected( $settings['calendar_source'], 'google_api' ); ?>>
						<?php esc_html_e( 'Google Calendar', 'take-action-toolkit' ); ?>
					</option>
					<option value="ics" <?php selected( $settings['calendar_source'], 'ics' ); ?>>
						<?php esc_html_e( 'iCal / ICS Feed (Outlook, Apple, etc.)', 'take-action-toolkit' ); ?>
					</option>
				</select>
			</td>
		</tr>
		<tr class="tat-google-api-field">
			<th><label for="calendar_id"><?php esc_html_e( 'Calendar ID', 'take-action-toolkit' ); ?></label></th>
			<td>
				<input type="text" id="calendar_id" name="calendar_id"
					value="<?php echo esc_attr( $settings['calendar_id'] ); ?>" class="large-text"
					placeholder="your-calendar-id@group.calendar.google.com">
			</td>
		</tr>
		<tr class="tat-google-api-field">
			<th><label for="google_api_key"><?php esc_html_e( 'Google API Key', 'take-action-toolkit' ); ?></label></th>
			<td>
				<input type="text" id="google_api_key" name="google_api_key"
					value="<?php echo esc_attr( $settings['google_api_key'] ); ?>" class="large-text">
			</td>
		</tr>
		<tr class="tat-ics-field">
			<th><label for="ics_url"><?php esc_html_e( 'ICS Feed URL', 'take-action-toolkit' ); ?></label></th>
			<td>
				<input type="url" id="ics_url" name="ics_url"
					value="<?php echo esc_attr( $settings['ics_url'] ); ?>" class="large-text">
			</td>
		</tr>
	</table>

	<div style="background: #f0f6fc; border: 1px solid #c5d9ed; border-radius: 4px; padding: 16px; margin-top: 16px;">
		<h3 style="margin-top: 0;"><?php esc_html_e( "Don't have a Google Calendar yet?", 'take-action-toolkit' ); ?></h3>
		<ol style="margin-bottom: 0;">
			<li><?php
				printf(
					esc_html__( 'Go to %s', 'take-action-toolkit' ),
					'<a href="https://calendar.google.com" target="_blank" rel="noopener">calendar.google.com</a>'
				);
			?></li>
			<li><?php esc_html_e( 'Click the + next to "Other calendars" → "Create new calendar"', 'take-action-toolkit' ); ?></li>
			<li><?php
				printf(
					esc_html__( 'Name it "Take Action %s Events"', 'take-action-toolkit' ),
					'<strong>' . esc_html( $settings['location_name'] ?: __( 'Your Location', 'take-action-toolkit' ) ) . '</strong>'
				);
			?></li>
			<li><?php esc_html_e( 'In Settings → Access permissions → check "Make available to public"', 'take-action-toolkit' ); ?></li>
			<li><?php esc_html_e( 'In Settings → Integrate calendar → copy the Calendar ID', 'take-action-toolkit' ); ?></li>
			<li><?php esc_html_e( 'Paste it above!', 'take-action-toolkit' ); ?></li>
		</ol>
	</div>

	<p class="description" style="margin-top: 12px;">
		<?php esc_html_e( 'You can skip this step and add your calendar later.', 'take-action-toolkit' ); ?>
	</p>

	<script>
	document.addEventListener('DOMContentLoaded', function() {
		const source = document.getElementById('calendar_source');
		function toggle() {
			const isGoogle = source.value === 'google_api';
			document.querySelectorAll('.tat-google-api-field').forEach(function(el) { el.style.display = isGoogle ? '' : 'none'; });
			document.querySelectorAll('.tat-ics-field').forEach(function(el) { el.style.display = isGoogle ? 'none' : ''; });
		}
		source.addEventListener('change', toggle);
		toggle();
	});
	</script>
	<?php
}

function tat_wizard_step_actions( $settings ) {
	?>
	<h2><?php esc_html_e( 'How can people take action?', 'take-action-toolkit' ); ?></h2>
	<p><?php esc_html_e( 'Add links where people can volunteer, donate, or sign up. All of these are optional — add what you have now.', 'take-action-toolkit' ); ?></p>

	<table class="form-table">
		<tr>
			<th><label for="volunteer_url"><?php esc_html_e( 'Volunteer Signup', 'take-action-toolkit' ); ?></label></th>
			<td>
				<input type="url" id="volunteer_url" name="volunteer_url"
					value="<?php echo esc_attr( $settings['volunteer_url'] ); ?>" class="large-text"
					placeholder="<?php esc_attr_e( 'Google Form, SignUpGenius, GetZelos, etc.', 'take-action-toolkit' ); ?>">
			</td>
		</tr>
		<tr>
			<th><label for="donate_url"><?php esc_html_e( 'Donation Link', 'take-action-toolkit' ); ?></label></th>
			<td>
				<input type="url" id="donate_url" name="donate_url"
					value="<?php echo esc_attr( $settings['donate_url'] ); ?>" class="large-text"
					placeholder="<?php esc_attr_e( 'Venmo, PayPal, ActBlue, GoFundMe, etc.', 'take-action-toolkit' ); ?>">
			</td>
		</tr>
		<tr>
			<th><label for="newsletter_url"><?php esc_html_e( 'Newsletter Signup', 'take-action-toolkit' ); ?></label></th>
			<td>
				<input type="url" id="newsletter_url" name="newsletter_url"
					value="<?php echo esc_attr( $settings['newsletter_url'] ); ?>" class="large-text"
					placeholder="<?php esc_attr_e( 'Mailchimp, HubSpot, Substack, etc.', 'take-action-toolkit' ); ?>">
			</td>
		</tr>
	</table>

	<h3><?php esc_html_e( 'Social Media', 'take-action-toolkit' ); ?></h3>
	<p><?php esc_html_e( 'Add links to your social accounts. These will appear in the site header and footer.', 'take-action-toolkit' ); ?></p>
	<table class="form-table">
		<?php
		$socials = array(
			'social_instagram' => 'Instagram',
			'social_facebook'  => 'Facebook',
			'social_bluesky'   => 'Bluesky',
			'social_tiktok'    => 'TikTok',
			'social_twitter'   => 'X / Twitter',
			'social_youtube'   => 'YouTube',
		);
		foreach ( $socials as $key => $label ) {
			printf(
				'<tr><th><label for="%1$s">%2$s</label></th><td><input type="url" id="%1$s" name="%1$s" value="%3$s" class="large-text"></td></tr>',
				esc_attr( $key ),
				esc_html( $label ),
				esc_attr( $settings[ $key ] )
			);
		}
		?>
	</table>
	<?php
}

function tat_wizard_step_launch( $settings ) {
	$location = $settings['location_name'] ?: __( 'Your Location', 'take-action-toolkit' );
	$has_calendar = ! empty( $settings['calendar_id'] ) || ! empty( $settings['ics_url'] );
	?>
	<h2><?php
		printf(
			esc_html__( 'Take Action %s is ready!', 'take-action-toolkit' ),
			esc_html( $location )
		);
	?></h2>

	<div style="background: #f0f6fc; padding: 16px; border-radius: 4px; margin: 16px 0;">
		<h3 style="margin-top: 0;"><?php esc_html_e( 'Setup Summary', 'take-action-toolkit' ); ?></h3>
		<ul style="list-style: none; padding: 0; margin: 0;">
			<li><?php echo ! empty( $settings['location_name'] ) ? '&#9989;' : '&#11036;'; ?>
				<?php printf( esc_html__( 'Location: %s', 'take-action-toolkit' ), '<strong>' . esc_html( $location ) . '</strong>' ); ?>
			</li>
			<li><?php echo $has_calendar ? '&#9989;' : '&#11036;'; ?>
				<?php echo $has_calendar
					? esc_html__( 'Calendar: connected', 'take-action-toolkit' )
					: esc_html__( 'Calendar: not connected yet', 'take-action-toolkit' ); ?>
			</li>
			<li><?php echo ! empty( $settings['volunteer_url'] ) ? '&#9989;' : '&#11036;'; ?>
				<?php echo ! empty( $settings['volunteer_url'] )
					? esc_html__( 'Volunteer signup: linked', 'take-action-toolkit' )
					: esc_html__( 'Volunteer signup: not set (add later)', 'take-action-toolkit' ); ?>
			</li>
			<li><?php echo ! empty( $settings['donate_url'] ) ? '&#9989;' : '&#11036;'; ?>
				<?php echo ! empty( $settings['donate_url'] )
					? esc_html__( 'Donation link: linked', 'take-action-toolkit' )
					: esc_html__( 'Donation link: not set (add later)', 'take-action-toolkit' ); ?>
			</li>
			<li><?php echo ! empty( $settings['contact_email'] ) ? '&#9989;' : '&#11036;'; ?>
				<?php echo ! empty( $settings['contact_email'] )
					? sprintf( esc_html__( 'Contact: %s', 'take-action-toolkit' ), esc_html( $settings['contact_email'] ) )
					: esc_html__( 'Contact email: not set (add later)', 'take-action-toolkit' ); ?>
			</li>
		</ul>
	</div>

	<h3><?php esc_html_e( 'After launch, here\'s what to do next:', 'take-action-toolkit' ); ?></h3>
	<ol>
		<li><?php printf(
			esc_html__( '%s to feature on your site', 'take-action-toolkit' ),
			'<a href="' . esc_url( admin_url( 'post-new.php?post_type=organization' ) ) . '">' .
			esc_html__( 'Add local organizations', 'take-action-toolkit' ) . '</a>'
		); ?></li>
		<li><?php printf(
			esc_html__( '%s that support the movement', 'take-action-toolkit' ),
			'<a href="' . esc_url( admin_url( 'post-new.php?post_type=business' ) ) . '">' .
			esc_html__( 'Add aligned businesses', 'take-action-toolkit' ) . '</a>'
		); ?></li>
		<li><?php esc_html_e( 'Share your site with local groups and activists!', 'take-action-toolkit' ); ?></li>
	</ol>
	<?php
}

function tat_process_wizard_step( $step, $settings ) {
	switch ( $step ) {
		case 1:
			$settings['location_name'] = sanitize_text_field( $_POST['location_name'] ?? '' );
			$settings['location_type'] = sanitize_text_field( $_POST['location_type'] ?? 'city' );
			$settings['tagline']       = sanitize_text_field( $_POST['tagline'] ?? '' );
			$settings['contact_email'] = sanitize_email( $_POST['contact_email'] ?? '' );
			break;

		case 2:
			$settings['primary_color']   = sanitize_hex_color( $_POST['primary_color'] ?? '' ) ?: '#dc2626';
			$settings['secondary_color'] = sanitize_hex_color( $_POST['secondary_color'] ?? '' ) ?: '#1e3a5f';
			break;

		case 3:
			$settings['calendar_source'] = sanitize_text_field( $_POST['calendar_source'] ?? 'google_api' );
			$settings['calendar_id']     = sanitize_text_field( $_POST['calendar_id'] ?? '' );
			$settings['google_api_key']  = sanitize_text_field( $_POST['google_api_key'] ?? '' );
			$settings['ics_url']         = esc_url_raw( $_POST['ics_url'] ?? '' );
			break;

		case 4:
			$settings['volunteer_url']    = esc_url_raw( $_POST['volunteer_url'] ?? '' );
			$settings['donate_url']       = esc_url_raw( $_POST['donate_url'] ?? '' );
			$settings['newsletter_url']   = esc_url_raw( $_POST['newsletter_url'] ?? '' );
			$settings['social_instagram'] = esc_url_raw( $_POST['social_instagram'] ?? '' );
			$settings['social_facebook']  = esc_url_raw( $_POST['social_facebook'] ?? '' );
			$settings['social_bluesky']   = esc_url_raw( $_POST['social_bluesky'] ?? '' );
			$settings['social_tiktok']    = esc_url_raw( $_POST['social_tiktok'] ?? '' );
			$settings['social_twitter']   = esc_url_raw( $_POST['social_twitter'] ?? '' );
			$settings['social_youtube']   = esc_url_raw( $_POST['social_youtube'] ?? '' );
			break;
	}

	return $settings;
}

add_action( 'admin_notices', 'tat_setup_complete_notice' );

function tat_setup_complete_notice() {
	if ( ! isset( $_GET['setup'] ) || 'complete' !== $_GET['setup'] ) {
		return;
	}

	$settings = get_option( 'tat_settings', tat_get_default_settings() );
	$location = $settings['location_name'] ?: __( 'your location', 'take-action-toolkit' );

	?>
	<div class="notice notice-success is-dismissible">
		<p>
			<strong><?php
				printf(
					esc_html__( 'Take Action %s is set up!', 'take-action-toolkit' ),
					esc_html( $location )
				);
			?></strong>
			<?php esc_html_e( 'Visit your site to see it live, or start adding organizations below.', 'take-action-toolkit' ); ?>
			<a href="<?php echo esc_url( home_url() ); ?>" class="button" style="margin-left: 10px;">
				<?php esc_html_e( 'View Site', 'take-action-toolkit' ); ?>
			</a>
		</p>
	</div>
	<?php
}
