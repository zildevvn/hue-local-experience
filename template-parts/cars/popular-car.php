<?php
$popular_car_rental = get_field('popular_car_rental');
$heading = $popular_car_rental['heading'] ?? '';
$options = $popular_car_rental['list_options'] ?? '';
$button = $popular_car_rental['button'] ?? '';
?>

<?php if (!empty($options)): ?>
    <section class="hle-section popular-car-section">
        <div class="container">
            <?php if (!empty($heading)): ?>
                <h2 class="hle-heading center text-center hle-heading-animation">
                    <?php echo $heading; ?>
                </h2>
            <?php endif; ?>

            <?php if (!empty($options)): ?>
                <div class="popular-car-price">
                    <div class="popular-car-price__table-wrapper">
                        <div class="popular-car-price__table">
                            <!-- Table header -->
                            <div class="popular-car-price__header popular-car-price__row popular-car-price__row--header">
                                <div class="popular-car-price__cell popular-car-price__cell--destination">
                                    Destination
                                </div>

                                <div class="popular-car-price__cell">
                                    Sedan
                                </div>

                                <div class="popular-car-price__cell">
                                    SUV
                                </div>

                                <div class="popular-car-price__cell">
                                    Van
                                </div>

                                <div class="popular-car-price__cell">
                                    35 Seats
                                </div>

                                <div class="popular-car-price__cell">
                                    45 Seats
                                </div>
                            </div>

                            <!-- Table content -->
                            <?php foreach ($options as $option): ?>
                                <div class="popular-car-price__row">
                                    <div class="popular-car-price__cell popular-car-price__cell--destination">
                                        <div class="popular-car-price__route-icon">
                                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none"
                                                xmlns="http://www.w3.org/2000/svg">
                                                <path
                                                    d="M12 21C12 21 19 15.5 19 9.5C19 5.36 15.86 2 12 2C8.14 2 5 5.36 5 9.5C5 15.5 12 21 12 21Z"
                                                    stroke="currentColor" stroke-width="1.8" stroke-linecap="round"
                                                    stroke-linejoin="round" />

                                                <circle cx="12" cy="9.5" r="2.5" stroke="currentColor" stroke-width="1.8" />
                                            </svg>
                                        </div>

                                        <div class="popular-car-price__route">
                                            <span class="popular-car-price__from">
                                                Hue City to
                                            </span>

                                            <strong class="popular-car-price__location">
                                                <?= esc_html($option['location'] ?? '') ?>
                                            </strong>
                                        </div>
                                    </div>

                                    <div class="popular-car-price__cell" data-label="Sedan">
                                        <span class="popular-car-price__value">
                                            <?= esc_html($option['sedan'] ?? 'Contact') ?>
                                        </span>
                                    </div>

                                    <div class="popular-car-price__cell" data-label="SUV">
                                        <span class="popular-car-price__value">
                                            <?= esc_html($option['suv'] ?? 'Contact') ?>
                                        </span>
                                    </div>

                                    <div class="popular-car-price__cell" data-label="Van">
                                        <span class="popular-car-price__value">
                                            <?= esc_html($option['van'] ?? 'Contact') ?>
                                        </span>
                                    </div>

                                    <div class="popular-car-price__cell" data-label="35 Seats">
                                        <span class="popular-car-price__value">
                                            <?= esc_html($option['35_seats'] ?? 'Contact') ?>
                                        </span>
                                    </div>

                                    <div class="popular-car-price__cell" data-label="45 Seats">
                                        <span class="popular-car-price__value">
                                            <?= esc_html($option['45_seats'] ?? 'Contact') ?>
                                        </span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="popular-car-price__footer">
                        <div class="popular-car-price__note">
                            <span class="popular-car-price__note-icon">i</span>

                            <span>
                                Prices may vary depending on travel time and additional requests.
                            </span>
                        </div>

                        <?php if (!empty($button['url'])): ?>
                            <a href="<?= esc_url($button['url']) ?>" target="<?= $button['target'] ?>" class="hle-button">
                                <?= esc_html($button['title']) ?>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>


        </div>
    </section>
<?php endif; ?>