<?php
add_action('wp_ajax_hle_ajax_filter_tours', 'hle_ajax_filter_tours');
add_action('wp_ajax_nopriv_hle_ajax_filter_tours', 'hle_ajax_filter_tours');
function hle_ajax_filter_tours()
{
    $idCate = isset($_POST['idCate']) ? $_POST['idCate'] : [];
    $tour_cat = isset($_POST['tour_cat']) ? $_POST['tour_cat'] : '';
    $keySeach = isset($_POST['keySeach']) ? $_POST['keySeach'] : [];
    $query = isset($_POST['query']) ? $_POST['query'] : [];
    $currentpage = isset($_POST['currentpage']) ? $_POST['currentpage'] : 1;
    $pax_min = isset($_POST['pax_min']) && $_POST['pax_min'] !== '' ? intval($_POST['pax_min']) : null;
    $pax_max = isset($_POST['pax_max']) && $_POST['pax_max'] !== '' ? intval($_POST['pax_max']) : null;
    $price_min = isset($_POST['price_min']) && $_POST['price_min'] !== '' ? intval($_POST['price_min']) : null;
    $price_max = isset($_POST['price_max']) && $_POST['price_max'] !== '' ? intval($_POST['price_max']) : null;
    $sort = isset($_POST['sort']) ? $_POST['sort'] : 'default';
    $searchHd = '';

    if (!empty($keySeach)) {
        $query['s'] = $keySeach;
    }

    if (!empty($idCate) && $idCate != 'all') {
        $query['cat'] = explode(",", $idCate);
    }

    if (!empty($tour_cat) && $tour_cat != 'all') {
        if (!isset($query['tax_query'])) {
            $query['tax_query'] = ['relation' => 'AND'];
        }
        $query['tax_query'][] = [
            'taxonomy' => 'tour_cats',
            'field'    => 'term_id',
            'terms'    => intval($tour_cat),
        ];
    }

    if (($pax_min !== null && $pax_max !== null) || ($price_min !== null && $price_max !== null) || $sort === 'price_low' || $sort === 'price_high') {
        if (!isset($query['meta_query'])) {
            $query['meta_query'] = ['relation' => 'AND'];
        }
        
        if ($pax_min !== null && $pax_max !== null) {
            $query['meta_query'][] = [
                'key' => 'paxs_tours_max',
                'value' => $pax_min,
                'compare' => '>=',
                'type' => 'NUMERIC'
            ];
            $query['meta_query'][] = [
                'key' => 'paxs_tours_min',
                'value' => $pax_max,
                'compare' => '<=',
                'type' => 'NUMERIC'
            ];
        }

        if ($price_min !== null && $price_max !== null) {
            $query['meta_query'][] = [
                'key' => 'price_tour',
                'value' => [$price_min, $price_max],
                'compare' => 'BETWEEN',
                'type' => 'NUMERIC'
            ];
        }

        if (($sort === 'price_low' || $sort === 'price_high') && ($price_min === null || $price_max === null)) {
             // Ensures price_tour meta is joined if not already filtered
            $query['meta_query'][] = [
                'key' => 'price_tour',
                'compare' => 'EXISTS'
            ];
        }
    }

    if ($sort === 'price_low') {
        $query['meta_key'] = 'price_tour';
        $query['orderby'] = 'meta_value_num';
        $query['order'] = 'ASC';
    } elseif ($sort === 'price_high') {
        $query['meta_key'] = 'price_tour';
        $query['orderby'] = 'meta_value_num';
        $query['order'] = 'DESC';
    } elseif ($sort === 'newest') {
        $query['orderby'] = 'date';
        $query['order'] = 'DESC';
    } elseif ($sort === 'title_az') {
        $query['orderby'] = 'title';
        $query['order'] = 'ASC';
    }

    $query['paged'] = $currentpage;

    ob_start();
    $the_query = new WP_Query($query);
    $count = $the_query->found_posts;

    if ($the_query->have_posts()) {

        if (!empty($keySeach)) {
            $searchHd = $the_query->found_posts;
        }

        while ($the_query->have_posts()) {
            $the_query->the_post();
            hle_tour_item();
        }

    } else { ?>
        <div class="sm-filter-posts-block--not-found"> Sorry, no posts matched your criteria.</div>
    <?php }

    $items = ob_get_clean();

    ob_start();
    $total_pages = $the_query->max_num_pages;
    if ($total_pages > 1) {
        echo '<div class="tours-pagination">';
        
        // Prev button
        if ($currentpage > 1) {
            echo '<button class="page-numbers prev" data-page="' . ($currentpage - 1) . '">&laquo; Prev</button>';
        } else {
            echo '<button class="page-numbers prev disabled" disabled>&laquo; Prev</button>';
        }

        // Page numbers
        for ($i = 1; $i <= $total_pages; $i++) {
            if ($i == $currentpage) {
                echo '<span class="page-numbers current">' . $i . '</span>';
            } else {
                echo '<button class="page-numbers" data-page="' . $i . '">' . $i . '</button>';
            }
        }

        // Next button
        if ($currentpage < $total_pages) {
            echo '<button class="page-numbers next" data-page="' . ($currentpage + 1) . '">Next &raquo;</button>';
        } else {
            echo '<button class="page-numbers next disabled" disabled>Next &raquo;</button>';
        }
        
        echo '</div>';
    }
    $pagination = ob_get_clean();

    wp_reset_postdata();
    wp_send_json([
        'items' => $items,
        'searchHd' => $searchHd,
        'pagination' => $pagination,
        'count' => $count
    ]);
    wp_die();
}

add_action('wp_ajax_hle_ajax_filter_posts', 'hle_ajax_filter_posts');
add_action('wp_ajax_nopriv_hle_ajax_filter_posts', 'hle_ajax_filter_posts');
function hle_ajax_filter_posts()
{
    $post_cat = isset($_POST['post_cat']) ? $_POST['post_cat'] : '';
    $keySeach = isset($_POST['keySeach']) ? sanitize_text_field($_POST['keySeach']) : '';
    $query = isset($_POST['query']) ? $_POST['query'] : [];
    $currentpage = isset($_POST['currentpage']) ? intval($_POST['currentpage']) : 1;
    $searchHd = '';

    if (!empty($keySeach)) {
        $query['s'] = $keySeach;
    }

    if (!empty($post_cat) && $post_cat != 'all') {
        $query['cat'] = intval($post_cat);
    }

    $query['paged'] = $currentpage;

    ob_start();
    $the_query = new WP_Query($query);
    $count = $the_query->found_posts;

    if ($the_query->have_posts()) {
        if (!empty($keySeach)) {
            $searchHd = $the_query->found_posts;
        }

        while ($the_query->have_posts()) {
            $the_query->the_post();
            hle_post_item();
        }
    } else {
        // Handled by frontend display:none JS logic, but output empty string.
    }
    $items = ob_get_clean();

    ob_start();
    $total_pages = $the_query->max_num_pages;
    if ($total_pages > 1) {
        echo '<div class="tours-pagination">';
        
        // Prev button
        if ($currentpage > 1) {
            echo '<button class="page-numbers prev" data-page="' . ($currentpage - 1) . '">&laquo; Prev</button>';
        } else {
            echo '<button class="page-numbers prev disabled" disabled>&laquo; Prev</button>';
        }

        // Page numbers
        for ($i = 1; $i <= $total_pages; $i++) {
            if ($i == $currentpage) {
                echo '<span class="page-numbers current">' . $i . '</span>';
            } else {
                echo '<button class="page-numbers" data-page="' . $i . '">' . $i . '</button>';
            }
        }

        // Next button
        if ($currentpage < $total_pages) {
            echo '<button class="page-numbers next" data-page="' . ($currentpage + 1) . '">Next &raquo;</button>';
        } else {
            echo '<button class="page-numbers next disabled" disabled>Next &raquo;</button>';
        }
        
        echo '</div>';
    }
    $pagination = ob_get_clean();

    wp_reset_postdata();
    wp_send_json([
        'items' => $items,
        'searchHd' => $searchHd,
        'pagination' => $pagination,
        'count' => $count
    ]);
    wp_die();
}