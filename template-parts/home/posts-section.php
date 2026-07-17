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

            <div class="posts-section__list post-grid" data-aos="fade-up">
                <?php while ($query->have_posts()):
                    $query->the_post();
                    ?>
                    <?php hle_post_item(); ?>
                <?php endwhile; ?>
                <?php wp_reset_postdata(); ?>
            </div>

            <div class="d-flex justify-content-center hle-button-container">
                <a href="/blog/" class="hle-button" aria-label="View All Posts"> View All </a>
            </div>
        </div>
    </section>
<?php endif; ?>