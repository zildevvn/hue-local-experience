<?php
/**
 * Shared Review Section Component
 * Uses WordPress native comments system.
 * Rating (1-5) is stored in comment meta key: 'rating'
 */

$post_id       = get_the_ID();
$post_type     = get_post_type($post_id);
$reviews_per_page = 5;
$current_page  = max(1, get_query_var('cpage', 1));

// Fetch approved comments
$approved_comments = get_comments([
    'post_id' => $post_id,
    'status'  => 'approve',
    'type'    => 'comment',
]);

$total_reviews = count($approved_comments);

// Compute average rating & breakdown
$rating_sum     = 0;
$rating_counts  = [5 => 0, 4 => 0, 3 => 0, 2 => 0, 1 => 0];

foreach ($approved_comments as $rc) {
    $r = intval(get_comment_meta($rc->comment_ID, 'rating', true));
    if ($r >= 1 && $r <= 5) {
        $rating_sum += $r;
        $rating_counts[$r]++;
    }
}

$avg_rating = $total_reviews > 0 ? round($rating_sum / $total_reviews, 1) : 0;

// Fetch unapproved comment if just submitted
$unapproved_comment = null;
$unapproved_id = isset($_GET['unapproved']) ? intval($_GET['unapproved']) : 0;
if ($unapproved_id > 0) {
    $temp_comment = get_comment($unapproved_id);
    if ($temp_comment && intval($temp_comment->comment_post_ID) === $post_id && $temp_comment->comment_approved === '0') {
        // Verify moderation hash
        $expected_hash = isset($_GET['moderation-hash']) ? sanitize_text_field($_GET['moderation-hash']) : '';
        if ($expected_hash === wp_hash($temp_comment->comment_date_gmt)) {
            $unapproved_comment = $temp_comment;
        }
    }
}

// Paginated comments
$paginated = get_comments([
    'post_id' => $post_id,
    'status'  => 'approve',
    'type'    => 'comment',
    'number'  => $reviews_per_page,
    'offset'  => ($current_page - 1) * $reviews_per_page,
    'order'   => 'DESC',
    'orderby' => 'comment_date',
]);

if ($unapproved_comment) {
    array_unshift($paginated, $unapproved_comment);
}

$total_pages = $total_reviews > 0 ? ceil($total_reviews / $reviews_per_page) : 1;

if (!function_exists('hle_render_stars')) {
    function hle_render_stars(float $rating, string $size = '18'): string
    {
        $out = '';
        for ($i = 1; $i <= 5; $i++) {
            if ($rating >= $i) {
                $fill = '#f59e0b'; // full star
            } elseif ($rating >= $i - 0.5) {
                $fill = '#f59e0b'; // simple amber representation
            } else {
                $fill = '#e5e7eb'; // empty star
            }
            $out .= '<svg width="' . esc_attr($size) . '" height="' . esc_attr($size) . '" viewBox="0 0 24 24" fill="' . $fill . '" xmlns="http://www.w3.org/2000/svg">
                <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
            </svg>';
        }
        return $out;
    }
}

// Determine singular/plural labels and title icons
$post_type_label = ($post_type === 'cars') ? 'car' : 'tour';
$section_id      = ($post_type === 'cars') ? 'car-review' : 'tour-review';
$empty_message   = ($post_type === 'cars') ? 'Be the first to leave a review for this car!' : 'Be the first to leave a review for this tour!';
?>

<div id="<?php echo esc_attr($section_id); ?>" class="hle-reviews">

    <h2 class="hle-reviews__title">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
        </svg>
        <?php esc_html_e('Guest Reviews', 'hue-local-experience'); ?>
    </h2>

    <?php if ($total_reviews > 0 || $unapproved_comment): ?>
        <div class="hle-reviews__container">
            
            <!-- Left Column: Summary Info -->
            <div class="hle-reviews__summary">
                <div class="hle-reviews__score-box">
                    <span class="hle-reviews__avg-score"><?php echo esc_html($avg_rating); ?></span>
                    <div class="hle-reviews__stars">
                        <?php echo hle_render_stars($avg_rating, '24'); ?>
                    </div>
                    <span class="hle-reviews__total-count">
                        <?php 
                        printf(
                            _n('%s Review', '%s Reviews', $total_reviews, 'hue-local-experience'),
                            number_format_i18n($total_reviews)
                        );
                        ?>
                    </span>
                </div>

                <div class="hle-reviews__breakdown">
                    <?php for ($star = 5; $star >= 1; $star--): ?>
                        <?php
                        $count = isset($rating_counts[$star]) ? $rating_counts[$star] : 0;
                        $pct   = $total_reviews > 0 ? round(($count / $total_reviews) * 100) : 0;
                        ?>
                        <div class="hle-reviews__breakdown-row">
                            <span class="hle-reviews__breakdown-label"><?php printf(esc_html__('%d star', 'hue-local-experience'), $star); ?></span>
                            <div class="hle-reviews__progress">
                                <div class="hle-reviews__progress-bar" style="width: <?php echo esc_attr($pct); ?>%"></div>
                            </div>
                            <span class="hle-reviews__breakdown-count"><?php echo esc_html($count); ?></span>
                        </div>
                    <?php endfor; ?>
                </div>
            </div>

            <!-- Right Column: Reviews List & Pagination -->
            <div class="hle-reviews__list-wrapper">
                <div class="hle-reviews__list">
                    <?php foreach ($paginated as $review):
                        $reviewer_rating = intval(get_comment_meta($review->comment_ID, 'rating', true));
                        $initials        = strtoupper(mb_substr($review->comment_author, 0, 1));
                    ?>
                        <div class="hle-reviews__card" id="comment-<?php echo esc_attr($review->comment_ID); ?>">
                            <div class="hle-reviews__card-header">
                                <div class="hle-reviews__avatar"><?php echo esc_html($initials); ?></div>
                                <div class="hle-reviews__author-meta">
                                    <h4 class="hle-reviews__author-name"><?php echo esc_html($review->comment_author); ?></h4>
                                    <span class="hle-reviews__date"><?php echo esc_html(date_i18n('F j, Y', strtotime($review->comment_date))); ?></span>
                                </div>
                                <?php if ($reviewer_rating): ?>
                                    <div class="hle-reviews__card-rating">
                                        <?php echo hle_render_stars($reviewer_rating, '16'); ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="hle-reviews__card-content">
                                <?php echo wp_kses_post(wpautop($review->comment_content)); ?>
                            </div>
                            <?php if ($review->comment_approved == '0'): ?>
                                <div class="hle-reviews__card-pending">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
                                    </svg>
                                    <em><?php esc_html_e('Your review is awaiting moderation.', 'hue-local-experience'); ?></em>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                    <div class="hle-reviews__pagination">
                        <?php
                        echo paginate_comments_links([
                            'base'      => add_query_arg('cpage', '%#%'),
                            'format'    => '',
                            'total'     => $total_pages,
                            'current'   => $current_page,
                            'prev_text' => '&laquo; Prev',
                            'next_text' => 'Next &raquo;',
                            'type'      => 'list',
                        ]);
                        ?>
                    </div>
                <?php endif; ?>
            </div>
            
        </div>
    <?php else: ?>
        <div class="hle-reviews__empty">
            <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
            </svg>
            <p><?php echo esc_html($empty_message); ?></p>
        </div>
    <?php endif; ?>

    <!-- Review Form -->
    <?php if (comments_open($post_id)): ?>
        <div class="hle-reviews__form-container">
            <h3 class="hle-reviews__form-title"><?php esc_html_e('Write a Review', 'hue-local-experience'); ?></h3>

            <?php if (isset($_GET['unapproved'])): ?>
                <div class="hle-reviews__notice hle-reviews__notice--pending">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
                    </svg>
                    <?php esc_html_e('Your review has been submitted and is awaiting moderation. Thank you!', 'hue-local-experience'); ?>
                </div>
            <?php elseif (isset($_GET['approved'])): ?>
                <div class="hle-reviews__notice hle-reviews__notice--success">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/>
                    </svg>
                    <?php esc_html_e('Your review has been published. Thank you!', 'hue-local-experience'); ?>
                </div>
            <?php endif; ?>

            <form id="hle-review-form" action="<?php echo esc_url(site_url('/wp-comments-post.php')); ?>" method="post" class="hle-reviews__form" novalidate>

                <!-- Star Rating Picker -->
                <div class="hle-reviews__field hle-reviews__field--rating">
                    <label class="hle-reviews__label"><?php esc_html_e('Your Rating', 'hue-local-experience'); ?> <span class="required">*</span></label>
                    <div class="star-picker" id="star-picker" role="radiogroup" aria-label="<?php esc_attr_e('Rating', 'hue-local-experience'); ?>">
                        <?php for ($s = 1; $s <= 5; $s++): ?>
                            <label class="star-picker__label" for="star-<?php echo esc_attr($s); ?>" title="<?php printf(esc_attr__('%d star%s', 'hue-local-experience'), $s, $s > 1 ? 's' : ''); ?>">
                                <input type="radio" class="star-picker__input" name="hle_tour_rating" id="star-<?php echo esc_attr($s); ?>" value="<?php echo esc_attr($s); ?>" required>
                                <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                                </svg>
                            </label>
                        <?php endfor; ?>
                        <span class="star-picker__text" id="star-picker-text" aria-live="polite"><?php esc_html_e('Select a rating', 'hue-local-experience'); ?></span>
                    </div>
                </div>

                <div class="hle-reviews__form-row">
                    <!-- Name -->
                    <div class="hle-reviews__field">
                        <label class="hle-reviews__label" for="hle-author"><?php esc_html_e('Name', 'hue-local-experience'); ?> <span class="required">*</span></label>
                        <input type="text" id="hle-author" name="author" class="hle-reviews__input"
                            placeholder="<?php esc_attr_e('Your full name', 'hue-local-experience'); ?>" required maxlength="245"
                            value="<?php echo esc_attr(isset($_POST['author']) ? $_POST['author'] : ''); ?>">
                    </div>
                    <!-- Email -->
                    <div class="hle-reviews__field">
                        <label class="hle-reviews__label" for="hle-email"><?php esc_html_e('Email', 'hue-local-experience'); ?> <span class="required">*</span></label>
                        <input type="email" id="hle-email" name="email" class="hle-reviews__input"
                            placeholder="<?php esc_attr_e('your@email.com', 'hue-local-experience'); ?>" required maxlength="100"
                            value="<?php echo esc_attr(isset($_POST['email']) ? $_POST['email'] : ''); ?>">
                    </div>
                </div>

                <!-- Review Content -->
                <div class="hle-reviews__field">
                    <label class="hle-reviews__label" for="hle-comment"><?php esc_html_e('Review', 'hue-local-experience'); ?> <span class="required">*</span></label>
                    <textarea id="hle-comment" name="comment" class="hle-reviews__textarea"
                        placeholder="<?php printf(esc_attr__('Share your experience with this %s...', 'hue-local-experience'), $post_type_label); ?>" required rows="5" maxlength="65525"></textarea>
                </div>

                <!-- WP hidden fields -->
                <?php wp_nonce_field('comment_nonce_action', 'comment_nonce_field'); ?>
                <input type="hidden" name="comment_post_ID" value="<?php echo esc_attr($post_id); ?>">
                <input type="hidden" name="comment_parent" value="0">

                <button type="submit" class="hle-reviews__submit">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/>
                    </svg>
                    <?php esc_html_e('Submit Review', 'hue-local-experience'); ?>
                </button>
            </form>
        </div>
    <?php endif; ?>

</div>
