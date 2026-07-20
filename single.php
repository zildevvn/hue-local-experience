<?php
/**
 * The template for displaying single posts
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/#single-post
 * @package     hle
 */

get_header();
?>
<div class="single-post-page">
    <main id="primary" class="site-main">
        <?php get_template_part('template-parts/single-post/hero-section'); ?>
        <?php get_template_part('template-parts/single-post/main-section'); ?>
        <?php get_template_part('template-parts/shared/team-section'); ?>
    </main>
</div>
<?php
get_footer();