<?php
/**
 * Template Name: Contact
 */
get_header();
?>
<main id="primary" class="site-main">
    <?php get_template_part('template-parts/contact/hero-section'); ?>
    <?php get_template_part('template-parts/contact/main-section'); ?>
    <?php get_template_part('template-parts/contact/map-section'); ?>
    <?php get_template_part('template-parts/shared/team-section'); ?>
</main>
<?php get_footer(); ?>