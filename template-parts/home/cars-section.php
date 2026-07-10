<?php
$heading = get_field('heading_car_hp');
$sub_heading = get_field('sub_heading_car_hp');


$args = array(
    'post_type' => 'cars',
    'posts_per_page' => 4,
    'post_status' => 'publish',
);
$query = new WP_Query($args);
?>


<?php if ($query->have_posts()): ?>
    <section class="hle-section cars-section">
        <div class="container">
            <div class="section-heading">
                <h2 class="hle-heading center">
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
                        $passengers = get_field('passengers_car');
                        $luggage = get_field('luggage_car');
                        $air_conditioning = get_field('air_conditioning_car');
                        ?>

                        <a class="car-item swiper-slide" href="<?= the_permalink(); ?>" aria-label="<?= the_title(); ?>">
                            <div class="car-item__thumb">
                                <img src="<?= get_the_post_thumbnail_url(); ?>" alt="<?= the_title(); ?>" loading="lazy">
                            </div>
                            <div class="car-item-content">
                                <h3 class="car-item__title h4">
                                    <?= the_title(); ?>
                                </h3>

                                <?php if (!empty($passengers)): ?>
                                    <div class="car-item-info passengers">
                                        <svg width="24px" height="24px" viewBox="0 0 24 24" stroke-width="1.5" fill="none"
                                            xmlns="http://www.w3.org/2000/svg" color="#000000">
                                            <path d="M7 18V17C7 14.2386 9.23858 12 12 12V12C14.7614 12 17 14.2386 17 17V18"
                                                stroke="#000000" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                            </path>
                                            <path d="M1 18V17C1 15.3431 2.34315 14 4 14V14" stroke="#000000" stroke-width="1.5"
                                                stroke-linecap="round" stroke-linejoin="round"></path>
                                            <path d="M23 18V17C23 15.3431 21.6569 14 20 14V14" stroke="#000000" stroke-width="1.5"
                                                stroke-linecap="round" stroke-linejoin="round"></path>
                                            <path
                                                d="M12 12C13.6569 12 15 10.6569 15 9C15 7.34315 13.6569 6 12 6C10.3431 6 9 7.34315 9 9C9 10.6569 10.3431 12 12 12Z"
                                                stroke="#000000" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                            </path>
                                            <path
                                                d="M4 14C5.10457 14 6 13.1046 6 12C6 10.8954 5.10457 10 4 10C2.89543 10 2 10.8954 2 12C2 13.1046 2.89543 14 4 14Z"
                                                stroke="#000000" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                            </path>
                                            <path
                                                d="M20 14C21.1046 14 22 13.1046 22 12C22 10.8954 21.1046 10 20 10C18.8954 10 18 10.8954 18 12C18 13.1046 18.8954 14 20 14Z"
                                                stroke="#000000" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                            </path>
                                        </svg>
                                        <?= $passengers ?> passengers
                                    </div>
                                <?php endif; ?>

                                <?php if (!empty($luggage)): ?>
                                    <div class="car-item-info luggage">
                                        <svg width="24px" height="24px" stroke-width="1.5" viewBox="0 0 24 24" fill="none"
                                            xmlns="http://www.w3.org/2000/svg" color="#000000">
                                            <path
                                                d="M8 7H4C2.89543 7 2 7.89543 2 9V19C2 20.1046 2.89543 21 4 21H20C21.1046 21 22 20.1046 22 19V9C22 7.89543 21.1046 7 20 7H16M8 7V3.6C8 3.26863 8.26863 3 8.6 3H15.4C15.7314 3 16 3.26863 16 3.6V7M8 7H16"
                                                stroke="#000000" stroke-width="1.5"></path>
                                        </svg>
                                        <?= $luggage ?> luggage
                                    </div>
                                <?php endif; ?>


                                <?php if (!empty($air_conditioning) && $air_conditioning): ?>
                                    <div class="car-item-info air-conditioning">
                                        <svg width="24px" height="24px" stroke-width="1.5" viewBox="0 0 24 24" fill="none"
                                            xmlns="http://www.w3.org/2000/svg" color="#000000">
                                            <path
                                                d="M3 7L6.5 9M21 17L17.5 15M12 12L6.5 9M12 12L6.5 15M12 12V5M12 12V18.5M12 12L17.5 15M12 12L17.5 9M12 2V5M12 22V18.5M21 7L17.5 9M3 17L6.5 15M6.5 9L3 10M6.5 9L6 5.5M6.5 15L3 14M6.5 15L6 18.5M12 5L9.5 4M12 5L14.5 4M12 18.5L14.5 20M12 18.5L9.5 20M17.5 15L18 18.5M17.5 15L21 14M17.5 9L21 10M17.5 9L18 5.5"
                                                stroke="#000000" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                            </path>
                                        </svg>

                                        Air conditioning
                                    </div>
                                <?php endif; ?>

                                <div class="hle-button">
                                    View More
                                    <svg width="24px" height="24px" viewBox="0 0 24 24" stroke-width="1.5" fill="none"
                                        xmlns="http://www.w3.org/2000/svg" color="#000000">
                                        <path d="M3 12L21 12M21 12L12.5 3.5M21 12L12.5 20.5" stroke="#000000" stroke-width="1.5"
                                            stroke-linecap="round" stroke-linejoin="round"></path>
                                    </svg>
                                </div>


                            </div>
                        </a>

                    <?php endwhile; ?>
                    <?php wp_reset_postdata(); ?>
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