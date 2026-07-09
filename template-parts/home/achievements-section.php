<?php
$bg = get_field('bg_achievements_hp');
$achievements = get_field('list_achievements_hp');
?>
<section class="hle-section achievements-section">
    <?php if (!empty($bg) && isset($bg)): ?>
        <div class="hle-section__bg">
            <img src="<?= $bg ?>" alt="image for achievements">
        </div>
    <?php endif; ?>

    <div class="container">
        <?php if (!empty($achievements) && isset($achievements)): ?>
            <div class="achievements-section__list" data-aos="fade-up">
                <?php foreach ($achievements as $key => $achievement): ?>
                    <div class="achievement-item">
                        <div
                            class="achievement-item__icon d-flex align-items-center align-content-center justify-content-center">
                            <img src="<?= $achievement['icon'] ?>"
                                alt="icon-achievement for <?= $achievement['heading'] ?? '' ?>">
                        </div>
                        <h3 class="achievement-item__number h2 hle-counter">
                            <?= $achievement['number'] ?? '' ?>
                        </h3>
                        <p class="achievement-item__label mb-0">
                            <?= $achievement['lable'] ?? '' ?>
                        </p>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>