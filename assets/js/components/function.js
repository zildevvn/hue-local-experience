

import Swiper from 'swiper';
import { Pagination, Navigation, Autoplay, EffectFade, Keyboard } from 'swiper/modules';
import 'swiper/css';
import 'swiper/css/pagination';
import 'swiper/css/navigation';
import 'swiper/css/effect-fade';
import { CountUp } from 'countup.js';


(function ($) {
    "use strict";
    const hleHeroSliders = () => {
        const $sliders = $('.hero-section-sliders');
        if ($sliders.length === 0) return;

        $sliders.each(function () {
            const $this = $(this);

            if (this.swiper) {
                return;
            }

            new Swiper(this, {
                modules: [Pagination, Navigation, Autoplay, EffectFade],
                slidesPerView: 1,
                loop: true,
                speed: 800,
                effect: 'fade',
                fadeEffect: {
                    crossFade: true
                },
                pagination: {
                    el: $this.find('.swiper-pagination')[0],
                    clickable: true,
                },
                navigation: {
                    nextEl: $this.find('.swiper-button-next')[0],
                    prevEl: $this.find('.swiper-button-prev')[0],
                    enabled: false
                },
                autoplay: {
                    delay: 5000,
                    disableOnInteraction: false,
                    pauseOnMouseEnter: true,
                },
                breakpoints: {
                    768: {
                        navigation: {
                            enabled: true
                        }
                    }
                }
            });
        });
    }


    const initBackToTop = () => {
        const $backToTop = $('#backToTop');
        if (!$backToTop.length) return;
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


    const hleInitParallax = () => {
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


    const hleInitCarsSlider = () => {
        const container = document.querySelector('.cars-section__list');
        if (!container) return null;

        const swiper = new Swiper(container, {
            modules: [Navigation, Pagination, Autoplay],
            slidesPerView: 1,
            spaceBetween: 20,
            observer: true,
            observeParents: true,
            loop: true,
            autoplay: {
                delay: 4000,
                disableOnInteraction: false,
                pauseOnMouseEnter: true,
            },
            navigation: {
                nextEl: '.cars-section .swiper-button-next',
                prevEl: '.cars-section .swiper-button-prev',
            },
            pagination: {
                el: '.cars-section .swiper-pagination',
                clickable: true,
            },
            breakpoints: {
                1400: { slidesPerView: 3 },
                1024: { slidesPerView: 3 },
                767: { slidesPerView: 2 },
            },
        });

        // [WHY] Return instance so caller can call swiper.destroy() if needed (e.g. SPA navigation)
        return swiper;
    }


    const hleInitTestimonialsSlider = () => {
        const testimonialsCarousel = document.querySelector('.testimonials-carousel')
        if (!testimonialsCarousel) return;

        const swiper = new Swiper(testimonialsCarousel, {
            modules: [Navigation, Pagination, Autoplay],
            slidesPerView: 1,
            spaceBetween: 20,
            // observer: true,
            // observeParents: true,
            loop: true,
            autoplay: {
                delay: 4000,
                disableOnInteraction: false,
                pauseOnMouseEnter: true,
            },
            navigation: {
                nextEl: '.swiper-button-next',
                prevEl: '.swiper-button-prev',
            },
            pagination: {
                el: '.swiper-pagination',
                clickable: true,
            },
            breakpoints: {
                1400: {
                    slidesPerView: 4,
                },
                1024: {
                    slidesPerView: 3,
                },
                767: {
                    slidesPerView: 2,
                },
            },
            on: {
                init: function () {
                    setTimeout(() => {
                        this.update();
                    }, 50);
                },
                resize: function () {
                    setTimeout(() => {
                        this.update();
                    }, 50);
                }
            }
        });
    }


    const hleInitFaqs = () => {
        const $faqsList = $('.accordion-list');
        if (!$faqsList.length) return;

        // Prevent duplicate event binding
        $faqsList.off('click keydown', '.accordion-question');

        $faqsList.on('click keydown', '.accordion-question', function (e) {
            // Handle keydown: only trigger for Enter (13) or Space (32) keys
            if (e.type === 'keydown' && e.which !== 13 && e.which !== 32) {
                return;
            }

            // Prevent default page scroll behavior on Space key
            if (e.which === 32) {
                e.preventDefault();
            }

            const $question = $(this);
            const $item = $question.closest('.accordion-item');
            const $answer = $item.find('.accordion-answer');
            const isActive = $item.hasClass('is-active') || $item.hasClass('active');

            // Find other active items and close them smoothly
            const $otherItems = $faqsList.find('.accordion-item').not($item);
            $otherItems.removeClass('is-active active');
            $otherItems.find('.accordion-question').attr('aria-expanded', 'false');
            $otherItems.find('.accordion-answer').slideUp(300);

            if (isActive) {
                // Toggle active class and slide up
                $item.removeClass('is-active active');
                $question.attr('aria-expanded', 'false');
                $answer.slideUp(300);
            } else {
                // Toggle active class and slide down
                $item.addClass('is-active active');
                $question.attr('aria-expanded', 'true');
                $answer.slideDown(300);
            }
        });
    }

    const hleVideoPopup = () => {
        const $buttons = $('.btn-play-video');
        if (!$buttons.length) return;

        if ($('#hle-video-modal').length === 0) {
            const modalHtml = `
                <div id="hle-video-modal" class="hle-video-modal" aria-hidden="true">
                    <div class="hle-video-modal__overlay"></div>
                    <div class="hle-video-modal__content">
                        <button class="hle-video-modal__close" aria-label="Close video">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                        </button>
                        <div class="hle-video-modal__iframe-container">
                            <iframe id="hle-video-iframe" src="" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
                        </div>
                    </div>
                </div>
            `;
            $('body').append(modalHtml);
        }

        const $modal = $('#hle-video-modal');
        const $overlay = $modal.find('.hle-video-modal__overlay');
        const $closeBtn = $modal.find('.hle-video-modal__close');
        const $iframe = $('#hle-video-iframe');
        const $body = $('body');

        function openModal(videoUrl) {
            let embedUrl = videoUrl;

            if (videoUrl.includes('youtube.com/watch?v=')) {
                const videoId = videoUrl.split('v=')[1].split('&')[0];
                embedUrl = `https://www.youtube.com/embed/${videoId}?autoplay=1`;
            } else if (videoUrl.includes('youtu.be/')) {
                const videoId = videoUrl.split('youtu.be/')[1].split('?')[0];
                embedUrl = `https://www.youtube.com/embed/${videoId}?autoplay=1`;
            } else if (videoUrl.includes('vimeo.com/') && !videoUrl.includes('player.vimeo.com')) {
                const videoId = videoUrl.split('vimeo.com/')[1].split('?')[0];
                embedUrl = `https://player.vimeo.com/video/${videoId}?autoplay=1`;
            }

            $iframe.attr('src', embedUrl);
            $modal.addClass('is-active').attr('aria-hidden', 'false');
            $body.css('overflow', 'hidden');
        }

        function closeModal() {
            $modal.removeClass('is-active').attr('aria-hidden', 'true');
            $body.css('overflow', '');
            setTimeout(() => {
                $iframe.attr('src', '');
            }, 300);
        }

        $buttons.off('click.hleVideo').on('click.hleVideo', function (e) {
            e.preventDefault();
            const videoUrl = $(this).attr('data-video-url');
            if (videoUrl) {
                openModal(videoUrl);
            }
        });

        $overlay.off('click.hleVideo').on('click.hleVideo', closeModal);
        $closeBtn.off('click.hleVideo').on('click.hleVideo', closeModal);

        $(document).off('keydown.hleVideo').on('keydown.hleVideo', function (e) {
            if (e.key === 'Escape' && $modal.hasClass('is-active')) {
                closeModal();
            }
        });
    }

    const hleInitImageParallax = () => {
        const $parallaxElements = $('[data-parallax="true"]');
        if (!$parallaxElements.length) return;

        // Respect prefers-reduced-motion
        const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        if (prefersReducedMotion) return;

        let requestId = null;

        function updateParallax() {
            const viewHeight = window.innerHeight;
            const isMobile = window.matchMedia('(max-width: 767px)').matches;

            $parallaxElements.each(function () {
                const $el = $(this);
                if (isMobile) {
                    $el.css('transform', '');
                    return;
                }

                const $container = $el.closest('.js-parallax-container');
                if (!$container.length) return;

                const rect = $container[0].getBoundingClientRect();

                // Only calculate if in viewport
                if (rect.top <= viewHeight && rect.bottom >= 0) {
                    const speed = parseFloat($el.attr('data-parallax-speed')) || 0.15;
                    const totalScrollableDistance = viewHeight + rect.height;
                    const currentScrolledDistance = viewHeight - rect.top;

                    let progress = currentScrolledDistance / totalScrollableDistance;
                    progress = Math.max(0, Math.min(1, progress));
                    const normalizedProgress = progress - 0.5;

                    const maxTranslate = rect.height * speed;
                    const translateY = normalizedProgress * maxTranslate;

                    $el.css('transform', `translate3d(0, ${translateY}px, 0)`);
                }
            });

            requestId = null;
        }

        function scheduleUpdate() {
            if (!requestId) {
                requestId = requestAnimationFrame(updateParallax);
            }
        }

        if (window.hleImageParallaxObserver) {
            window.hleImageParallaxObserver.disconnect();
        }

        const observer = new IntersectionObserver((entries) => {
            let isAnyVisible = false;
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    $(entry.target).data('is-visible', true);
                } else {
                    $(entry.target).data('is-visible', false);
                }
            });

            $parallaxElements.each(function () {
                const $container = $(this).closest('.js-parallax-container');
                if ($container.data('is-visible')) {
                    isAnyVisible = true;
                }
            });

            if (isAnyVisible) {
                $(window).off('scroll.hleImgParallax resize.hleImgParallax').on('scroll.hleImgParallax resize.hleImgParallax', scheduleUpdate);
                scheduleUpdate();
            } else {
                $(window).off('scroll.hleImgParallax resize.hleImgParallax');
            }
        }, {
            threshold: 0,
            rootMargin: '100px 0px 100px 0px'
        });

        window.hleImageParallaxObserver = observer;

        $parallaxElements.each(function () {
            const $container = $(this).closest('.js-parallax-container');
            if ($container.length) {
                observer.observe($container[0]);
            }
        });

        scheduleUpdate();
    }


    const btAnimateText = (selector, direction = 'right') => {
        const elements = document.querySelectorAll(selector);

        elements.forEach(el => {
            const originalText = el.textContent.trim();
            let delay = 0;
            const delayStep = 0.1;

            el.setAttribute('aria-label', originalText);
            el.classList.add('hle-animated', `hle-animation-${direction}`);

            const words = originalText.split(' ');

            const wordSpans = words.map(word => {
                const letterSpans = word.split('').map(letter => {
                    const span = document.createElement('span');
                    span.className = 'hle-letter';
                    // [WHY] Store delay in data attribute, apply only when in viewport
                    span.dataset.delay = delay.toFixed(1);
                    span.textContent = letter;
                    delay += delayStep;
                    return span;
                });

                const wordSpan = document.createElement('span');
                wordSpan.className = 'hle-word';
                wordSpan.setAttribute('aria-hidden', 'true');
                letterSpans.forEach(ls => wordSpan.appendChild(ls));
                return wordSpan;
            });

            el.textContent = '';
            wordSpans.forEach((ws, i) => {
                el.appendChild(ws);
                if (i < wordSpans.length - 1) {
                    el.appendChild(document.createTextNode(' '));
                }
            });
        });

        /* -------------------------------------------------------------------------- */
        /* SINGLE TOUR GALLERY LIGHTBOX (Fancybox)
        /* -------------------------------------------------------------------------- */
        if (typeof Fancybox !== "undefined") {
            Fancybox.bind('[data-fancybox="tour-gallery"]', {
                Thumbs: {
                    type: "modern"
                },
                Toolbar: {
                    display: {
                        left: ["infobar"],
                        middle: [],
                        right: ["slideshow", "thumbs", "close"],
                    },
                },
            });
        }
        /* -------------------------------------------------------------------------- */

        // [WHY] IntersectionObserver triggers animation only when heading enters viewport
        // — better than scroll event listener (no debounce needed, runs off main thread)
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (!entry.isIntersecting) return;

                // Apply animation-delay to each letter now that element is visible
                entry.target.querySelectorAll('.hle-letter').forEach(letter => {
                    letter.style.animationDelay = `${letter.dataset.delay}s`;
                });

                entry.target.classList.add('hle-in-view');

                // [WHY] Unobserve after triggered — animation should only play once
                observer.unobserve(entry.target);
            });
        }, {
            threshold: 0.2, // trigger khi 20% element visible
            rootMargin: '0px 0px -50px 0px' // trigger sớm hơn 50px trước khi vào viewport
        });

        document.querySelectorAll(selector).forEach(el => observer.observe(el));
    }


    const hleFilterTours = () => {
        const $isBlock = $('.tours-list-section ')
        if (!$isBlock.length) return;

        const resultsElement = $isBlock.find('#hle-tours-results'),
            paginationElement = $isBlock.find('#hle-tours-pagination'),
            query = resultsElement.data('query'),
            fieldSearch = $isBlock.find('#hle-tours-search-input');

        const paxSlider = document.getElementById('hle-tours-pax-slider');
        const $paxDisplay = $('#hle-tours-pax-display');
        const $paxMinInput = $('#hle-tours-pax-min');
        const $paxMaxInput = $('#hle-tours-pax-max');

        const priceSlider = document.getElementById('hle-tours-price-slider');
        const $priceDisplay = $('#hle-tours-price-display');
        const $priceMinInput = $('#hle-tours-price-min');
        const $priceMaxInput = $('#hle-tours-price-max');

        const $catRadios = $isBlock.find('input[name="tour_cat"]');

        const $sortSelect = $('#hle-tours-sort');
        const $clearFiltersBtn = $('#hle-clear-filters');
        const $toursCount = $('#hle-tours-count');

        let searchTimeout;
        let currentAjaxRequest = null;
        let currentPage = 1;

        function updateClearFiltersVisibility(searchVal, paxMinVal, paxMaxVal, priceMinVal, priceMaxVal, catVal, sortVal) {
            const isSearchActive = searchVal !== '';
            const isPaxActive = parseInt(paxMinVal) !== 1 || parseInt(paxMaxVal) !== 50;
            const isPriceActive = parseInt(priceMinVal) !== 0 || parseInt(priceMaxVal) !== 1000;
            const isCatActive = catVal !== 'all';
            const isSortActive = sortVal !== 'default';

            if (isSearchActive || isPaxActive || isPriceActive || isCatActive || isSortActive) {
                $clearFiltersBtn.show();
            } else {
                $clearFiltersBtn.hide();
            }
        }

        function triggerSearch(resetPage = true) {
            if (resetPage) {
                currentPage = 1;
            }

            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(function () {
                const searchVal = fieldSearch.val().trim();
                const paxMinVal = $paxMinInput.val();
                const paxMaxVal = $paxMaxInput.val();
                const priceMinVal = $priceMinInput.val();
                const priceMaxVal = $priceMaxInput.val();
                const catVal = $isBlock.find('input[name="tour_cat"]:checked').val();
                const sortVal = $sortSelect.val();

                updateClearFiltersVisibility(searchVal, paxMinVal, paxMaxVal, priceMinVal, priceMaxVal, catVal, sortVal);

                __ajax_filter({
                    keySeach: searchVal,
                    pax_min: paxMinVal,
                    pax_max: paxMaxVal,
                    price_min: priceMinVal,
                    price_max: priceMaxVal,
                    tour_cat: catVal,
                    sort: sortVal,
                    query: query,
                    currentpage: currentPage
                });
            }, 500);
        }

        $catRadios.on('change', () => triggerSearch(true));
        $sortSelect.on('change', () => triggerSearch(true));

        $clearFiltersBtn.on('click', function (e) {
            e.preventDefault();
            fieldSearch.val('');

            // Reset categories
            $isBlock.find('input[name="tour_cat"][value="all"]').prop('checked', true);

            // Reset sort
            $sortSelect.val('default');

            // Reset sliders
            if (paxSlider && paxSlider.noUiSlider) {
                paxSlider.noUiSlider.set([1, 50]);
            }
            if (priceSlider && priceSlider.noUiSlider) {
                priceSlider.noUiSlider.set([0, 1000]);
            }

            triggerSearch(true);
        });

        if (paxSlider && typeof noUiSlider !== 'undefined') {
            noUiSlider.create(paxSlider, {
                start: [1, 50],
                connect: true,
                step: 1,
                range: {
                    'min': 1,
                    'max': 50
                },
                format: {
                    to: value => Math.round(value),
                    from: value => value
                }
            });

            paxSlider.noUiSlider.on('update', function (values, handle) {
                $paxDisplay.text(values[0] + ' \u2013 ' + values[1] + ' pax');
                $paxMinInput.val(values[0]);
                $paxMaxInput.val(values[1]);
            });

            paxSlider.noUiSlider.on('change', () => triggerSearch(true));
        }

        if (priceSlider && typeof noUiSlider !== 'undefined') {
            noUiSlider.create(priceSlider, {
                start: [0, 1000],
                connect: true,
                step: 10,
                range: {
                    'min': 0,
                    'max': 1000
                },
                format: {
                    to: value => Math.round(value),
                    from: value => value
                }
            });

            priceSlider.noUiSlider.on('update', function (values, handle) {
                $priceDisplay.text('$' + values[0] + ' \u2013 $' + values[1]);
                $priceMinInput.val(values[0]);
                $priceMaxInput.val(values[1]);
            });

            priceSlider.noUiSlider.on('change', () => triggerSearch(true));
        }

        fieldSearch.on('input', () => triggerSearch(true));

        paginationElement.on('click', '.page-numbers:not(.disabled):not(.current)', function (e) {
            e.preventDefault();
            const selectedPage = parseInt($(this).attr('data-page'));
            if (!isNaN(selectedPage)) {
                currentPage = selectedPage;
                triggerSearch(false);
            }
        });

        function __ajax_filter(val = {}) {
            if (currentAjaxRequest) {
                currentAjaxRequest.abort();
            }

            const $contentWrapper = $isBlock.find('.tours-content');
            const $sidebarWrapper = $isBlock.find('.tours-sidebar');

            try {
                $contentWrapper.addClass('is-loading');
                $sidebarWrapper.addClass('is-loading');

                currentAjaxRequest = $.ajax({
                    type: "post",
                    url: php_data.ajax_url,
                    dataType: "json",
                    data: {
                        ...{ action: "hle_ajax_filter_tours" },
                        ...val,
                    },
                    success: function (data) {

                        if (!resultsElement.hasClass("results-filter")) {
                            resultsElement.addClass('results-filter')
                        }

                        if (data.count !== undefined) {
                            $toursCount.text(data.count);
                        }

                        resultsElement.html(data.items);
                        paginationElement.html(data.pagination);

                        if (data.items.trim() === '') {
                            $('#hle-tours-empty').show();
                        } else {
                            $('#hle-tours-empty').hide();
                        }

                        // Smooth scroll to top of listing section when changing pages
                        if (val.currentpage > 1 || (val.currentpage === 1 && $(window).scrollTop() > $isBlock.offset().top)) {
                            $('html, body').animate({
                                scrollTop: $isBlock.offset().top - 80 // Adjust offset for sticky header if needed
                            }, 600);
                        }
                    },

                    complete: function () {
                        currentAjaxRequest = null;
                        $contentWrapper.removeClass('is-loading');
                        $sidebarWrapper.removeClass('is-loading');
                    }
                });

            } catch (e) {
                console.log(e);
                $contentWrapper.removeClass('is-loading');
                $sidebarWrapper.removeClass('is-loading');
            }
        }

    }

    const hleFilterPosts = () => {
        const $isBlock = $('.posts-list-section');
        if (!$isBlock.length) return;

        const resultsElement = $isBlock.find('#hle-posts-results'),
            paginationElement = $isBlock.find('#hle-posts-pagination'),
            query = resultsElement.data('query'),
            fieldSearch = $isBlock.find('#hle-posts-search-input');

        const $catRadios = $isBlock.find('input[name="post_cat"]');

        const $clearFiltersBtn = $('#hle-posts-clear-filters');
        const $postsCount = $('#hle-posts-count');

        const $dropdown = $isBlock.find('.posts-category-dropdown');
        const $trigger = $dropdown.find('.hle-dropdown-trigger');
        const $menu = $dropdown.find('.hle-dropdown-menu');
        const $label = $trigger.find('.hle-dropdown-label');

        let searchTimeout;
        let currentAjaxRequest = null;
        let currentPage = 1;

        function updateClearFiltersVisibility(searchVal, catVal) {
            const isSearchActive = searchVal !== '';
            const isCatActive = catVal !== 'all';

            if (isSearchActive || isCatActive) {
                $clearFiltersBtn.show();
            } else {
                $clearFiltersBtn.hide();
            }
        }

        function triggerSearch(resetPage = true) {
            if (resetPage) {
                currentPage = 1;
            }

            clearTimeout(searchTimeout);

            searchTimeout = setTimeout(() => {
                const searchVal = fieldSearch.val().trim();
                const catVal = $isBlock.find('input[name="post_cat"]:checked').val();

                updateClearFiltersVisibility(searchVal, catVal);

                __ajax_filter({
                    keySeach: searchVal,
                    post_cat: catVal,
                    query: query,
                    currentpage: currentPage
                });
            }, 500);
        }

        function updateDropdownLabel() {
            const $checked = $catRadios.filter(':checked');
            if ($checked.length) {
                const labelText = $checked.attr('data-label') || $checked.siblings('.tours-category-name').text().trim();
                $label.text(labelText);
            }
        }

        function openDropdown() {
            $menu.addClass('is-open');
            $trigger.attr('aria-expanded', 'true');
        }

        function closeDropdown() {
            $menu.removeClass('is-open');
            $trigger.attr('aria-expanded', 'false');
        }

        $trigger.on('click', function (e) {
            e.preventDefault();
            const isOpen = $menu.hasClass('is-open');
            if (isOpen) {
                closeDropdown();
            } else {
                openDropdown();
            }
        });

        $(document).on('click', function (e) {
            if ($dropdown.length && !$dropdown.is(e.target) && $dropdown.has(e.target).length === 0) {
                closeDropdown();
            }
        });

        $(document).on('keydown', function (e) {
            if (e.key === 'Escape' && $menu.hasClass('is-open')) {
                closeDropdown();
            }
        });

        // Initialize label on load
        updateDropdownLabel();

        $catRadios.on('change', () => {
            updateDropdownLabel();
            closeDropdown();
            triggerSearch(true);
        });

        $clearFiltersBtn.on('click', function (e) {
            e.preventDefault();
            fieldSearch.val('');

            // Reset categories
            $isBlock.find('input[name="post_cat"][value="all"]').prop('checked', true);
            updateDropdownLabel();

            triggerSearch(true);
        });

        fieldSearch.on('input', () => triggerSearch(true));

        paginationElement.on('click', '.page-numbers:not(.disabled):not(.current)', function (e) {
            e.preventDefault();
            const selectedPage = parseInt($(this).attr('data-page'));
            if (!isNaN(selectedPage)) {
                currentPage = selectedPage;
                triggerSearch(false);
            }
        });

        function __ajax_filter(val = {}) {
            if (currentAjaxRequest) {
                currentAjaxRequest.abort();
            }

            const $contentWrapper = $isBlock.find('.posts-list-wrapper');

            try {
                $contentWrapper.addClass('is-loading');

                currentAjaxRequest = $.ajax({
                    type: "post",
                    url: php_data.ajax_url,
                    dataType: "json",
                    data: {
                        action: "hle_ajax_filter_posts",
                        ...val
                    },
                    success: function (data) {
                        if (data.count !== undefined) {
                            $postsCount.text(data.count);
                        }

                        resultsElement.html(data.items);
                        paginationElement.html(data.pagination);

                        if (data.items.trim() === '') {
                            $('#hle-posts-empty').show();
                            $isBlock.find('.posts-main__header').hide();
                        } else {
                            $('#hle-posts-empty').hide();
                            $isBlock.find('.posts-main__header').show();
                        }

                        // Smooth scroll to top of listing section when changing pages
                        if (val.currentpage > 1 || (val.currentpage === 1 && $(window).scrollTop() > $isBlock.offset().top)) {
                            $('html, body').animate({
                                scrollTop: $isBlock.offset().top - 80 // Adjust offset for sticky header if needed
                            }, 600);
                        }
                    },

                    complete: function () {
                        currentAjaxRequest = null;
                        $contentWrapper.removeClass('is-loading');
                    }
                });

            } catch (e) {
                console.log(e);
                $contentWrapper.removeClass('is-loading');
            }
        }
    }

    /**
     * Tour Review — Interactive Star Picker
     * Handles hover highlighting, click-to-select, and keyboard accessibility.
     */

    const hleInitStarPicker = () => {
        const $picker = $('#star-picker');
        if (!$picker.length) return;

        const $labels = $picker.find('.star-picker__label');
        const $inputs = $picker.find('.star-picker__input');
        const $text = $('#star-picker-text');

        const ratingLabels = ['', 'Terrible', 'Poor', 'Average', 'Good', 'Excellent'];

        function setHighlight(upTo) {
            $labels.each(function (i) {
                $(this).toggleClass('is-hovered', i < upTo);
            });
        }

        function setActive(value) {
            $labels.each(function (i) {
                $(this).toggleClass('is-active', i < value);
            });
            $text.text(value ? ratingLabels[value] + ' (' + value + '/5)' : 'Select a rating');
        }

        $labels.on('mouseenter', function () {
            setHighlight($labels.index(this) + 1);
        }).on('mouseleave', function () {
            const selected = parseInt($inputs.filter(':checked').val()) || 0;
            setHighlight(selected);
        });

        $inputs.on('change', function () {
            const val = parseInt($(this).val());
            setActive(val);
            setHighlight(val);
        });

        $inputs.on('keydown', function (e) {
            const currentVal = parseInt($(this).val());
            if (e.key === 'ArrowRight' || e.key === 'ArrowUp') {
                e.preventDefault();
                const next = $inputs.filter('[value="' + Math.min(5, currentVal + 1) + '"]');
                if (next.length) { next.prop('checked', true).trigger('change'); next.focus(); }
            } else if (e.key === 'ArrowLeft' || e.key === 'ArrowDown') {
                e.preventDefault();
                const prev = $inputs.filter('[value="' + Math.max(1, currentVal - 1) + '"]');
                if (prev.length) { prev.prop('checked', true).trigger('change'); prev.focus(); }
            }
        });

        $('#hle-review-form').on('submit', function (e) {
            if (!$inputs.filter(':checked').length) {
                e.preventDefault();
                $picker.addClass('star-picker--error');
                $text.text('Please select a rating!').css('color', '#dc3545');
                $picker[0].scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        });

        $inputs.on('change', function () {
            $picker.removeClass('star-picker--error');
            $text.css('color', '');
        });
    }

    /**
     * Single Tour Anchor Navigation Bar
     * Smooth scrolling, active highlighting with IntersectionObserver,
     * and horizontal mobile auto-scroll.
     */
    function hleInitTourAnchorNav() {
        const $nav = $('#hle-anchor-nav');
        if (!$nav.length) return;

        const $links = $nav.find('.anchor-nav__link');
        const $list = $nav.find('.anchor-nav__list');

        // Map IDs to section elements that actually exist in DOM
        const sections = [];
        $links.each(function () {
            const id = $(this).attr('href');
            const $sec = $(id);
            if ($sec.length) {
                sections.push($sec[0]);
            }
        });

        if (!sections.length) return;

        // Active link helper
        function makeActive(id) {
            $links.removeClass('is-active');
            const $activeLink = $links.filter(`[href="#${id}"]`);
            $activeLink.addClass('is-active');

            // Automatically scroll the active mobile menu item into view
            if ($activeLink.length) {
                const listWidth = $list.outerWidth();
                const listScrollLeft = $list.scrollLeft();
                const linkOffsetLeft = $activeLink.position().left;
                const linkWidth = $activeLink.outerWidth();

                // Scroll if link is partially or fully out of view
                if (linkOffsetLeft < 0 || (linkOffsetLeft + linkWidth) > listWidth) {
                    $list.animate({
                        scrollLeft: listScrollLeft + linkOffsetLeft - (listWidth / 2) + (linkWidth / 2)
                    }, 300);
                }
            }
        }

        // Smooth scroll click handler
        $links.on('click', function (e) {
            e.preventDefault();
            const targetId = $(this).attr('href');
            const $targetSec = $(targetId);
            if ($targetSec.length) {
                const offset = $('body').hasClass('logged-in') ? 182 : 150;
                const targetScrollTop = $targetSec.offset().top - offset + 2;

                const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

                if (prefersReducedMotion) {
                    window.scrollTo(0, targetScrollTop);
                    makeActive(targetId.replace('#', ''));
                    // Update hash without scrolling
                    history.pushState(null, null, targetId);
                } else {
                    $('html, body').stop().animate({
                        scrollTop: targetScrollTop
                    }, 500, function () {
                        makeActive(targetId.replace('#', ''));
                        history.pushState(null, null, targetId);
                    });
                }
            }
        });

        // IntersectionObserver logic
        const observerOptions = {
            root: null,
            rootMargin: '-160px 0px -60% 0px',
            threshold: [0, 0.1, 0.2]
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    makeActive(entry.target.id);
                }
            });
        }, observerOptions);

        sections.forEach(sec => observer.observe(sec));

        // Backup logic: fallback if scrolled to the very bottom
        $(window).on('scroll', function () {
            if ($(window).scrollTop() + $(window).height() >= $(document).height() - 50) {
                const lastId = sections[sections.length - 1].id;
                makeActive(lastId);
            }
        });

        // Handle initial URL hash on load
        const initialHash = window.location.hash;
        if (initialHash) {
            const $targetSec = $(initialHash);
            if ($targetSec.length && $links.filter(`[href="${initialHash}"]`).length) {
                setTimeout(() => {
                    const offset = $('body').hasClass('logged-in') ? 182 : 150;
                    const targetScrollTop = $targetSec.offset().top - offset + 2;
                    $('html, body').scrollTop(targetScrollTop);
                    makeActive(initialHash.replace('#', ''));
                }, 150);
            }
        }
    }


    const hleHeaderTopMobile = () => {

        if ($(window).width() >= 768) return;

        const headerTop = $('#header-booking-top');
        if (!headerTop.length) return;

        $(window).on('scroll', function () {
            if ($(this).scrollTop() > 100) {
                headerTop.addClass('is-active');
            } else {
                headerTop.removeClass('is-active');
            }
        });
    }

    const hleFilterCars = () => {
        const $isBlock = $('.list-cars-section');
        if (!$isBlock.length) return;

        const resultsElement = $isBlock.find('#hle-cars-results'),
            paginationElement = $isBlock.find('#hle-cars-pagination'),
            query = resultsElement.data('query');

        let currentAjaxRequest = null;
        let currentPage = resultsElement.data('currentpage') || 1;

        paginationElement.on('click', '.page-numbers:not(.disabled):not(.current)', function (e) {
            e.preventDefault();
            const selectedPage = parseInt($(this).attr('data-page'));
            if (!isNaN(selectedPage)) {
                currentPage = selectedPage;
                __ajax_filter({
                    query: query,
                    currentpage: currentPage
                });
            }
        });

        function __ajax_filter(val = {}) {
            if (currentAjaxRequest) {
                currentAjaxRequest.abort();
            }

            const $contentWrapper = $isBlock.find('.list-cars-wrapper');

            try {
                $contentWrapper.addClass('is-loading');

                currentAjaxRequest = $.ajax({
                    type: "post",
                    url: php_data.ajax_url,
                    dataType: "json",
                    data: {
                        ...{ action: "hle_ajax_filter_cars" },
                        ...val,
                    },
                    success: function (data) {
                        resultsElement.html(data.items);
                        paginationElement.html(data.pagination);

                        // Smooth scroll to top of listing section when changing pages
                        if (val.currentpage > 1 || (val.currentpage === 1 && $(window).scrollTop() > $isBlock.offset().top)) {
                            $('html, body').animate({
                                scrollTop: $isBlock.offset().top - 80 // Adjust offset for sticky header if needed
                            }, 600);
                        }
                    },
                    complete: function () {
                        currentAjaxRequest = null;
                        $contentWrapper.removeClass('is-loading');
                    }
                });

            } catch (e) {
                console.log(e);
                $contentWrapper.removeClass('is-loading');
            }
        }
    }

    const hleInitSearchFormInterceptor = () => {
        $(document).on('submit', 'form[role="search"]', function (e) {
            e.preventDefault();
            const $form = $(this);
            const action = $form.attr('action') || window.location.origin + '/';
            const keyword = $form.find('input[name="s"]').val() || '';
            
            const url = new URL(action, window.location.origin);
            url.searchParams.set('s', keyword);
            url.hash = 'search-results';
            window.location.href = url.toString();
        });
    };

    const hleInitSearchScroll = () => {
        const isSearchPage = document.body.classList.contains('search-results') || document.body.classList.contains('search');
        if (!isSearchPage || window.location.hash !== '#search-results') return;

        const searchResults = document.getElementById('search-results');
        if (!searchResults) return;

        const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        const headerOffset = 80;
        
        setTimeout(() => {
            const elementPosition = searchResults.getBoundingClientRect().top;
            const offsetPosition = elementPosition + window.pageYOffset - headerOffset;

            window.scrollTo({
                top: offsetPosition,
                behavior: prefersReducedMotion ? 'auto' : 'smooth'
            });
        }, 100);
    };

    $(document).ready(function () {
        btAnimateText('.hle-heading-animation', 'right');
        initBackToTop()
        hleHeroSliders()
        hleInitCounters()
        hleInitParallax()
        hleInitImageParallax()
        hleInitCarsSlider()
        hleInitTestimonialsSlider()
        hleInitFaqs()
        hleVideoPopup()
        hleFilterTours()
        hleFilterPosts()
        hleFilterCars()
        hleInitStarPicker()
        hleInitTourAnchorNav()
        AOS.init();
        hleHeaderTopMobile()
        hleInitSearchFormInterceptor()
        hleInitSearchScroll()
    });
})(jQuery);