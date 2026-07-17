<?php
$args = array(
    'post_type' => 'post',
    'posts_per_page' => 12,
    'post_status' => 'publish',
);
$query = new WP_Query($args);
?>
<?php if ($query->have_posts()): ?>
    <section class="posts-list-section hle-section main-section">
        <div class="container">
            <div class="posts-filter-bar">
                <div class="posts-filter-bar__search">
                    <div class="search-input-wrapper">
                        <svg class="search-icon" xmlns="http://www.w3.org/2000/svg" width="20" height="20"
                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                            stroke-linejoin="round">
                            <circle cx="11" cy="11" r="8"></circle>
                            <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                        </svg>
                        <input type="text" id="hle-posts-search-input" placeholder="Search insights, news, and guides..."
                            class="form-control">
                    </div>
                </div>

                <?php
                $blog_cats = get_terms(array(
                    'taxonomy' => 'category',
                    'hide_empty' => false,
                ));
                ?>
                <div class="posts-filter-bar__cats">
                    <div class="hle-custom-dropdown posts-category-dropdown">
                        <button type="button" class="hle-dropdown-trigger" aria-expanded="false"
                            aria-controls="posts-category-menu">
                            <span class="hle-dropdown-label">All Categories</span>
                            <svg class="hle-dropdown-icon" xmlns="http://www.w3.org/2000/svg" width="20" height="20"
                                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="6 9 12 15 18 9"></polyline>
                            </svg>
                        </button>
                        <div id="posts-category-menu" class="hle-dropdown-menu tours-category-list">
                            <label class="tours-category-item">
                                <input type="radio" name="post_cat" value="all" checked data-label="All Categories">
                                <span class="tours-category-name">All Categories</span>
                            </label>
                            <?php if (!empty($blog_cats) && !is_wp_error($blog_cats)): ?>
                                <?php foreach ($blog_cats as $cat): ?>
                                    <label class="tours-category-item">
                                        <input type="radio" name="post_cat" value="<?php echo esc_attr($cat->term_id); ?>"
                                            data-label="<?php echo esc_attr($cat->name); ?>">
                                        <span class="tours-category-name">
                                            <?php echo esc_html($cat->name); ?>
                                        </span>
                                    </label>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="posts-filter-bar__actions">
                    <button type="button" id="hle-posts-clear-filters" class="btn-clear-filters hle-button"
                        style="display: none;">
                        Clear Filters
                    </button>
                </div>
            </div>

            <div class="posts-list-wrapper position-relative">
                <div class="tours-loading-overlay">
                    <div class="tours-loading-spinner">
                        <div class="spinner-dot"></div>
                    </div>
                </div>

                <div class="posts-main">
                    <div class="posts-main__header d-flex justify-content-between align-items-center mb-4">
                        <div class="posts-result-count">
                            <span id="hle-posts-count"><?= $query->found_posts ?></span> posts available
                        </div>
                    </div>

                    <div id="hle-posts-results" class="post-grid" data-query='<?= json_encode($args) ?>'
                        data-currentpage="1">
                        <?php while ($query->have_posts()):
                            $query->the_post();
                            hle_post_item();
                        endwhile; ?>
                        <?php wp_reset_postdata(); ?>
                    </div>
                </div>

                <div id="hle-posts-pagination">
                    <?php
                    $total_pages = $query->max_num_pages;
                    if ($total_pages > 1) {
                        echo '<div class="hel-pagination">';

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

                <div id="hle-posts-empty" style="display: none;">
                    <div class="empty-state">
                        <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"
                            class="mb-3 text-muted">
                            <circle cx="11" cy="11" r="8"></circle>
                            <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                        </svg>
                        <h3>No posts found</h3>
                        <p>We couldn't find any posts matching your criteria. Try adjusting your search or filters.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>
<?php endif; ?>