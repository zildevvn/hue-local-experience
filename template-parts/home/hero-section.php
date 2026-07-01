<?php
$sliders = get_field('sliders_hero_home');
?>

<?php if (!empty($sliders) && isset($sliders)): ?>
    <section class="hle-section hero-section">
        <div class="hero-section-sliders swiper">
            <div class="swiper-wrapper">
                <?php foreach ($sliders as $slider): ?>
                    <div class="swiper-slide slider-item">
                        <div class="slider-item__bg">
                            <img src="<?= $slider['image'] ?>" alt="image for slider">
                        </div>
                        <div class="container">

                            <?php if (!empty($slider['heading'])): ?>
                                <h1>
                                    <?= $slider['heading'] ?>
                                </h1>
                            <?php endif; ?>

                            <?php if (!empty($slider['sub_heading'])): ?>
                                <p>
                                    <?= $slider['sub_heading'] ?>
                                </p>
                            <?php endif; ?>

                            <?php if (!empty($slider['button'])): ?>
                                <a href="<?= $slider['button']['url'] ?>" target="<?= $slider['button']['target'] ?>"
                                    class="hle-button">
                                    <?= $slider['button']['title'] ?>
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach ?>
            </div>
            <div class="swiper-pagination"></div>
        </div>

        <div class="hero-section__overlay"> </div>
    </section>
<?php endif; ?>