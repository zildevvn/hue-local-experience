<?php
$duration = get_field('duration_car');
$scenic_stopovers = get_field('scenic_stopovers_car');
$pick_up = get_field('pick_up_car');
$drop_off = get_field('drop_off_car');
$driver = get_field('driver_car');
$group_size = get_field('group_size_car');
$original_price = get_field('original_price_car');
$discount_price = get_field('discount_price_car');
?>
<section class="hle-car-info hle-section">
    <div class="container">
        <?php if ($duration || $scenic_stopovers || $pick_up || $drop_off || $driver || $group_size): ?>
            <div class="car-info-card">
                <?php if ($pick_up): ?>
                    <div class="info-item">
                        <div class="info-icon">
                            <svg width="24px" height="24px" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                                <circle cx="12" cy="10" r="3"></circle>
                            </svg>
                        </div>
                        <div class="info-content">
                            <span class="info-label">Pick Up</span>
                            <span class="info-value"><?php echo esc_html($pick_up); ?></span>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if ($duration): ?>
                    <div class="info-item">
                        <div class="info-icon">
                            <svg width="24px" height="24px" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="12" r="10"></circle>
                                <polyline points="12 6 12 12 16 14"></polyline>
                            </svg>
                        </div>
                        <div class="info-content">
                            <span class="info-label">Duration</span>
                            <span class="info-value"><?php echo esc_html($duration); ?></span>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if ($group_size): ?>
                    <div class="info-item">
                        <div class="info-icon">
                            <svg width="24px" height="24px" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                                <circle cx="9" cy="7" r="4"></circle>
                                <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                                <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                            </svg>
                        </div>
                        <div class="info-content">
                            <span class="info-label">Group Size</span>
                            <span class="info-value"><?php echo esc_html($group_size) ?></span>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if ($drop_off): ?>
                    <div class="info-item">
                        <div class="info-icon">
                            <svg width="24px" height="24px" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                                <circle cx="12" cy="10" r="3"></circle>
                            </svg>
                        </div>
                        <div class="info-content">
                            <span class="info-label">Drop Off</span>
                            <span class="info-value "><?php echo $drop_off; ?></span>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Price -->
                <?php if ($original_price || $discount_price): ?>
                    <div class="info-item">
                        <div class="info-icon">
                            <svg width="24px" height="24px" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                                <circle cx="12" cy="10" r="3"></circle>
                            </svg>
                        </div>
                        <div class="info-content">
                            <span class="info-label">Price</span>
                            <span class="info-value ">
                                <?php if ($discount_price): ?>
                                    <span class="car-item__price-discount">
                                        <?= esc_html($discount_price) ?>$
                                    </span>
                                    <?php if ($original_price): ?>
                                        <span class="car-item__price-original">
                                            <?= esc_html($original_price) ?>$
                                        </span>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span class="car-item__price-discount">
                                        <?= esc_html($original_price) ?>$
                                    </span>
                                <?php endif; ?>
                            </span>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <?php
        $gallery_car = get_field('gallery_car');
        if ($gallery_car && is_array($gallery_car)):
            $image_count = count($gallery_car);
            ?>
            <div class="car-gallery">
                <div class="car-gallery-grid <?php echo 'grid-count-' . min($image_count, 5); ?>">
                    <?php
                    $display_count = min($image_count, 5); // Display up to 5 images
                
                    for ($i = 0; $i < $display_count; $i++):
                        $image = $gallery_car[$i];
                        $img_url = is_array($image) ? $image['url'] : (is_numeric($image) ? wp_get_attachment_url($image) : $image);
                        $img_alt = is_array($image) && !empty($image['alt']) ? $image['alt'] : get_the_title();
                        // Try to use 'large' size if available for the grid
                        $img_thumb = is_array($image) && isset($image['sizes']['large']) ? $image['sizes']['large'] : $img_url;
                        ?>
                        <div class="car-gallery-item">
                            <a href="<?php echo esc_url($img_url); ?>" data-fancybox="car-gallery"
                                data-caption="<?php echo esc_attr($img_alt); ?>">
                                <img src="<?php echo esc_url($img_thumb); ?>" alt="<?php echo esc_attr($img_alt); ?>"
                                    loading="lazy">
                                <div class="car-gallery-overlay"></div>
                            </a>
                        </div>
                    <?php endfor; ?>
                </div>

                <?php if ($image_count > 1): ?>
                    <button class="btn-view-all" onclick="document.querySelector('.car-gallery-item a').click();">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                            stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                            <circle cx="8.5" cy="8.5" r="1.5"></circle>
                            <polyline points="21 15 16 10 5 21"></polyline>
                        </svg>
                        View All Photos
                    </button>
                <?php endif; ?>

                <?php
                // Render the rest of the images hidden so Fancybox can loop them
                if ($image_count > 5):
                    for ($i = 5; $i < $image_count; $i++):
                        $image = $gallery_car[$i];
                        $img_url = is_array($image) ? $image['url'] : (is_numeric($image) ? wp_get_attachment_url($image) : $image);
                        $img_alt = is_array($image) && !empty($image['alt']) ? $image['alt'] : get_the_title();
                        ?>
                        <a href="<?php echo esc_url($img_url); ?>" data-fancybox="car-gallery"
                            data-caption="<?php echo esc_attr($img_alt); ?>" style="display: none;"></a>
                    <?php endfor;
                endif;
                ?>
            </div>
        <?php endif; ?>
    </div>
</section>