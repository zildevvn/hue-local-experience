<?php
$heading = get_field('heading_contact');
$desc = get_field('desc_contact');
$form_contact = get_field('form_contact');
$address = get_field('address', 'option');
$phone = get_field('phone', 'option');
$email = get_field('email', 'option');
$open_time = get_field('open_time', 'option');
?>
<section class="hle-section main-section">
    <div class="container">
        <div class="section-heading">
            <h2 class="hle-heading center hle-heading-animation">
                <?= $heading ?>
            </h2>
            <p class="hle-sub-heading">
                <?= $desc ?>
            </p>
        </div>

        <div class="main-section-warp d-flex justify-content-between">
            <div class="main-section__left">
                <div class="main-section__left-inner">
                    <?php if (!empty($address)): ?>
                        <div class="contact-info d-flex address">
                            <div class="contact-info__icon">
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
                            </div>

                            <div class="contact-info__content">
                                <h3 class="h5">Address</h3>
                                <p class="mb-0"> <?= $address ?> </p>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($phone)): ?>
                        <a href="tel:<?= $phone ?>" aria-label="Call Us" class="contact-info d-flex">
                            <div class="contact-info__icon">
                                <svg width="24px" height="24px" viewBox="0 0 24 24" stroke-width="1.5" fill="none"
                                    xmlns="http://www.w3.org/2000/svg" color="#000000">
                                    <path fill-rule="evenodd" clip-rule="evenodd"
                                        d="M15.4697 5.53033C15.1768 5.23744 15.1768 4.76256 15.4697 4.46967L18.4697 1.46967C18.7626 1.17678 19.2374 1.17678 19.5303 1.46967C19.8232 1.76256 19.8232 2.23744 19.5303 2.53033L17.8107 4.25H22C22.4142 4.25 22.75 4.58579 22.75 5C22.75 5.41421 22.4142 5.75 22 5.75H17.8107L19.5303 7.46967C19.8232 7.76256 19.8232 8.23744 19.5303 8.53033C19.2374 8.82322 18.7626 8.82322 18.4697 8.53033L15.4697 5.53033Z"
                                        fill="#000000"></path>
                                    <path fill-rule="evenodd" clip-rule="evenodd"
                                        d="M4.06343 1.25L7.81435 1.25C8.12672 1.25 8.40638 1.44361 8.51634 1.73599L9.97178 5.60588C10.02 5.73398 10.0322 5.87281 10.0071 6.00735L9.2778 9.91931C10.1742 12.0273 11.6548 13.4439 14.1104 14.7146L17.9754 13.9657C18.1126 13.9391 18.2545 13.9514 18.3851 14.0012L22.2669 15.4804C22.5577 15.5912 22.7498 15.87 22.7498 16.1812L22.7498 19.7655C22.7498 21.391 21.3176 22.7101 19.6424 22.3456C16.5888 21.6811 10.9315 19.9923 6.9695 16.0303C3.17436 12.2352 1.90282 6.99252 1.47478 4.15869C1.23055 2.54172 2.52735 1.25 4.06343 1.25Z"
                                        fill="#000000"></path>
                                </svg>
                            </div>

                            <div class="contact-info__content">
                                <h3 class="h5">Call Us</h3>
                                <p class="mb-0">
                                    <?= $phone ?>
                                </p>
                            </div>
                        </a>
                    <?php endif; ?>

                    <?php if (!empty($email)): ?>
                        <a href="mailto:<?= $email ?>" aria-label="Send us email" class="contact-info d-flex">
                            <div class="contact-info__icon">
                                <svg width="24px" height="24px" viewBox="0 0 24 24" fill="none"
                                    xmlns="http://www.w3.org/2000/svg" color="#000000" stroke-width="1.5">
                                    <path fill-rule="evenodd" clip-rule="evenodd"
                                        d="M4 4.25C2.48122 4.25 1.25 5.48122 1.25 7V17C1.25 18.5188 2.48122 19.75 4 19.75H20C21.5188 19.75 22.75 18.5188 22.75 17V7C22.75 5.48122 21.5188 4.25 20 4.25H4ZM7.4301 8.38558C7.09076 8.14804 6.62311 8.23057 6.38558 8.5699C6.14804 8.90924 6.23057 9.37689 6.5699 9.61442L11.5699 13.1144C11.8281 13.2952 12.1719 13.2952 12.4301 13.1144L17.4301 9.61442C17.7694 9.37689 17.852 8.90924 17.6144 8.5699C17.3769 8.23057 16.9092 8.14804 16.5699 8.38558L12 11.5845L7.4301 8.38558Z"
                                        fill="#000000"></path>
                                </svg>
                            </div>

                            <div class="contact-info__content">
                                <h3 class="h5">Email Us</h3>
                                <p class="mb-0">
                                    <?= $email ?>
                                </p>
                            </div>
                        </a>
                    <?php endif; ?>

                    <?php if (!empty($open_time)): ?>
                        <div class="contact-info d-flex">
                            <div class="contact-info__icon">
                                <svg width="24px" height="24px" viewBox="0 0 24 24" fill="none"
                                    xmlns="http://www.w3.org/2000/svg" color="#000000" stroke-width="1.5">
                                    <path fill-rule="evenodd" clip-rule="evenodd"
                                        d="M12 3.25C6.61522 3.25 2.25 7.61522 2.25 13C2.25 18.3848 6.61522 22.75 12 22.75C17.3848 22.75 21.75 18.3848 21.75 13C21.75 7.61522 17.3848 3.25 12 3.25ZM12 13.75C11.5858 13.75 11.25 13.4142 11.25 13V8C11.25 7.58579 11.5858 7.25 12 7.25C12.4142 7.25 12.75 7.58579 12.75 8V12.25H17C17.4142 12.25 17.75 12.5858 17.75 13C17.75 13.4142 17.4142 13.75 17 13.75H12Z"
                                        fill="#000000"></path>
                                    <path fill-rule="evenodd" clip-rule="evenodd"
                                        d="M4.40004 3.94996C4.64857 4.28133 5.11867 4.34848 5.45004 4.09996L7.45004 2.59996C7.78141 2.35143 7.84857 1.88133 7.60004 1.54996C7.35152 1.21859 6.88141 1.15143 6.55004 1.39996L4.55004 2.89996C4.21867 3.14848 4.15152 3.61858 4.40004 3.94996Z"
                                        fill="#000000"></path>
                                    <path fill-rule="evenodd" clip-rule="evenodd"
                                        d="M19.6 3.94996C19.3514 4.28133 18.8813 4.34848 18.55 4.09996L16.55 2.59996C16.2186 2.35143 16.1514 1.88133 16.4 1.54996C16.6485 1.21859 17.1186 1.15143 17.45 1.39996L19.45 2.89996C19.7813 3.14848 19.8485 3.61858 19.6 3.94996Z"
                                        fill="#000000"></path>
                                </svg>
                            </div>
                            <div class="contact-info__content">
                                <h3 class="h5">Opening Time</h3>
                                <p class="mb-0">
                                    <?= $open_time ?>
                                </p>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            <div class="main-section__right">
                <?php if (!empty($form_contact)): ?>
                    <h3>Contact Form</h3>
                    <?= do_shortcode($form_contact) ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>