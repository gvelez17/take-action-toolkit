<?php
/**
 * Server-side render for the Organization Directory block.
 *
 * @var array    $attributes Block attributes.
 * @var string   $content    Block content.
 * @var WP_Block $block      Block instance.
 */

$show_filter = $attributes['showFilter'] ?? true;
$columns     = $attributes['columns'] ?? 2;

$orgs = get_posts( array(
	'post_type'      => 'organization',
	'posts_per_page' => -1,
	'orderby'        => 'title',
	'order'          => 'ASC',
	'post_status'    => 'publish',
) );

if ( empty( $orgs ) ) {
	if ( current_user_can( 'edit_posts' ) ) {
		printf(
			'<div class="tat-notice"><p>%s <a href="%s">%s</a></p></div>',
			esc_html__( 'No organizations added yet.', 'take-action-toolkit' ),
			esc_url( admin_url( 'post-new.php?post_type=organization' ) ),
			esc_html__( 'Add your first organization →', 'take-action-toolkit' )
		);
	} else {
		printf(
			'<div class="tat-notice"><p>%s</p></div>',
			esc_html__( 'Organizations coming soon!', 'take-action-toolkit' )
		);
	}
	return;
}

$categories = get_terms( array(
	'taxonomy'   => 'org_category',
	'hide_empty' => true,
) );

$context = array(
	'filter' => 'all',
);
?>

<div
	<?php echo get_block_wrapper_attributes( array( 'class' => 'tat-org-directory' ) ); ?>
	data-wp-interactive="take-action/orgs"
	<?php echo wp_interactivity_data_wp_context( $context ); ?>
>
	<?php if ( $show_filter && ! empty( $categories ) && ! is_wp_error( $categories ) ) : ?>
		<div class="tat-org-filters">
			<button
				class="tat-filter-btn"
				data-wp-on--click="actions.setFilter"
				data-wp-class--active="context.filter === 'all'"
				data-filter="all"
			><?php esc_html_e( 'All', 'take-action-toolkit' ); ?></button>

			<?php foreach ( $categories as $cat ) : ?>
				<button
					class="tat-filter-btn"
					data-wp-on--click="actions.setFilter"
					data-wp-class--active="<?php echo esc_attr( "context.filter === '" . $cat->slug . "'" ); ?>"
					data-filter="<?php echo esc_attr( $cat->slug ); ?>"
				><?php echo esc_html( $cat->name ); ?></button>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>

	<div class="tat-org-grid" style="--tat-columns: <?php echo absint( $columns ); ?>;">
		<?php foreach ( $orgs as $org ) :
			$org_cats   = wp_get_object_terms( $org->ID, 'org_category', array( 'fields' => 'slugs' ) );
			$cat_string = implode( ',', $org_cats );
			$website    = get_post_meta( $org->ID, 'tat_website', true );
			$email      = get_post_meta( $org->ID, 'tat_email', true );
			$phone      = get_post_meta( $org->ID, 'tat_phone', true );

			$social_keys = array( 'tat_instagram', 'tat_facebook', 'tat_bluesky', 'tat_twitter', 'tat_youtube', 'tat_tiktok' );
			$socials     = array();
			foreach ( $social_keys as $sk ) {
				$val = get_post_meta( $org->ID, $sk, true );
				if ( ! empty( $val ) ) {
					$socials[ str_replace( 'tat_', '', $sk ) ] = $val;
				}
			}

			$org_context = array( 'categories' => $cat_string );
		?>
			<article
				class="tat-org-card"
				data-wp-context='<?php echo wp_json_encode( $org_context ); ?>'
				data-wp-bind--hidden="state.isHidden"
			>
				<?php if ( has_post_thumbnail( $org->ID ) ) : ?>
					<div class="tat-org-logo">
						<?php echo get_the_post_thumbnail( $org->ID, 'medium', array( 'loading' => 'lazy' ) ); ?>
					</div>
				<?php endif; ?>

				<div class="tat-org-info">
					<h3 class="tat-org-name">
						<?php if ( ! empty( $website ) ) : ?>
							<a href="<?php echo esc_url( $website ); ?>" target="_blank" rel="noopener">
								<?php echo esc_html( $org->post_title ); ?>
							</a>
						<?php else : ?>
							<?php echo esc_html( $org->post_title ); ?>
						<?php endif; ?>
					</h3>

					<?php if ( ! empty( $org->post_content ) ) : ?>
						<div class="tat-org-description">
							<?php echo wp_kses_post( wpautop( $org->post_content ) ); ?>
						</div>
					<?php elseif ( ! empty( $org->post_excerpt ) ) : ?>
						<p class="tat-org-description"><?php echo esc_html( $org->post_excerpt ); ?></p>
					<?php endif; ?>

					<div class="tat-org-contact">
						<?php if ( ! empty( $email ) ) : ?>
							<a href="mailto:<?php echo esc_attr( $email ); ?>" class="tat-contact-link" title="<?php esc_attr_e( 'Email', 'take-action-toolkit' ); ?>">
								<span class="dashicons dashicons-email"></span>
							</a>
						<?php endif; ?>
						<?php if ( ! empty( $website ) ) : ?>
							<a href="<?php echo esc_url( $website ); ?>" target="_blank" rel="noopener" class="tat-contact-link" title="<?php esc_attr_e( 'Website', 'take-action-toolkit' ); ?>">
								<span class="dashicons dashicons-admin-site-alt3"></span>
							</a>
						<?php endif; ?>
						<?php if ( ! empty( $phone ) ) : ?>
							<a href="tel:<?php echo esc_attr( $phone ); ?>" class="tat-contact-link" title="<?php esc_attr_e( 'Phone', 'take-action-toolkit' ); ?>">
								<span class="dashicons dashicons-phone"></span>
							</a>
						<?php endif; ?>
						<?php foreach ( $socials as $platform => $url ) : ?>
							<a href="<?php echo esc_url( $url ); ?>" target="_blank" rel="noopener" class="tat-contact-link tat-social-<?php echo esc_attr( $platform ); ?>" title="<?php echo esc_attr( ucfirst( $platform ) ); ?>">
								<span class="dashicons dashicons-share"></span>
							</a>
						<?php endforeach; ?>
					</div>

					<?php if ( ! empty( $org_cats ) ) : ?>
						<div class="tat-org-tags">
							<?php
							$cat_names = wp_get_object_terms( $org->ID, 'org_category', array( 'fields' => 'names' ) );
							foreach ( $cat_names as $cat_name ) :
							?>
								<span class="tat-tag"><?php echo esc_html( $cat_name ); ?></span>
							<?php endforeach; ?>
						</div>
					<?php endif; ?>
				</div>
			</article>
		<?php endforeach; ?>
	</div>
</div>
