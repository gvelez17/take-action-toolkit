<?php
/**
 * Server-side render for the Event List block.
 *
 * @var array    $attributes Block attributes.
 * @var string   $content    Block content.
 * @var WP_Block $block      Block instance.
 */

$events = tat_get_calendar_events();
if ( is_wp_error( $events ) || empty( $events ) ) {
	$settings = get_option( 'tat_settings', tat_get_default_settings() );
	$has_calendar = ! empty( $settings['calendar_id'] ) || ! empty( $settings['ics_url'] );

	if ( ! $has_calendar ) {
		if ( current_user_can( 'manage_options' ) ) {
			printf(
				'<div class="tat-notice"><p>%s <a href="%s">%s</a></p></div>',
				esc_html__( 'No calendar connected yet.', 'take-action-toolkit' ),
				esc_url( admin_url( 'admin.php?page=take-action-settings' ) ),
				esc_html__( 'Connect your calendar →', 'take-action-toolkit' )
			);
		} else {
			printf(
				'<div class="tat-notice"><p>%s</p></div>',
				esc_html__( 'Events coming soon! Check back for upcoming actions.', 'take-action-toolkit' )
			);
		}
		return;
	}

	if ( is_wp_error( $events ) ) {
		printf(
			'<div class="tat-notice"><p>%s</p></div>',
			esc_html__( 'Unable to load events right now. Please check back later.', 'take-action-toolkit' )
		);
		return;
	}

	return;
}

$limit          = $attributes['limit'] ?? 20;
$show_filter    = $attributes['showFilter'] ?? true;
$show_subscribe = $attributes['showSubscribe'] ?? true;
$show_map       = $attributes['showMap'] ?? true;

$events         = array_slice( $events, 0, $limit );
$subscribe_urls = tat_get_calendar_subscribe_urls();

$event_types = array();
foreach ( $events as $event ) {
	$event_types[ $event['type'] ] = true;
}

$context = array(
	'filter'     => 'all',
	'events'     => $events,
	'showMap'    => $show_map,
);
?>

<div
	<?php echo get_block_wrapper_attributes( array( 'class' => 'tat-event-list' ) ); ?>
	data-wp-interactive="take-action/events"
	<?php echo wp_interactivity_data_wp_context( $context ); ?>
>
	<?php if ( $show_filter && count( $event_types ) > 1 ) : ?>
		<div class="tat-event-filters">
			<button
				class="tat-filter-btn"
				data-wp-on--click="actions.setFilter"
				data-wp-class--active="context.filter === 'all'"
				data-filter="all"
			><?php esc_html_e( 'All', 'take-action-toolkit' ); ?></button>

			<?php if ( isset( $event_types['in-person'] ) ) : ?>
				<button
					class="tat-filter-btn"
					data-wp-on--click="actions.setFilter"
					data-wp-class--active="context.filter === 'in-person'"
					data-filter="in-person"
				><?php esc_html_e( 'In-Person', 'take-action-toolkit' ); ?></button>
			<?php endif; ?>

			<?php if ( isset( $event_types['virtual'] ) ) : ?>
				<button
					class="tat-filter-btn"
					data-wp-on--click="actions.setFilter"
					data-wp-class--active="context.filter === 'virtual'"
					data-filter="virtual"
				><?php esc_html_e( 'Virtual', 'take-action-toolkit' ); ?></button>
			<?php endif; ?>

			<?php if ( isset( $event_types['hybrid'] ) ) : ?>
				<button
					class="tat-filter-btn"
					data-wp-on--click="actions.setFilter"
					data-wp-class--active="context.filter === 'hybrid'"
					data-filter="hybrid"
				><?php esc_html_e( 'Hybrid', 'take-action-toolkit' ); ?></button>
			<?php endif; ?>
		</div>
	<?php endif; ?>

	<?php if ( $show_subscribe && ! empty( $subscribe_urls ) ) : ?>
		<div class="tat-subscribe-links">
			<span class="tat-subscribe-label"><?php esc_html_e( 'Subscribe:', 'take-action-toolkit' ); ?></span>
			<?php if ( ! empty( $subscribe_urls['google'] ) ) : ?>
				<a href="<?php echo esc_url( $subscribe_urls['google'] ); ?>" target="_blank" rel="noopener" class="tat-subscribe-link">
					<?php esc_html_e( 'Google Calendar', 'take-action-toolkit' ); ?>
				</a>
			<?php endif; ?>
			<?php if ( ! empty( $subscribe_urls['ical'] ) ) : ?>
				<a href="<?php echo esc_url( $subscribe_urls['ical'] ); ?>" class="tat-subscribe-link">
					<?php esc_html_e( 'Apple / Outlook', 'take-action-toolkit' ); ?>
				</a>
			<?php endif; ?>
		</div>
	<?php endif; ?>

	<div class="tat-events">
		<?php
		$current_date = '';
		foreach ( $events as $index => $event ) :
			$start_ts = strtotime( $event['start'] );
			$date_key = wp_date( 'Y-m-d', $start_ts );
			$new_date = $date_key !== $current_date;
			if ( $new_date ) {
				$current_date = $date_key;
			}

			$event_context = array(
				'type'  => $event['type'],
				'index' => $index,
			);
		?>
			<?php if ( $new_date ) : ?>
				<h3 class="tat-date-heading"
					data-wp-context='<?php echo wp_json_encode( $event_context ); ?>'
					data-wp-bind--hidden="state.isHidden"
				>
					<?php echo esc_html( wp_date( 'l, F j', $start_ts ) ); ?>
				</h3>
			<?php endif; ?>

			<article
				class="tat-event-card"
				data-wp-context='<?php echo wp_json_encode( $event_context ); ?>'
				data-wp-bind--hidden="state.isHidden"
			>
				<div class="tat-event-time">
					<?php if ( $event['is_all_day'] ) : ?>
						<span class="tat-all-day"><?php esc_html_e( 'All Day', 'take-action-toolkit' ); ?></span>
					<?php else : ?>
						<time datetime="<?php echo esc_attr( $event['start'] ); ?>">
							<?php echo esc_html( wp_date( 'g:i A', $start_ts ) ); ?>
						</time>
						<?php if ( ! empty( $event['end'] ) ) : ?>
							<span class="tat-time-sep">&ndash;</span>
							<time datetime="<?php echo esc_attr( $event['end'] ); ?>">
								<?php echo esc_html( wp_date( 'g:i A', strtotime( $event['end'] ) ) ); ?>
							</time>
						<?php endif; ?>
					<?php endif; ?>
				</div>

				<div class="tat-event-details">
					<h4 class="tat-event-title">
						<?php if ( ! empty( $event['url'] ) ) : ?>
							<a href="<?php echo esc_url( $event['url'] ); ?>" target="_blank" rel="noopener">
								<?php echo esc_html( $event['title'] ); ?>
							</a>
						<?php else : ?>
							<?php echo esc_html( $event['title'] ); ?>
						<?php endif; ?>
						<span class="tat-event-type-badge tat-type-<?php echo esc_attr( $event['type'] ); ?>">
							<?php echo esc_html( ucfirst( $event['type'] ) ); ?>
						</span>
					</h4>

					<?php if ( ! empty( $event['location'] ) ) : ?>
						<div class="tat-event-location">
							<span class="tat-location-text"><?php echo esc_html( $event['location'] ); ?></span>
							<?php if ( $show_map && ! empty( $event['map_google'] ) ) : ?>
								<span class="tat-map-links">
									<a href="<?php echo esc_url( $event['map_google'] ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'Google', 'take-action-toolkit' ); ?></a>
									<?php if ( ! empty( $event['map_apple'] ) ) : ?>
										<a href="<?php echo esc_url( $event['map_apple'] ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'Apple', 'take-action-toolkit' ); ?></a>
									<?php endif; ?>
									<?php if ( ! empty( $event['map_openstreetmap'] ) ) : ?>
										<a href="<?php echo esc_url( $event['map_openstreetmap'] ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'OSM', 'take-action-toolkit' ); ?></a>
									<?php endif; ?>
								</span>
							<?php endif; ?>
						</div>
					<?php endif; ?>

					<?php if ( ! empty( $event['organizer'] ) ) : ?>
						<div class="tat-event-organizer">
							<?php echo esc_html( $event['organizer'] ); ?>
						</div>
					<?php endif; ?>

					<?php if ( ! empty( $event['description'] ) ) : ?>
						<div class="tat-event-description">
							<?php echo wp_kses_post( wpautop( $event['description'] ) ); ?>
						</div>
					<?php endif; ?>
				</div>
			</article>
		<?php endforeach; ?>
	</div>
</div>
