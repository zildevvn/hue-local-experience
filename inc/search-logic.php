<?php
/**
 * Advanced Search Logic
 */

add_filter('posts_search', 'hle_advanced_search_where', 20, 2);
function hle_advanced_search_where($search, $wp_query) {
    global $wpdb;

    if (!is_admin() && $wp_query->is_main_query() && $wp_query->is_search()) {
        $search_query = $wp_query->get('s');
        if (empty($search_query)) return $search;

        $words = hle_get_search_tokens($search_query);
        if (empty($words)) return $search;

        $exact_like = '%' . $wpdb->esc_like($search_query) . '%';
        $stripped_like = '%' . $wpdb->esc_like(str_replace(' ', '', $search_query)) . '%';

        $conditions = [];
        $conditions[] = $wpdb->prepare("({$wpdb->posts}.post_title LIKE %s OR {$wpdb->posts}.post_content LIKE %s)", $exact_like, $exact_like);
        $conditions[] = $wpdb->prepare("(REPLACE({$wpdb->posts}.post_title, ' ', '') LIKE %s)", $stripped_like);

        foreach ($words as $word) {
            $word_like = '%' . $wpdb->esc_like($word) . '%';
            $conditions[] = $wpdb->prepare("({$wpdb->posts}.post_title LIKE %s OR {$wpdb->posts}.post_content LIKE %s)", $word_like, $word_like);

            if (mb_strlen($word) > 4) {
                $trunc_like = '%' . $wpdb->esc_like(mb_substr($word, 0, 4)) . '%';
                $conditions[] = $wpdb->prepare("({$wpdb->posts}.post_title LIKE %s)", $trunc_like);
            }
        }

        return " AND (" . implode(' OR ', $conditions) . ")";
    }

    return $search;
}

add_filter('posts_clauses', 'hle_advanced_search_clauses', 20, 2);
function hle_advanced_search_clauses($clauses, $wp_query) {
    global $wpdb;

    if (!is_admin() && $wp_query->is_main_query() && $wp_query->is_search()) {
        $search_query = $wp_query->get('s');
        if (empty($search_query)) return $clauses;

        $words = hle_get_search_tokens($search_query);
        if (empty($words)) return $clauses;

        $exact_like = '%' . $wpdb->esc_like($search_query) . '%';
        $stripped_like = '%' . $wpdb->esc_like(str_replace(' ', '', $search_query)) . '%';

        $score_sql = [];
        $score_sql[] = $wpdb->prepare("IF({$wpdb->posts}.post_title LIKE %s, 100, 0)", $exact_like);
        $score_sql[] = $wpdb->prepare("IF(REPLACE({$wpdb->posts}.post_title, ' ', '') LIKE %s, 50, 0)", $stripped_like);
        $score_sql[] = $wpdb->prepare("IF({$wpdb->posts}.post_content LIKE %s, 30, 0)", $exact_like);

        // For "All Words in Title", we can add a score boost if ALL words match the title
        $all_words_conditions = [];
        foreach ($words as $word) {
            $all_words_conditions[] = $wpdb->prepare("{$wpdb->posts}.post_title LIKE %s", '%' . $wpdb->esc_like($word) . '%');
        }
        if (!empty($all_words_conditions)) {
            $all_words_sql = implode(' AND ', $all_words_conditions);
            $score_sql[] = "IF($all_words_sql, 50, 0)";
        }

        foreach ($words as $word) {
            $word_like = '%' . $wpdb->esc_like($word) . '%';
            $score_sql[] = $wpdb->prepare("IF({$wpdb->posts}.post_title LIKE %s, 10, 0)", $word_like);
            $score_sql[] = $wpdb->prepare("IF({$wpdb->posts}.post_content LIKE %s, 1, 0)", $word_like);

            if (mb_strlen($word) > 4) {
                $trunc_like = '%' . $wpdb->esc_like(mb_substr($word, 0, 4)) . '%';
                $score_sql[] = $wpdb->prepare("IF({$wpdb->posts}.post_title LIKE %s, 5, 0)", $trunc_like);
            }
        }

        $score_select = implode(' + ', $score_sql) . " AS relevance_score";
        $clauses['fields'] .= ", " . $score_select;
        
        $clauses['orderby'] = "relevance_score DESC, " . $clauses['orderby'];
        $clauses['groupby'] = "{$wpdb->posts}.ID";
    }

    return $clauses;
}

function hle_get_search_tokens($search_query) {
    $raw_query = mb_strtolower(trim($search_query));
    $raw_query = preg_replace('/\s+/', ' ', $raw_query);
    $raw_query = preg_replace('/[^\p{L}\p{N}\s]/u', '', $raw_query);

    $stop_words = ['in', 'the', 'a', 'an', 'and', 'to', 'of', 'for', 'with', 'on', 'at'];
    $raw_words = explode(' ', $raw_query);
    $words = [];
    foreach ($raw_words as $word) {
        $word = trim($word);
        if (!empty($word) && !in_array($word, $stop_words)) {
            // Keep original exact token
            $words[] = $word;
            
            // Generate normalized fallback for plurals/suffixes
            $len = mb_strlen($word);
            if ($len > 3) { // Only normalize words long enough to remain meaningful
                $normalized = $word;
                if (preg_match('/ies$/', $word)) {
                    // e.g. activities -> activity, cities -> city
                    $normalized = mb_substr($word, 0, -3) . 'y';
                } elseif (preg_match('/(ss|sh|ch|x)es$/', $word)) {
                    // e.g. boxes -> box, matches -> match
                    $normalized = mb_substr($word, 0, -2);
                } elseif (preg_match('/es$/', $word)) {
                    // e.g. experiences -> experience, hues -> hue
                    $normalized = mb_substr($word, 0, -1);
                } elseif (preg_match('/[^s]s$/', $word)) {
                    // e.g. tours -> tour, cars -> car (ends in s but not ss)
                    $normalized = mb_substr($word, 0, -1);
                }
                
                // Add normalized token if it changed and remains valid length
                if ($normalized !== $word && mb_strlen($normalized) >= 3) {
                    $words[] = $normalized;
                }
            }
        }
    }

    if (empty($words)) {
        $words = array_filter(explode(' ', $raw_query));
    }
    
    // Remove duplicate tokens (e.g. if a generated fallback matches an existing token)
    return array_unique($words);
}
