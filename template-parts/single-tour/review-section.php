<?php
/**
 * Tour Review Section
 * Uses WordPress native comments system.
 * Rating (1-5) is stored in comment meta key: 'rating'
 */

$post_id       = get_the_ID();
$reviews_per_page = 5;
$current_page  = max(1, get_query_var('cpage', 1));

// ── Fetch approved comments for this tour ────────────────────────────────────
$approved_comments = get_comments([
    'post_id' => $post_id,
    'status'  => 'approve',
    'type'    => 'comment',
]);

$total_reviews = count($approved_comments);

// ── Compute average rating & breakdown ──────────────────────────────────────
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

// ── Fetch unapproved comment if just submitted ───────────────────────────────
$unapproved_comment = null;
$unapproved_id = isset($_GET['unapproved']) ? intval($_GET['unapproved']) : 0;
if ($unapproved_id > 0) {
    $temp_comment = get_comment($unapproved_id);
    if ($temp_comment && intval($temp_comment->comment_post_ID) === $post_id && $temp_comment->comment_approved === '0') {
        // Verify moderation hash to ensure it belongs to the current user
        $expected_hash = isset($_GET['moderation-hash']) ? sanitize_text_field($_GET['moderation-hash']) : '';
        if ($expected_hash === wp_hash($temp_comment->comment_date_gmt)) {
            $unapproved_comment = $temp_comment;
        }
    }
}

// ── Paginated comments for display ──────────────────────────────────────────
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


// ── Helper: render star SVGs ──────────────────────────────────────────────────
function hle_render_stars(float $rating, string $size = '18'): string
{
    $out = '';
    for ($i = 1; $i <= 5; $i++) {
        if ($rating >= $i) {
            $fill = '#f59e0b'; // full star
        } elseif ($rating >= $i - 0.5) {
            $fill = 'url(#half)'; // half star (simple approximation – full amber)
            $fill = '#f59e0b';
        } else {
            $fill = '#e5e7eb'; // empty star
        }
        $out .= '<svg width="' . esc_attr($size) . '" height="' . esc_attr($size) . '" viewBox="0 0 24 24" fill="' . $fill . '" xmlns="http://www.w3.org/2000/svg">
            <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
        </svg>';
    }
    return $out;
}
?>

<div id="tour-review" class="tour-review">

    <h2 class="tour-review__title">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
        </svg>
        Guest Reviews
    </h2>

    <?php if ($total_reviews > 0 || $unapproved_comment): ?>

        <!-- ── Rating Summary ─────────────────────────────────────── -->
        <div class="tour-review__summary">
            <div class="review-summary__score">
                <span class="review-summary__avg"><?php echo esc_html($avg_rating); ?></span>
                <div class="review-summary__stars">
                    <?php echo hle_render_stars($avg_rating, '22'); ?>
                </div>
                <span class="review-summary__count"><?php echo esc_html($total_reviews); ?> review<?php echo $total_reviews !== 1 ? 's' : ''; ?></span>
            </div>

            <div class="review-summary__breakdown">
                <?php for ($star = 5; $star >= 1; $star--): ?>
                    <?php
                    $count = $rating_counts[$star];
                    $pct   = $total_reviews > 0 ? round(($count / $total_reviews) * 100) : 0;
                    ?>
                    <div class="breakdown-row">
                        <span class="breakdown-label"><?php echo esc_html($star); ?> star</span>
                        <div class="breakdown-bar">
                            <div class="breakdown-bar__fill" style="width: <?php echo esc_attr($pct); ?>%"></div>
                        </div>
                        <span class="breakdown-count"><?php echo esc_html($count); ?></span>
                    </div>
                <?php endfor; ?>
            </div>
        </div>

        <!-- ── Review List ──────────────────────────────────────────── -->
        <div class="tour-review__list">
            <?php foreach ($paginated as $review):
                $reviewer_rating = intval(get_comment_meta($review->comment_ID, 'rating', true));
                $initials        = strtoupper(mb_substr($review->comment_author, 0, 1));
            ?>
                <div class="review-item" id="comment-<?php echo $review->comment_ID; ?>">
                    <div class="review-item__header">
                        <div class="review-item__avatar"><?php echo esc_html($initials); ?></div>
                        <div class="review-item__meta">
                            <span class="review-item__author"><?php echo esc_html($review->comment_author); ?></span>
                            <span class="review-item__date"><?php echo esc_html(date_i18n('F j, Y', strtotime($review->comment_date))); ?></span>
                        </div>
                        <?php if ($reviewer_rating): ?>
                            <div class="review-item__stars">
                                <?php echo hle_render_stars($reviewer_rating, '16'); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="review-item__body">
                        <?php echo wp_kses_post(wpautop($review->comment_content)); ?>
                    </div>
                    <?php if ($review->comment_approved == '0'): ?>
                        <p class="review-item__pending"><em><?php esc_html_e('Your review is awaiting moderation.'); ?></em></p>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- ── Pagination ──────────────────────────────────────────── -->
        <?php if ($total_pages > 1): ?>
            <div class="tour-review__pagination">
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

    <?php else: ?>
        <div class="tour-review__empty">
            <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
            </svg>
            <p>Be the first to leave a review for this tour!</p>
        </div>
    <?php endif; ?>

    <!-- ── Review Form ──────────────────────────────────────────── -->
    <?php if (comments_open($post_id)): ?>
        <div class="tour-review__form-wrap">
            <h3 class="tour-review__form-title">Write a Review</h3>

            <?php
            // Show message if comment was just submitted
            if (isset($_GET['unapproved'])):
            ?>
                <div class="review-notice review-notice--pending">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                    Your review has been submitted and is awaiting moderation. Thank you!
                </div>
            <?php elseif (isset($_GET['approved'])): ?>
                <div class="review-notice review-notice--success">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                    Your review has been published. Thank you!
                </div>
            <?php endif; ?>

            <form id="hle-review-form" action="<?php echo esc_url(site_url('/wp-comments-post.php')); ?>" method="post" class="review-form" novalidate>

                <!-- Star Rating Picker -->
                <div class="review-form__rating-field">
                    <label class="review-form__label">Your Rating <span class="required">*</span></label>
                    <div class="star-picker" id="star-picker" role="radiogroup" aria-label="Rating">
                        <?php for ($s = 1; $s <= 5; $s++): ?>
                            <label class="star-picker__label" for="star-<?php echo $s; ?>" title="<?php echo esc_attr($s); ?> star<?php echo $s > 1 ? 's' : ''; ?>">
                                <input type="radio" class="star-picker__input" name="hle_tour_rating" id="star-<?php echo $s; ?>" value="<?php echo $s; ?>" required>
                                <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                                </svg>
                            </label>
                        <?php endfor; ?>
                        <span class="star-picker__text" id="star-picker-text" aria-live="polite">Select a rating</span>
                    </div>
                </div>

                <div class="review-form__row">
                    <!-- Name -->
                    <div class="review-form__field">
                        <label class="review-form__label" for="hle-author">Name <span class="required">*</span></label>
                        <input type="text" id="hle-author" name="author" class="review-form__input"
                            placeholder="Your full name" required maxlength="245"
                            value="<?php echo esc_attr(isset($_POST['author']) ? $_POST['author'] : ''); ?>">
                    </div>
                    <!-- Email -->
                    <div class="review-form__field">
                        <label class="review-form__label" for="hle-email">Email <span class="required">*</span></label>
                        <input type="email" id="hle-email" name="email" class="review-form__input"
                            placeholder="your@email.com" required maxlength="100"
                            value="<?php echo esc_attr(isset($_POST['email']) ? $_POST['email'] : ''); ?>">
                    </div>
                </div>

                <!-- Review Content -->
                <div class="review-form__field">
                    <label class="review-form__label" for="hle-comment">Review <span class="required">*</span></label>
                    <textarea id="hle-comment" name="comment" class="review-form__textarea"
                        placeholder="Share your experience with this tour..." required rows="5" maxlength="65525"></textarea>
                </div>

                <!-- WP hidden fields -->
                <?php wp_nonce_field('comment_nonce_action', 'comment_nonce_field'); ?>
                <input type="hidden" name="comment_post_ID" value="<?php echo esc_attr($post_id); ?>">
                <input type="hidden" name="comment_parent" value="0">

                <button type="submit" class="review-form__submit">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/>
                    </svg>
                    Submit Review
                </button>
            </form>
        </div>
    <?php endif; ?>

</div>

