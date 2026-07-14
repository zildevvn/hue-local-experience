<?php
$heading = get_field('hd_posts_hp');
$sub_heading = get_field('sub_hd_posts_hp');

$args = array(
    'post_type' => 'post',
    'posts_per_page' => 3,
    'post_status' => 'publish',
);
$query = new WP_Query($args);

?>
<?php if ($query->have_posts()): ?>
    <section class="hle-section posts-section">
        <div class="container">
            <div class="section-heading">
                <?php if ($heading): ?>
                    <h2 class="hle-heading center hle-heading-animation"><?php echo esc_html($heading); ?></h2>
                <?php endif; ?>

                <?php if ($sub_heading): ?>
                    <p class="hle-sub-heading"><?php echo esc_html($sub_heading); ?></p>
                <?php endif; ?>
            </div>

            <div class="posts-section__list" data-aos="fade-up">
                <?php while ($query->have_posts()):
                    $query->the_post();
                    ?>

                    <a href="<?= the_permalink(); ?>" class="post-item" aria-label="read more <?= the_title(); ?>">
                        <div class="post-item__thumb">
                            <img src="<?= get_the_post_thumbnail_url(); ?>" alt="image for <?= the_title(); ?>">
                        </div>
                        <div class="post-item__date d-flex align-items-center gap-2">
                            <svg width="24px" height="24px" stroke-width="1.5" viewBox="0 0 24 24" fill="none"
                                xmlns="http://www.w3.org/2000/svg" color="#000000">
                                <path
                                    d="M15 4V2M15 4V6M15 4H10.5M3 10V19C3 20.1046 3.89543 21 5 21H19C20.1046 21 21 20.1046 21 19V10H3Z"
                                    stroke="#000000" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                                <path d="M3 10V6C3 4.89543 3.89543 4 5 4H7" stroke="#000000" stroke-width="1.5"
                                    stroke-linecap="round" stroke-linejoin="round"></path>
                                <path d="M7 2V6" stroke="#000000" stroke-width="1.5" stroke-linecap="round"
                                    stroke-linejoin="round"></path>
                                <path d="M21 10V6C21 4.89543 20.1046 4 19 4H18.5" stroke="#000000" stroke-width="1.5"
                                    stroke-linecap="round" stroke-linejoin="round"></path>
                            </svg>
                            <?= get_the_date('F j, Y'); ?>
                        </div>
                        <div class="post-item__content">
                            <h3 class="post-item__title h4">
                                <?= the_title(); ?>
                            </h3>

                            <div class="post-item__excerpt">
                                <?= the_excerpt(); ?>
                            </div>

                            <button class="hle-button" aria-label="read more <?= the_title(); ?>"> Read More </button>
                        </div>
                    </a>

                <?php endwhile; ?>
                <?php wp_reset_postdata(); ?>
            </div>

            <div class="d-flex justify-content-center hle-button-container">
                <a href="/blog/" class="hle-button" aria-label="View All Posts"> View All </a>
            </div>
        </div>
    </section>
<?php endif; ?>