<?php
use Edemchinim\Content\Blocks;
use Edemchinim\Support\Fields;

$images = array_values(array_filter(array_map([Blocks::class, 'imageId'], (array) Blocks::sub('gallery_images', array()))));
$gallery_page = get_page_by_path('vyezdy');
$gallery_archive = $gallery_page ? get_permalink($gallery_page) : '';
?>
<section class="gallery-grid landing-section" id="visits" aria-labelledby="gallery-grid-title">
	<div class="gallery-grid__panel">
		<header class="gallery-grid__header"><div class="gallery-grid__heading"><?php Blocks::heading('gallery', 'gallery-grid'); ?></div></header>
		<?php if ($images) : ?>
		<div class="gallery-grid__track" data-gallery-slider tabindex="0" aria-label="Фотографии выездов">
			<?php foreach ($images as $image_id) :
				$full = wp_get_attachment_image_url($image_id, 'full');
				$title = get_the_title($image_id);
				$label = Fields::get('shina_photo_label', $image_id, $title);
				$place = wp_get_attachment_caption($image_id);
				if (! $full) continue;
				?>
			<figure class="gallery-grid__card"><a class="gallery-grid__link" href="<?php echo esc_url($full); ?>" data-gallery="visits" aria-label="<?php echo esc_attr(sprintf('Открыть фото: %s', $title)); ?>">
				<?php echo wp_get_attachment_image($image_id, 'large', false, array('class' => 'gallery-grid__image', 'loading' => 'lazy')); ?>
			</a></figure>
			<?php endforeach; ?>
		</div>
		<div class="gallery-grid__controls" role="group" aria-label="Листать фотографии выездов"><button class="landing-slider-arrow gallery-grid__arrow" type="button" data-gallery-prev aria-label="Предыдущий выезд"><?php Blocks::arrow('left'); ?></button><button class="landing-slider-arrow landing-slider-arrow--next gallery-grid__arrow" type="button" data-gallery-next aria-label="Следующий выезд"><?php Blocks::arrow(); ?></button></div>
		<?php endif; ?>
		<?php if ($gallery_archive) : ?><footer class="gallery-grid__footer"><a class="gallery-grid__all landing-more-link" href="<?php echo esc_url($gallery_archive); ?>">Посмотреть больше выездов<?php Blocks::arrow(); ?></a></footer><?php endif; ?>
	</div>
</section>
