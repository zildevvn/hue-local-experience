(function ($) {
  "use strict";


  function hleModalSearch() {
    const $searchTrigger = $('.header-main__search');
    const $searchModal = $('#search-modal');
    const $searchClose = $('#search-modal-close');
    const $searchOverlay = $('.search-modal__overlay');
    const $searchInput = $('#search-modal-input');
    const $body = $('body');

    function openSearch() {
      $searchModal.addClass('is-active').attr('aria-hidden', 'false');
      $body.addClass('search-modal-open');
      $searchTrigger.addClass('is-active');
      setTimeout(() => {
        $searchInput.focus();
      }, 100);
    }

    function closeSearch() {
      $searchModal.removeClass('is-active').attr('aria-hidden', 'true');
      $body.removeClass('search-modal-open');
      $searchTrigger.removeClass('is-active');
      $searchTrigger.focus();
    }


    $searchTrigger.on('click', function (e) {
      e.preventDefault();
      openSearch();
    });

    $searchClose.on('click', function (e) {
      e.preventDefault();
      closeSearch();
    });

    $searchOverlay.on('click', function (e) {
      closeSearch();
    });

    $(document).on('keydown', function (e) {
      if (e.key === 'Escape' && $searchModal.hasClass('is-active')) {
        closeSearch();
      }
    });
  }


  $(window).on("load", function () {

  })


  $(document).ready(function () {
    hleModalSearch()
  });

})(jQuery);

