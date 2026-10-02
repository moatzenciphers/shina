<?php
$posts = get_posts(array('post_type' => 'services', 'post_status' => 'publish', 'posts_per_page' => -1, 'orderby' => 'menu_order title', 'order' => 'ASC'));
$archive = get_post_type_archive_link('services');
$filters = array('popular' => 'Популярные');
$service_terms = array();
foreach ($posts as $service) {
    $service_terms[$service->ID] = array();
    foreach (edemchinim_service_areas($service->ID) as $term) {
        $key = $term->slug;
        $filters[$key] = $term->name;
        $service_terms[$service->ID][] = $key;
    }
}
$filters['all'] = 'Все услуги';
?>
<section class="cards-grid landing-section" id="services" aria-labelledby="cards-grid-title" data-service-catalog>
    <header class="cards-grid__header">
        <?php edemchinim_landing_heading('services', 'cards-grid'); ?>
        <p class="cards-grid__intro landing-text"><?php echo esc_html(edemchinim_landing_sub('services_intro')); ?></p>
    </header>
    <?php if ($posts) : ?>
    <div class="cards-grid__filters" role="group" aria-label="Категория услуг">
        <?php foreach ($filters as $key => $label) : ?>
            <button class="cards-grid__filter" type="button" data-service-filter="<?php echo esc_attr($key); ?>" aria-pressed="<?php echo $key === 'all' ? 'true' : 'false'; ?>" aria-controls="services-items">
                <?php if ($key === 'popular') : ?><svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M12 3c1 5 6 6 6 11a6 6 0 0 1-12 0c0-3 2-5 3-6 0 3 1 4 2 4 2-2 2-5 1-9Z" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg><?php endif; ?>
                <span><?php echo esc_html($label); ?></span>
            </button>
        <?php endforeach; ?>
    </div>
    <p class="visually-hidden" data-service-filter-status role="status" aria-live="polite"></p>
    <div class="cards-grid__items" id="services-items">
        <?php foreach ($posts as $service) :
            $price = edemchinim_get_field('service_price_from', $service->ID);
            $note = edemchinim_get_field('service_price_note', $service->ID);
            $description = edemchinim_get_field('service_card_description', $service->ID);
            $description = $description ?: wp_trim_words(wp_strip_all_tags(get_the_excerpt($service)), 18, '…');
        ?>
        <article class="cards-grid__item" id="service-<?php echo esc_attr($service->post_name); ?>" data-service-card data-service-terms="<?php echo esc_attr(implode(' ', $service_terms[$service->ID])); ?>">
            <div class="cards-grid__card-heading">
                <?php echo edemchinim_service_icon($service->ID, 'cards-grid__icon'); ?>
                <a class="cards-grid__link landing-subtitle" href="<?php echo esc_url(get_permalink($service)); ?>"><?php echo esc_html(get_the_title($service)); ?></a>
            </div>
            <?php if ($description) : ?><p class="cards-grid__description"><?php echo esc_html($description); ?></p><?php endif; ?>
            <div class="cards-grid__bottom">
                <div class="cards-grid__price-group">
                    <?php if ($note) : ?><p class="cards-grid__price-note"><?php echo esc_html($note); ?></p><?php endif; ?>
                    <?php if ($price !== '' && $price !== false && $price !== null) : ?><p class="cards-grid__price">от <?php echo esc_html(number_format_i18n((float) $price, 0)); ?> ₽</p><?php endif; ?>
                </div>
                <span class="cards-grid__details" aria-hidden="true"><svg class="cards-grid__arrow" viewBox="0 0 24 24" fill="none"><path d="M5 19 19 5M7 5h12v12" stroke="currentColor" stroke-width="2" stroke-linecap="square"/></svg></span>
            </div>
        </article>
        <?php endforeach; ?>
    </div>
    <p class="cards-grid__empty" data-service-filter-empty hidden>В этой категории пока нет услуг.</p>
    <?php endif; ?>
    <footer class="cards-grid__footer">
        <p class="cards-grid__footer-text landing-text"><?php echo esc_html(edemchinim_landing_sub('services_footer')); ?></p>
        <?php if ($archive) : ?><a class="cards-grid__all-link landing-more-link" href="<?php echo esc_url($archive); ?>">Посмотреть все услуги<?php edemchinim_landing_arrow(); ?></a><?php endif; ?>
    </footer>
</section>
