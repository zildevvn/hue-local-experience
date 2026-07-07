

import Swiper from 'swiper';
import { Pagination, Navigation, Autoplay, EffectFade } from 'swiper/modules';
import 'swiper/css';
import 'swiper/css/pagination';
import 'swiper/css/navigation';
import 'swiper/css/effect-fade';


(function ($) {
    "use strict";

    function hleHeroSliders() {
        if ($('.hero-section-sliders').length > 0) {
            new Swiper('.hero-section-sliders', {
                modules: [Pagination, Navigation, Autoplay, EffectFade],
                slidesPerView: 1,
                loop: true,
                effect: 'fade',
                fadeEffect: {
                    crossFade: true
                },
                pagination: {
                    el: '.hero-section-sliders .swiper-pagination',
                    clickable: true,
                },
                navigation: {
                    nextEl: '.hero-section-sliders .swiper-button-next',
                    prevEl: '.hero-section-sliders .swiper-button-prev',
                },
                autoplay: {
                    delay: 5000,
                    disableOnInteraction: false,
                },

            });
        }
    }

    function initBackToTop() {
        const $backToTop = $('#backToTop');
        if ($backToTop.length) {
            $(window).on('scroll', function () {
                if ($(this).scrollTop() > 300) {
                    $backToTop.addClass('show');
                } else {
                    $backToTop.removeClass('show');
                }
            });

            $backToTop.on('click', function (e) {
                e.preventDefault();
                window.scrollTo({
                    top: 0,
                    behavior: 'smooth'
                });
            });
        }
    }

    $(document).ready(function () {

        // initHeaderScroll();
        initBackToTop()
        hleHeroSliders()

    });
})(jQuery); 