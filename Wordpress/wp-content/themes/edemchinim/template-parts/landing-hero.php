<?php
/** Hero block for the flexible landing page. */
$image_id = edemchinim_landing_image_id(edemchinim_landing_sub('hero_background'));
$mobile_image_id = edemchinim_landing_image_id(edemchinim_landing_sub('hero_background_mobile'));
$image_url = $image_id ? wp_get_attachment_image_url($image_id, 'full') : '';
$mobile_image_url = $mobile_image_id ? wp_get_attachment_image_url($mobile_image_id, 'full') : '';
$image_url = $image_url ?: get_template_directory_uri() . '/img/back.png';
$prices = edemchinim_landing_sub('hero_prices', array());
if (! $prices) {
	$prices = array(
		array('label' => 'Выезд в пределах МКАД —', 'price' => 'от 1 500 ₽'),
		array('label' => 'Выезд за МКАД —', 'price' => 'от 2 000 ₽ + 40 ₽/км'),
	);
}
?>
<section class="landing-hero" aria-labelledby="landing-hero-title">
	<picture class="landing-hero__picture" aria-hidden="true">
		<?php if ($mobile_image_url) : ?>
			<source media="(max-width: 850px)" srcset="<?php echo esc_url($mobile_image_url); ?>">
		<?php endif; ?>
		<img src="<?php echo esc_url($image_url); ?>" alt="" decoding="async" fetchpriority="high">
	</picture>
	<div class="landing-hero__inner">
		<div class="landing-hero__content" id="top">
			<p class="landing-hero__outline" aria-hidden="true"><span>ЕДЕМ–</span><span>ЧИНИМ</span></p>
			<h1 class="landing-hero__title landing-title landing-title--hero" id="landing-hero-title"><?php echo nl2br(esc_html(edemchinim_landing_sub('hero_title', 'ВЫЕЗДНОЙ ШИНОМОНТАЖ 24/7'))); ?></h1>
			<p class="landing-hero__lead landing-text landing-text--hero"><?php echo esc_html(edemchinim_landing_sub('hero_lead')); ?></p>
			<button class="landing-hero__cta landing-cta" type="button" data-open-calculator><span><?php echo esc_html(edemchinim_landing_sub('hero_button', 'Вызвать мастера')); ?></span><?php edemchinim_landing_arrow(); ?></button>
			<div class="landing-hero__prices" aria-label="Стоимость выезда">
				<?php foreach ($prices as $price) : ?>
					<div class="landing-hero__price"><span><?php echo esc_html($price['label'] ?? ''); ?></span><strong><?php echo esc_html($price['price'] ?? ''); ?></strong></div>
				<?php endforeach; ?>
				<p class="landing-hero__minimum">Минимальный заказ — <strong><?php echo esc_html(edemchinim_landing_sub('hero_minimum', 'от 2 500 ₽')); ?></strong></p>
			</div>
		</div>
	</div>
</section>
