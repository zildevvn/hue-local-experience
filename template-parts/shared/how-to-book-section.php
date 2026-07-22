<?php
$heading = get_field('hd_htb_hp');
$sub_heading = get_field('sub_hd_htb_hp');
$bg = get_field('bg_htb_hp');
$steps = get_field('steps_htb_hp');

?>
<section class="hle-section how-to-book-section">
    <?php if (!empty($bg)): ?>
        <div class="hle-section__bg js-parallax-container">
            <img src="<?= esc_url($bg); ?>" alt=" background image for  <?= esc_attr($heading); ?>" data-parallax="true"
                data-parallax-speed="0.2">
        </div>
    <?php endif; ?>

    <div class="container">

        <div class="section-heading">
            <?php if ($heading): ?>
                <h2 class="hle-heading center hle-heading-animation">
                    <?php echo esc_html($heading); ?>
                </h2>
            <?php endif; ?>

            <?php if ($sub_heading): ?>
                <p class="hle-sub-heading">
                    <?php echo esc_html($sub_heading); ?>
                </p>
            <?php endif; ?>
        </div>

        <?php if (!empty($steps)): ?>
            <div class="steps-list">
                <?php foreach ($steps as $key => $step): ?>
                    <div class="step-item" data-aos="fade-up">
                        <div class="step-item__number">
                            <div class="bg-image">
                                <img src="<?php echo get_template_directory_uri(); ?>/assets/images/img-bubble-num-02.png"
                                    alt="image step <?= $key + 1 ?>">
                            </div>
                            <span class="h2">
                                <?= $key + 1 ?>
                            </span>
                        </div>

                        <h3 class="h5">
                            <?= $step['heading'] ?>
                        </h3>

                        <p class="mb-0">
                            <?= $step['desc'] ?>
                        </p>

                        <div class="step-item__arrow">
                            <img src="<?= get_template_directory_uri() ?>/assets/images/icon-arrow-long.svg"
                                alt="arrow for step <?= $key + 1 ?>">
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>