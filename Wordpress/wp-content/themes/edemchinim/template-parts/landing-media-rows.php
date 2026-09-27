<?php $rows = edemchinim_landing_sub('media_items', array()); ?>
<section class="media-rows landing-section" aria-labelledby="media-rows-title">
	<header class="media-rows__header"><?php edemchinim_landing_heading('media', 'media-rows'); ?></header>
	<div class="media-rows__items">
		<?php foreach ($rows as $row) : $image_id = edemchinim_landing_image_id($row['image'] ?? 0); ?>
		<div class="media-rows__row">
			<?php if ($image_id) : echo wp_get_attachment_image($image_id, 'large', false, array('class' => 'media-rows__image', 'loading' => 'lazy')); endif; ?>
			<ul class="media-rows__list">
				<?php foreach (($row['points'] ?? array()) as $point) : ?>
				<li class="media-rows__point"><h3 class="media-rows__point-title landing-subtitle"><?php echo esc_html($point['title'] ?? ''); ?></h3><p class="media-rows__point-text landing-text"><?php echo esc_html($point['text'] ?? ''); ?></p></li>
				<?php endforeach; ?>
			</ul>
		</div>
		<?php endforeach; ?>
	</div>
</section>
