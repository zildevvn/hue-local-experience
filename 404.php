<?php
/**
 * The template for displaying 404 pages (not found)
 * @link https://codex.wordpress.org/Creating_an_Error_404_Page
 * @package hle
 */

get_header();

$cta_group = [
    'cta' => [
        'url' => home_url(),
        'title' => 'Go back to Home',
        'target' => '_self'
    ],
    'cta_style' => 'primary'
];
?>
<main class="site-main">
    <section class="error-404-section hle-section">
        <div class="container">
            <div class="error-404-section__code">404</div>
            <h1 class="error-404-section__title">Oops! Page Not Found</h1>
            <p class="error-404-section__desc">The page you are looking for might have been removed, had its name changed, or is temporarily unavailable. Let's get you back on track.</p>
            
            <div class="search-form-wrapper">
                <form role="search" method="get" action="<?php echo esc_url(home_url('/')); ?>" class="modern-search-form">
                    <div class="input-group">
                        <svg class="search-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="11" cy="11" r="8"></circle>
                            <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                        </svg>
                        <input type="search" name="s" placeholder="Search for tours, cars, or articles..." required />
                        <button type="submit" class="hle-button hle-button--primary search-submit">Search</button>
                    </div>
                </form>
            </div>

            <a href="<?php echo esc_url(home_url('/')); ?>" class="hle-button hle-button--primary back-home-btn">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="19" y1="12" x2="5" y2="12"></line>
                    <polyline points="12 19 5 12 12 5"></polyline>
                </svg>
                Back to Home
            </a>
        </div>
    </section>
</main><!-- #main -->
<?php
get_footer();