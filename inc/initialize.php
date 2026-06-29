<?php
/**
 * Use this file for initialization of the theme.
 */
add_action('after_setup_theme', function () {
	add_theme_support('automatic-feed-links');
	add_theme_support('title-tag');
	add_theme_support('post-thumbnails');
	add_theme_support('align-wide');
	add_theme_support('custom-line-height');
	add_theme_support('html5', [
		'search-form',
		'comment-form',
		'comment-list',
		'gallery',
		'caption',
		'style',
		'script',
	]);

	add_theme_support('custom-logo');
	add_theme_support('wp-block-styles');
	add_theme_support('editor-styles');
	add_editor_style('build/editor.css');
});

add_filter('use_block_editor_for_post', '__return_false', 10);
add_filter('use_block_editor_for_post_type', '__return_false', 10);

function hle_add_slug_to_body_class($classes)
{
	if (is_singular()) {
		global $post;
		$classes[] = 'page-' . sanitize_html_class($post->post_name);
	}
	return $classes;
}
add_filter('body_class', 'hle_add_slug_to_body_class');

