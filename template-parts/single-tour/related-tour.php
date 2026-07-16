<?php

$current_post_id = get_the_ID();
$args = [
    'post_type' => 'tours',
    'posts_per_page' => 3,
    'post_status' => 'publish',
    'post__not_in' => [$current_post_id],
    'orderby' => 'rand',
];
$the_query = new WP_Query($args);
?>

<?php if ($the_query->have_posts()): ?>
    <section class="hle-section related-tour-section">
        <div class="container">
            <h2 class="hle-heading hle-heading-animation">Other Tours</h2>
            <div class="related-tour-section__list">
                <?php while ($the_query->have_posts()):
                    $the_query->the_post();
                    hle_tour_item();
                endwhile;
                wp_reset_postdata();
                ?>
            </div>
        </div>
    </section>
<?php endif; ?>