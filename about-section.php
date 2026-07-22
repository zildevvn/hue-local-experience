<?php
/**
 * Template Name: About
 */
get_header();
?>
<main id="primary" class="site-main">
    <?php get_template_part('template-parts/about/hero-section'); ?>
    <?php get_template_part('template-parts/about/intro-section'); ?>
    <?php get_template_part('template-parts/shared/why-choose-us-section'); ?>
    <?php get_template_part('template-parts/shared/how-to-book-section'); ?>
    <?php get_template_part('template-parts/shared/team-section'); ?>
</main>
<?php get_footer(); ?>