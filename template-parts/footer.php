<?php
$about_text_ft = get_field('about_text_ft', 'option');
$address = get_field('address', 'option');
$email = get_field('email', 'option');
$phone = get_field('phone', 'option');
$copyright = get_field('copyright_ft', 'option');
$socials = get_field('socials', 'option');
?>



<footer class="main-footer">
    <div class="main-footer-top">
        <div class="main-footer-top__bg">
            <img src="<?php echo get_template_directory_uri(); ?>/assets/images/bg-footer.png" alt="image for footer" />
        </div>
        <div class="container">
            <div class="main-footer-inner">
                <?php if (!empty($about_text_ft)): ?>
                    <div class="main-footer-widget">
                        <h2 class="widget-title">About</h2>
                        <div class="about-ft"><?= $about_text_ft ?></div>
                    </div>
                <?php endif; ?>


                <?php if (has_nav_menu('company-menu')): ?>
                    <div class="main-footer-widget">
                        <h2 class="widget-title">Company</h2>
                        <div class="widget-menu">
                            <?php wp_nav_menu(array('theme_location' => 'company-menu', 'menu_class' => 'company-menu')) ?>
                        </div>
                    </div>
                <?php endif; ?>


                <?php if (has_nav_menu('tours-menu')): ?>
                    <div class="main-footer-widget">
                        <h2 class="widget-title">Tour</h2>
                        <div class="widget-menu">
                            <?php wp_nav_menu(array('theme_location' => 'tours-menu', 'menu_class' => 'tours-menu')) ?>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="main-footer-widget">
                    <h2 class="widget-title">Contact</h2>

                    <div class="contact-list">
                        <?php if (!empty($address)): ?>
                            <p class="d-flex align-items-center">
                                <svg width="24px" height="24px" stroke-width="1.5" viewBox="0 0 24 24" fill="none"
                                    xmlns="http://www.w3.org/2000/svg" color="#000000">
                                    <path
                                        d="M20 10C20 14.4183 12 22 12 22C12 22 4 14.4183 4 10C4 5.58172 7.58172 2 12 2C16.4183 2 20 5.58172 20 10Z"
                                        stroke="#000000" stroke-width="1.5"></path>
                                    <path
                                        d="M12 11C12.5523 11 13 10.5523 13 10C13 9.44772 12.5523 9 12 9C11.4477 9 11 9.44772 11 10C11 10.5523 11.4477 11 12 11Z"
                                        fill="#000000" stroke="#000000" stroke-width="1.5" stroke-linecap="round"
                                        stroke-linejoin="round"></path>
                                </svg>
                                <?= $address ?>
                            </p>
                        <?php endif; ?>

                        <?php if (!empty($phone)): ?>
                            <a class="d-flex align-items-center" href="tel:<?= $phone ?>">
                                <svg width="24px" height="24px" viewBox="0 0 24 24" stroke-width="1.5" fill="none"
                                    xmlns="http://www.w3.org/2000/svg" color="#000000">
                                    <path fill-rule="evenodd" clip-rule="evenodd"
                                        d="M4.06343 1.25L7.81435 1.25C8.12672 1.25 8.40638 1.44361 8.51634 1.73599L9.97178 5.60588C10.02 5.73398 10.0322 5.87281 10.0071 6.00735L9.2778 9.91931C10.1742 12.0273 11.6548 13.4439 14.1104 14.7146L17.9754 13.9657C18.1126 13.9391 18.2545 13.9514 18.3851 14.0012L22.2669 15.4804C22.5577 15.5912 22.7498 15.87 22.7498 16.1812L22.7498 19.7655C22.7498 21.391 21.3176 22.7101 19.6424 22.3456C16.5888 21.6811 10.9315 19.9923 6.9695 16.0303C3.17436 12.2352 1.90282 6.99252 1.47478 4.15869C1.23055 2.54172 2.52735 1.25 4.06343 1.25Z"
                                        fill="#000000"></path>
                                </svg>
                                <?= $phone ?>
                            </a>
                        <?php endif; ?>

                        <?php if (!empty($email)): ?>
                            <a class="d-flex align-items-center" href="mailto:<?= $email ?>">
                                <svg width="24px" height="24px" viewBox="0 0 24 24" fill="none"
                                    xmlns="http://www.w3.org/2000/svg" color="#000000" stroke-width="1.5">
                                    <path fill-rule="evenodd" clip-rule="evenodd"
                                        d="M4 4.25C2.48122 4.25 1.25 5.48122 1.25 7V17C1.25 18.5188 2.48122 19.75 4 19.75H20C21.5188 19.75 22.75 18.5188 22.75 17V7C22.75 5.48122 21.5188 4.25 20 4.25H4ZM7.4301 8.38558C7.09076 8.14804 6.62311 8.23057 6.38558 8.5699C6.14804 8.90924 6.23057 9.37689 6.5699 9.61442L11.5699 13.1144C11.8281 13.2952 12.1719 13.2952 12.4301 13.1144L17.4301 9.61442C17.7694 9.37689 17.852 8.90924 17.6144 8.5699C17.3769 8.23057 16.9092 8.14804 16.5699 8.38558L12 11.5845L7.4301 8.38558Z"
                                        fill="#000000"></path>
                                </svg>
                                <?= $email ?>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="main-footer-bottom">
        <div class="container">
            <div class="footer-bottom-inner d-flex align-items-center justify-content-between">
                <?php if (!empty($copyright)): ?>
                    <p class="mb-0">
                        <?= $copyright ?>
                    </p>
                <?php endif ?>

                <?php if (!empty($socials)): ?>
                    <div class="main-footer__socials d-flex align-items-center">
                        <?php foreach ($socials as $social): ?>
                            <div class="item-social">
                                <a href="<?= $social['link'] ?>" class="d-flex align-items-center justify-content-center">
                                    <img src="<?= $social['icon'] ?>" alt="icon-footer">
                                </a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <button id="backToTop" class="back-to-top" aria-label="Back to Top">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M12 19V5M12 5L5 12M12 5L19 12" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"
                stroke-linejoin="round" />
        </svg>
    </button>
</footer>