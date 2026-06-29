<?php
$page_id = get_the_ID();
$count = count(get_field('hle_page_content', $page_id));
if (have_rows('hle_page_content', $page_id)) {
    while (have_rows('hle_page_content', $page_id)) {
        $i = get_row_index() + 1;
        the_row();
        $layout_name = str_replace('_', '-', get_row_layout());

        $layout_class = 'layout--' . $layout_name;

        $section_count = 'layout--single';
        if ($i == 1) {
            $section_class = 'layout--first';
        } elseif ($i == $count) {
            $section_class = 'layout--last';
        }
        if ($count > 1) {
            $section_count = 'layout--multiple';
        }

        $html_anchor = get_sub_field('html_anchor');
        $layout_classes = array($layout_class, $section_class);

        $anchor_attr = $html_anchor ? 'id="' . esc_attr($html_anchor) . '" ' : '';
        echo '<div ' . $anchor_attr . 'class="layout ' . implode(" ", $layout_classes) . '">';
        get_template_part('layout/' . $layout_name);
        echo '</div>';
    }
}
?>