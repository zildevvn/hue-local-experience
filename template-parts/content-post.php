<?php

/**
 * Template part for displaying posts
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 * @package hle
 */

$post_date = get_the_date('Y/m/d');
$categories = get_the_terms(get_the_ID(), 'category-news');
if (empty($categories) || is_wp_error($categories)) {
    // Fallback to standard WP categories
    $wp_cats = get_the_category();
    $category_name = !empty($wp_cats) ? esc_html($wp_cats[0]->name) : '';
} else {
    $category_name = esc_html($categories[0]->name);
}
?>

<div class="container container-content">

    <article id="post-<?php the_ID(); ?>" <?php post_class('article-post'); ?>>
        <header class="entry-header">
            <div class="entry-meta">
                <span class="posted-on"><?php echo esc_html($post_date); ?></span>
                <?php if ($category_name): ?>
                    <span class="cat-links"><?php echo $category_name; ?></span>
                <?php endif; ?>
            </div>
            <?php the_title('<h1 class="entry-title">', '</h1>'); ?>
            <div class="entry-divider"></div>
        </header>

        <?php if (has_post_thumbnail()): ?>
            <div class="post-thumbnail">
                <?php the_post_thumbnail('full', array('class' => 'img-fluid')); ?>
            </div>
        <?php endif; ?>

        <div class="entry-content">
            <?php
            the_content();
            ?>
        </div>
    </article>
</div>