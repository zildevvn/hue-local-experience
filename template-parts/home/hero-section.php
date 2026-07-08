<?php
$sliders = get_field('sliders_hero_home');

if (!function_exists('hle_split_words_preserve_html')) {
    function hle_split_words_preserve_html($html)
    {
        if (empty($html))
            return '';
        // Match HTML tags, whitespace sequences, or words
        preg_match_all('/(<[^>]+>)|(\s+)|([^<>\s]+)/', $html, $matches);

        $word_count = 0;
        foreach ($matches[0] as $token) {
            if (!preg_match('/^<[^>]+>$/', $token) && !preg_match('/^\s+$/', $token)) {
                $word_count++;
            }
        }

        $result = '';
        $current_word = 0;
        foreach ($matches[0] as $token) {
            if (preg_match('/^<[^>]+>$/', $token) || preg_match('/^\s+$/', $token)) {
                $result .= $token;
            } else {
                // Calculate reverse index so right-most words animate first
                $reverse_index = $word_count - $current_word - 1;
                $result .= '<span class="split-word" style="--word-index: ' . $reverse_index . ';">' . $token . '</span>';
                $current_word++;
            }
        }
        return $result;
    }
}
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
                                    <?= hle_split_words_preserve_html($slider['heading']) ?>
                                </h1>
                            <?php endif; ?>

                            <?php if (!empty($slider['sub_heading'])): ?>
                                <p class="sub-heading">
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
            <div class="swiper-button-prev">
                <svg width="24px" height="24px" viewBox="0 0 24 24" stroke-width="1.5" fill="none"
                    xmlns="http://www.w3.org/2000/svg" color="#000000">
                    <path d="M21 12L3 12M3 12L11.5 3.5M3 12L11.5 20.5" stroke="#000000" stroke-width="1.5"
                        stroke-linecap="round" stroke-linejoin="round"></path>
                </svg>
            </div>

            <div class="swiper-button-next"><svg width="24px" height="24px" viewBox="0 0 24 24" stroke-width="1.5"
                    fill="none" xmlns="http://www.w3.org/2000/svg" color="#000000">
                    <path d="M3 12L21 12M21 12L12.5 3.5M21 12L12.5 20.5" stroke="#000000" stroke-width="1.5"
                        stroke-linecap="round" stroke-linejoin="round"></path>
                </svg></div>
        </div>

        <div class="hero-section__overlay"> </div>
    </section>
<?php endif; ?>