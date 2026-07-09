<?php
$images = get_field('images_wcu_hp');
$experience_box = get_field('experience_box');
$descriptions = get_field('descriptions');
$features = get_field('features');
$quote = get_field('quote_box_hp');
$button = get_field('button_wcu_hp');
$phone = get_field('phone__wcu_hp');
?>
<section class="hle-section wcu-section">
    <div class="container">
        <div class="wcu-section-warp d-flex justify-content-between flex-wrap flex-lg-nowrap">
            <div class="wcu-section__left " data-aos="fade-up">
                <?php if ($images): ?>
                    <div class="wcu-images d-flex justify-content-between flex-wrap flex-sm-nowrap">
                        <div class="wcu-images__left ">
                            <?php if (!empty($images['image_large'])): ?>
                                <img class="image_large" src="<?php echo esc_url($images['image_large']); ?>"
                                    alt="image choose us large">
                            <?php endif; ?>

                            <?php if (!empty($experience_box)): ?>
                                <div class="experience-box d-flex align-items-center gap-3">
                                    <?php if (!empty($experience_box['icon'])): ?>
                                        <div class="experience-box__icon d-flex align-items-center justify-content-center">
                                            <img src="<?php echo esc_url($experience_box['icon']); ?>" alt="experience-box__icon">
                                        </div>
                                    <?php endif; ?>

                                    <div class="experience-box__content">
                                        <?php if (!empty($experience_box['number_text'])): ?>
                                            <p class="experience-box__number mb-0"> <?= $experience_box['number_text'] ?></p>
                                        <?php endif; ?>

                                        <?php if (!empty($experience_box['label'])): ?>
                                            <p class="experience-box__label mb-0"><?= $experience_box['label'] ?></p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="wcu-images__right ">
                            <?php if (!empty($images['image_circle'])): ?>
                                <div class="wcu-images__circle">
                                    <svg class="wgl-dashes inner-dashed-border animated-dashes">
                                        <rect rx="50%" ry="50%"> </rect>
                                    </svg>

                                    <img src="<?php echo esc_url($images['image_circle']); ?>" alt="image choose us circle">
                                <?php endif; ?>
                            </div>

                            <?php if (!empty($images['image_bottom'])): ?>
                                <img class="wcu-images__bottom" src="<?php echo esc_url($images['image_bottom']); ?>" alt="">
                            <?php endif; ?>
                        </div>


                    </div>
                <?php endif; ?>
            </div>
            <div class="wcu-section__right" data-aos="fade-up" data-aos-delay="150">
                <h2 class="hle-heading mb-0">Why Choose Us?</h2>

                <?php if ($descriptions): ?>
                    <p class="wcu-section__desc"><?= $descriptions ?></p>
                <?php endif; ?>

                <div class="wcu-section__right-warp d-flex gap-3 flex-wrap flex-sm-nowrap">
                    <?php if (!empty($features)): ?>
                        <div class="features">
                            <?php foreach ($features as $key => $feature): ?>
                                <div class="feature-item d-flex align-items-center gap-3">
                                    <?php if (!empty($feature['icon'])): ?>
                                        <div class="feature-item__icon d-flex align-items-center justify-content-center">
                                            <img src="<?php echo esc_url($feature['icon']); ?>" alt="feature-item__icon">
                                        </div>
                                    <?php endif; ?>

                                    <div class="feature-item__content">
                                        <?php if (!empty($feature['number'])): ?>
                                            <p class="feature-item__number h3 mb-0">
                                                <?= $feature['number'] ?>
                                            </p>
                                        <?php endif; ?>

                                        <?php if (!empty($feature['heading'])): ?>
                                            <h3 class="feature-item__heading mb-0 h6">
                                                <?= $feature['heading'] ?>
                                            </h3>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($quote)): ?>
                        <div class="quote-box d-flex align-items-center">
                            <?= $quote ?>

                            <svg width="24px" height="24px" viewBox="0 0 24 24" fill="none"
                                xmlns="http://www.w3.org/2000/svg" color="#000000" stroke-width="1.5">
                                <path fill-rule="evenodd" clip-rule="evenodd"
                                    d="M9.21255 12.75C9.12943 13.5242 8.9054 14.1421 8.5147 14.6891C7.99181 15.4211 7.11571 16.1036 5.66459 16.8292C5.29411 17.0144 5.14394 17.4649 5.32918 17.8354C5.51442 18.2059 5.96493 18.3561 6.33541 18.1708C7.88429 17.3964 9.00819 16.5789 9.7353 15.5609C10.4761 14.5238 10.75 13.3571 10.75 12V7.5C10.75 6.53351 9.96649 5.75 9 5.75H5C4.03351 5.75 3.25 6.53351 3.25 7.5V11C3.25 11.9665 4.03352 12.75 5 12.75H9.21255Z"
                                    fill="#000000"></path>
                                <path fill-rule="evenodd" clip-rule="evenodd"
                                    d="M19.2125 12.75C19.1294 13.5242 18.9054 14.1421 18.5147 14.6891C17.9918 15.4211 17.1157 16.1036 15.6646 16.8292C15.2941 17.0144 15.1439 17.4649 15.3292 17.8354C15.5144 18.2059 15.9649 18.3561 16.3354 18.1708C17.8843 17.3964 19.0082 16.5789 19.7353 15.5609C20.4761 14.5238 20.75 13.3571 20.75 12V7.5C20.75 6.53352 19.9665 5.75 19 5.75H15C14.0335 5.75 13.25 6.53352 13.25 7.5V11C13.25 11.9665 14.0335 12.75 15 12.75H19.2125Z"
                                    fill="#000000"></path>
                            </svg>
                        </div>
                    <?php endif; ?>
                </div>

                <?php if (!empty($button) || !empty($phone)): ?>
                    <div class="wcu-section__right-footer d-flex align-items-center gap-4">
                        <?php if (!empty($button)): ?>
                            <a href="<?= $button['url'] ?>" class="hle-button hle-button--primary">
                                <?= $button['title'] ?>
                            </a>
                        <?php endif; ?>

                        <?php if (!empty($phone)): ?>
                            <a href="tel:<?= $phone ?>"
                                class="wcu-section__right-footer__phone d-flex align-items-center gap-2">
                                <div class="icon">
                                    <svg width="24px" height="24px" viewBox="0 0 24 24" stroke-width="1.5" fill="none"
                                        xmlns="http://www.w3.org/2000/svg" color="#000000">
                                        <path fill-rule="evenodd" clip-rule="evenodd"
                                            d="M22.5303 5.53033C22.8232 5.23744 22.8232 4.76256 22.5303 4.46967L19.5303 1.46967C19.2374 1.17678 18.7626 1.17678 18.4697 1.46967C18.1768 1.76256 18.1768 2.23744 18.4697 2.53033L20.1893 4.25H16C15.5858 4.25 15.25 4.58579 15.25 5C15.25 5.41421 15.5858 5.75 16 5.75H20.1893L18.4697 7.46967C18.1768 7.76256 18.1768 8.23744 18.4697 8.53033C18.7626 8.82322 19.2374 8.82322 19.5303 8.53033L22.5303 5.53033Z"
                                            fill="#000000"></path>
                                        <path fill-rule="evenodd" clip-rule="evenodd"
                                            d="M4.06343 1.25L7.81435 1.25C8.12672 1.25 8.40638 1.44361 8.51634 1.73599L9.97178 5.60588C10.02 5.73398 10.0322 5.87281 10.0071 6.00735L9.2778 9.91931C10.1742 12.0273 11.6548 13.4439 14.1104 14.7146L17.9754 13.9657C18.1126 13.9391 18.2545 13.9514 18.3851 14.0012L22.2669 15.4804C22.5577 15.5912 22.7498 15.87 22.7498 16.1812L22.7498 19.7655C22.7498 21.391 21.3176 22.7101 19.6424 22.3456C16.5888 21.6811 10.9315 19.9923 6.9695 16.0303C3.17436 12.2352 1.90282 6.99252 1.47478 4.15869C1.23055 2.54172 2.52735 1.25 4.06343 1.25Z"
                                            fill="#000000"></path>
                                    </svg>
                                </div>

                                <div class="content">
                                    <p class="mb-0 h6"> Call Now </p>
                                    <p class="mb-0"> <?= $phone ?> </p>
                                </div>
                            </a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>