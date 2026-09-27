<?php
$images = array_values(array_filter(array_map('edemchinim_landing_image_id', (array) edemchinim_landing_sub('gallery_images', array()))));
$gallery_page = get_page_by_path('vyezdy');
$gallery_archive = $gallery_page ? get_permalink($gallery_page) : '';
?>
<section class="gallery-grid landing-section" id="visits" aria-labelledby="gallery-grid-title">
	<div class="gallery-grid__panel">
		<header class="gallery-grid__header"><div class="gallery-grid__heading"><?php edemchinim_landing_heading('gallery', 'gallery-grid'); ?></div></header>
		<?php if ($images) : ?>
		<div class="gallery-grid__track" data-gallery-slider tabindex="0" aria-label="Фотографии выездов">
			<?php foreach ($images as $image_id) :
				$full = wp_get_attachment_image_url($image_id, 'full');
				$title = get_the_title($image_id);
				$label = edemchinim_get_field('shina_photo_label', $image_id, $title);
				$place = wp_get_attachment_caption($image_id);
				if (! $full) continue;
				?>
			<figure class="gallery-grid__card"><a class="gallery-grid__link" href="<?php echo esc_url($full); ?>" data-gallery="visits" data-title="<?php echo esc_attr($label); ?>" data-description="<?php echo esc_attr($place); ?>" aria-label="<?php echo esc_attr(sprintf('Открыть фото: %s', $title)); ?>">
				<?php echo wp_get_attachment_image($image_id, 'large', false, array('class' => 'gallery-grid__image', 'loading' => 'lazy')); ?>
				<span class="gallery-grid__caption"><strong class="gallery-grid__card-title"><?php echo esc_html($title); ?></strong><span class="gallery-grid__place"><?php echo esc_html($place); ?></span></span>
			</a></figure>
			<?php endforeach; ?>
		</div>
		<div class="gallery-grid__controls" role="group" aria-label="Листать фотографии выездов"><button class="landing-slider-arrow gallery-grid__arrow" type="button" data-gallery-prev aria-label="Предыдущий выезд"><?php edemchinim_landing_arrow('left'); ?></button><button class="landing-slider-arrow landing-slider-arrow--next gallery-grid__arrow" type="button" data-gallery-next aria-label="Следующий выезд"><?php edemchinim_landing_arrow(); ?></button></div>
		<?php endif; ?>
		<?php if ($gallery_archive) : ?><footer class="gallery-grid__footer"><a class="gallery-grid__all landing-more-link" href="<?php echo esc_url($gallery_archive); ?>">Посмотреть больше выездов<?php edemchinim_landing_arrow(); ?></a></footer><?php endif; ?>
	</div>
</section>
