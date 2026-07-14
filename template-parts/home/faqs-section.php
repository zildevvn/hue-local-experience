<?php
$heading = get_field('hd_faqs_hp');
$description = get_field('desc_faqs_hp');
$faqs = get_field('faqs_list_hp');
?>
<section class="hle-section faqs-section">
    <div class="container">
        <div class="faqs-section-inner d-flex ">
            <div class="faqs-section-left">
                <?php if (!empty($heading)): ?>
                    <h2 class="hle-heading hle-heading-animation"><?php echo esc_html($heading); ?></h2>
                <?php endif; ?>

                <?php if (!empty($description)): ?>
                    <div class="hle-sub-heading"><?php echo $description; ?></div>
                <?php endif; ?>
            </div>
            <div class="faqs-section-right">
                <?php if (!empty($faqs)): ?>
                    <div class="faqs-list">
                        <?php foreach ($faqs as $key => $faq): ?>
                            <div class="faq-item" data-aos="fade-up" data-aos-delay="<?= $key * 100 ?>">
                                <h3 class="faq-question h6 d-flex align-items-center" tabindex="0" role="button"
                                    aria-expanded="false">
                                    <div class="icon d-flex align-items-center align-content-center justify-content-center">
                                        <svg width="24px" height="24px" stroke-width="1.5" viewBox="0 0 24 24" fill="none"
                                            xmlns="http://www.w3.org/2000/svg" color="#000000">
                                            <path
                                                d="M7.90039 8.07954C7.90039 3.30678 15.4004 3.30682 15.4004 8.07955C15.4004 11.4886 11.9913 10.8067 11.9913 14.8976"
                                                stroke="#000000" stroke-width="1.5" stroke-linecap="round"
                                                stroke-linejoin="round"></path>
                                            <path d="M12 19.01L12.01 18.9989" stroke="#000000" stroke-width="1.5"
                                                stroke-linecap="round" stroke-linejoin="round"></path>
                                        </svg>
                                    </div>

                                    <?= $faq['question'] ?>

                                    <div class="arrow">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="plus" width="18" height="18"
                                            viewBox="0 0 160 160">
                                            <rect class="vertical-line" x="70" width="15" height="160" rx="7" ry="7"></rect>
                                            <rect class="horizontal-line" y="70" width="160" height="15" rx="7" ry="7"></rect>
                                        </svg>
                                    </div>
                                </h3>
                                <div class="faq-answer">
                                    <?= $faq['answer'] ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>