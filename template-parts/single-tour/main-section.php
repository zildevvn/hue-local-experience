<?php
$booking_form = get_field('form_booking', 'option');
$included_tour = get_field('included_tour');
$excluded_tour = get_field('excluded_tour');
$itinerary_tour = get_field('itinerary_tour');
?>
<section class="hle-section main-section">
    <div class="container">
        <div class="main-section-inner d-flex justify-content-between">
            <div class="main-section-left">
                <h2>Overview Tour</h2>
                <?php the_content() ?>

                <?php
                if (!empty($included_tour) || !empty($excluded_tour)):
                    ?>
                    <div class="tour-inclusions">
                        <?php if (!empty($included_tour)): ?>
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
                                    <?php foreach ($included_tour as $index => $item):
                                        $text = is_array($item) ? current($item) : $item;
                                        if (empty($text))
                                            continue;
                                        ?>
                                        <li data-aos="fade-up" data-aos-delay="<?php echo $index * 100; ?>">
                                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                                <polyline points="20 6 9 17 4 12"></polyline>
                                            </svg>
                                            <span><?php echo esc_html($text); ?></span>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($excluded_tour)): ?>
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
                                    <?php foreach ($excluded_tour as $index => $item):
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
                                            <span><?php echo esc_html($text); ?></span>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <?php if (!empty($itinerary_tour)): ?>
                    <h2>Itinerary</h2>
                    <div class="accordion-list">
                        <?php foreach ($itinerary_tour as $index => $item): ?>
                            <?php
                            $title = $item['title'];
                            $image = $item['image'];
                            $desc = $item['desc'];
                            ?>
                            <div class="accordion-item" data-aos="fade-up" data-aos-delay="<?= $key * 100 ?>">
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
                                            <rect class="horizontal-line" y="70" width="160" height="15" rx="7" ry="7"></rect>
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

            <div class="main-section-right">
                <div class="booking-form-wrapper">
                    <h2 class="h4">Booking Tour</h2>
                    <?php if ($booking_form)
                        echo do_shortcode($booking_form); ?>
                </div>
            </div>
        </div>
    </div>
</section>