<?php
$image = get_the_post_thumbnail_url();
?>
<section class="hle-section main-section">
    <div class="container">
        <div class="main-section-inner d-flex justify-content-between">
            <div class="main-section-left">
                <div class="main-section-left__thumb">
                    <img src="<?= $image ?>" alt="image <?= the_title() ?>">
                </div>

                <div class="main-section-left__info post-meta d-flex flex-wrap align-items-center gap-4">
                    <div class="post-meta__item d-flex align-items-center gap-2">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                            <circle cx="12" cy="7" r="4"></circle>
                        </svg>
                        <span>By <a
                                href="<?php echo esc_url(get_author_posts_url(get_the_author_meta('ID'))); ?>"><?php echo esc_html(get_the_author()); ?></a></span>
                    </div>

                    <div class="post-meta__item d-flex align-items-center gap-2">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                            <line x1="16" y1="2" x2="16" y2="6"></line>
                            <line x1="8" y1="2" x2="8" y2="6"></line>
                            <line x1="3" y1="10" x2="21" y2="10"></line>
                        </svg>
                        <span><?php echo esc_html(get_the_date()); ?></span>
                    </div>

                    <?php if (comments_open()): ?>
                        <div class="post-meta__item d-flex align-items-center gap-2">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                            </svg>
                            <span>
                                <?php
                                $comments_number = get_comments_number();
                                if ($comments_number == 0) {
                                    echo 'No Comments';
                                } elseif ($comments_number == 1) {
                                    echo '1 Comment';
                                } else {
                                    echo sprintf('%s Comments', number_format_i18n($comments_number));
                                }
                                ?>
                            </span>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="main-section-left__content">
                    <?php
                    the_content();
                    ?>
                </div>

                <?php
                comments_template();
                ?>
            </div>
            <div class="main-section-right sidebar">
                <div class="sidebar-widget widget-search">
                    <h3 class="widget-title">Search</h3>
                    <form role="search" method="get" class="search-form" action="<?php echo esc_url(home_url('/')); ?>">
                        <div class="search-input-wrapper">
                            <input type="search" class="search-field form-control" placeholder="Search..."
                                value="<?php echo get_search_query(); ?>" name="s" title="Search for:" />
                            <button type="submit" class="search-submit" aria-label="Submit search">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <circle cx="11" cy="11" r="8"></circle>
                                    <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                                </svg>
                            </button>
                        </div>
                    </form>
                </div>

                <div class="sidebar-widget widget-recent-posts">
                    <h3 class="widget-title">Recent Posts</h3>
                    <div class="recent-posts-list">
                        <?php
                        $recent_args = array(
                            'post_type' => 'post',
                            'posts_per_page' => 4,
                            'post_status' => 'publish',
                            'post__not_in' => array(get_the_ID()),
                            'ignore_sticky_posts' => true
                        );
                        $recent_query = new WP_Query($recent_args);

                        if ($recent_query->have_posts()):
                            while ($recent_query->have_posts()):
                                $recent_query->the_post();
                                ?>
                                <div class="recent-post-item d-flex align-items-center gap-3">
                                    <div class="recent-post-item__thumb">
                                        <a href="<?php the_permalink(); ?>">
                                            <?php if (has_post_thumbnail()): ?>
                                                <?php the_post_thumbnail('thumbnail'); ?>
                                            <?php else: ?>
                                                <div class="placeholder-thumb"></div>
                                            <?php endif; ?>
                                        </a>
                                    </div>
                                    <div class="recent-post-item__info">
                                        <h4 class="recent-post-item__title">
                                            <a href="<?php the_permalink(); ?>"><?php echo esc_html(get_the_title()); ?></a>
                                        </h4>
                                        <div class="recent-post-item__date d-flex align-items-center gap-2">
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                                                <line x1="16" y1="2" x2="16" y2="6"></line>
                                                <line x1="8" y1="2" x2="8" y2="6"></line>
                                                <line x1="3" y1="10" x2="21" y2="10"></line>
                                            </svg>
                                            <span><?php echo esc_html(get_the_date()); ?></span>
                                        </div>
                                    </div>
                                </div>
                                <?php
                            endwhile;
                            wp_reset_postdata();
                        else:
                            ?>
                            <p>No recent posts found.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>