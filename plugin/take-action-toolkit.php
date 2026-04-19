<?php
/**
 * Plugin Name: Take Action Toolkit
 * Description: A cloneable activism hub with event calendar, organization directory, and action links.
 * Version: 1.0.0
 * Requires at least: 6.5
 * Requires PHP: 8.0
 * Author: LinkedTrust / What's Cookin'
 * License: GPL-2.0-or-later
 * Text Domain: take-action-toolkit
 * Domain Path: /languages
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'TAT_VERSION', '1.0.0' );
define( 'TAT_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'TAT_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

require_once TAT_PLUGIN_DIR . 'includes/post-types.php';
require_once TAT_PLUGIN_DIR . 'includes/calendar.php';
require_once TAT_PLUGIN_DIR . 'includes/settings.php';
require_once TAT_PLUGIN_DIR . 'includes/setup-wizard.php';
require_once TAT_PLUGIN_DIR . 'includes/rest-api.php';

add_action( 'init', 'tat_register_blocks' );

function tat_register_blocks() {
	$blocks = array(
		'event-list',
		'org-directory',
		'business-directory',
		'action-hub',
	);

	foreach ( $blocks as $block ) {
		$block_dir = TAT_PLUGIN_DIR . 'build/blocks/' . $block;
		if ( file_exists( $block_dir . '/block.json' ) ) {
			register_block_type( $block_dir );
		}
	}
}

add_action( 'init', 'tat_load_textdomain' );

function tat_load_textdomain() {
	load_plugin_textdomain( 'take-action-toolkit', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
}

register_activation_hook( __FILE__, 'tat_activate' );

function tat_activate() {
	tat_register_post_types();
	tat_register_taxonomies();
	flush_rewrite_rules();

	if ( ! get_option( 'tat_settings' ) ) {
		update_option( 'tat_needs_setup', true );
	}
}

register_deactivation_hook( __FILE__, 'tat_deactivate' );

function tat_deactivate() {
	wp_clear_scheduled_hook( 'tat_refresh_calendar' );
	flush_rewrite_rules();
}
