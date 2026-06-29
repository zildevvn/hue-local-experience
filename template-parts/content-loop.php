<?php
/**
 * Post loop
 */
$index = 1;
?>
<div class="post-loop-container">
    <div class="row">
        <?php
        /* Start the Loop */
        while (have_posts()):
            the_post();
            do_action('hle_hook_post_loop_item', get_the_ID(), $index);
            $index++;
        endwhile;
        ?>
    </div>
</div>