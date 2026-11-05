<?php
$query = $args['query'];
$summary = edemchinim_review_summary();
?>
<?php if ($summary['count']) : ?>
<div class="reviews-page__summary"><span class="reviews-page__score"><?php echo esc_html(number_format($summary['rating'], 1, ',', '')); ?><span> / 5</span></span><span><?php echo esc_html(edemchinim_get_option_field('reviews_count_text', 'Отзывы клиентов')); ?></span></div>
<?php endif; ?>
<?php if ($query->have_posts()) : ?>
<div class="reviews-page__list">
    <?php foreach ($query->posts as $review_post) : $review = edemchinim_support_review($review_post); ?>
    <article class="reviews-page__item" id="review-<?php echo esc_attr($review['id']); ?>">
        <header class="reviews-page__meta"><span class="reviews-page__stars" role="img" aria-label="<?php echo esc_attr(sprintf('Оценка %s из 5', $review['rating'])); ?>"><?php echo esc_html(str_repeat('★', (int) round($review['rating'])) . str_repeat('☆', 5 - (int) round($review['rating']))); ?></span><?php if ($review['date']) : ?><time datetime="<?php echo esc_attr($review['date']); ?>"><?php echo esc_html($review['date_label']); ?></time><?php endif; ?></header>
        <div class="reviews-page__body">
            <div><?php if ($review['heading']) : ?><h2 class="reviews-page__heading landing-subtitle"><?php echo esc_html($review['heading']); ?></h2><?php endif; ?><blockquote class="reviews-page__quote"><?php echo esc_html($review['text']); ?></blockquote></div>
            <?php if ($review['photos']) : ?><div class="reviews-page__photos"><?php foreach ($review['photos'] as $photo) : ?><a href="<?php echo esc_url($photo); ?>" data-collection-photo data-gallery="review-page-<?php echo esc_attr($review['id']); ?>" aria-label="Открыть фотографию отзыва"><img src="<?php echo esc_url($photo); ?>" alt="Фото из отзыва клиента" width="80" height="80" loading="lazy"></a><?php endforeach; ?></div><?php endif; ?>
        </div>
        <footer class="reviews-page__author"><cite><?php echo esc_html($review['author']); ?></cite><?php if ($review['service']) : ?><a href="<?php echo esc_url(get_permalink($review['service_id'])); ?>"><?php echo edemchinim_service_icon($review['service_id'], 'reviews-page__service-icon'); ?><span><?php echo esc_html($review['service']); ?></span></a><?php endif; ?></footer>
    </article>
    <?php endforeach; ?>
</div>
<?php edemchinim_support_pagination($query); ?>
<?php else : ?><p class="support-page__empty landing-text">Отзывы пока не добавлены.</p><?php endif; ?>
