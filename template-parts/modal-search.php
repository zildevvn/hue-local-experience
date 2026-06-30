<?php
/**
 * Template part for displaying a search modal
 *
 * @package hle
 */
?>
<div id="search-modal" class="search-modal" role="dialog" aria-modal="true" aria-labelledby="search-modal-title" aria-hidden="true">
    <div class="search-modal__overlay"></div>
    <button id="search-modal-close" class="search-modal__close" aria-label="<?php esc_attr_e('Close search', 'hle'); ?>">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <line x1="18" y1="6" x2="6" y2="18"></line>
            <line x1="6" y1="6" x2="18" y2="18"></line>
        </svg>
    </button>
    <div class="search-modal__content">
        <div class="search-modal__inner">
            <h2 id="search-modal-title" class="visually-hidden"><?php esc_html_e('Search Site', 'hle'); ?></h2>
            <form role="search" method="get" class="search-modal__form" action="<?php echo esc_url(home_url('/')); ?>">
                <div class="search-modal__field-wrapper">
                    <input type="search" id="search-modal-input" class="search-modal__input" name="s" placeholder="<?php echo esc_attr_x('Search anything...', 'placeholder', 'hle'); ?>" value="<?php echo get_search_query(); ?>" required autocomplete="off" />
                    <button type="submit" class="search-modal__submit" aria-label="<?php esc_attr_e('Submit Search', 'hle'); ?>">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="11" cy="11" r="8"></circle>
                            <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                        </svg>
                    </button>
                </div>
                <span class="search-modal__info"><?php esc_html_e('Press Enter to search or ESC to close', 'hle'); ?></span>
            </form>
        </div>
    </div>
</div>
