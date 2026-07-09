

import Swiper from 'swiper';
import { Pagination, Navigation, Autoplay, EffectFade } from 'swiper/modules';
import 'swiper/css';
import 'swiper/css/pagination';
import 'swiper/css/navigation';
import 'swiper/css/effect-fade';
import { CountUp } from 'countup.js';


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

    function hleInitCounters() {
        const counters = document.querySelectorAll('.hle-counter');
        if (!counters.length) return;

        const parseValue = (text) => {
            const match = text.trim().match(/^([^\d\-\.]+)?(-?[\d\.,]+)([^\d]+)?$/);
            if (!match) return null;

            const prefix = match[1] || '';
            const numberStr = match[2].replace(/,/g, '');
            const suffix = match[3] || '';

            const number = parseFloat(numberStr);
            if (isNaN(number)) return null;

            const decimalPlaces = numberStr.includes('.') ? numberStr.split('.')[1].length : 0;

            return { prefix, number, suffix, decimalPlaces };
        };

        const observer = new IntersectionObserver((entries, obs) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const el = entry.target;
                    const parsed = parseValue(el.innerText);

                    if (parsed) {
                        const countUp = new CountUp(el, parsed.number, {
                            decimalPlaces: parsed.decimalPlaces,
                            prefix: parsed.prefix,
                            suffix: parsed.suffix,
                            duration: 2.5,
                            useEasing: true,
                            useGrouping: true
                        });

                        if (!countUp.error) {
                            countUp.start();
                        } else {
                            console.error(countUp.error);
                        }
                    }

                    obs.unobserve(el);
                }
            });
        }, { threshold: 0.1 });

        counters.forEach(counter => observer.observe(counter));
    }

    $(document).ready(function () {

        // initHeaderScroll();
        initBackToTop()
        hleHeroSliders()
        hleInitCounters()

        AOS.init();

    });
})(jQuery); 