<?php
/**
 * Server-side render for the Action Hub block.
 *
 * @var array    $attributes Block attributes.
 * @var string   $content    Block content.
 * @var WP_Block $block      Block instance.
 */

$settings       = get_option( 'tat_settings', tat_get_default_settings() );
$show_volunteer = $attributes['showVolunteer'] ?? true;
$show_donate    = $attributes['showDonate'] ?? true;
$show_newsletter = $attributes['showNewsletter'] ?? true;

$volunteer_url    = $settings['volunteer_url'] ?? '';
$donate_url       = $settings['donate_url'] ?? '';
$newsletter_url   = $settings['newsletter_url'] ?? '';
$newsletter_embed = $settings['newsletter_embed'] ?? '';
$contact_email    = $settings['contact_email'] ?? '';

$has_content = ( $show_volunteer && ! empty( $volunteer_url ) )
	|| ( $show_donate && ! empty( $donate_url ) )
	|| ( $show_newsletter && ( ! empty( $newsletter_url ) || ! empty( $newsletter_embed ) ) )
	|| ! empty( $contact_email );

if ( ! $has_content ) {
	if ( current_user_can( 'manage_options' ) ) {
		printf(
			'<div class="tat-notice"><p>%s <a href="%s">%s</a></p></div>',
			esc_html__( 'No action links configured yet.', 'take-action-toolkit' ),
			esc_url( admin_url( 'admin.php?page=take-action-settings' ) ),
			esc_html__( 'Add your action links →', 'take-action-toolkit' )
		);
	}
	return;
}
?>

<div <?php echo get_block_wrapper_attributes( array( 'class' => 'tat-action-hub' ) ); ?>>
	<div class="tat-action-buttons">
		<?php if ( $show_volunteer && ! empty( $volunteer_url ) ) : ?>
			<a href="<?php echo esc_url( $volunteer_url ); ?>" class="tat-action-btn tat-btn-volunteer" target="_blank" rel="noopener">
				<span class="dashicons dashicons-heart"></span>
				<?php esc_html_e( 'Volunteer', 'take-action-toolkit' ); ?>
			</a>
		<?php endif; ?>

		<?php if ( $show_donate && ! empty( $donate_url ) ) : ?>
			<a href="<?php echo esc_url( $donate_url ); ?>" class="tat-action-btn tat-btn-donate" target="_blank" rel="noopener">
				<span class="dashicons dashicons-money-alt"></span>
				<?php esc_html_e( 'Donate', 'take-action-toolkit' ); ?>
			</a>
		<?php endif; ?>

		<?php if ( ! empty( $contact_email ) ) : ?>
			<a href="mailto:<?php echo esc_attr( $contact_email ); ?>" class="tat-action-btn tat-btn-contact">
				<span class="dashicons dashicons-email"></span>
				<?php esc_html_e( 'Contact Us', 'take-action-toolkit' ); ?>
			</a>
		<?php endif; ?>
	</div>

	<?php if ( $show_newsletter ) : ?>
		<?php if ( ! empty( $newsletter_embed ) ) : ?>
			<div class="tat-newsletter-embed">
				<?php echo $newsletter_embed; // Already sanitized by tat_sanitize_settings. ?>
			</div>
		<?php elseif ( ! empty( $newsletter_url ) ) : ?>
			<div class="tat-newsletter-link">
				<a href="<?php echo esc_url( $newsletter_url ); ?>" class="tat-action-btn tat-btn-newsletter" target="_blank" rel="noopener">
					<span class="dashicons dashicons-email-alt"></span>
					<?php esc_html_e( 'Sign Up for Updates', 'take-action-toolkit' ); ?>
				</a>
			</div>
		<?php endif; ?>
	<?php endif; ?>
</div>
