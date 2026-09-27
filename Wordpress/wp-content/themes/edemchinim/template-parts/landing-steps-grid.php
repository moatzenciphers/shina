<?php
$items = edemchinim_landing_sub('steps_items', array());
if (! $items) {
	$items = array(
		array('title' => 'Укажите адрес и услугу', 'text' => 'Выберите, что случилось, добавьте параметры автомобиля и получите предварительный расчёт.'),
		array('title' => 'Подтвердите заявку', 'text' => 'Для срочного заказа покажем ориентировочное время приезда. Несрочный выезд можно запланировать.'),
		array('title' => 'Мастер приедет на оборудованном автомобиле', 'text' => 'Специалист привезёт инструменты и оборудование для работы на месте.'),
		array('title' => 'Примите и оплатите работу', 'text' => 'Перед началом мастер подтвердит стоимость. После выполнения вы проверите результат и оплатите заказ.'),
	);
}
?>
<section class="steps-grid landing-section" id="process" aria-labelledby="steps-grid-title">
	<header class="steps-grid__header"><?php edemchinim_landing_heading('steps', 'steps-grid'); ?></header>
	<ol class="steps-grid__items">
		<?php foreach ($items as $index => $item) : ?>
		<li class="steps-grid__item"><span class="steps-grid__number" aria-hidden="true"><?php echo esc_html(sprintf('%02d', $index + 1)); ?></span><div class="steps-grid__item-content"><h3 class="steps-grid__item-title landing-subtitle"><?php echo esc_html($item['title'] ?? ''); ?></h3><p class="steps-grid__item-text landing-text"><?php echo esc_html($item['text'] ?? ''); ?></p></div></li>
		<?php endforeach; ?>
	</ol>
	<div class="steps-grid__footer"><button class="steps-grid__cta landing-cta" type="button" data-open-calculator><span><?php echo esc_html(edemchinim_landing_sub('steps_button', 'Рассчитать стоимость')); ?></span><?php edemchinim_landing_arrow(); ?></button></div>
</section>
