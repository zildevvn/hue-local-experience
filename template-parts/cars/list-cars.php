<?php
$paged = get_query_var('paged') ?: (get_query_var('page') ?: 1);

$args = array(
    'post_type' => 'cars',
    'posts_per_page' => 12,
    'paged' => $paged,
    'post_status' => 'publish',
    'tax_query' => array(
        array(
            'taxonomy' => 'car_category',
            'field' => 'slug',
            'terms' => 'tour',
        ),
    ),
);
$query = new WP_Query($args);
?>

<?php if ($query->have_posts()): ?>
    <section class="hle-section list-cars-section">
        <div class="container">
            <h2 class="hle-heading hle-heading-animation">Hue Car Rental with Sightseeing</h2>

            <div class="list-cars-wrapper position-relative">
                <div class="tours-loading-overlay cars-loading-overlay">
                    <div class="tours-loading-spinner">
                        <div class="spinner-dot"></div>
                    </div>
                    <span class="tours-loading-text">Loading cars...</span>
                </div>

                <div id="hle-cars-results" class="list-cars-section__cars" data-query='<?= json_encode($args) ?>'
                    data-currentpage="<?= $paged ?>">
                    <?php while ($query->have_posts()):
                        $query->the_post();
                        hle_car_tour_item();
                    endwhile;
                    wp_reset_postdata(); ?>
                </div>

                <div id="hle-cars-pagination">
                    <?php
                    $total_pages = $query->max_num_pages;
                    if ($total_pages > 1) {
                        echo '<div class="hle-pagination">';

                        // Prev button
                        if ($paged > 1) {
                            echo '<button class="page-numbers prev" data-page="' . ($paged - 1) . '"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="15 18 9 12 15 6"/></svg><span>' . __('Prev', 'hue-local-experience') . '</span></button>';
                        } else {
                            echo '<button class="page-numbers prev disabled" disabled><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="15 18 9 12 15 6"/></svg><span>' . __('Prev', 'hue-local-experience') . '</span></button>';
                        }

                        // Page numbers
                        for ($i = 1; $i <= $total_pages; $i++) {
                            if ($i == $paged) {
                                echo '<span class="page-numbers current">' . $i . '</span>';
                            } else {
                                echo '<button class="page-numbers" data-page="' . $i . '">' . $i . '</button>';
                            }
                        }

                        // Next button
                        if ($paged < $total_pages) {
                            echo '<button class="page-numbers next" data-page="' . ($paged + 1) . '"><span>' . __('Next', 'hue-local-experience') . '</span><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="9 18 15 12 9 6"/></svg></button>';
                        } else {
                            echo '<button class="page-numbers next disabled" disabled><span>' . __('Next', 'hue-local-experience') . '</span><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="9 18 15 12 9 6"/></svg></button>';
                        }

                        echo '</div>';
                    }
                    ?>
                </div>
            </div>

        </div>
    </section>
<?php endif; ?>