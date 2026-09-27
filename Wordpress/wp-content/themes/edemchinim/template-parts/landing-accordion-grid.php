<?php
$mode = edemchinim_landing_sub('faq_mode', 'latest');
$selected = $mode === 'selected' ? edemchinim_landing_sub('faq_posts', array()) : array();
$posts = edemchinim_landing_posts($selected, 'faq', 10);
?>
<section class="accordion-grid landing-section" id="faq" aria-labelledby="accordion-grid-title">
	<header class="accordion-grid__header"><?php edemchinim_landing_heading('faq', 'accordion-grid'); ?></header>
	<?php if ($posts) : ?>
	<div class="accordion-grid__columns">
		<?php foreach (array_chunk($posts, 5) as $column_index => $column) : ?>
		<div class="accordion-grid__column">
			<?php foreach ($column as $item_index => $item) : ?>
			<details class="accordion-grid__item"<?php echo $column_index === 0 && $item_index === 0 ? ' open' : ''; ?>><summary class="accordion-grid__question"><?php echo esc_html(get_the_title($item)); ?></summary><div class="accordion-grid__answer landing-text"><?php echo wp_kses_post(apply_filters('the_content', $item->post_content)); ?></div></details>
			<?php endforeach; ?>
		</div>
		<?php endforeach; ?>
	</div>
	<?php endif; ?>
</section>
