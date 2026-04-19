<?php
/**
 * Take Action Theme functions.
 *
 * Minimal — most functionality lives in the plugin.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'after_setup_theme', 'tat_theme_setup' );

function tat_theme_setup() {
	add_theme_support( 'wp-block-styles' );
	add_editor_style( 'style.css' );
}

add_action( 'wp_enqueue_scripts', 'tat_theme_enqueue' );

function tat_theme_enqueue() {
	wp_enqueue_style(
		'take-action-theme-style',
		get_stylesheet_uri(),
		array(),
		wp_get_theme()->get( 'Version' )
	);
}

add_action( 'after_switch_theme', 'tat_theme_check_plugin' );

function tat_theme_check_plugin() {
	if ( ! is_plugin_active( 'take-action-toolkit/take-action-toolkit.php' ) ) {
		add_action( 'admin_notices', function () {
			?>
			<div class="notice notice-warning">
				<p>
					<strong><?php esc_html_e( 'Take Action Theme', 'take-action-theme' ); ?>:</strong>
					<?php esc_html_e( 'This theme works best with the Take Action Toolkit plugin. Please install and activate it for the full experience.', 'take-action-theme' ); ?>
				</p>
			</div>
			<?php
		} );
	}
}
