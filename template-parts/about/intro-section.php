<?php
$heading = get_field('heading_intro_ab');
$sub_hd = get_field('sub_hd_intro_ab');
$desc = get_field('desc_intro_ab');
$img = get_field('img_intro_ab');
?>
<section class="hle-section intro-section">
    <div class="container">
        <div class="intro-section__inner d-flex justify-content-between">
            <div class="intro-section__content d-flex align-items-center">
                <div class="content-warp">
                    <?php if (!empty($sub_hd)): ?>
                        <p class="sub-heading mb-0">
                            <?= $sub_hd ?? '' ?>
                        </p>
                    <?php endif; ?>

                    <?php if (!empty($heading)): ?>
                        <h2 class="hle-heading  hle-heading-animation">
                            <?= $heading ?? '' ?>
                        </h2>
                    <?php endif; ?>

                    <?php if (!empty($desc)): ?>
                        <div class="intro-section__desc">
                            <?= $desc ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="intro-section__image">
                <?php if (!empty($img)): ?>
                    <img src="<?= $img ?>" alt="image for <?= $heading ?>" />
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>