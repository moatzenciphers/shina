<?php $columns = edemchinim_landing_sub('columns_items', array()); ?>
<section class="columns-list landing-section" aria-labelledby="columns-list-title">
	<header class="columns-list__header">
		<?php edemchinim_landing_heading('columns', 'columns-list'); ?>
		<p class="columns-list__description landing-text"><?php echo esc_html(edemchinim_landing_sub('columns_description')); ?></p>
	</header>
	<div class="columns-list__columns">
		<?php foreach ($columns as $column) : ?>
		<div class="columns-list__column">
			<h3 class="columns-list__subtitle landing-subtitle"><?php echo esc_html($column['title'] ?? ''); ?></h3>
			<ul class="columns-list__items">
				<?php foreach (($column['items'] ?? array()) as $item) : ?>
				<li class="columns-list__item"><p class="landing-text"><?php echo esc_html($item['text'] ?? ''); ?></p></li>
				<?php endforeach; ?>
			</ul>
		</div>
		<?php endforeach; ?>
	</div>
	<div class="columns-list__footer">
		<p class="columns-list__note landing-text"><?php echo esc_html(edemchinim_landing_sub('columns_note')); ?></p>
		<button class="columns-list__cta landing-cta" type="button" data-open-calculator><span><?php echo esc_html(edemchinim_landing_sub('columns_button', 'Вызвать мастера')); ?></span><?php edemchinim_landing_arrow(); ?></button>
	</div>
</section>
