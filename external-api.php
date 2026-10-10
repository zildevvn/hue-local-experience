<?php
/**
 * Template Name: External API
 */

// Require login.
if (!is_user_logged_in()) {
    auth_redirect();
}

$current_user = wp_get_current_user();

// Get the WordPress Administration Email Address.
$admin_email = (string) get_option('admin_email');

// Check user role.
$is_accountant = in_array(
    'accountant',
    (array) $current_user->roles,
    true
);

// Check whether the user's email matches the site admin email.
$is_admin_email = (
    $admin_email !== ''
    && strcasecmp(trim($current_user->user_email), trim($admin_email)) === 0
);

// Deny access if neither condition is met.
if (!$is_accountant && !$is_admin_email) {
    wp_safe_redirect(home_url('/'));
    exit;
}

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