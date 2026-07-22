<?php
$bg_banner = get_field('bg_banner', 'option');
$title = get_the_title();
$image = get_the_post_thumbnail_url();
$url_image = $bg_banner ?? $image;
hle_hero_section_shared($title, $url_image);
?>