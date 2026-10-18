<?php
/** Shared output for the services archive and an optional catalog page. */
$settings_id = $args['settings_id'] ?? 'services-archive';
$title = edemchinim_get_field('catalog_title', $settings_id, 'Все услуги');
$intro = edemchinim_get_field('catalog_intro', $settings_id, 'Выездной шиномонтаж и техпомощь в Москве и Московской области. Выберите услугу, чтобы узнать условия обслуживания и посмотреть подробный прайс.');
get_header();
?>
<div class="landing landing--wp-header" data-landing>
    <main class="landing__main" id="primary">
        <?php
        get_template_part('template-parts/landing-cards-grid', null, array(
            'catalog' => true,
            'title' => $title ?: 'Все услуги',
            'intro' => $intro,
        ));
        edemchinim_render_landing_blocks($settings_id);
        ?>
    </main>
    <?php get_template_part('template-parts/landing-mobile-actions'); ?>
</div>
<?php
get_template_part('template-parts/landing-quick-call', null, array('settings_id' => $settings_id));
get_footer();
