<?php

$args = array(
    'post_type' => 'tours',
    'posts_per_page' => 12,
    'post_status' => 'publish',
);
$query = new WP_Query($args);

?>
<?php if ($query->have_posts()): ?>
    <section class="tours-list-section hle-section">
        <div class="container">
            <h2 class="hle-heading hle-heading-animation">Tours List</h2>

            <div class="tours-list-wrapper">
                <div class="tours-sidebar">
                    <div class="tours-sidebar__widget tours-sidebar__search">
                        <div class="search-input-wrapper">
                            <svg class="search-icon" xmlns="http://www.w3.org/2000/svg" width="18" height="18"
                                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="11" cy="11" r="8"></circle>
                                <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                            </svg>
                            <input type="text" id="hle-tours-search-input" placeholder="Search tours..."
                                class="form-control">
                        </div>
                    </div>
                    <div class="tours-sidebar__widget tours-sidebar__pax">
                        <div class="range-header">
                            <label class="form-label">Number of Pax</label>
                            <span class="range-value"><span id="hle-tours-pax-display">1 &ndash; 20</span> pax</span>
                        </div>
                        <div class="range-slider-container">
                            <div id="hle-tours-pax-slider"></div>
                        </div>
                        <input type="hidden" id="hle-tours-pax-min" value="1">
                        <input type="hidden" id="hle-tours-pax-max" value="50">
                    </div>

                    <div class="tours-sidebar__widget tours-sidebar__price">
                        <div class="range-header">
                            <label class="form-label">Price Range</label>
                            <span class="range-value"><span id="hle-tours-price-display">$0 &ndash; $1000</span></span>
                        </div>
                        <div class="range-slider-container">
                            <div id="hle-tours-price-slider"></div>
                        </div>
                        <input type="hidden" id="hle-tours-price-min" value="0">
                        <input type="hidden" id="hle-tours-price-max" value="1000">
                    </div>

                    <?php
                    $tour_cats = get_terms(array(
                        'taxonomy' => 'tour_cats',
                        'hide_empty' => false,
                    ));
                    ?>
                    <div class="tours-sidebar__widget tours-sidebar__cats">
                        <h4 class="form-label"
                            style="font-weight: 600; margin-bottom: 16px; display: block; font-size: 15px; color: #333;">
                            Categories</h4>
                        <div class="tours-category-list">
                            <label class="tours-category-item">
                                <input type="radio" name="tour_cat" value="all" checked>
                                <span class="custom-radio"></span>
                                <span class="tours-category-name">All Tours</span>
                            </label>
                            <?php if (!empty($tour_cats) && !is_wp_error($tour_cats)): ?>
                                <?php foreach ($tour_cats as $cat): ?>
                                    <label class="tours-category-item">
                                        <input type="radio" name="tour_cat" value="<?php echo esc_attr($cat->term_id); ?>">
                                        <span class="custom-radio"></span>
                                        <span class="tours-category-name">
                                            <?php echo esc_html($cat->name); ?>
                                        </span>
                                    </label>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="tours-content position-relative">
                    <div class="tours-loading-overlay">
                        <div class="tours-loading-spinner">
                            <div class="spinner-dot"></div>
                        </div>
                        <span class="tours-loading-text">Finding the best tours...</span>
                    </div>

                    <div class="tours-main">
                        <div class="tours-main__header">
                            <div class="tours-result-count">
                                <span id="hle-tours-count"><?= $query->found_posts ?></span> tours found
                            </div>
                            <div class="tours-header-controls">
                                <button type="button" id="hle-clear-filters" class="btn-clear-filters"
                                    style="display: none;">
                                    Clear Filters
                                </button>
                                <div class="tours-sort">
                                    <select id="hle-tours-sort" class="form-control">
                                        <option value="default">Default Sorting</option>
                                        <option value="price_low">Price: Low to High</option>
                                        <option value="price_high">Price: High to Low</option>
                                        <option value="newest">Newest</option>
                                        <option value="title_az">Title: A–Z</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div id="hle-tours-results" data-query='<?= json_encode($args) ?>' data-currentpage="1">
                            <?php while ($query->have_posts()):
                                $query->the_post();
                                hle_tour_item();
                                ?>
                            <?php endwhile; ?>
                            <?php wp_reset_postdata(); ?>
                        </div>
                    </div>

                    <div id="hle-tours-pagination">
                        <?php
                        $total_pages = $query->max_num_pages;
                        if ($total_pages > 1) {
                            echo '<div class="tours-pagination">';

                            // Prev button
                            echo '<button class="page-numbers prev disabled" disabled>&laquo; Prev</button>';

                            // Page numbers
                            for ($i = 1; $i <= $total_pages; $i++) {
                                if ($i == 1) {
                                    echo '<span class="page-numbers current">' . $i . '</span>';
                                } else {
                                    echo '<button class="page-numbers" data-page="' . $i . '">' . $i . '</button>';
                                }
                            }

                            // Next button
                            if (1 < $total_pages) {
                                echo '<button class="page-numbers next" data-page="2">Next &raquo;</button>';
                            } else {
                                echo '<button class="page-numbers next disabled" disabled>Next &raquo;</button>';
                            }

                            echo '</div>';
                        }
                        ?>
                    </div>

                    <div id="hle-tours-empty" style="display: none;">No results found for your search.</div>
                </div>
            </div>
        </div>
    </section>
<?php endif; ?>