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
		$url_parts    = explode('?', $pagenum_link);
		$existing_args = [];
		if (isset($url_parts[1])) {
			wp_parse_str($url_parts[1], $existing_args);
		}

		$merged_args = array_merge($existing_args, $query_args);
		$pagenum_link = remove_query_arg(array_keys($existing_args), $pagenum_link);
		$pagenum_link = trailingslashit($pagenum_link) . '%_%';

		$format  = $wp_rewrite->using_index_permalinks() && !strpos($pagenum_link, 'index.php') ? 'index.php/' : '';
		$format .= $wp_rewrite->using_permalinks() ? user_trailingslashit('page/%#%', 'paged') : '?paged=%#%';

		$links = paginate_links([
			'base'      => $pagenum_link,
			'format'    => $format,
			'current'   => $current_page,
			'total'     => $total_pages,
			'type'      => 'list',
			'prev_text' => hle_svg_icon('arrow_prev') ? hle_svg_icon('arrow_prev') : '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"></polyline></svg>',
			'next_text' => hle_svg_icon('arrow_next') ? hle_svg_icon('arrow_next') : '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"></polyline></svg>',
			'add_args'  => $merged_args,
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