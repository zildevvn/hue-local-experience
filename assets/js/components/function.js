

import Swiper from 'swiper';
import { Pagination, Navigation, Autoplay, EffectFade } from 'swiper/modules';
import 'swiper/css';
import 'swiper/css/pagination';
import 'swiper/css/navigation';
import 'swiper/css/effect-fade';


(function ($) {
    "use strict";

    // function initHeaderScroll() {

    // }

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
        initBackToTop();

    });
})(jQuery); 