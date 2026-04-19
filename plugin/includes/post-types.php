<?php
/**
 * Custom Post Types and Taxonomies for Take Action Toolkit.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'init', 'tat_register_post_types' );
add_action( 'init', 'tat_register_taxonomies' );
add_action( 'init', 'tat_register_meta_fields' );

function tat_register_post_types() {
	register_post_type( 'organization', array(
		'labels'       => array(
			'name'               => __( 'Organizations', 'take-action-toolkit' ),
			'singular_name'      => __( 'Organization', 'take-action-toolkit' ),
			'add_new_item'       => __( 'Add New Organization', 'take-action-toolkit' ),
			'edit_item'          => __( 'Edit Organization', 'take-action-toolkit' ),
			'new_item'           => __( 'New Organization', 'take-action-toolkit' ),
			'view_item'          => __( 'View Organization', 'take-action-toolkit' ),
			'search_items'       => __( 'Search Organizations', 'take-action-toolkit' ),
			'not_found'          => __( 'No organizations found', 'take-action-toolkit' ),
			'not_found_in_trash' => __( 'No organizations found in Trash', 'take-action-toolkit' ),
			'all_items'          => __( 'All Organizations', 'take-action-toolkit' ),
			'menu_name'          => __( 'Organizations', 'take-action-toolkit' ),
		),
		'public'       => true,
		'show_in_rest' => true,
		'has_archive'  => true,
		'menu_icon'    => 'dashicons-groups',
		'supports'     => array( 'title', 'editor', 'thumbnail', 'custom-fields', 'excerpt' ),
		'rewrite'      => array( 'slug' => 'orgs' ),
	) );

	register_post_type( 'business', array(
		'labels'       => array(
			'name'               => __( 'Businesses', 'take-action-toolkit' ),
			'singular_name'      => __( 'Business', 'take-action-toolkit' ),
			'add_new_item'       => __( 'Add New Business', 'take-action-toolkit' ),
			'edit_item'          => __( 'Edit Business', 'take-action-toolkit' ),
			'new_item'           => __( 'New Business', 'take-action-toolkit' ),
			'view_item'          => __( 'View Business', 'take-action-toolkit' ),
			'search_items'       => __( 'Search Businesses', 'take-action-toolkit' ),
			'not_found'          => __( 'No businesses found', 'take-action-toolkit' ),
			'not_found_in_trash' => __( 'No businesses found in Trash', 'take-action-toolkit' ),
			'all_items'          => __( 'All Businesses', 'take-action-toolkit' ),
			'menu_name'          => __( 'Businesses', 'take-action-toolkit' ),
		),
		'public'       => true,
		'show_in_rest' => true,
		'has_archive'  => true,
		'menu_icon'    => 'dashicons-store',
		'supports'     => array( 'title', 'editor', 'thumbnail', 'custom-fields', 'excerpt' ),
		'rewrite'      => array( 'slug' => 'businesses' ),
	) );
}

function tat_register_taxonomies() {
	register_taxonomy( 'org_category', 'organization', array(
		'labels'       => array(
			'name'          => __( 'Categories', 'take-action-toolkit' ),
			'singular_name' => __( 'Category', 'take-action-toolkit' ),
			'search_items'  => __( 'Search Categories', 'take-action-toolkit' ),
			'all_items'     => __( 'All Categories', 'take-action-toolkit' ),
			'edit_item'     => __( 'Edit Category', 'take-action-toolkit' ),
			'add_new_item'  => __( 'Add New Category', 'take-action-toolkit' ),
			'menu_name'     => __( 'Categories', 'take-action-toolkit' ),
		),
		'hierarchical' => true,
		'public'       => true,
		'show_in_rest' => true,
		'rewrite'      => array( 'slug' => 'org-category' ),
	) );

	register_taxonomy( 'business_category', 'business', array(
		'labels'       => array(
			'name'          => __( 'Categories', 'take-action-toolkit' ),
			'singular_name' => __( 'Category', 'take-action-toolkit' ),
			'search_items'  => __( 'Search Categories', 'take-action-toolkit' ),
			'all_items'     => __( 'All Categories', 'take-action-toolkit' ),
			'edit_item'     => __( 'Edit Category', 'take-action-toolkit' ),
			'add_new_item'  => __( 'Add New Category', 'take-action-toolkit' ),
			'menu_name'     => __( 'Categories', 'take-action-toolkit' ),
		),
		'hierarchical' => true,
		'public'       => true,
		'show_in_rest' => true,
		'rewrite'      => array( 'slug' => 'business-category' ),
	) );

	$default_org_categories = array(
		'healthcare'       => __( 'Healthcare', 'take-action-toolkit' ),
		'labor'            => __( 'Organized Labor', 'take-action-toolkit' ),
		'immigrant-support' => __( 'Immigrant Support', 'take-action-toolkit' ),
		'environment'      => __( 'Environment', 'take-action-toolkit' ),
		'education'        => __( 'Education', 'take-action-toolkit' ),
		'local-resistance' => __( 'Local Resistance', 'take-action-toolkit' ),
		'mutual-aid'       => __( 'Mutual Aid', 'take-action-toolkit' ),
		'politics'         => __( 'Politics', 'take-action-toolkit' ),
		'veterans'         => __( 'Veterans', 'take-action-toolkit' ),
		'faith'            => __( 'Faith', 'take-action-toolkit' ),
		'arts-culture'     => __( 'Arts & Culture', 'take-action-toolkit' ),
	);

	foreach ( $default_org_categories as $slug => $name ) {
		if ( ! term_exists( $slug, 'org_category' ) ) {
			wp_insert_term( $name, 'org_category', array( 'slug' => $slug ) );
		}
	}
}

function tat_register_meta_fields() {
	$org_meta = array(
		'tat_website'   => 'string',
		'tat_email'     => 'string',
		'tat_phone'     => 'string',
		'tat_instagram' => 'string',
		'tat_facebook'  => 'string',
		'tat_bluesky'   => 'string',
		'tat_twitter'   => 'string',
		'tat_youtube'   => 'string',
		'tat_tiktok'    => 'string',
	);

	foreach ( $org_meta as $key => $type ) {
		$args = array(
			'type'              => $type,
			'single'            => true,
			'show_in_rest'      => true,
			'sanitize_callback' => 'sanitize_text_field',
		);

		register_post_meta( 'organization', $key, $args );
		register_post_meta( 'business', $key, $args );
	}

	register_post_meta( 'business', 'tat_address', array(
		'type'              => 'string',
		'single'            => true,
		'show_in_rest'      => true,
		'sanitize_callback' => 'sanitize_text_field',
	) );

	register_post_meta( 'business', 'tat_map_url', array(
		'type'              => 'string',
		'single'            => true,
		'show_in_rest'      => true,
		'sanitize_callback' => 'esc_url_raw',
	) );
}
