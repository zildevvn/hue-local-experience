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
    <div class="container">
        <h1>404</h1>
        <h2>Oops! Page not found.</h2>
        <p>Sorry, the page you are looking for does not exist.</p>
        <?php get_template_part('template-parts/cta', null, ['cta' => $cta_group]); ?>
    </div>
</main><!-- #main -->
<?php
get_footer();