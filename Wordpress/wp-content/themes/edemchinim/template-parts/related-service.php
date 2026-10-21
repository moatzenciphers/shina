<?php
$service_id = absint($args['service_id'] ?? 0);
if (! $service_id || get_post_type($service_id) !== 'services' || get_post_status($service_id) !== 'publish') return;
$description = edemchinim_get_field('service_card_description', $service_id, wp_trim_words(wp_strip_all_tags(get_the_excerpt($service_id)), 30, '…'));
?>
<section class="related-service landing-section" aria-labelledby="related-service-title">
    <div class="related-service__inner">
        <div class="related-service__copy">
            <p class="landing-eyebrow">УСЛУГА ПО ТЕМЕ СТАТЬИ</p>
            <h2 class="related-service__title landing-title landing-title--section" id="related-service-title"><?php echo esc_html(get_the_title($service_id)); ?></h2>
            <?php if ($description) : ?><p class="related-service__text landing-text"><?php echo esc_html($description); ?></p><?php endif; ?>
        </div>
        <a class="related-service__cta landing-cta" href="<?php echo esc_url(get_permalink($service_id)); ?>"><span>Подробнее об услуге</span><?php edemchinim_landing_arrow(); ?></a>
    </div>
</section>
