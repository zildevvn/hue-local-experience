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
 * Force comments to be open for standard posts and tours.
 */
add_filter('comments_open', function ($open, $post_id) {
	if (in_array(get_post_type($post_id), ['post', 'tours'])) {
		return true;
	}
	return $open;
}, 20, 2);

/**
 * Save tour review rating as comment meta after a comment is posted.
 */
add_action('comment_post', function ($comment_id, $comment_approved) {
	if (isset($_POST['hle_tour_rating'])) {
		$rating = intval($_POST['hle_tour_rating']);
		if ($rating >= 1 && $rating <= 5) {
			add_comment_meta($comment_id, 'rating', $rating, true);
		}
	}
}, 10, 2);

/**
 * Validate that a rating is submitted for tour review comments.
 */
add_filter('preprocess_comment', function ($commentdata) {
	$post_id = isset($commentdata['comment_post_ID']) ? intval($commentdata['comment_post_ID']) : 0;
	if ($post_id && get_post_type($post_id) === 'tours') {
		if (empty($_POST['hle_tour_rating']) || intval($_POST['hle_tour_rating']) < 1) {
			wp_die(__('Please select a star rating before submitting your review.'), __('Missing Rating'), ['back_link' => true, 'response' => 400]);
		}
	}
	return $commentdata;
});

/**
 * Handle redirect after tour review submission.
 */
add_filter('comment_post_redirect', function ($location, $comment) {
	if (get_post_type($comment->comment_post_ID) === 'tours') {
		$location = remove_query_arg(['unapproved', 'approved', 'moderation-hash'], $location);
		// Strip any hash anchor first
		$parts = explode('#', $location);
		$base_url = $parts[0];
		
		if ($comment->comment_approved == '1') {
			$base_url = add_query_arg('approved', '1', $base_url);
		} else {
			$base_url = add_query_arg([
				'unapproved' => $comment->comment_ID,
				'moderation-hash' => wp_hash($comment->comment_date_gmt)
			], $base_url);
		}
		
		$location = $base_url . '#tour-review';
	}
	return $location;
}, 10, 2);

