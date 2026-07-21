<?php
$heading = get_field('heading_car_hp');
$sub_heading = get_field('sub_heading_car_hp');

$args = array(
    'post_type' => 'cars',
    'posts_per_page' => 8,
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
    <section class="hle-section cars-section">
        <div class="container">
            <div class="section-heading">
                <h2 class="hle-heading center hle-heading-animation">
                    <?= $heading ?? '' ?>
                </h2>
                <p class="hle-sub-heading">
                    <?= $sub_heading ?? '' ?>
                </p>
            </div>

            <div class="cars-section__list swiper" data-aos="fade-up">
                <div class="swiper-wrapper">
                    <?php while ($query->have_posts()):
                        $query->the_post();
                        hle_car_tour_item('swiper-slide');
                    endwhile;
                    wp_reset_postdata();
                    ?>
                </div>

                <div class="swiper-button-prev">
                    <svg width="24px" height="24px" viewBox="0 0 24 24" stroke-width="1.5" fill="none"
                        xmlns="http://www.w3.org/2000/svg" color="#000000">
                        <path d="M21 12L3 12M3 12L11.5 3.5M3 12L11.5 20.5" stroke="#000000" stroke-width="1.5"
                            stroke-linecap="round" stroke-linejoin="round"></path>
                    </svg>
                </div>

                <div class="swiper-button-next">
                    <svg width="24px" height="24px" viewBox="0 0 24 24" stroke-width="1.5" fill="none"
                        xmlns="http://www.w3.org/2000/svg" color="#000000">
                        <path d="M3 12L21 12M21 12L12.5 3.5M21 12L12.5 20.5" stroke="#000000" stroke-width="1.5"
                            stroke-linecap="round" stroke-linejoin="round"></path>
                    </svg>
                </div>

                <div class="swiper-pagination"></div>
            </div>

            <div class="d-flex justify-content-center hle-button-container">
                <a class="hle-button" href="/cars" role="button"> View All</a>
            </div>
        </div>
    </section>

<?php endif; ?>