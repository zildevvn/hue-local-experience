<?php
$price_tour = get_field('price_tour');
?>
<div id="header-top-bar-tour" class="hle-tour-header-bar d-md-none">
    <div class="d-flex align-items-center justify-content-between">
        <?php if (!empty($price_tour)): ?>
            <h2 class="h4 mb-0">From: <?= $price_tour ?>$</h2>
        <?php endif; ?>

        <a href="#tour-booking-form" class="hle-button" label="Book a tour" data-scroll-target="#tour-booking-form">
            Book a tour
        </a>
    </div>
</div>