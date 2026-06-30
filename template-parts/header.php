<?php
$custom_logo_id = get_theme_mod('custom_logo');
$logo_url = wp_get_attachment_url($custom_logo_id);
$cta_header = get_field('cta_header', 'option');
?>

<header id="site-header" class="header-main">
    <div class="container">
        <div class="d-flex align-items-center justify-content-between">
            <div class="header-main__logo">
                <a class="d-flex " href="<?php echo home_url(); ?>" aria-label="<?php echo get_bloginfo('name'); ?>">
                    <img src="<?php echo $logo_url; ?>" alt="<?php echo get_bloginfo('name'); ?>">
                </a>
            </div>

            <div class="header-main__menu d-none d-md-block">
                <?php if (has_nav_menu('primary-menu')): ?>
                    <?php wp_nav_menu(array('theme_location' => 'primary-menu', 'menu_class' => 'primary-menu')) ?>
                <?php endif; ?>
            </div>

            <div class=" header-main__actions d-flex align-items-center justify-content-end gap-3">
                <button class="header-main__search d-flex align-items-center justify-content-center"
                    aria-label="<?php esc_attr_e('Open search', 'hle'); ?>" type="button">
                    <svg width="24px" height="24px" viewBox="0 0 24 24" stroke-width="1.5" fill="none"
                        xmlns="http://www.w3.org/2000/svg">
                        <path d="M17 17L21 21" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"
                            stroke-linejoin="round"></path>
                        <path
                            d="M3 11C3 15.4183 6.58172 19 11 19C13.213 19 15.2161 18.1015 16.6644 16.6493C18.1077 15.2022 19 13.2053 19 11C19 6.58172 15.4183 3 11 3C6.58172 3 3 6.58172 3 11Z"
                            stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                        </path>
                    </svg>
                </button>

                <?php if (!empty($cta_header)): ?>
                    <div class=" header-main__cta d-none d-md-block">
                        <a class="hle-button" href="<?php echo $cta_header['url']; ?>">
                            <?php echo $cta_header['title']; ?>
                        </a>
                    </div>
                <?php endif; ?>

                <div class="header-humberger d-block d-md-none ">
                    <button type="button" id="menu-toggle" class="menu-toggle" aria-label="Toggle menu">
                        <span></span>
                        <span></span>
                        <span></span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</header>