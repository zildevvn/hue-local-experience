<?php
$services = get_field('items_sr_hp');

?>
<?php if (!empty($services) && isset($services)): ?>
    <section class="hle-section services-section">
        <div class="container">
            <div class="services-section__list" data-aos="fade-up">
                <?php foreach ($services as $key => $service): ?>
                    <div class="service-item">
                        <span class="count">
                            <?= $key + 1 ?>
                        </span>

                        <?php if (!empty($service['icon']) && isset($service['icon'])): ?>
                            <div class="service-item__icon d-flex align-items-center align-content-center justify-content-center">
                                <img src="<?= $service['icon'] ?>" alt="icon-service for <?= $service['heading'] ?? '' ?>">
                            </div>
                        <?php endif; ?>

                        <h3 class="service-item__title h4">
                            <?= $service['heading'] ?? '' ?>
                        </h3>

                        <p class="service-item__desc mb-0">
                            <?= $service['desc'] ?? '' ?>
                        </p>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
<?php endif; ?>