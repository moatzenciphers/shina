<?php
$posts = edemchinim_landing_posts(edemchinim_landing_sub('services_posts', array()), 'services', (int) edemchinim_landing_sub('services_count', 8));
$archive = get_post_type_archive_link('services');
?>
<section class="cards-grid landing-section" id="services" aria-labelledby="cards-grid-title">
	<header class="cards-grid__header">
		<?php edemchinim_landing_heading('services', 'cards-grid'); ?>
		<p class="cards-grid__intro landing-text"><?php echo esc_html(edemchinim_landing_sub('services_intro')); ?></p>
	</header>
	<?php if ($posts) : ?>
	<div class="cards-grid__items">
		<?php foreach ($posts as $service) :
			$price = function_exists('get_field') ? get_field('service_price_from', $service->ID) : get_post_meta($service->ID, 'service_price_from', true);
			$note = function_exists('get_field') ? get_field('service_price_note', $service->ID) : get_post_meta($service->ID, 'service_price_note', true);
			?>
		<article class="cards-grid__item<?php echo $note ? ' cards-grid__item--note' : ''; ?>" id="service-<?php echo esc_attr($service->post_name); ?>">
			<a class="cards-grid__link landing-subtitle" href="<?php echo esc_url(get_permalink($service)); ?>"><span><?php echo esc_html(get_the_title($service)); ?></span><svg class="cards-grid__arrow" viewBox="0 0 32 32" fill="none" aria-hidden="true"><path d="M5 16h21m-8-8 8 8-8 8" stroke="currentColor" stroke-width="2"/></svg></a>
			<?php if ($note) : ?><p class="cards-grid__price-note"><?php echo esc_html($note); ?></p><?php endif; ?>
			<?php if ($price !== '' && $price !== false && $price !== null) : ?><p class="cards-grid__price">от <?php echo esc_html(number_format_i18n((float) $price, 0)); ?> ₽</p><?php endif; ?>
		</article>
		<?php endforeach; ?>
	</div>
	<?php endif; ?>
	<footer class="cards-grid__footer">
		<p class="cards-grid__footer-text landing-text"><?php echo esc_html(edemchinim_landing_sub('services_footer')); ?></p>
		<?php if ($archive) : ?><a class="cards-grid__all-link landing-more-link" href="<?php echo esc_url($archive); ?>">Посмотреть все услуги<?php edemchinim_landing_arrow(); ?></a><?php endif; ?>
	</footer>
</section>
