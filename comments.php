<?php
if (post_password_required()) {
    return;
}
?>

<div id="comments" class="post-comments-section">
    <?php if (have_comments()): ?>
        <div class="comments-header">
            <h3 class="comments-title widget-title">
                <?php
                $comments_number = get_comments_number();
                if ('1' === $comments_number) {
                    printf(esc_html__('1 Comment', 'hue-local-experience'));
                } else {
                    printf(
                        esc_html(_nx('%1$s Comment', '%1$s Comments', $comments_number, 'comments title', 'hue-local-experience')),
                        number_format_i18n($comments_number)
                    );
                }
                ?>
            </h3>
        </div>

        <ol class="comment-list">
            <?php
            wp_list_comments(array(
                'style'       => 'ol',
                'short_ping'  => true,
                'avatar_size' => 64,
            ));
            ?>
        </ol>

        <?php
        if (get_comment_pages_count() > 1 && get_option('page_comments')) :
            ?>
            <nav class="comment-navigation" role="navigation">
                <div class="nav-links hel-pagination">
                    <?php paginate_comments_links(array('prev_text' => '&laquo; Prev', 'next_text' => 'Next &raquo;')); ?>
                </div>
            </nav>
        <?php endif; ?>
    <?php endif; ?>

    <?php if (!comments_open() && get_comments_number() && post_type_supports(get_post_type(), 'comments')): ?>
        <p class="no-comments"><?php esc_html_e('Comments are closed.', 'hue-local-experience'); ?></p>
    <?php endif; ?>

    <?php
    $commenter = wp_get_current_commenter();
    $req = get_option('require_name_email');
    $aria_req = ($req ? " aria-required='true'" : '');

    $fields = array(
        'author' => '<div class="row"><div class="col-md-4 comment-form-author mb-3"><input id="author" name="author" type="text" placeholder="Name' . ($req ? ' *' : '') . '" value="' . esc_attr($commenter['comment_author']) . '" size="30"' . $aria_req . ' class="form-control" /></div>',
        'email'  => '<div class="col-md-4 comment-form-email mb-3"><input id="email" name="email" type="email" placeholder="Email' . ($req ? ' *' : '') . '" value="' . esc_attr($commenter['comment_author_email']) . '" size="30"' . $aria_req . ' class="form-control" /></div>',
        'url'    => '<div class="col-md-4 comment-form-url mb-3"><input id="url" name="url" type="url" placeholder="Website" value="' . esc_attr($commenter['comment_author_url']) . '" size="30" class="form-control" /></div></div>',
    );

    $comments_args = array(
        'fields'               => $fields,
        'comment_field'        => '<div class="comment-form-comment mb-4"><textarea id="comment" name="comment" placeholder="Your Comment *" cols="45" rows="5" aria-required="true" class="form-control"></textarea></div>',
        'class_form'           => 'comment-form hle-comment-form',
        'class_submit'         => 'submit hle-button w-auto',
        'title_reply_before'   => '<h3 id="reply-title" class="comment-reply-title widget-title">',
        'title_reply_after'    => '</h3>',
        'title_reply'          => 'Leave a Comment',
        'label_submit'         => 'Post Comment',
        'comment_notes_before' => '',
    );

    comment_form($comments_args);
    ?>
</div>
