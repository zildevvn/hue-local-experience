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


  function hleMobileMenu() {
    const $menuToggle = $('#menu-toggle');
    const $mobileDrawer = $('#mobile-menu-drawer');
    const $mobileClose = $('#mobile-menu-close');
    const $mobileOverlay = $('.mobile-menu-drawer__overlay');
    const $body = $('body');

    function openMobileMenu() {
      $mobileDrawer.addClass('is-active').attr('aria-hidden', 'false');
      $menuToggle.addClass('is-active').attr('aria-expanded', 'true');
      $body.addClass('mobile-menu-open');
    }

    function closeMobileMenu() {
      $mobileDrawer.removeClass('is-active').attr('aria-hidden', 'true');
      $menuToggle.removeClass('is-active').attr('aria-expanded', 'false');
      $body.removeClass('mobile-menu-open');
      $menuToggle.focus();
    }

    $menuToggle.on('click', function (e) {
      e.preventDefault();
      if ($mobileDrawer.hasClass('is-active')) {
        closeMobileMenu();
      } else {
        openMobileMenu();
      }
    });

    $mobileClose.on('click', function (e) {
      e.preventDefault();
      closeMobileMenu();
    });

    $mobileOverlay.on('click', function () {
      closeMobileMenu();
    });

    $(document).on('keydown', function (e) {
      if (e.key === 'Escape' && $mobileDrawer.hasClass('is-active')) {
        closeMobileMenu();
      }
    });

    // Mobile Sub-menu accordion
    const $menuItemsWithChildren = $('.mobile-navigation .menu-item-has-children');

    $menuItemsWithChildren.each(function () {
      const $li = $(this);
      const $a = $li.children('a');

      const $toggleButton = $('<button type="button" class="sub-menu-toggle" aria-expanded="false" aria-label="Toggle sub-menu"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"></polyline></svg></button>');
      $a.after($toggleButton);

      $toggleButton.on('click', function (e) {
        e.preventDefault();
        e.stopPropagation();

        const $subMenu = $li.children('ul.sub-menu');
        const isExpanded = $toggleButton.attr('aria-expanded') === 'true';

        if (isExpanded) {
          $subMenu.slideUp(250);
          $toggleButton.attr('aria-expanded', 'false').removeClass('is-active');
        } else {
          $subMenu.slideDown(250);
          $toggleButton.attr('aria-expanded', 'true').addClass('is-active');
        }
      });
    });
  }


  $(window).on("load", function () {

  })


  function hleStickyHeader() {
    const $header = $('.header-main');
    if ($header.length === 0) return;

    // Create a placeholder to prevent content jump/flickering when header becomes fixed
    const $placeholder = $('<div class="header-main-placeholder"></div>');
    $placeholder.hide();
    $header.before($placeholder);

    let isFixed = false;
    let headerOffset = $header.offset().top;

    // Recalculate offset on window resize
    $(window).on('resize', function () {
      if (!isFixed) {
        headerOffset = $header.offset().top;
      }
    });

    function toggleSticky() {
      const scrollTop = $(window).scrollTop();

      if (scrollTop > headerOffset) {
        if (!isFixed) {
          $placeholder.height($header.outerHeight()).show();
          $header.addClass('fixed-top');
          isFixed = true;
        }
      } else {
        if (isFixed) {
          $header.removeClass('fixed-top');
          $placeholder.hide().height(0);
          isFixed = false;
        }
      }
    }

    // Use requestAnimationFrame for smooth and optimized scroll handling
    let ticking = false;
    $(window).on('scroll', function () {
      if (!ticking) {
        window.requestAnimationFrame(function () {
          toggleSticky();
          ticking = false;
        });
        ticking = true;
      }
    });

    // Run once on load to ensure correct initial state
    toggleSticky();
  }

  $(document).ready(function () {
    hleModalSearch();
    hleMobileMenu();
    // hleStickyHeader();
  });

})(jQuery);

