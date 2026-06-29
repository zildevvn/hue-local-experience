<?php

/**
 * Use this file to register any custom post types you wish to create.
 */
if (!function_exists('hle_create_custom_post_type')) {
	// Register Custom Post Type
	function hle_create_custom_post_type()
	{



	}

	add_action('init', 'hle_create_custom_post_type', 0);
}

if (!function_exists('hle_create_custom_taxonomy')) {
	function hle_create_custom_taxonomy()
	{

	}
	add_action('init', 'hle_create_custom_taxonomy', 0);
}
