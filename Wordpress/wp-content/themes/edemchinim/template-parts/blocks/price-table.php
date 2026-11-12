<?php
use Edemchinim\Content\Blocks;
use Edemchinim\Content\Prices;
use Edemchinim\Support\Fields;

$prices = array();
$additional = array();
foreach (Prices::posts(get_queried_object_id()) as $price) {
	if (Fields::get('price_additional', $price->ID, false)) {
		$additional[] = $price;
	} else {
		$prices[] = $price;
	}
}
$title = Blocks::sub('pricing_title', 'Стоимость услуг');
?>
<section class="price-table landing-section" id="pricing" aria-labelledby="price-table-title">
	<div class="price-table__inner">
		<div class="price-table__header">
			<?php Blocks::heading('pricing', 'price-table', 'УСЛУГА И СТОИМОСТЬ', 'Стоимость услуг'); ?>
			<p class="price-table__description landing-text"><?php echo esc_html(Blocks::sub('pricing_description')); ?></p>
		</div>
		<?php Prices::renderTable($prices, $title); ?>
		<?php if ($additional) : ?>
		<div class="price-table__group">
			<div class="price-table__group-title landing-eyebrow">ДОПЛАТЫ</div>
			<?php Prices::renderTable($additional, 'Доплаты', true); ?>
			<?php if (Blocks::sub('pricing_group_note')) : ?><p class="price-table__group-note landing-text"><?php echo esc_html(Blocks::sub('pricing_group_note')); ?></p><?php endif; ?>
		</div>
		<?php endif; ?>
		<div class="price-table__footer">
			<button class="price-table__cta landing-cta" type="button" data-quick-call-open><span>Вызвать мастера</span><?php Blocks::arrow(); ?></button>
			<p class="price-table__note landing-text">Окончательную стоимость согласуем до начала работ.</p>
		</div>
	</div>
</section>
