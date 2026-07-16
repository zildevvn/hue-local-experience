<?php
$location_tour = get_field('location_tour');
$time_tour = get_field('time_tour');
$price_tour = get_field('price_tour');
$paxs_tours = get_field('paxs_tours');

$min_pax = isset($paxs_tours['min']) ? $paxs_tours['min'] : '';
$max_pax = isset($paxs_tours['max']) ? $paxs_tours['max'] : '';
?>
<section class="hle-tour-info hle-section">
    <div class="container">
        <?php if ($location_tour || $time_tour || $price_tour || ($min_pax && $max_pax)): ?>
            <div class="tour-info-card">
                <?php if ($location_tour): ?>
                    <div class="info-item">
                        <div class="info-icon">
                            <svg width="24px" height="24px" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                                <circle cx="12" cy="10" r="3"></circle>
                            </svg>
                        </div>
                        <div class="info-content">
                            <span class="info-label">Location</span>
                            <span class="info-value"><?php echo esc_html($location_tour); ?></span>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if ($time_tour): ?>
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
                            <span class="info-value"><?php echo esc_html($time_tour); ?></span>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if ($min_pax && $max_pax): ?>
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
                            <span class="info-value"><?php echo esc_html($min_pax) . '&ndash;' . esc_html($max_pax); ?>
                                guests</span>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if ($price_tour): ?>
                    <div class="info-item">
                        <div class="info-icon">
                            <svg width="24px" height="24px" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <line x1="12" y1="1" x2="12" y2="23"></line>
                                <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
                            </svg>
                        </div>
                        <div class="info-content">
                            <span class="info-label">Price</span>
                            <span class="info-value price-value">$<?php echo number_format((float) $price_tour, 2); ?></span>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <?php
        $gallery_tour = get_field('gallery_tour');
        if ($gallery_tour && is_array($gallery_tour)):
            ?>
            <div class="tour-gallery">
                <div class="tour-gallery-grid">
                    <?php
                    $image_count = count($gallery_tour);
                    $display_count = min($image_count, 4); // Display up to 4 images
                
                    for ($i = 0; $i < $display_count; $i++):
                        $image = $gallery_tour[$i];
                        $img_url = is_array($image) ? $image['url'] : (is_numeric($image) ? wp_get_attachment_url($image) : $image);
                        $img_alt = is_array($image) && !empty($image['alt']) ? $image['alt'] : get_the_title();
                        // Try to use 'large' size if available for the grid
                        $img_thumb = is_array($image) && isset($image['sizes']['large']) ? $image['sizes']['large'] : $img_url;
                        $extra_class = ($i === 3 && $image_count > 4) ? 'has-more' : '';
                        ?>
                        <div class="tour-gallery-item <?php echo $extra_class; ?>">
                            <a href="<?php echo esc_url($img_url); ?>" data-fancybox="tour-gallery"
                                data-caption="<?php echo esc_attr($img_alt); ?>">
                                <img src="<?php echo esc_url($img_thumb); ?>" alt="<?php echo esc_attr($img_alt); ?>"
                                    loading="lazy">
                                <?php if ($i === 3 && $image_count > 4): ?>
                                    <div class="tour-gallery-more">
                                        <span>+<?php echo $image_count - 4; ?> Photos</span>
                                    </div>
                                <?php endif; ?>
                            </a>
                        </div>
                    <?php endfor; ?>
                </div>

                <?php
                // Render the rest of the images hidden so Fancybox can loop them
                if ($image_count > 4):
                    for ($i = 4; $i < $image_count; $i++):
                        $image = $gallery_tour[$i];
                        $img_url = is_array($image) ? $image['url'] : (is_numeric($image) ? wp_get_attachment_url($image) : $image);
                        $img_alt = is_array($image) && !empty($image['alt']) ? $image['alt'] : get_the_title();
                        ?>
                        <a href="<?php echo esc_url($img_url); ?>" data-fancybox="tour-gallery"
                            data-caption="<?php echo esc_attr($img_alt); ?>" style="display: none;"></a>
                    <?php endfor;
                endif;
                ?>
            </div>
        <?php endif; ?>
    </div>
</section>