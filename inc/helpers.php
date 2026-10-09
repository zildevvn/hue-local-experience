<?php

/**
 * Helpers
 */

function dump($data)
{
	print "<pre style=' background: rgba(0, 0, 0, 0.1); margin-bottom: 1.618em; padding: 1.618em; overflow: auto; max-width: 100%; '>==========================\n";
	if (is_array($data)) {
		print_r($data);
	} elseif (is_object($data)) {
		var_dump($data);
	} else {
		var_dump($data);
	}
	print "===========================</pre>";
}


if (!function_exists('hle_svg_icon')) {

	/**
	 * @param $icon
	 *
	 * @return mixed|string
	 */
	function hle_svg_icon($icon)
	{
		$icons = require(__DIR__ . '/svg.php');
		return isset($icons[$icon]) ? $icons[$icon] : '';
	}
}

if (!function_exists('hle_pagination')) {
	function hle_pagination($current_page = null, $total_pages = null, $query_args = [])
	{
		global $wp_query, $wp_rewrite;

		$paged = get_query_var('paged') ? get_query_var('paged') : (get_query_var('page') ? get_query_var('page') : 1);
		$current_page = $current_page ? max(1, intval($current_page)) : max(1, intval($paged));
		$total_pages = $total_pages ? intval($total_pages) : $wp_query->max_num_pages;

		if ($total_pages < 2) {
			return;
		}

		$pagenum_link = html_entity_decode(get_pagenum_link());
		$url_parts = explode('?', $pagenum_link);
		$existing_args = [];
		if (isset($url_parts[1])) {
			wp_parse_str($url_parts[1], $existing_args);
		}

		$merged_args = array_merge($existing_args, $query_args);
		$pagenum_link = remove_query_arg(array_keys($existing_args), $pagenum_link);
		$pagenum_link = trailingslashit($pagenum_link) . '%_%';

		$format = $wp_rewrite->using_index_permalinks() && !strpos($pagenum_link, 'index.php') ? 'index.php/' : '';
		$format .= $wp_rewrite->using_permalinks() ? user_trailingslashit('page/%#%', 'paged') : '?paged=%#%';

		$links = paginate_links([
			'base' => $pagenum_link,
			'format' => $format,
			'current' => $current_page,
			'total' => $total_pages,
			'type' => 'list',
			'prev_text' => hle_svg_icon('arrow_prev') ? hle_svg_icon('arrow_prev') : '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"></polyline></svg>',
			'next_text' => hle_svg_icon('arrow_next') ? hle_svg_icon('arrow_next') : '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"></polyline></svg>',
			'add_args' => $merged_args,
		]);

		if ($links) {
			echo '<nav class="hle-pagination" aria-label="Pagination">';
			echo $links;
			echo '</nav>';
		}
	}
}




if (!function_exists('hle_split_words_preserve_html')) {
	function hle_split_words_preserve_html($html)
	{
		if (empty($html))
			return '';
		// Match HTML tags, whitespace sequences, or words
		preg_match_all('/(<[^>]+>)|(\s+)|([^<>\s]+)/', $html, $matches);

		$word_count = 0;
		foreach ($matches[0] as $token) {
			if (!preg_match('/^<[^>]+>$/', $token) && !preg_match('/^\s+$/', $token)) {
				$word_count++;
			}
		}

		$result = '';
		$current_word = 0;
		foreach ($matches[0] as $token) {
			if (preg_match('/^<[^>]+>$/', $token) || preg_match('/^\s+$/', $token)) {
				$result .= $token;
			} else {
				// Calculate reverse index so right-most words animate first
				$reverse_index = $word_count - $current_word - 1;
				$result .= '<span class="split-word" style="--word-index: ' . $reverse_index . ';">' . $token . '</span>';
				$current_word++;
			}
		}
		return $result;
	}
}


/**
 * Generate Table of Contents từ nội dung bài viết
 */
function hle_generate_toc($content)
{
	if (empty($content))
		return ['toc' => '', 'content' => $content];

	$headings = [];
	$index = 0;

	$content = preg_replace_callback(
		'/<(h[23])([^>]*)>(.*?)<\/h[23]>/is',
		function ($matches) use (&$headings, &$index) {
			$tag = $matches[1];
			$attrs = $matches[2];
			$text = strip_tags($matches[3]);
			$id = 'toc-' . $index . '-' . sanitize_title($text);

			$headings[] = [
				'tag' => $tag,
				'text' => $text,
				'id' => $id,
			];

			$index++;

			return "<{$tag}{$attrs} id=\"{$id}\">{$matches[3]}</{$tag}>";
		},
		$content
	);

	if (empty($headings))
		return ['toc' => '', 'content' => $content];

	$toc = '<nav class="hle-toc" aria-label="Table of Contents">';
	$toc .= '<ol class="hle-toc__list">';

	foreach ($headings as $heading) {
		$class = $heading['tag'] === 'h3' ? ' class="hle-toc__item--sub"' : '';
		$toc .= "<li{$class}>";
		$toc .= '<a href="#' . esc_attr($heading['id']) . '">' . esc_html($heading['text']) . '</a>';
		$toc .= '</li>';
	}

	$toc .= '</ol></nav>';

	return ['toc' => $toc, 'content' => $content];
}

/**
 * [WHY] Cache kết quả để không gọi hle_generate_toc 2 lần
 * Sidebar dùng để lấy TOC, main content dùng để lấy content đã inject id
 */
function hle_get_toc_result()
{
	static $cached = null;

	if ($cached === null) {
		$raw = get_the_content();
		$raw = apply_filters('the_content', $raw);
		$cached = hle_generate_toc($raw);
	}

	return $cached;
}


/**
 * External API request.
 */
function vm_external_api_request(
	$endpoint,
	$method = 'GET',
	$body = null
) {
	if (
		!defined('VM_EXTERNAL_API_URL') ||
		!defined('VM_EXTERNAL_API_KEY')
	) {
		return [
			'ok' => false,
			'status' => 500,
			'message' => 'External API configuration is missing.',
		];
	}

	$url = rtrim(
		VM_EXTERNAL_API_URL,
		'/'
	) . '/' . ltrim(
		$endpoint,
		'/'
	);

	$args = [
		'method' => strtoupper($method),
		'timeout' => 20,

		'headers' => [
			'Authorization' =>
				'Bearer ' . VM_EXTERNAL_API_KEY,

			'Accept' =>
				'application/json',
		],
	];

	if ($body !== null) {
		$args['headers']['Content-Type'] =
			'application/json';

		$args['body'] =
			wp_json_encode($body);
	}

	$response = wp_remote_request(
		$url,
		$args
	);

	if (is_wp_error($response)) {
		return [
			'ok' => false,
			'status' => 500,
			'message' =>
				$response->get_error_message(),
		];
	}

	$status = wp_remote_retrieve_response_code(
		$response
	);

	$responseBody = wp_remote_retrieve_body(
		$response
	);

	/*
	 * 204 No Content
	 */
	if ($status === 204) {
		return [
			'ok' => true,
			'status' => 204,
			'data' => null,
		];
	}

	$data = json_decode(
		$responseBody,
		true
	);

	if (!is_array($data) && !empty($responseBody)) {
		return [
			'ok' => false,
			'status' => $status,
			'message' => 'Invalid API response.',
			'raw_body' => $responseBody,
		];
	}

	/*
	 * HTTP error
	 */
	if ($status < 200 || $status >= 300) {
		return [
			'ok' => false,
			'status' => $status,
			'message' =>
				$data['message']
				?? 'API request failed.',
			'data' => $data,
		];
	}

	return [
		'ok' => true,
		'status' => $status,
		'data' => $data,
	];
}


/**
 * AJAX: Get orders.
 */
function vm_external_ajax_get_orders()
{
	check_ajax_referer('vm_external_orders', 'nonce');

	$orderId = isset($_POST['order_id'])
		? absint($_POST['order_id'])
		: 0;

	$dateFrom = isset($_POST['date_from'])
		? sanitize_text_field(wp_unslash($_POST['date_from']))
		: '';

	$dateTo = isset($_POST['date_to'])
		? sanitize_text_field(wp_unslash($_POST['date_to']))
		: '';

	$page = isset($_POST['page'])
		? max(1, absint($_POST['page']))
		: 1;

	$perPage = isset($_POST['per_page'])
		? min(100, max(1, absint($_POST['per_page'])))
		: 20;

	/*
	 * Search by Order ID
	 */
	if ($orderId > 0) {

		$query = [
			'order_id' => $orderId,
			'page' => $page,
			'per_page' => $perPage,
		];

		/*
		 * Filter by date
		 */
	} else {

		if (empty($dateFrom) || empty($dateTo)) {
			wp_send_json_error([
				'message' => 'Date range is required.',
			], 400);
		}

		$query = [
			'date_from' => $dateFrom,
			'date_to' => $dateTo,
			'page' => $page,
			'per_page' => $perPage,
		];
	}

	$result = vm_external_api_request(
		'orders?' . http_build_query($query)
	);

	if (empty($result['ok'])) {
		wp_send_json_error([
			'message' => $result['message'] ?? 'Unable to load orders.',
		], $result['status'] ?? 500);
	}

	wp_send_json_success($result['data']);
}

add_action(
	'wp_ajax_vm_external_get_orders',
	'vm_external_ajax_get_orders'
);