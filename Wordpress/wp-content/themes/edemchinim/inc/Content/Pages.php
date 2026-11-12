<?php
namespace Edemchinim\Content;

if (! defined('ABSPATH')) {
	exit;
}

/** Support-page links, pagination and ACF settings. */
final class Pages
{
	public static function url($kind)
	{
		if ($kind === 'reviews') {
			$archive = get_post_type_archive_link('reviews');
			if ($archive) return $archive;
		}
		$pages = get_posts(array('post_type' => 'page', 'post_status' => 'publish', 'posts_per_page' => 1, 'meta_key' => '_wp_page_template', 'meta_value' => 'page-' . $kind . '.php', 'no_found_rows' => true));
		if ($pages) return get_permalink($pages[0]);
		return '';
	}

	public static function pageNumber()
	{
		return max(1, absint(get_query_var('paged')), isset($_GET['pagenum']) ? absint(wp_unslash($_GET['pagenum'])) : 1);
	}

	public static function pagination($query)
	{
		if ($query->max_num_pages < 2) return;
		$current = self::pageNumber();
		$arguments = array('current' => $current, 'total' => (int) $query->max_num_pages, 'type' => 'list', 'prev_text' => '<svg viewBox="0 0 24 24" width="20" height="20" fill="none" aria-hidden="true"><path d="M20 12H4m6-6-6 6 6 6" stroke="currentColor" stroke-width="1.5"/></svg><span class="visually-hidden">Назад</span>', 'next_text' => '<svg viewBox="0 0 24 24" width="20" height="20" fill="none" aria-hidden="true"><path d="M4 12h16m-6-6 6 6-6 6" stroke="currentColor" stroke-width="1.5"/></svg><span class="visually-hidden">Далее</span>', 'aria_current' => 'page');
		if (is_page()) {
			$arguments['base'] = str_replace('999999', '%#%', esc_url(add_query_arg('pagenum', 999999, get_permalink())));
			$arguments['format'] = '';
		}
		echo '<nav class="support-page__pagination" aria-label="Страницы списка">' . paginate_links($arguments) . '</nav>';
	}

	public static function registerSettings()
	{
		if (function_exists('acf_add_options_sub_page')) {
			acf_add_options_sub_page(array('page_title' => 'Настройки страницы отзывов', 'menu_title' => 'Настройки страницы', 'parent_slug' => 'edit.php?post_type=reviews', 'menu_slug' => 'reviews-archive', 'post_id' => 'reviews-archive', 'capability' => 'edit_posts'));
		}
		$path = get_template_directory() . '/inc/acf-support-pages.json';
		if (! function_exists('acf_add_local_field_group') || ! is_readable($path)) return;
		foreach ((array) json_decode(file_get_contents($path), true) as $group) acf_add_local_field_group($group);
	}
}
