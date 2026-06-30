<?php

/**
 * Use this file to register any custom post types you wish to create.
 */
if (!function_exists('hle_create_custom_post_type')) {
	// Register Custom Post Type
	function hle_create_custom_post_type()
	{

		register_post_type('tours', array(
			'labels' => array(
				'name' => __('Tours'),
				'singular_name' => __('Tour'),
				'add_new' => __('Add New'),
				'add_new_item' => __('Add New Tour'),
				'edit_item' => __('Edit Tour'),
				'new_item' => __('New Tour'),
				'view_item' => __('View Tour'),
				'search_items' => __('Search Tours'),
				'not_found' => __('No tours found'),
				'not_found_in_trash' => __('No tours found in trash'),
				'all_items' => __('All Tours'),
				'menu_name' => __('Tours'),
			),
			'label' => __('Tours', 'hle'),
			'supports' => array('title', 'editor', 'thumbnail', 'revisions', 'page-attributes'),
			'menu_icon' => 'dashicons-admin-generic',
			'hierarchical' => false,
			'public' => true,
			'show_ui' => true,
			'show_in_menu' => true,
			'menu_position' => 5,
			'show_in_admin_bar' => true,
			'show_in_nav_menus' => true,
			'can_export' => true,
			'has_archive' => false,
			'exclude_from_search' => false,
			'publicly_queryable' => true,
			'show_in_rest' => true,
			'rewrite' => array('slug' => 'news-item'),
		));

	}

	add_action('init', 'hle_create_custom_post_type', 0);
}

if (!function_exists('hle_create_custom_taxonomy')) {
	function hle_create_custom_taxonomy()
	{

	}
	add_action('init', 'hle_create_custom_taxonomy', 0);
}
