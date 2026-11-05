<?php $query = $args['query']; ?>
<?php if ($query->have_posts()) : ?>
<div class="works-page__grid">
    <?php foreach ($query->posts as $image) :
        $id = $image->ID;
        $full = wp_get_attachment_image_url($id, 'full');
        if (! $full) continue;
        $label = edemchinim_get_field('shina_photo_label', $id, '');
        $caption = wp_get_attachment_caption($id);
    ?>
    <figure class="works-page__item"><a class="works-page__photo" href="<?php echo esc_url($full); ?>" data-collection-photo data-gallery="work-photos" <?php if ($label) : ?>data-title="<?php echo esc_attr($label); ?>"<?php endif; ?> aria-label="<?php echo esc_attr($label ? 'Открыть фото: ' . $label : 'Открыть фотографию работы'); ?>"><?php echo wp_get_attachment_image($id, 'large', false, array('loading' => 'lazy', 'decoding' => 'async')); ?></a><?php if ($label || $caption) : ?><figcaption><?php if ($label) : ?><strong><?php echo esc_html($label); ?></strong><?php endif; ?><?php if ($caption) : ?><span><?php echo esc_html($caption); ?></span><?php endif; ?></figcaption><?php endif; ?></figure>
    <?php endforeach; ?>
</div>
<?php edemchinim_support_pagination($query); ?>
<?php else : ?><p class="support-page__empty landing-text">Фотографии пока не добавлены.</p><?php endif; ?>
