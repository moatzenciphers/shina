<?php
$posts = edemchinim_landing_posts(edemchinim_landing_sub('reviews_posts', array()), 'reviews', (int) edemchinim_landing_sub('reviews_count', 6));
$posts = array_values(array_filter($posts, static function ($post) {
	return (bool) edemchinim_get_field('shina_review_is_active', $post->ID, 1);
}));
$archive = get_post_type_archive_link('reviews');
?>
<section class="slider-grid landing-section" id="reviews" aria-labelledby="slider-grid-title">
	<header class="slider-grid__header">
		<?php edemchinim_landing_heading('reviews', 'slider-grid'); ?>
		<p class="slider-grid__lead landing-text"><?php echo esc_html(edemchinim_landing_sub('reviews_lead')); ?></p>
	</header>
	<?php if ($posts) : ?>
	<div class="slider-grid__track" data-review-slider tabindex="0" aria-label="Отзывы клиентов">
		<?php foreach ($posts as $review) :
			$rating = (float) edemchinim_get_field('shina_review_rating', $review->ID, 5);
			$date = edemchinim_get_field('shina_review_date', $review->ID, get_the_date('Y-m-d', $review->ID));
			$service_key = edemchinim_get_field('shina_review_repair_type', $review->ID, '');
			$service_names = array('seasonal' => 'Сезонная замена шин', 'puncture' => 'Ремонт прокола', 'storage' => 'Хранение шин', 'conditioner' => 'Заправка кондиционера');
			?>
		<article class="slider-grid__item">
			<span class="slider-grid__stars" role="img" aria-label="<?php echo esc_attr(sprintf('Оценка %s из 5', $rating)); ?>"><?php echo esc_html(str_repeat('★', max(0, min(5, (int) round($rating))))); ?></span>
			<blockquote class="slider-grid__quote"><?php echo esc_html(wp_strip_all_tags($review->post_content)); ?></blockquote>
			<p class="slider-grid__service"><?php echo esc_html($service_names[$service_key] ?? ''); ?></p>
			<div class="slider-grid__author">
				<?php if (has_post_thumbnail($review)) : echo get_the_post_thumbnail($review, 'thumbnail', array('class' => 'slider-grid__avatar', 'alt' => '', 'loading' => 'lazy')); else : ?><span class="slider-grid__avatar slider-grid__avatar--initials" aria-hidden="true"><?php echo esc_html(mb_substr(get_the_title($review), 0, 1)); ?></span><?php endif; ?>
				<div class="slider-grid__author-meta"><cite class="slider-grid__author-name"><?php echo esc_html(get_the_title($review)); ?></cite><time class="slider-grid__date" datetime="<?php echo esc_attr($date); ?>"><?php echo esc_html(wp_date('j F Y', strtotime($date))); ?></time></div>
			</div>
		</article>
		<?php endforeach; ?>
	</div>
	<div class="slider-grid__controls" role="group" aria-label="Управление отзывами">
		<button class="landing-slider-arrow slider-grid__arrow" type="button" data-review-prev aria-label="Предыдущий отзыв"><?php edemchinim_landing_arrow('left'); ?></button>
		<span class="slider-grid__counter" aria-live="polite"><span data-review-current>01</span><span class="slider-grid__counter-total"> / <?php echo esc_html(sprintf('%02d', count($posts))); ?></span></span>
		<button class="landing-slider-arrow landing-slider-arrow--next slider-grid__arrow" type="button" data-review-next aria-label="Следующий отзыв"><?php edemchinim_landing_arrow(); ?></button>
	</div>
	<?php endif; ?>
	<footer class="slider-grid__footer"><?php if ($archive) : ?><a class="slider-grid__all landing-more-link" href="<?php echo esc_url($archive); ?>">Смотреть все отзывы<?php edemchinim_landing_arrow(); ?></a><?php endif; ?></footer>
</section>
