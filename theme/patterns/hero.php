<?php
/**
 * Title: Hero Section
 * Slug: take-action-theme/hero
 * Categories: featured
 * Description: The main hero banner with site title and tagline.
 */

$settings = function_exists( 'tat_get_setting' )
	? get_option( 'tat_settings', array() )
	: array();

$location = $settings['location_name'] ?? '';
$tagline  = $settings['tagline'] ?? '';

if ( empty( $tagline ) && ! empty( $location ) ) {
	$tagline = sprintf(
		/* translators: %s: location name */
		__( "Your central hub for %s's pro-democracy activism", 'take-action-theme' ),
		$location
	);
}

if ( empty( $tagline ) ) {
	$tagline = __( 'Your central hub for pro-democracy activism', 'take-action-theme' );
}

$site_title = ! empty( $location )
	? sprintf( __( 'Take Action %s', 'take-action-theme' ), $location )
	: get_bloginfo( 'name' );
?>

<!-- wp:group {"style":{"spacing":{"padding":{"top":"64px","bottom":"64px","left":"24px","right":"24px"}}},"backgroundColor":"secondary","textColor":"base","layout":{"type":"constrained"}} -->
<div class="wp-block-group has-base-color has-secondary-background-color has-text-color has-background" style="padding-top:64px;padding-right:24px;padding-bottom:64px;padding-left:24px">

	<!-- wp:heading {"textAlign":"center","level":1,"fontSize":"xx-large","textColor":"base"} -->
	<h1 class="wp-block-heading has-text-align-center has-base-color has-text-color has-xx-large-font-size"><?php echo esc_html( $site_title ); ?></h1>
	<!-- /wp:heading -->

	<!-- wp:paragraph {"align":"center","fontSize":"large","textColor":"base"} -->
	<p class="has-text-align-center has-base-color has-text-color has-large-font-size"><?php echo esc_html( $tagline ); ?></p>
	<!-- /wp:paragraph -->

</div>
<!-- /wp:group -->
