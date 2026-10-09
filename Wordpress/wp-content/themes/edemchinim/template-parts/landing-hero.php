<?php
/** Informative first screen with an editable review caption. */
$image_id = edemchinim_landing_image_id(edemchinim_landing_sub('hero_background'));
$mobile_image_id = edemchinim_landing_image_id(edemchinim_landing_sub('hero_background_mobile'));
$image_url = $image_id ? wp_get_attachment_image_url($image_id, 'full') : '';
$mobile_image_url = $mobile_image_id ? wp_get_attachment_image_url($mobile_image_id, 'full') : '';
$image_url = $image_url ?: get_template_directory_uri() . '/img/back.png';
$review_caption = edemchinim_get_option_field('reviews_count_text', 'Отзывы клиентов');
$prices = edemchinim_landing_sub('hero_prices', array());
if (! $prices) {
    $prices = array(
        array('label' => 'Выезд в пределах МКАД —', 'price' => 'от 1 500 ₽'),
        array('label' => 'Выезд за МКАД —', 'price' => 'от 2 000 ₽ + 40 ₽/км'),
    );
}
$rating = max(0, min(5, (float) edemchinim_landing_sub('hero_rating', 4.9)));
$title = trim(preg_replace('/24\s*\/\s*7/u', '', edemchinim_landing_sub('hero_title', 'ВЫЕЗДНОЙ ШИНОМОНТАЖ')));
$title_lines = preg_split('/\s+/u', $title, 2);
$default_benefits = array(
    array('Быстрый выезд', 'от 15 минут', 'M12 8v5l3 2 M9 2h6 M12 2v3 M18 6l2-2 M21 14a9 9 0 1 1-18 0 9 9 0 0 1 18 0'),
    array('Прозрачная стоимость', 'до начала работ', 'M6 3h9l4 4v14l-3-2-4 2-4-2-3 2V3Z M14 3v5h5 M8 11h7 M8 15h7'),
    array('Гарантия', 'на работы и материалы', 'M12 2 3 6v6c0 5 4 8 9 10 5-2 9-5 9-10V6l-9-4Z M8 12l3 3 5-6'),
);
$benefits = edemchinim_landing_sub('hero_benefits', array());
if (! $benefits) {
    $benefits = array_map(static function ($benefit) {
        return array('title' => $benefit[0], 'text' => $benefit[1], 'icon' => '');
    }, $default_benefits);
}
$benefits = array_slice($benefits, 0, 3);
?>
<section class="landing-hero" aria-labelledby="landing-hero-title">
    <picture class="landing-hero__picture" aria-hidden="true">
        <?php if ($mobile_image_url) : ?><source media="(max-width: 850px)" srcset="<?php echo esc_url($mobile_image_url); ?>"><?php endif; ?>
        <img src="<?php echo esc_url($image_url); ?>" alt="" decoding="async" fetchpriority="high">
    </picture>
    <div class="landing-hero__inner">
        <div class="landing-hero__content" id="top">
            <a class="landing-hero__rating" href="#reviews" aria-label="<?php echo esc_attr(sprintf('Наш рейтинг %s из 5. Отзывы клиентов', number_format($rating, 1, ',', ''))); ?>">
                <span class="landing-hero__rating-label">НАШ РЕЙТИНГ</span>
                <span class="landing-hero__rating-row">
                    <span class="landing-hero__stars" aria-hidden="true">
                        <?php for ($index = 0; $index < 5; $index++) : $cut = 100 - max(0, min(100, ($rating - $index) * 100)); ?>
                            <svg viewBox="0 0 24 24"><path class="landing-hero__star-base" d="m12 2 3.1 6.3 6.9 1-5 4.9 1.2 6.8-6.2-3.2-6.2 3.2 1.2-6.8-5-4.9 6.9-1Z"/><path class="landing-hero__star-fill" style="clip-path: inset(0 <?php echo esc_attr($cut); ?>% 0 0)" d="m12 2 3.1 6.3 6.9 1-5 4.9 1.2 6.8-6.2-3.2-6.2 3.2 1.2-6.8-5-4.9 6.9-1Z"/></svg>
                        <?php endfor; ?>
                    </span>
                    <span class="landing-hero__rating-value"><strong><?php echo esc_html(number_format($rating, 1, ',', '')); ?></strong> из 5</span>
                    <span class="landing-hero__rating-count"><?php echo esc_html($review_caption); ?></span>
                </span>
            </a>
            <h1 class="landing-hero__title" id="landing-hero-title"><span class="landing-hero__title-first"><?php echo esc_html($title_lines[0]); ?></span><span class="landing-hero__title-second"><?php echo esc_html($title_lines[1] ?? 'ШИНОМОНТАЖ'); ?></span><span class="landing-hero__hours" aria-label="24/7"><span aria-hidden="true">24/7</span></span></h1>
            <p class="landing-hero__lead"><?php echo esc_html(edemchinim_landing_sub('hero_offer', 'Приедем и починим на месте.')); ?></p>
            <ul class="landing-hero__benefits">
                <?php foreach ($benefits as $index => $benefit) :
                    $icon = edemchinim_sanitize_icon($benefit['icon'] ?? '');
                    if (stripos($icon, '<svg') === false) {
                        $icon = '<svg viewBox="0 0 24 24" fill="none"><path d="' . esc_attr($default_benefits[$index][2]) . '" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg>';
                    }
                ?>
                    <li><span class="landing-hero__benefit-icon" aria-hidden="true"><?php echo $icon; ?></span><div><strong><?php echo esc_html($benefit['title'] ?? ''); ?></strong><span><?php echo esc_html($benefit['text'] ?? ''); ?></span></div></li>
                <?php endforeach; ?>
            </ul>
            <div class="landing-hero__visual-space" aria-hidden="true"></div>
            <a class="landing-hero__cta landing-cta" href="<?php echo esc_url(edemchinim_calculator_url()); ?>" data-open-calculator><span>Рассчитать стоимость</span><?php edemchinim_landing_arrow(); ?></a>
            <div class="landing-hero__prices" aria-label="Стоимость выезда">
                <?php foreach ($prices as $price) : ?>
                    <div class="landing-hero__price"><span><?php echo esc_html($price['label'] ?? ''); ?></span><strong><?php echo esc_html($price['price'] ?? ''); ?></strong></div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</section>
