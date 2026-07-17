<?php

function hle_post_item()
{ ?>
    <a href="<?= the_permalink(); ?>" class="post-item" aria-label="read more <?= the_title(); ?>">
        <div class="post-item__thumb">
            <img src="<?= get_the_post_thumbnail_url(); ?>" alt="image for <?= the_title(); ?>">
        </div>
        <div class="post-item__date d-flex align-items-center gap-2">
            <svg width="24px" height="24px" stroke-width="1.5" viewBox="0 0 24 24" fill="none"
                xmlns="http://www.w3.org/2000/svg" color="#000000">
                <path d="M15 4V2M15 4V6M15 4H10.5M3 10V19C3 20.1046 3.89543 21 5 21H19C20.1046 21 21 20.1046 21 19V10H3Z"
                    stroke="#000000" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                <path d="M3 10V6C3 4.89543 3.89543 4 5 4H7" stroke="#000000" stroke-width="1.5" stroke-linecap="round"
                    stroke-linejoin="round"></path>
                <path d="M7 2V6" stroke="#000000" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                <path d="M21 10V6C21 4.89543 20.1046 4 19 4H18.5" stroke="#000000" stroke-width="1.5" stroke-linecap="round"
                    stroke-linejoin="round"></path>
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
<?php }

function hle_breadcrumbs($title)
{
    ?>
    <p class="hle-breadcrumbs d-flex align-items-center justify-content-center mb-0">
        <a href="<?= home_url() ?>">Home</a>
        <span>
            <svg width="24px" height="24px" viewBox="0 0 24 24" stroke-width="1.5" fill="none"
                xmlns="http://www.w3.org/2000/svg" color="#000000">
                <path d="M13 6L19 12L13 18" stroke="#000000" stroke-width="1.5" stroke-linecap="round"
                    stroke-linejoin="round"></path>
                <path d="M5 6L11 12L5 18" stroke="#000000" stroke-width="1.5" stroke-linecap="round"
                    stroke-linejoin="round"></path>
            </svg>
        </span>

        <?php if (is_singular('tours')): ?>
            <a href="<?= home_url('/hue-experience-all-tour') ?>">Tours</a>
            <span>
                <svg width="24px" height="24px" viewBox="0 0 24 24" stroke-width="1.5" fill="none"
                    xmlns="http://www.w3.org/2000/svg" color="#000000">
                    <path d="M13 6L19 12L13 18" stroke="#000000" stroke-width="1.5" stroke-linecap="round"
                        stroke-linejoin="round"></path>
                    <path d="M5 6L11 12L5 18" stroke="#000000" stroke-width="1.5" stroke-linecap="round"
                        stroke-linejoin="round"></path>
                </svg>
            </span>
        <?php endif; ?>

        <?= $title ?>
    </p>
<?php }


function hle_hero_section_shared($title = '', $image = '')
{ ?>
    <section class="hle-section hero-section-shared">
        <div class="hle-section__bg">
            <img src="<?= $image ?>" alt="background hero for <?= $title ?>" />
        </div>

        <div class="container">
            <div class="hero-section-shared__box">
                <h1 class="hle-heading-animation"><?= $title ?></h1>
                <?php hle_breadcrumbs($title) ?>
            </div>
        </div>
    </section>
<?php }


function hle_tour_item()
{
    $location_tour = get_field('location_tour');
    $time_tour = get_field('time_tour');
    $paxs_tours = get_field('paxs_tours');
    $price_tours = get_field('price_tour');
    $min_pax = $paxs_tours['min'] ?? '';
    $max_pax = $paxs_tours['max'] ?? '';
    ?>

    <a href="<?= the_permalink(); ?>" class="tour-item" aria-label="<?= the_title(); ?>">
        <div class="tour-item__thumb">
            <div class="icon d-flex align-items-center align-content-center justify-content-center">
                <svg width="24px" height="24px" stroke-width="1.5" viewBox="0 0 24 24" fill="none"
                    xmlns="http://www.w3.org/2000/svg" color="#000000">
                    <path
                        d="M14 11.9976C14 9.5059 11.683 7 8.85714 7C8.52241 7 7.41904 7.00001 7.14286 7.00001C4.30254 7.00001 2 9.23752 2 11.9976C2 14.376 3.70973 16.3664 6 16.8714C6.36756 16.9525 6.75006 16.9952 7.14286 16.9952"
                        stroke="#000000" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                    </path>
                    <path
                        d="M10 11.9976C10 14.4893 12.317 16.9952 15.1429 16.9952C15.4776 16.9952 16.581 16.9952 16.8571 16.9952C19.6975 16.9952 22 14.7577 22 11.9976C22 9.6192 20.2903 7.62884 18 7.12383C17.6324 7.04278 17.2499 6.99999 16.8571 6.99999"
                        stroke="#000000" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                    </path>
                </svg>
            </div>
            <img src="<?= get_the_post_thumbnail_url(); ?>" alt="<?= the_title(); ?>">

            <div class="tour-item__category d-flex align-items-center align-content-center justify-content-center gap-2">
                <svg width="24px" height="24px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"
                    color="#000000" stroke-width="1.5">
                    <path
                        d="M5 21V5C5 3.89543 5.89543 3 7 3H17C18.1046 3 19 3.89543 19 5V21L13.0815 17.1953C12.4227 16.7717 11.5773 16.7717 10.9185 17.1953L5 21Z"
                        fill="#000000" stroke="#000000" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                    </path>
                </svg>
                <?php
                $terms = get_the_terms(get_the_ID(), 'tour_cats');
                if ($terms && !is_wp_error($terms)) {
                    foreach ($terms as $term) {
                        echo $term->name;
                    }
                }
                ?>
            </div>
        </div>
        <div class="tour-item-content">
            <div class="tour-item-meta d-flex align-items-center justify-content-between">
                <?php if (!empty($location_tour) && isset($location_tour)): ?>
                    <span class="location d-flex align-items-center gap-2">
                        <svg width="24px" height="24px" stroke-width="1.5" viewBox="0 0 24 24" fill="none"
                            xmlns="http://www.w3.org/2000/svg" color="#000000">
                            <path
                                d="M9 19L3.78974 20.7368C3.40122 20.8663 3 20.5771 3 20.1675L3 5.43246C3 5.1742 3.16526 4.94491 3.41026 4.86325L9 3M9 19L15 21M9 19L9 3M15 21L20.5897 19.1368C20.8347 19.0551 21 18.8258 21 18.5675L21 3.83246C21 3.42292 20.5988 3.13374 20.2103 3.26325L15 5M15 21L15 5M15 5L9 3"
                                stroke="#000000" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                            </path>
                        </svg>
                        <?= $location_tour ?>
                    </span>
                <?php endif; ?>

                <div class="tour-item__rating d-flex align-items-center justify-content-end">
                    <svg width="24px" height="24px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"
                        color="#000000" stroke-width="1.5">
                        <path
                            d="M8.58737 8.23597L11.1849 3.00376C11.5183 2.33208 12.4817 2.33208 12.8151 3.00376L15.4126 8.23597L21.2215 9.08017C21.9668 9.18848 22.2638 10.0994 21.7243 10.6219L17.5217 14.6918L18.5135 20.4414C18.6409 21.1798 17.8614 21.7428 17.1945 21.3941L12 18.678L6.80547 21.3941C6.1386 21.7428 5.35909 21.1798 5.48645 20.4414L6.47825 14.6918L2.27575 10.6219C1.73617 10.0994 2.03322 9.18848 2.77852 9.08017L8.58737 8.23597Z"
                            fill="#000000" stroke="#000000" stroke-width="1.5" stroke-linecap="round"
                            stroke-linejoin="round"></path>
                    </svg>

                    <svg width="24px" height="24px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"
                        color="#000000" stroke-width="1.5">
                        <path
                            d="M8.58737 8.23597L11.1849 3.00376C11.5183 2.33208 12.4817 2.33208 12.8151 3.00376L15.4126 8.23597L21.2215 9.08017C21.9668 9.18848 22.2638 10.0994 21.7243 10.6219L17.5217 14.6918L18.5135 20.4414C18.6409 21.1798 17.8614 21.7428 17.1945 21.3941L12 18.678L6.80547 21.3941C6.1386 21.7428 5.35909 21.1798 5.48645 20.4414L6.47825 14.6918L2.27575 10.6219C1.73617 10.0994 2.03322 9.18848 2.77852 9.08017L8.58737 8.23597Z"
                            fill="#000000" stroke="#000000" stroke-width="1.5" stroke-linecap="round"
                            stroke-linejoin="round"></path>
                    </svg>


                    <svg width="24px" height="24px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"
                        color="#000000" stroke-width="1.5">
                        <path
                            d="M8.58737 8.23597L11.1849 3.00376C11.5183 2.33208 12.4817 2.33208 12.8151 3.00376L15.4126 8.23597L21.2215 9.08017C21.9668 9.18848 22.2638 10.0994 21.7243 10.6219L17.5217 14.6918L18.5135 20.4414C18.6409 21.1798 17.8614 21.7428 17.1945 21.3941L12 18.678L6.80547 21.3941C6.1386 21.7428 5.35909 21.1798 5.48645 20.4414L6.47825 14.6918L2.27575 10.6219C1.73617 10.0994 2.03322 9.18848 2.77852 9.08017L8.58737 8.23597Z"
                            fill="#000000" stroke="#000000" stroke-width="1.5" stroke-linecap="round"
                            stroke-linejoin="round"></path>
                    </svg>

                    <svg width="24px" height="24px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"
                        color="#000000" stroke-width="1.5">
                        <path
                            d="M8.58737 8.23597L11.1849 3.00376C11.5183 2.33208 12.4817 2.33208 12.8151 3.00376L15.4126 8.23597L21.2215 9.08017C21.9668 9.18848 22.2638 10.0994 21.7243 10.6219L17.5217 14.6918L18.5135 20.4414C18.6409 21.1798 17.8614 21.7428 17.1945 21.3941L12 18.678L6.80547 21.3941C6.1386 21.7428 5.35909 21.1798 5.48645 20.4414L6.47825 14.6918L2.27575 10.6219C1.73617 10.0994 2.03322 9.18848 2.77852 9.08017L8.58737 8.23597Z"
                            fill="#000000" stroke="#000000" stroke-width="1.5" stroke-linecap="round"
                            stroke-linejoin="round"></path>
                    </svg>


                    <svg width="24px" height="24px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"
                        color="#000000" stroke-width="1.5">
                        <path
                            d="M8.58737 8.23597L11.1849 3.00376C11.5183 2.33208 12.4817 2.33208 12.8151 3.00376L15.4126 8.23597L21.2215 9.08017C21.9668 9.18848 22.2638 10.0994 21.7243 10.6219L17.5217 14.6918L18.5135 20.4414C18.6409 21.1798 17.8614 21.7428 17.1945 21.3941L12 18.678L6.80547 21.3941C6.1386 21.7428 5.35909 21.1798 5.48645 20.4414L6.47825 14.6918L2.27575 10.6219C1.73617 10.0994 2.03322 9.18848 2.77852 9.08017L8.58737 8.23597Z"
                            fill="#000000" stroke="#000000" stroke-width="1.5" stroke-linecap="round"
                            stroke-linejoin="round"></path>
                    </svg>
                </div>
            </div>

            <h3 class="h4">
                <?= the_title(); ?>
            </h3>

            <div class="tour-item__excerpt">
                <?= the_excerpt(); ?>
            </div>

            <?php if (!empty($time_tour) || !empty($paxs_tours) || !empty($price_tours)): ?>
                <div class="tour-item-info d-flex align-content-center justify-content-between">
                    <?php if (!empty($time_tour)): ?>
                        <div class="tour-item-info__time d-flex align-items-center ">
                            <svg width="24px" height="24px" stroke-width="1.5" viewBox="0 0 24 24" fill="none"
                                xmlns="http://www.w3.org/2000/svg" color="#000000">
                                <path d="M17 13H12V8" stroke="#000000" stroke-width="1.5" stroke-linecap="round"
                                    stroke-linejoin="round"></path>
                                <path d="M5 3.5L7 2" stroke="#000000" stroke-width="1.5" stroke-linecap="round"
                                    stroke-linejoin="round"></path>
                                <path d="M19 3.5L17 2" stroke="#000000" stroke-width="1.5" stroke-linecap="round"
                                    stroke-linejoin="round"></path>
                                <path
                                    d="M12 22C16.9706 22 21 17.9706 21 13C21 8.02944 16.9706 4 12 4C7.02944 4 3 8.02944 3 13C3 17.9706 7.02944 22 12 22Z"
                                    stroke="#000000" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                            </svg>
                            <?= $time_tour ?>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($min_pax) && !empty($max_pax)): ?>
                        <div class="tour-item-info__paxs d-flex align-items-center ">
                            <svg width="24px" height="24px" viewBox="0 0 24 24" stroke-width="1.5" fill="none"
                                xmlns="http://www.w3.org/2000/svg" color="#000000">
                                <path d="M7 18V17C7 14.2386 9.23858 12 12 12V12C14.7614 12 17 14.2386 17 17V18" stroke="#000000"
                                    stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                                <path d="M1 18V17C1 15.3431 2.34315 14 4 14V14" stroke="#000000" stroke-width="1.5"
                                    stroke-linecap="round" stroke-linejoin="round"></path>
                                <path d="M23 18V17C23 15.3431 21.6569 14 20 14V14" stroke="#000000" stroke-width="1.5"
                                    stroke-linecap="round" stroke-linejoin="round"></path>
                                <path
                                    d="M12 12C13.6569 12 15 10.6569 15 9C15 7.34315 13.6569 6 12 6C10.3431 6 9 7.34315 9 9C9 10.6569 10.3431 12 12 12Z"
                                    stroke="#000000" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                                <path
                                    d="M4 14C5.10457 14 6 13.1046 6 12C6 10.8954 5.10457 10 4 10C2.89543 10 2 10.8954 2 12C2 13.1046 2.89543 14 4 14Z"
                                    stroke="#000000" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                                <path
                                    d="M20 14C21.1046 14 22 13.1046 22 12C22 10.8954 21.1046 10 20 10C18.8954 10 18 10.8954 18 12C18 13.1046 18.8954 14 20 14Z"
                                    stroke="#000000" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                            </svg>
                            <?= $min_pax . ' - ' . $max_pax ?> Pax
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($price_tours)): ?>
                        <div class="tour-item-info__price d-flex align-content-center">
                            $<?= $price_tours ?>
                        </div>
                    <?php endif; ?>

                </div>
            <?php endif; ?>
        </div>
    </a>
<?php }