<?php

function add_gtm_head_script()
{
    ?>
    
    <?php
}
add_action('wp_head', 'add_gtm_head_script');


function add_gtm_body_script()
{
    ?>
    
    <?php
}
add_action('wp_body_open', 'add_gtm_body_script');