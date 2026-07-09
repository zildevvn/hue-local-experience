

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

    function hleInitParallax() {
        const $section = $('.achievements-section');
        if (!$section.length) return;

        const $bgImage = $section.find('.hle-section__bg img');
        const $items = $section.find('.achievement-item');
        if (!$bgImage.length && !$items.length) return;

        let inViewport = false;
        let requestId = null;

        // IntersectionObserver to detect when section is in viewport
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                inViewport = entry.isIntersecting;
                if (inViewport) {
                    scheduleUpdate();
                } else {
                    if (requestId) {
                        cancelAnimationFrame(requestId);
                        requestId = null;
                    }
                }
            });
        }, {
            threshold: 0,
            rootMargin: '100px 0px 100px 0px' // Start slightly before entering viewport
        });

        observer.observe($section[0]);

        function updateParallax() {
            if (!inViewport) {
                requestId = null;
                return;
            }

            const isMobile = window.matchMedia('(max-width: 767px)').matches;

            if (isMobile) {
                if ($bgImage.length) $bgImage.css('transform', '');
                if ($items.length) $items.css('transform', '');
                requestId = null;
                return;
            }

            const rect = $section[0].getBoundingClientRect();
            const viewHeight = window.innerHeight;

            // Calculate progress (0 = enters viewport bottom, 1 = leaves viewport top)
            const totalScrollableDistance = viewHeight + rect.height;
            const currentScrolledDistance = viewHeight - rect.top;
            
            let progress = currentScrolledDistance / totalScrollableDistance;
            progress = Math.max(0, Math.min(1, progress)); // Clamp [0, 1]
            const normalizedProgress = progress - 0.5; // [-0.5, 0.5]

            // Apply parallax to background image
            if ($bgImage.length) {
                // Background moves in same direction as scroll (slower relative to screen viewport)
                const maxBgTranslate = rect.height * 0.15; // 15% of section height
                const bgTranslateY = normalizedProgress * maxBgTranslate;
                $bgImage.css('transform', `translate3d(0, ${bgTranslateY}px, 0)`);
            }

            // Apply parallax to achievements list items (subtle floating depth)
            if ($items.length) {
                $items.each(function (index) {
                    // Alternate translate speeds for even/odd items to create independent layer depth
                    const factor = (index % 2 === 0) ? -20 : -10;
                    const itemTranslateY = normalizedProgress * factor;
                    $(this).css('transform', `translate3d(0, ${itemTranslateY}px, 0)`);
                });
            }

            requestId = null;
        }

        function scheduleUpdate() {
            if (!requestId) {
                requestId = requestAnimationFrame(updateParallax);
            }
        }

        $(window).on('scroll resize', scheduleUpdate);
    }

    $(document).ready(function () {

        // initHeaderScroll();
        initBackToTop()
        hleHeroSliders()
        hleInitCounters()
        hleInitParallax()

        AOS.init();

    });
})(jQuery); 