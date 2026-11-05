<?php
$kind = $args['kind'];
$settings_id = $args['settings_id'];
$fallbacks = array('reviews' => 'Отзывы клиентов', 'works' => 'Фотографии работ', 'contacts' => 'Контакты');
$title = edemchinim_get_field('support_title', $settings_id, is_page() ? get_the_title($settings_id) : $fallbacks[$kind]);
$intro = edemchinim_get_field('support_intro', $settings_id, '');
$query = $args['query'] ?? null;
if ($kind === 'reviews' && ! $query) $query = new WP_Query(array('post_type' => 'reviews', 'post_status' => 'publish', 'posts_per_page' => 9, 'paged' => edemchinim_support_page_number(), 'meta_query' => edemchinim_public_reviews_meta()));
if ($kind === 'works') {
    $chosen = array_values(array_filter(array_map('edemchinim_landing_image_id', (array) edemchinim_get_field('works_images', $settings_id, array()))));
    $query_args = array('post_type' => 'attachment', 'post_status' => 'inherit', 'post_mime_type' => 'image', 'posts_per_page' => 12, 'paged' => edemchinim_support_page_number());
    if ($chosen) { $query_args['post__in'] = $chosen; $query_args['orderby'] = 'post__in'; }
    else $query_args['meta_query'] = array(array('key' => 'shina_photo_is_active', 'value' => '1'));
    $query = new WP_Query($query_args);
}
get_header();
?>
<div class="landing landing--wp-header" data-landing>
    <main class="support-page landing-section" id="primary">
        <header class="support-page__header">
            <nav class="support-page__breadcrumbs" aria-label="Хлебные крошки"><a href="<?php echo esc_url(home_url('/')); ?>">Главная</a><span aria-hidden="true">/</span><span aria-current="page"><?php echo esc_html($title); ?></span></nav>
            <h1 class="support-page__title landing-title"><?php echo esc_html($title); ?></h1>
            <?php if ($intro) : ?><p class="support-page__intro landing-text"><?php echo esc_html($intro); ?></p><?php endif; ?>
        </header>
        <?php get_template_part('template-parts/support-' . $kind, null, array('settings_id' => $settings_id, 'query' => $query)); ?>
        <?php $shortcode = edemchinim_get_field('support_form_shortcode', $settings_id, ''); if ($shortcode) : ?>
        <section class="support-page__form" aria-labelledby="support-form-title"><h2 class="landing-title landing-title--section" id="support-form-title">Связаться с нами</h2><?php edemchinim_landing_cf7($shortcode); ?></section>
        <?php endif; ?>
    </main>
    <?php get_template_part('template-parts/landing-mobile-actions'); ?>
</div>
<?php get_template_part('template-parts/landing-quick-call', null, array('settings_id' => $settings_id)); get_footer(); ?>
