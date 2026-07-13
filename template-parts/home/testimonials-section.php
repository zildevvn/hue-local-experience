<?php

$heading = get_field('hd_testimonials_hp');
$description = get_field('sub_hd_testimonials_hp');
$testimonials = get_field('testimonials_list_hp');
?>
<section class="hle-section testimonials-section">
    <div class="container">
        <div class="section-heading">
            <?php if (!empty($heading)): ?>
                <h2 class="hle-heading center">
                    <?php echo esc_html($heading); ?>
                </h2>
            <?php endif; ?>

            <?php if (!empty($description)): ?>
                <div class="hle-sub-heading">
                    <?php echo $description; ?>
                </div>
            <?php endif; ?>
        </div>

        <?php if (!empty($testimonials)): ?>
            <div class="testimonials-carousel swiper">
                <div class="swiper-wrapper">
                    <?php foreach ($testimonials as $key => $testimonial): ?>
                        <div class="testimonial-item swiper-slide">
                            <div class="testimonial-item__rate d-flex align-items-center">
                                <svg width="24px" height="24px" viewBox="0 0 24 24" fill="none"
                                    xmlns="http://www.w3.org/2000/svg" color="#000000" stroke-width="1.5">
                                    <path
                                        d="M8.58737 8.23597L11.1849 3.00376C11.5183 2.33208 12.4817 2.33208 12.8151 3.00376L15.4126 8.23597L21.2215 9.08017C21.9668 9.18848 22.2638 10.0994 21.7243 10.6219L17.5217 14.6918L18.5135 20.4414C18.6409 21.1798 17.8614 21.7428 17.1945 21.3941L12 18.678L6.80547 21.3941C6.1386 21.7428 5.35909 21.1798 5.48645 20.4414L6.47825 14.6918L2.27575 10.6219C1.73617 10.0994 2.03322 9.18848 2.77852 9.08017L8.58737 8.23597Z"
                                        fill="#000000" stroke="#000000" stroke-width="1.5" stroke-linecap="round"
                                        stroke-linejoin="round"></path>
                                </svg>

                                <svg width="24px" height="24px" viewBox="0 0 24 24" fill="none"
                                    xmlns="http://www.w3.org/2000/svg" color="#000000" stroke-width="1.5">
                                    <path
                                        d="M8.58737 8.23597L11.1849 3.00376C11.5183 2.33208 12.4817 2.33208 12.8151 3.00376L15.4126 8.23597L21.2215 9.08017C21.9668 9.18848 22.2638 10.0994 21.7243 10.6219L17.5217 14.6918L18.5135 20.4414C18.6409 21.1798 17.8614 21.7428 17.1945 21.3941L12 18.678L6.80547 21.3941C6.1386 21.7428 5.35909 21.1798 5.48645 20.4414L6.47825 14.6918L2.27575 10.6219C1.73617 10.0994 2.03322 9.18848 2.77852 9.08017L8.58737 8.23597Z"
                                        fill="#000000" stroke="#000000" stroke-width="1.5" stroke-linecap="round"
                                        stroke-linejoin="round"></path>
                                </svg>

                                <svg width="24px" height="24px" viewBox="0 0 24 24" fill="none"
                                    xmlns="http://www.w3.org/2000/svg" color="#000000" stroke-width="1.5">
                                    <path
                                        d="M8.58737 8.23597L11.1849 3.00376C11.5183 2.33208 12.4817 2.33208 12.8151 3.00376L15.4126 8.23597L21.2215 9.08017C21.9668 9.18848 22.2638 10.0994 21.7243 10.6219L17.5217 14.6918L18.5135 20.4414C18.6409 21.1798 17.8614 21.7428 17.1945 21.3941L12 18.678L6.80547 21.3941C6.1386 21.7428 5.35909 21.1798 5.48645 20.4414L6.47825 14.6918L2.27575 10.6219C1.73617 10.0994 2.03322 9.18848 2.77852 9.08017L8.58737 8.23597Z"
                                        fill="#000000" stroke="#000000" stroke-width="1.5" stroke-linecap="round"
                                        stroke-linejoin="round"></path>
                                </svg>

                                <svg width="24px" height="24px" viewBox="0 0 24 24" fill="none"
                                    xmlns="http://www.w3.org/2000/svg" color="#000000" stroke-width="1.5">
                                    <path
                                        d="M8.58737 8.23597L11.1849 3.00376C11.5183 2.33208 12.4817 2.33208 12.8151 3.00376L15.4126 8.23597L21.2215 9.08017C21.9668 9.18848 22.2638 10.0994 21.7243 10.6219L17.5217 14.6918L18.5135 20.4414C18.6409 21.1798 17.8614 21.7428 17.1945 21.3941L12 18.678L6.80547 21.3941C6.1386 21.7428 5.35909 21.1798 5.48645 20.4414L6.47825 14.6918L2.27575 10.6219C1.73617 10.0994 2.03322 9.18848 2.77852 9.08017L8.58737 8.23597Z"
                                        fill="#000000" stroke="#000000" stroke-width="1.5" stroke-linecap="round"
                                        stroke-linejoin="round"></path>
                                </svg>

                                <svg width="24px" height="24px" viewBox="0 0 24 24" fill="none"
                                    xmlns="http://www.w3.org/2000/svg" color="#000000" stroke-width="1.5">
                                    <path
                                        d="M8.58737 8.23597L11.1849 3.00376C11.5183 2.33208 12.4817 2.33208 12.8151 3.00376L15.4126 8.23597L21.2215 9.08017C21.9668 9.18848 22.2638 10.0994 21.7243 10.6219L17.5217 14.6918L18.5135 20.4414C18.6409 21.1798 17.8614 21.7428 17.1945 21.3941L12 18.678L6.80547 21.3941C6.1386 21.7428 5.35909 21.1798 5.48645 20.4414L6.47825 14.6918L2.27575 10.6219C1.73617 10.0994 2.03322 9.18848 2.77852 9.08017L8.58737 8.23597Z"
                                        fill="#000000" stroke="#000000" stroke-width="1.5" stroke-linecap="round"
                                        stroke-linejoin="round"></path>
                                </svg>
                            </div>

                            <?php if (!empty($testimonial['quote'])): ?>
                                <div class="testimonial-item__quote">
                                    <?= $testimonial['quote'] ?>
                                </div>
                            <?php endif; ?>

                            <div class="testimonial-item-content d-flex align-items-center gap-3">
                                <?php if (!empty($testimonial['avatar'])): ?>
                                    <div class="testimonial-item__avatar">

                                        <svg class="wgl-dashes inner-dashed-border animated-dashes">
                                            <rect rx="50%" ry="50%"> </rect>
                                        </svg>
                                        <img src="<?php echo esc_url($testimonial['avatar']); ?>" alt="">
                                    </div>

                                    <div class="testimonial-info">
                                        <?php if (!empty($testimonial['name'])): ?>
                                            <h3 class="testimonial-item__name h5">
                                                <?= $testimonial['name'] ?>
                                            </h3>
                                        <?php endif; ?>

                                        <?php if (!empty($testimonial['position'])): ?>
                                            <span class="testimonial-item__position">
                                                <?= $testimonial['position'] ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <div class="testimonial-item__icon d-flex align-items-center justify-content-center">
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
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="swiper-pagination d-md-none"></div>
                <div class="swiper-button-prev d-none d-md-flex">
                    <svg width="24px" height="24px" viewBox="0 0 24 24" stroke-width="1.5" fill="none"
                        xmlns="http://www.w3.org/2000/svg" color="#000000">
                        <path d="M21 12L3 12M3 12L11.5 3.5M3 12L11.5 20.5" stroke="#000000" stroke-width="1.5"
                            stroke-linecap="round" stroke-linejoin="round"></path>
                    </svg>
                </div>
                <div class="swiper-button-next d-none d-md-flex">
                    <svg width="24px" height="24px" viewBox="0 0 24 24" stroke-width="1.5" fill="none"
                        xmlns="http://www.w3.org/2000/svg" color="#000000">
                        <path d="M3 12L21 12M21 12L12.5 3.5M21 12L12.5 20.5" stroke="#000000" stroke-width="1.5"
                            stroke-linecap="round" stroke-linejoin="round"></path>
                    </svg>
                </div>
            </div>
        <?php endif; ?>
    </div>
</section>