<?php

/**
 * Hooks.
 */

function imageTagForJs($response, $attachment)
{
	foreach ($response['sizes'] as $size => $datas) {
		$response['sizes'][$size]['tag'] = wp_get_attachment_image($attachment->ID, $size);
		$response['sizes'][$size]['srcset'] = wp_get_attachment_image_srcset($attachment->ID, $size);
	}
	return $response;
}
add_filter('wp_prepare_attachment_for_js', 'imageTagForJs', 10, 2);


/**
 * Allow upload json file
 */
add_filter('upload_mimes', function ($mime_types) {
	$mime_types['json'] = 'application/json'; // Adding .json extension
	$mime_types['svg'] = 'image/svg+xml';
	$mime_types['svgz'] = 'image/svg+xml';
	$mime_types['ttf'] = 'application/x-font-ttf';
	$mime_types['otf'] = 'application/x-font-opentype';
	$mime_types['woff'] = 'application/font-woff';
	$mime_types['woff2'] = 'application/font-woff2';
	return $mime_types;
}, 1);

/**
 * Header template
 * @return void
 */
add_action('hle_hook_header', 'hle_header_template');
function hle_header_template()
{
	load_template(get_template_directory() . '/template-parts/header.php', false);
}

/**
 * Footer template
 * @return void
 */
add_action('hle_hook_footer', 'hle_footer_template');
function hle_footer_template()
{
	load_template(get_template_directory() . '/template-parts/footer.php', false);
}

/**
 * Search template
 * @return void
 */
add_action('hle_hook_search', 'hle_search_template');
function hle_search_template()
{
	load_template(get_template_directory() . '/template-parts/modal-search.php', false);
}


/**
 * Post loop item template
 *
 * @param Int $post_id
 *
 * @return void
 */
add_action('hle_hook_post_loop_item', 'hle_post_loop_item_template', 20, 2);
function hle_post_loop_item_template($post_id, $index)
{
	set_query_var('post_id', $post_id);
	?>
	<article <?php post_class('col-md-4') ?>>
		<?php hle_post_item() ?>
	</article>
	<?php
}

/**
 * Force comments to be open for standard posts.
 */
add_filter('comments_open', function ($open, $post_id) {
	if (get_post_type($post_id) === 'post') {
		return true;
	}
	return $open;
}, 20, 2);
