<?php

/**
 * Template tags
 */

if (!function_exists('hle_template_news_hero_header')) {
    function hle_template_news_hero_header()
    {
        $page_for_posts_id = get_option('page_for_posts');
        $blog_link = get_permalink($page_for_posts_id);
        ob_start(); ?>
        <div class="news-hero text-center text-white">
            <div class="container">
                <div class="hero-inner">
                    <h2 class="hle-title">Blog</h2>
                    <p class="hle-subtitle"><?= get_bloginfo('description'); ?></p>
                </div>
            </div>
        </div>

        <div class="news-header">
            <div class="row">
                <div class="col-md-6">

                </div>
                <div class="col-md-6 text-md-right">
                    <div class="dropdown-alt">
                        <select onchange="window.location = this.value ? this.value  : '<?= esc_url($blog_link); ?>'">
                            <option value="">all</option>
                            <?php wp_get_archives([
                                'type' => 'yearly',
                                'format' => 'option',
                            ]); ?>
                        </select>
                    </div>
                </div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
}