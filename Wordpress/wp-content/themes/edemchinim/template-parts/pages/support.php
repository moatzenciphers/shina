<?php
use Edemchinim\Calculator\Config;
use Edemchinim\Content\Blocks;
use Edemchinim\Content\Pages;
use Edemchinim\Content\Reviews;
use Edemchinim\Content\Services;
use Edemchinim\Support\Fields;
use Edemchinim\Support\Formatting;

$kind = $args['kind'];
$settings_id = $args['settings_id'];
$fallbacks = array('reviews' => 'Отзывы клиентов', 'works' => 'Фотографии работ', 'contacts' => 'Контакты');
$title = Fields::get('support_title', $settings_id, is_page() ? get_the_title($settings_id) : $fallbacks[$kind]);
$intro = Fields::get('support_intro', $settings_id, '');
$query = $args['query'] ?? null;
if ($kind === 'reviews' && ! $query) $query = new WP_Query(array('post_type' => 'reviews', 'post_status' => 'publish', 'posts_per_page' => 9, 'paged' => Pages::pageNumber(), 'meta_query' => Reviews::publicMeta()));
if ($kind === 'works') {
    $chosen = array_values(array_filter(array_map([Blocks::class, 'imageId'], (array) Fields::get('works_images', $settings_id, array()))));
    $query_args = array('post_type' => 'attachment', 'post_status' => 'inherit', 'post_mime_type' => 'image', 'posts_per_page' => 12, 'paged' => Pages::pageNumber());
    if ($chosen) { $query_args['post__in'] = $chosen; $query_args['orderby'] = 'post__in'; }
    else $query_args['meta_query'] = array(array('key' => 'shina_photo_is_active', 'value' => '1'));
    $query = new WP_Query($query_args);
}
get_header();
?>
<div class="landing landing--wp-header" data-landing>
    <main class="support-page landing-section" id="primary">
        <header class="support-page__header">
            <nav class="support-page__breadcrumbs" aria-label="Хлебные крошки"><a href="<?php echo esc_url(home_url('/')); ?>">Главная</a><span aria-hidden="true">/</span><span aria-current="page"><?php echo esc_html($title); ?></span></nav>
            <h1 class="support-page__title landing-title"><?php echo esc_html($title); ?></h1>
            <?php if ($intro) : ?><p class="support-page__intro landing-text"><?php echo esc_html($intro); ?></p><?php endif; ?>
        </header>
        <?php if ($kind === 'contacts') {

$phone = Fields::option('shina_phone', '+7 (499) 648-19-90');
$email = Fields::option('shina_email', '');
$schedule = Fields::option('shina_work_schedule', '24/7');
$details = Fields::option('shina_legal_details', '');
$links = Fields::option('shina_contact_links', array());
?>
<div class="contacts-page__layout">
    <div class="contacts-page__details">
        <?php if ($phone) : ?><a class="contacts-page__phone" href="<?php echo esc_url(Formatting::phoneHref($phone)); ?>"><?php echo esc_html($phone); ?></a><?php endif; ?>
        <dl class="contacts-page__facts"><div><dt>График работы</dt><dd><?php echo esc_html($schedule); ?></dd></div><div><dt>Зона выезда</dt><dd>Москва и Московская область</dd></div><div><dt>Формат работы</dt><dd>Только выезд к автомобилю</dd></div><?php if ($email && is_email($email)) : ?><div><dt>Электронная почта</dt><dd><a href="mailto:<?php echo esc_attr($email); ?>"><?php echo esc_html($email); ?></a></dd></div><?php endif; ?></dl>
        <?php if (is_array($links)) : ?><div class="contacts-page__links"><?php foreach ($links as $link) : if (empty($link['label']) || empty($link['url'])) continue; ?><a class="landing-more-link" href="<?php echo esc_url($link['url']); ?>"><?php echo esc_html($link['label']);
 Blocks::arrow(); ?></a><?php endforeach; ?></div><?php endif; ?>
        <a class="landing-cta contacts-page__cta" href="<?php echo esc_url(Config::url()); ?>" data-open-calculator><span>Рассчитать стоимость</span><?php Blocks::arrow(); ?></a>
    </div>
    <section class="contacts-page__coverage" aria-labelledby="coverage-title"><h2 class="landing-subtitle" id="coverage-title">Зона выезда</h2><div class="contacts-page__map" data-coverage-map>
    <div class="map-panel__canvas" data-coverage-canvas role="application" aria-label="Карта зоны выезда: Москва и Московская область"></div>
    <p class="contacts-page__map-status" role="status" aria-live="polite" data-coverage-status>Загружаем карту…</p>
    <div class="map-overlay"><div class="map-overlay__zoom" aria-label="Масштаб карты">
        <button class="map-overlay__control" type="button" data-coverage-zoom="in" aria-label="Приблизить карту" disabled><svg viewBox="0 0 24 24" width="24" height="24" fill="none" aria-hidden="true"><path d="M5 12h14M12 5v14" stroke="currentColor" stroke-width="2"/></svg></button>
        <button class="map-overlay__control" type="button" data-coverage-zoom="out" aria-label="Отдалить карту" disabled><svg viewBox="0 0 24 24" width="24" height="24" fill="none" aria-hidden="true"><path d="M5 12h14" stroke="currentColor" stroke-width="2"/></svg></button>
    </div><button class="map-overlay__location" type="button" data-coverage-location aria-label="Моё местоположение" disabled><svg viewBox="0 0 24 24" width="24" height="24" fill="none" aria-hidden="true"><circle cx="12" cy="12" r="7" stroke="currentColor" stroke-width="1.6"/><path d="M12 2v3M12 19v3M2 12h3M19 12h3" stroke="currentColor" stroke-width="1.6"/></svg></button></div>
</div>
</section>
</div>
<?php if ($details) : ?><section class="contacts-page__legal" aria-labelledby="legal-title"><h2 class="landing-title landing-title--section" id="legal-title">Реквизиты</h2><p class="landing-text"><?php echo nl2br(esc_html($details)); ?></p></section><?php endif; ?>
<?php  } elseif ($kind === 'works') {

  ?>
<?php if ($query->have_posts()) : ?>
<div class="works-page__grid">
    <?php foreach ($query->posts as $image) :
        $id = $image->ID;
        $full = wp_get_attachment_image_url($id, 'full');
        if (! $full) continue;
        $label = Fields::get('shina_photo_label', $id, '');
        $caption = wp_get_attachment_caption($id);
    ?>
    <figure class="works-page__item"><a class="works-page__photo" href="<?php echo esc_url($full); ?>" data-collection-photo data-gallery="work-photos" <?php if ($label) : ?>data-title="<?php echo esc_attr($label); ?>"<?php endif; ?> aria-label="<?php echo esc_attr($label ? 'Открыть фото: ' . $label : 'Открыть фотографию работы'); ?>"><?php echo wp_get_attachment_image($id, 'large', false, array('loading' => 'lazy', 'decoding' => 'async')); ?></a><?php if ($label || $caption) : ?><figcaption><?php if ($label) : ?><strong><?php echo esc_html($label); ?></strong><?php endif;
 if ($caption) : ?><span><?php echo esc_html($caption); ?></span><?php endif; ?></figcaption><?php endif; ?></figure>
    <?php endforeach; ?>
</div>
<?php Pages::pagination($query); ?>
<?php else : ?><p class="support-page__empty landing-text">Фотографии пока не добавлены.</p><?php endif; ?>
<?php  } elseif ($kind === 'reviews') {

$summary = Reviews::summary();
?>
<?php if ($summary['count']) : ?>
<div class="reviews-page__summary"><span class="reviews-page__score"><?php echo esc_html(number_format($summary['rating'], 1, ',', '')); ?><span> / 5</span></span><span><?php echo esc_html(Fields::option('reviews_count_text', 'Отзывы клиентов')); ?></span></div>
<?php endif; ?>
<?php if ($query->have_posts()) : ?>
<div class="reviews-page__list">
    <?php foreach ($query->posts as $review_post) : $review = Reviews::prepare($review_post); ?>
    <article class="reviews-page__item" id="review-<?php echo esc_attr($review['id']); ?>">
        <header class="reviews-page__meta">
            <div class="reviews-page__identity">
                <span class="reviews-page__name"><?php echo esc_html($review['author']); ?></span>
                <?php if ($review['date']) : ?><time datetime="<?php echo esc_attr($review['date']); ?>"><?php echo esc_html($review['date_label']); ?></time><?php endif; ?>
            </div>
            <span class="reviews-page__stars" role="img" aria-label="<?php echo esc_attr(sprintf('Оценка %s из 5', $review['rating'])); ?>"><?php echo esc_html(str_repeat('★', (int) round($review['rating'])) . str_repeat('☆', 5 - (int) round($review['rating']))); ?></span>
        </header>
        <div class="reviews-page__body">
            <div><?php if ($review['heading']) : ?><h2 class="reviews-page__heading landing-subtitle"><?php echo esc_html($review['heading']); ?></h2><?php endif; ?><blockquote class="reviews-page__quote"><?php echo esc_html($review['text']); ?></blockquote></div>
            <?php if ($review['photos']) : ?><div class="reviews-page__photos"><?php foreach ($review['photos'] as $photo) : ?><a href="<?php echo esc_url($photo); ?>" data-collection-photo data-gallery="review-page-<?php echo esc_attr($review['id']); ?>" aria-label="Открыть фотографию отзыва"><img src="<?php echo esc_url($photo); ?>" alt="Фото из отзыва клиента" width="80" height="80" loading="lazy"></a><?php endforeach; ?></div><?php endif; ?>
        </div>
        <footer class="reviews-page__author">
            <button class="reviews-page__more" type="button" data-review-open aria-haspopup="dialog" hidden>Читать полностью</button>
            <?php if ($review['service']) : ?>
            <div class="reviews-page__service">
                <span class="reviews-page__service-label">Услуга:</span>
                <a href="<?php echo esc_url(get_permalink($review['service_id'])); ?>"><?php echo Services::icon($review['service_id'], 'reviews-page__service-icon'); ?><span><?php echo esc_html($review['service']); ?></span></a>
            </div>
            <?php endif; ?>
        </footer>
    </article>
    <?php endforeach; ?>
</div>
<dialog class="slider-grid__dialog" data-review-dialog aria-labelledby="review-dialog-title">
    <div class="slider-grid__dialog-inner">
        <button class="slider-grid__dialog-close" type="button" data-review-close aria-label="Закрыть отзыв"><svg viewBox="0 0 24 24" width="24" height="24" fill="none" aria-hidden="true" focusable="false"><path d="m6 6 12 12M18 6 6 18" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg></button>
        <div class="slider-grid__dialog-title" id="review-dialog-title">Отзыв клиента</div>
        <span class="slider-grid__stars" data-review-dialog-stars role="img"></span>
        <blockquote class="slider-grid__dialog-quote" data-review-dialog-text></blockquote>
        <div class="slider-grid__dialog-photos" data-review-dialog-photos></div>
        <div class="slider-grid__dialog-service" data-review-dialog-service></div>
        <div class="slider-grid__dialog-author"><cite data-review-dialog-author></cite><time data-review-dialog-date></time></div>
    </div>
</dialog>
<?php Pages::pagination($query); ?>
<?php else : ?><p class="support-page__empty landing-text">Отзывы пока не добавлены.</p><?php endif; ?>
<?php  } ?>
        
        <?php 
        get_template_part('template-parts/blocks/split-form', null, array('form' => array(
            'title' => 'Нужна помощь с автомобилем?',
            'lead' => Fields::get('article_form_lead', $settings_id, 'Опишите ситуацию. Оператор уточнит детали и согласует необходимую помощь.'),
            'shortcode' => '[contact-form-7 id="93c12f0" title="Форма в CTA"]',
        )));
        ?>
    </main>
    </div>
<?php  get_footer(); ?>
