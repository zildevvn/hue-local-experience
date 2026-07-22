<?php
$booking_form = get_field('form_car_booking', 'option');
$included_car = get_field('included_car');
$excluded_car = get_field('excluded_car');
$itinerary_car = get_field('itinerary_tour_car');
$highlights = get_field('highlights_car');
$tour_car_price = get_field('tour_car_price');
$sightseeing_stop = get_field('sightseeing_stop_car');
$routes_and_stop_over_car = get_field('routes_and_stop_over_car');
?>
<section class="hle-section main-section">
    <div class="container">
        <div class="main-section-inner d-flex justify-content-between">
            <div class="main-section-left">

                <?php
                // Build array of menu items to display dynamically
                $nav_items = [];

                // Overview (always exists if there's content or title)
                $nav_items['car-overview'] = [
                    'label' => __('Overview', 'hue-local-experience'),
                    'show' => true
                ];

                // Highlights
                $nav_items['car-highlights'] = [
                    'label' => __('Highlights', 'hue-local-experience'),
                    'show' => !empty($highlights)
                ];

                // Inclusions
                $nav_items['car-inclusions'] = [
                    'label' => __('Inclusions', 'hue-local-experience'),
                    'show' => (!empty($included_car) || !empty($excluded_car))
                ];

                // Prices
                $nav_items['car-prices'] = [
                    'label' => __('Prices', 'hue-local-experience'),
                    'show' => !empty($tour_car_price)
                ];

                // Itinerary
                $nav_items['car-itinerary'] = [
                    'label' => __('Itinerary', 'hue-local-experience'),
                    'show' => !empty($itinerary_car)
                ];

                // sightseeing_stop
                $nav_items['sightseeing-stop'] = [
                    'label' => __('Sightseeing Stop', 'hue-local-experience'),
                    'show' => !empty($itinerary_car)
                ];

                ?>

                <!-- Anchor Navigation Menu -->
                <nav class="anchor-nav" id="hle-anchor-nav"
                    aria-label="<?php esc_attr_e('car navigation', 'hue-local-experience'); ?>">
                    <div class="anchor-nav__inner">
                        <ul class="anchor-nav__list">
                            <?php foreach ($nav_items as $id => $item):
                                if (!$item['show'])
                                    continue;
                                ?>
                                <li class="anchor-nav__item">
                                    <a href="#<?php echo esc_attr($id); ?>" class="anchor-nav__link">
                                        <?php echo esc_html($item['label']); ?>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </nav>

                <div id="car-overview">
                    <h2 class="hle-heading hle-heading-animation">Overview car</h2>
                    <?php the_content() ?>
                </div>

                <?php if (!empty($highlights)): ?>
                    <div id="car-highlights">
                        <?= $highlights ?>
                    </div>
                <?php endif; ?>

                <?php
                if (!empty($included_car) || !empty($excluded_car)):
                    ?>
                    <div id="car-inclusions" class="car-inclusions">
                        <?php if (!empty($included_car)): ?>
                            <div class="inclusion-card card-included">
                                <h3>
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                        stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                                        <polyline points="22 4 12 14.01 9 11.01"></polyline>
                                    </svg>
                                    What's Included
                                </h3>
                                <ul>
                                    <?php foreach ($included_car as $index => $item):
                                        $text = is_array($item) ? current($item) : $item;
                                        if (empty($text))
                                            continue;
                                        ?>
                                        <li data-aos="fade-up" data-aos-delay="<?php echo $index * 100; ?>">
                                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                                <polyline points="20 6 9 17 4 12"></polyline>
                                            </svg>
                                            <span>
                                                <?php echo esc_html($text); ?>
                                            </span>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($excluded_car)): ?>
                            <div class="inclusion-card card-excluded">
                                <h3>
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                        stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                        <circle cx="12" cy="12" r="10"></circle>
                                        <line x1="15" y1="9" x2="9" y2="15"></line>
                                        <line x1="9" y1="9" x2="15" y2="15"></line>
                                    </svg>
                                    Not Included
                                </h3>
                                <ul>
                                    <?php foreach ($excluded_car as $index => $item):
                                        $text = is_array($item) ? current($item) : $item;
                                        if (empty($text))
                                            continue;
                                        ?>
                                        <li data-aos="fade-up" data-aos-delay="<?php echo $index * 100; ?>">
                                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                                <line x1="18" y1="6" x2="6" y2="18"></line>
                                                <line x1="6" y1="6" x2="18" y2="18"></line>
                                            </svg>
                                            <span>
                                                <?php echo esc_html($text); ?>
                                            </span>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>


                <?php if (!empty($tour_car_price)): ?>
                    <div id="car-prices" class="car-prices">
                        <h2 class="hle-heading hle-heading-animation">Car Prices</h2>
                        <div class="price-card private-price-card">

                            <div class="price-card__content">
                                <h3 class="price-card__title">Price (USD/Car)</h3>
                                <div class="price-card__grid">
                                    <?php
                                    $cars_map = [
                                        'sedan' => 'Sedan',
                                        'suv' => 'SUV',
                                        'van' => 'Van',
                                        'd-car' => 'D-Car',
                                    ];
                                    ?>


                                    <?php foreach ($cars_map as $key => $label): ?>
                                        <?php if (!empty($tour_car_price[$key])): ?>
                                            <div class="price-card__grid-item">
                                                <span class="price-card__grid-label">
                                                    <?= esc_html($label) ?>
                                                </span>
                                                <span class="price-card__grid-value">
                                                    <strong>
                                                        <?= esc_html($tour_car_price[$key]) ?>$
                                                    </strong>

                                                </span>
                                            </div>
                                            <?php
                                        endif;
                                    endforeach;
                                    ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <div id="car-itinerary">
                    <?php if (!empty($itinerary_car)): ?>
                        <h2 class="hle-heading hle-heading-animation">Itinerary</h2>
                        <div class="accordion-list">
                            <?php foreach ($itinerary_car as $index => $item): ?>
                                <?php
                                $title = $item['title'];
                                $image = $item['image'];
                                $desc = $item['desc'];
                                ?>
                                <div class="accordion-item" data-aos="fade-up" data-aos-delay="<?= $index * 100 ?>">
                                    <h3 class="accordion-question h6 d-flex align-items-center" tabindex="0" role="button"
                                        aria-expanded="false">
                                        <div class="icon d-flex align-items-center align-content-center justify-content-center">
                                            <svg width="24px" height="24px" stroke-width="1.5" viewBox="0 0 24 24" fill="none"
                                                xmlns="http://www.w3.org/2000/svg" color="#000000">
                                                <path
                                                    d="M7.90039 8.07954C7.90039 3.30678 15.4004 3.30682 15.4004 8.07955C15.4004 11.4886 11.9913 10.8067 11.9913 14.8976"
                                                    stroke="#000000" stroke-width="1.5" stroke-linecap="round"
                                                    stroke-linejoin="round"></path>
                                                <path d="M12 19.01L12.01 18.9989" stroke="#000000" stroke-width="1.5"
                                                    stroke-linecap="round" stroke-linejoin="round"></path>
                                            </svg>
                                        </div>

                                        <?= $title ?>

                                        <div class="arrow">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="plus" width="18" height="18"
                                                viewBox="0 0 160 160">
                                                <rect class="vertical-line" x="70" width="15" height="160" rx="7" ry="7"></rect>
                                                <rect class="horizontal-line" y="70" width="160" height="15" rx="7" ry="7">
                                                </rect>
                                            </svg>
                                        </div>
                                    </h3>
                                    <div class="accordion-answer">
                                        <?= $desc ?>

                                        <?php if (!empty($image)): ?>
                                            <img src="<?= $image ?>" alt="image for itinerary <?= $title ?>" />
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>


                <?php if (!empty($sightseeing_stop)): ?>
                    <div id="sightseeing-stop" class="sightseeing-stop">
                        <h2 class="hle-heading hle-heading-animation">
                            Add-ons Sightseeing Price
                        </h2>

                        <div class="sightseeing-stop__list">
                            <?php foreach ($sightseeing_stop as $index => $item): ?>
                                <article class="item-sightseeing-stop">
                                    <div class="item-sightseeing-stop__destination">
                                        <span class="item-sightseeing-stop__number">
                                            <?= esc_html(str_pad($index + 1, 2, '0', STR_PAD_LEFT)) ?>
                                        </span>

                                        <div class="item-sightseeing-stop__destination-content">
                                            <span class="item-sightseeing-stop__label">
                                                Sightseeing stop
                                            </span>

                                            <h3 class="item-sightseeing-stop__name">
                                                <?= esc_html($item['add_on'] ?? '') ?>
                                            </h3>
                                        </div>
                                    </div>

                                    <div class="item-sightseeing-stop__prices">
                                        <div class="item-sightseeing-stop__price">
                                            <span class="item-sightseeing-stop__vehicle">
                                                Sedan
                                            </span>

                                            <strong class="item-sightseeing-stop__value">
                                                <?= !empty($item['sedan'])
                                                    ? '+ ' . esc_html($item['sedan']) . '$'
                                                    : 'Contact' ?>
                                            </strong>
                                        </div>

                                        <div class="item-sightseeing-stop__price">
                                            <span class="item-sightseeing-stop__vehicle">
                                                SUV
                                            </span>

                                            <strong class="item-sightseeing-stop__value">
                                                <?= !empty($item['suv'])
                                                    ? '+ ' . esc_html($item['suv']) . '$'
                                                    : 'Contact' ?>
                                            </strong>
                                        </div>

                                        <div class="item-sightseeing-stop__price">
                                            <span class="item-sightseeing-stop__vehicle">
                                                Van
                                            </span>

                                            <strong class="item-sightseeing-stop__value">
                                                <?= !empty($item['van'])
                                                    ? '+ ' . esc_html($item['van']) . '$'
                                                    : 'Contact' ?>
                                            </strong>
                                        </div>
                                    </div>
                                </article>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>


                <div id="car-routes-stop-over">
                    <?php if (!empty($routes_and_stop_over_car)): ?>
                        <h2 class="hle-heading hle-heading-animation">Routes and Stop Over</h2>
                        <div class="accordion-list">
                            <?php foreach ($routes_and_stop_over_car as $index => $item): ?>
                                <?php
                                $title = $item['title'];
                                $image = $item['image'];
                                $desc = $item['desc'];
                                ?>
                                <div class="accordion-item" data-aos="fade-up" data-aos-delay="<?= $index * 100 ?>">
                                    <h3 class="accordion-question h6 d-flex align-items-center" tabindex="0" role="button"
                                        aria-expanded="false">
                                        <div class="icon d-flex align-items-center align-content-center justify-content-center">
                                            <svg width="24px" height="24px" viewBox="0 0 24 24" stroke-width="1.5" fill="none"
                                                xmlns="http://www.w3.org/2000/svg" color="#000000">
                                                <path d="M16 16.01L16.01 15.9989" stroke="#000000" stroke-width="1.5"
                                                    stroke-linecap="round" stroke-linejoin="round"></path>
                                                <path d="M6 16.01L6.01 15.9989" stroke="#000000" stroke-width="1.5"
                                                    stroke-linecap="round" stroke-linejoin="round"></path>
                                                <path d="M20 22V15V8M20 8H18L18 2H22V8H20Z" stroke="#000000" stroke-width="1.5"
                                                    stroke-linecap="round" stroke-linejoin="round"></path>
                                                <path
                                                    d="M16 20H2.6C2.26863 20 2 19.7314 2 19.4V12.6C2 12.2686 2.26863 12 2.6 12H16"
                                                    stroke="#000000" stroke-width="1.5" stroke-linecap="round"
                                                    stroke-linejoin="round"></path>
                                                <path d="M14 8H6M14 2H6C3.79086 2 2 3.79086 2 6V8" stroke="#000000"
                                                    stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                                                <path
                                                    d="M3.5 20V21.9C3.5 22.2314 3.76863 22.5 4.1 22.5H6.9C7.23137 22.5 7.5 22.2314 7.5 21.9V20"
                                                    stroke="#000000" stroke-width="1.5" stroke-linecap="round"
                                                    stroke-linejoin="round"></path>
                                                <path d="M14.5 20V21.9C14.5 22.2314 14.7686 22.5 15.1 22.5H16" stroke="#000000"
                                                    stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                                            </svg>

                                        </div>

                                        <?= $title ?>

                                        <div class="arrow">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="plus" width="18" height="18"
                                                viewBox="0 0 160 160">
                                                <rect class="vertical-line" x="70" width="15" height="160" rx="7" ry="7"></rect>
                                                <rect class="horizontal-line" y="70" width="160" height="15" rx="7" ry="7">
                                                </rect>
                                            </svg>
                                        </div>
                                    </h3>
                                    <div class="accordion-answer">
                                        <?= $desc ?>

                                        <?php if (!empty($image)): ?>
                                            <img src="<?= $image ?>" alt="image for itinerary <?= $title ?>" />
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <?php get_template_part('template-parts/single-car/review-section'); ?>
            </div>

            <div class="main-section-right">
                <div class="booking-form-wrapper">
                    <h2 id="car-booking-form" class="h4">Booking Request</h2>
                    <?php if ($booking_form)
                        echo do_shortcode($booking_form); ?>
                </div>
            </div>
        </div>
    </div>
</section>