<?php
/**
 * Server-side render for the Business Directory block.
 *
 * @var array    $attributes Block attributes.
 * @var string   $content    Block content.
 * @var WP_Block $block      Block instance.
 */

$columns = $attributes['columns'] ?? 3;

$businesses = get_posts( array(
	'post_type'      => 'business',
	'posts_per_page' => -1,
	'orderby'        => 'title',
	'order'          => 'ASC',
	'post_status'    => 'publish',
) );

if ( empty( $businesses ) ) {
	if ( current_user_can( 'edit_posts' ) ) {
		printf(
			'<div class="tat-notice"><p>%s <a href="%s">%s</a></p></div>',
			esc_html__( 'No businesses added yet.', 'take-action-toolkit' ),
			esc_url( admin_url( 'post-new.php?post_type=business' ) ),
			esc_html__( 'Add your first business →', 'take-action-toolkit' )
		);
	}
	return;
}
?>

<div <?php echo get_block_wrapper_attributes( array( 'class' => 'tat-business-directory' ) ); ?>>
	<div class="tat-business-grid" style="--tat-columns: <?php echo absint( $columns ); ?>;">
		<?php foreach ( $businesses as $biz ) :
			$website = get_post_meta( $biz->ID, 'tat_website', true );
			$address = get_post_meta( $biz->ID, 'tat_address', true );
			$phone   = get_post_meta( $biz->ID, 'tat_phone', true );
			$map_url = get_post_meta( $biz->ID, 'tat_map_url', true );
			$email   = get_post_meta( $biz->ID, 'tat_email', true );

			if ( empty( $map_url ) && ! empty( $address ) ) {
				$map_url = 'https://www.google.com/maps/search/?api=1&query=' . rawurlencode( $address );
			}
		?>
			<article class="tat-business-card">
				<?php if ( has_post_thumbnail( $biz->ID ) ) : ?>
					<div class="tat-business-logo">
						<?php echo get_the_post_thumbnail( $biz->ID, 'medium', array( 'loading' => 'lazy' ) ); ?>
					</div>
				<?php endif; ?>

				<div class="tat-business-info">
					<h3 class="tat-business-name">
						<?php if ( ! empty( $website ) ) : ?>
							<a href="<?php echo esc_url( $website ); ?>" target="_blank" rel="noopener">
								<?php echo esc_html( $biz->post_title ); ?>
							</a>
						<?php else : ?>
							<?php echo esc_html( $biz->post_title ); ?>
						<?php endif; ?>
					</h3>

					<?php if ( ! empty( $biz->post_content ) ) : ?>
						<div class="tat-business-description">
							<?php echo wp_kses_post( wpautop( $biz->post_content ) ); ?>
						</div>
					<?php endif; ?>

					<?php if ( ! empty( $address ) ) : ?>
						<p class="tat-business-address">
							<?php if ( ! empty( $map_url ) ) : ?>
								<a href="<?php echo esc_url( $map_url ); ?>" target="_blank" rel="noopener">
									<?php echo esc_html( $address ); ?>
								</a>
							<?php else : ?>
								<?php echo esc_html( $address ); ?>
							<?php endif; ?>
						</p>
					<?php endif; ?>

					<div class="tat-business-contact">
						<?php if ( ! empty( $phone ) ) : ?>
							<a href="tel:<?php echo esc_attr( $phone ); ?>"><?php echo esc_html( $phone ); ?></a>
						<?php endif; ?>
						<?php if ( ! empty( $email ) ) : ?>
							<a href="mailto:<?php echo esc_attr( $email ); ?>">
								<span class="dashicons dashicons-email"></span>
							</a>
						<?php endif; ?>
					</div>
				</div>
			</article>
		<?php endforeach; ?>
	</div>
</div>
