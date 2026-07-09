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
    <?php get_template_part('template-parts/home/why-choose-us-section'); ?>
    <?php get_template_part('template-parts/home/achievements-section'); ?>
    <?php get_template_part('template-parts/home/featured-tours'); ?>
</main>
<?php
get_footer();