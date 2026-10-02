<?php
$posts = edemchinim_landing_posts(edemchinim_landing_sub('reviews_posts', array()), 'reviews', min(9, max(1, (int) edemchinim_landing_sub('reviews_count', 9))));
$posts = array_values(array_filter($posts, static function ($post) {
	return (bool) edemchinim_get_field('shina_review_is_active', $post->ID, 1);
}));
$posts = array_slice($posts, 0, 9);
$archive = get_post_type_archive_link('reviews');
$summary = edemchinim_review_summary();
$filters = array('all' => 'Все');
$review_services = array();
$legacy_names = array('seasonal' => 'Сезонная замена шин', 'puncture' => 'Ремонт проколов', 'storage' => 'Сезонное хранение шин', 'conditioner' => 'Заправка кондиционера');
foreach ($posts as $review) {
    $service_id = edemchinim_review_service_id($review->ID);
    $legacy = edemchinim_get_field('shina_review_repair_type', $review->ID, '');
    $key = $service_id ? 'service-' . $service_id : 'legacy-' . sanitize_key($legacy);
    $label = $service_id ? get_the_title($service_id) : ($legacy_names[$legacy] ?? '');
    if ($label) $filters[$key] = $label;
    $review_services[$review->ID] = array('id' => $service_id, 'key' => $key, 'label' => $label);
}
?>
<section class="slider-grid landing-section" id="reviews" aria-labelledby="slider-grid-title" data-review-catalog>
	<header class="slider-grid__header">
		<?php edemchinim_landing_heading('reviews', 'slider-grid'); ?>
		<p class="slider-grid__lead landing-text"><?php echo esc_html(edemchinim_landing_sub('reviews_lead')); ?></p>
        <div class="slider-grid__rating">
            <span class="slider-grid__stars" aria-hidden="true"><?php $filled = max(0, min(5, (int) round($summary['rating']))); echo esc_html(str_repeat('★', $filled) . str_repeat('☆', 5 - $filled)); ?></span>
            <strong><?php echo esc_html(number_format($summary['rating'], 1, ',', '')); ?> из 5</strong>
            <span><?php echo esc_html(edemchinim_get_option_field('reviews_count_text', 'Отзывы клиентов')); ?></span>
        </div>
	</header>
	<?php if ($posts) : ?>
    <div class="slider-grid__filters cards-grid__filters" role="group" aria-label="Отзывы по услугам">
        <?php foreach ($filters as $key => $label) : ?>
            <button class="cards-grid__filter" type="button" data-review-filter="<?php echo esc_attr($key); ?>" aria-pressed="<?php echo $key === 'all' ? 'true' : 'false'; ?>" aria-controls="reviews-track"><?php echo esc_html($label); ?></button>
        <?php endforeach; ?>
    </div>
    <p class="visually-hidden" data-review-filter-status role="status" aria-live="polite"></p>
	<div id="reviews-track" class="slider-grid__track" data-review-slider tabindex="0" aria-label="Отзывы клиентов">
		<?php foreach ($posts as $review) :
			$rating = (float) edemchinim_get_field('shina_review_rating', $review->ID, 5);
			$date = edemchinim_get_field('shina_review_date', $review->ID, get_the_date('Y-m-d', $review->ID));
            $service = $review_services[$review->ID];
            $heading = edemchinim_get_field('review_heading', $review->ID, '');
            $photos = edemchinim_rest_review_photos(edemchinim_get_field('shina_review_gallery', $review->ID, array()));
			?>
		<article class="slider-grid__item" data-review-card data-review-service="<?php echo esc_attr($service['key']); ?>">
			<span class="slider-grid__stars" role="img" aria-label="<?php echo esc_attr(sprintf('Оценка %s из 5', $rating)); ?>"><?php echo esc_html(str_repeat('★', max(0, min(5, (int) round($rating))))); ?></span>
            <div class="slider-grid__body">
                <div class="slider-grid__text">
                    <?php if ($heading) : ?><h3 class="slider-grid__review-heading"><?php echo esc_html($heading); ?></h3><?php endif; ?>
                    <blockquote class="slider-grid__quote"><?php echo esc_html(wp_strip_all_tags($review->post_content)); ?></blockquote>
                    <button class="slider-grid__more" type="button" data-review-open hidden>Читать полностью</button>
                </div>
                <?php if ($photos) : ?><div class="slider-grid__photos" data-review-photos><?php foreach ($photos as $photo) : ?><a href="<?php echo esc_url($photo); ?>" data-review-photo data-gallery="review-<?php echo esc_attr($review->ID); ?>" aria-label="Открыть фото из отзыва"><img src="<?php echo esc_url($photo); ?>" alt="Фото из отзыва клиента" loading="lazy"></a><?php endforeach; ?></div><?php endif; ?>
            </div>
            <?php if ($service['label']) : ?><p class="slider-grid__service"><?php echo edemchinim_service_icon($service['id'], 'slider-grid__service-icon'); ?><span><?php echo esc_html($service['label']); ?></span></p><?php endif; ?>
			<div class="slider-grid__author">
				<?php if (has_post_thumbnail($review)) : echo get_the_post_thumbnail($review, 'thumbnail', array('class' => 'slider-grid__avatar', 'alt' => '', 'loading' => 'lazy')); else : ?><span class="slider-grid__avatar slider-grid__avatar--initials" aria-hidden="true"><?php echo esc_html(mb_substr(get_the_title($review), 0, 1)); ?></span><?php endif; ?>
				<div class="slider-grid__author-meta"><cite class="slider-grid__author-name"><?php echo esc_html(get_the_title($review)); ?></cite><time class="slider-grid__date" datetime="<?php echo esc_attr($date); ?>"><?php echo esc_html(wp_date('j F Y', strtotime($date))); ?></time></div>
			</div>
		</article>
		<?php endforeach; ?>
	</div>
	<div class="slider-grid__controls" role="group" aria-label="Управление отзывами">
		<button class="landing-slider-arrow slider-grid__arrow" type="button" data-review-prev aria-label="Предыдущий отзыв"><?php edemchinim_landing_arrow('left'); ?></button>
		<span class="slider-grid__counter" aria-live="polite"><span data-review-current>01</span><span class="slider-grid__counter-total"> / <span data-review-total><?php echo esc_html(sprintf('%02d', count($posts))); ?></span></span></span>
		<button class="landing-slider-arrow landing-slider-arrow--next slider-grid__arrow" type="button" data-review-next aria-label="Следующий отзыв"><?php edemchinim_landing_arrow(); ?></button>
	</div>
	<?php endif; ?>
	<dialog class="slider-grid__dialog" data-review-dialog aria-labelledby="slider-grid-dialog-title">
		<div class="slider-grid__dialog-inner">
			<button class="slider-grid__dialog-close" type="button" data-review-close aria-label="Закрыть отзыв">×</button>
			<h3 class="slider-grid__dialog-title" id="slider-grid-dialog-title">Отзыв клиента</h3>
			<span class="slider-grid__stars" data-review-dialog-stars role="img"></span>
			<blockquote class="slider-grid__dialog-quote" data-review-dialog-text></blockquote>
            <div class="slider-grid__dialog-photos" data-review-dialog-photos></div>
			<p class="slider-grid__dialog-service" data-review-dialog-service></p>
			<div class="slider-grid__dialog-author"><cite data-review-dialog-author></cite><time data-review-dialog-date></time></div>
		</div>
	</dialog>
	<footer class="slider-grid__footer"><?php if ($archive) : ?><a class="slider-grid__all landing-more-link" href="<?php echo esc_url($archive); ?>">Больше отзывов<?php edemchinim_landing_arrow(); ?></a><?php endif; ?></footer>
</section>
