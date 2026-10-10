<?php
/**
 * Template Name: External API
 */

get_header();
?>

<main id="primary" class="site-main container">
    <div id="vm-external-orders" class="vm-external-orders">
        <?php get_template_part('template-parts/external-api/filters'); ?>
        <?php get_template_part('template-parts/external-api/toolbar'); ?>
        <?php get_template_part('template-parts/external-api/orders-table'); ?>
        <?php get_template_part('template-parts/external-api/pagination'); ?>
    </div>
    <?php get_template_part('template-parts/external-api/confirm-modal'); ?>
</main>
<?php get_footer(); ?>