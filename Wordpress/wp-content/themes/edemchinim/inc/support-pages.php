<?php
/** Data for reviews, work photos and contacts. Existing records remain the source. */
if (! defined('ABSPATH')) exit;

function edemchinim_public_reviews_meta()
{
    return array('relation' => 'OR', array('key' => 'shina_review_is_active', 'compare' => 'NOT EXISTS'), array('key' => 'shina_review_is_active', 'value' => '1'));
}

function edemchinim_support_url($kind)
{
    if ($kind === 'reviews') {
        $archive = get_post_type_archive_link('reviews');
        if ($archive) return $archive;
    }
    $pages = get_posts(array('post_type' => 'page', 'post_status' => 'publish', 'posts_per_page' => 1, 'meta_key' => '_wp_page_template', 'meta_value' => 'page-' . $kind . '.php', 'no_found_rows' => true));
    if ($pages) return get_permalink($pages[0]);
    return '';
}

function edemchinim_support_page_number()
{
    return max(1, absint(get_query_var('paged')), isset($_GET['pagenum']) ? absint(wp_unslash($_GET['pagenum'])) : 1);
}

function edemchinim_support_pagination($query)
{
    if ($query->max_num_pages < 2) return;
    $current = edemchinim_support_page_number();
    $arguments = array('current' => $current, 'total' => (int) $query->max_num_pages, 'type' => 'list', 'prev_text' => '<svg viewBox="0 0 24 24" width="20" height="20" fill="none" aria-hidden="true"><path d="M20 12H4m6-6-6 6 6 6" stroke="currentColor" stroke-width="1.5"/></svg><span class="visually-hidden">Назад</span>', 'next_text' => '<svg viewBox="0 0 24 24" width="20" height="20" fill="none" aria-hidden="true"><path d="M4 12h16m-6-6 6 6-6 6" stroke="currentColor" stroke-width="1.5"/></svg><span class="visually-hidden">Далее</span>', 'aria_current' => 'page');
    if (is_page()) {
        $arguments['base'] = str_replace('999999', '%#%', esc_url(add_query_arg('pagenum', 999999, get_permalink())));
        $arguments['format'] = '';
    }
    echo '<nav class="support-page__pagination" aria-label="Страницы списка">' . paginate_links($arguments) . '</nav>';
}

function edemchinim_reviews_archive_query($query)
{
    if (is_admin() || ! $query->is_main_query() || ! $query->is_post_type_archive('reviews')) return;
    $query->set('posts_per_page', 9);
    $query->set('meta_query', edemchinim_public_reviews_meta());
}
add_action('pre_get_posts', 'edemchinim_reviews_archive_query');

function edemchinim_support_review($post)
{
    $id = $post->ID;
    $date = (string) edemchinim_get_field('shina_review_date', $id, get_the_date('Y-m-d', $id));
    $parsed = DateTimeImmutable::createFromFormat('!Y-m-d', $date) ?: DateTimeImmutable::createFromFormat('!Ymd', $date);
    $service = edemchinim_review_service_id($id);
    return array(
        'id' => $id, 'author' => get_the_title($id),
        'heading' => edemchinim_get_field('review_heading', $id, ''),
        'text' => wp_strip_all_tags($post->post_content),
        'rating' => max(0, min(5, (float) edemchinim_get_field('shina_review_rating', $id, 5))),
        'date' => $parsed ? $parsed->format('Y-m-d') : '',
        'date_label' => $parsed ? wp_date(get_option('date_format'), $parsed->getTimestamp()) : '',
        'service_id' => $service, 'service' => $service ? get_the_title($service) : '',
        'photos' => edemchinim_rest_review_photos(edemchinim_get_field('shina_review_gallery', $id, array())),
    );
}

add_action('acf/init', static function () {
    if (function_exists('acf_add_options_sub_page')) {
        acf_add_options_sub_page(array('page_title' => 'Настройки страницы отзывов', 'menu_title' => 'Настройки страницы', 'parent_slug' => 'edit.php?post_type=reviews', 'menu_slug' => 'reviews-archive', 'post_id' => 'reviews-archive', 'capability' => 'edit_posts'));
    }
    $path = get_template_directory() . '/inc/acf-support-pages.json';
    if (! function_exists('acf_add_local_field_group') || ! is_readable($path)) return;
    foreach ((array) json_decode(file_get_contents($path), true) as $group) acf_add_local_field_group($group);
});
