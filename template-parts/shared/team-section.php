<?php
// $section_label = get_field('label_team_hp');
// $heading = get_field('hd_team_hp') ?: 'Meet Our Support Team';
// $description = get_field('sub_hd_team_hp') ?: 'We are always here to help you have the best experience.';
// $team_members = get_field('team_list_hp');

$team = get_field('team_st', 'option');
$heading = $team['heading'];
$description = $team['sub_heading'];
$team_members = $team['team_list'];


?>
<section class="hle-section team-section">
    <div class="container">
        <div class="section-heading">
            <?php if (!empty($heading)): ?>
                <h2 class="hle-heading center hle-heading-animation"><?php echo esc_html($heading); ?></h2>
            <?php endif; ?>

            <?php if (!empty($description)): ?>
                <p class="hle-sub-heading"><?php echo esc_html($description); ?></p>
            <?php endif; ?>
        </div>

        <div class="team-section__list" data-aos="fade-up">
            <?php foreach ($team_members as $member): ?>
                <div class="team-item">
                    <div class="team-item__thumb">
                        <img src="<?= esc_url($member['avatar'] ?? ''); ?>"
                            alt="avatar for <?= esc_attr($member['name'] ?? ''); ?>" loading="lazy">
                    </div>

                    <div class="team-item__content">
                        <h3 class="team-item__name h4"><?= esc_html($member['name'] ?? ''); ?></h3>
                        <span class="team-item__position"><?= esc_html($member['position'] ?? ''); ?></span>

                        <?php if (!empty($member['whatsapp'])): ?>
                            <div class="mt-4">
                                <a href="https://wa.me/<?= esc_attr(str_replace(['+', ' '], '', $member['whatsapp'])); ?>"
                                    class="hle-button" target="_blank" rel="noopener noreferrer"
                                    aria-label="Chat with <?= esc_attr($member['name'] ?? ''); ?> on WhatsApp">
                                    <svg width="24px" height="24px" viewBox="0 0 24 24" fill="none"
                                        xmlns="http://www.w3.org/2000/svg">
                                        <path
                                            d="M12.01 2.013c-5.465 0-9.914 4.449-9.914 9.915 0 1.745.454 3.447 1.317 4.954L2 21.996l5.247-1.376a9.855 9.855 0 004.763 1.22h.004c5.463 0 9.911-4.449 9.911-9.915 0-2.646-1.03-5.134-2.9-7.006a9.878 9.878 0 00-7.015-2.906z"
                                            fill="#fff" />
                                    </svg>
                                    WhatsApp
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>