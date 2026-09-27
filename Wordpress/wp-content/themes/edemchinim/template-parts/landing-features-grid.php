<?php
$items = edemchinim_landing_sub('features_items', array());
if (! $items) {
	$items = array(
		array('title' => '15–30 минут', 'text' => 'Среднее время подачи ближайшего свободного мастера.'),
		array('title' => 'Работаем 24/7', 'text' => 'Выезжаем днём, ночью, в выходные и праздники.'),
		array('title' => 'Цена известна заранее', 'text' => 'Показываем диапазон стоимости и объясняем, из чего он складывается.'),
		array('title' => 'Оплата после работы', 'text' => 'Сначала мастер выполняет и показывает результат — затем вы оплачиваете услугу.'),
	);
}
?>
<section class="features-grid landing-section" id="about" aria-labelledby="features-grid-title">
	<div class="features-grid__inner">
		<header class="features-grid__header"><?php edemchinim_landing_heading('features', 'features-grid'); ?></header>
		<div class="features-grid__items">
			<?php foreach ($items as $item) : ?>
			<article class="features-grid__item"><h3 class="features-grid__item-title landing-subtitle"><?php echo esc_html($item['title'] ?? ''); ?></h3><p class="features-grid__item-text landing-text"><?php echo esc_html($item['text'] ?? ''); ?></p></article>
			<?php endforeach; ?>
		</div>
		<div class="features-grid__footer">
			<p class="features-grid__note landing-text"><?php echo esc_html(edemchinim_landing_sub('features_note')); ?></p>
			<button class="features-grid__cta landing-cta" type="button" data-quick-call-open aria-haspopup="dialog" aria-controls="quick-call-dialog"><span><?php echo esc_html(edemchinim_landing_sub('features_button', 'Быстрый вызов')); ?></span><?php edemchinim_landing_arrow(); ?></button>
		</div>
	</div>
</section>
