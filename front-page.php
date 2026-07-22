<?php
/**
 * Template Name: Homepage
 * Front Page Template
 */

get_header();
?>
<main id="primary" class="site-main">
    <?php get_template_part('template-parts/home/hero-section'); ?>
    <?php get_template_part('template-parts/home/services-section'); ?>
    <?php get_template_part('template-parts/shared/why-choose-us-section'); ?>
    <?php get_template_part('template-parts/home/achievements-section'); ?>
    <?php get_template_part('template-parts/home/featured-tours'); ?>
    <?php get_template_part('template-parts/home/video-section'); ?>
    <?php get_template_part('template-parts/home/cars-section'); ?>
    <?php get_template_part('template-parts/shared/how-to-book-section'); ?>
    <?php get_template_part('template-parts/home/faqs-section'); ?>
    <?php get_template_part('template-parts/home/testimonials-section'); ?>
    <?php get_template_part('template-parts/home/posts-section'); ?>
    <?php get_template_part('template-parts/shared/team-section'); ?>
</main>
<?php
get_footer();