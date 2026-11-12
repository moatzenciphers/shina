<?php
namespace Edemchinim\Content;

use Edemchinim\Support\Fields;

if (! defined('ABSPATH')) {
	exit;
}

/** Article category selection, queries, templates and contents. */
final class Articles
{
	public static function root()
	{
		return get_category_by_slug('articles');
	}

	public static function url()
	{
		$term = self::root();
		return $term ? get_category_link($term->term_id) : home_url('/');
	}

	public static function isArticle($post_id)
	{
		$root = self::root();
		if (! $root || get_post_type($post_id) !== 'post') return false;
		foreach (wp_get_post_categories($post_id) as $category_id) {
			if ((int) $category_id === (int) $root->term_id || term_is_ancestor_of($root->term_id, $category_id, 'category')) return true;
		}
		return false;
	}

	public static function template($template)
	{
		if (is_singular('post') && self::isArticle(get_queried_object_id())) {
			$article_template = locate_template('single-article.php');
			if ($article_template) return $article_template;
		}
		return $template;
	}

	public static function query($query)
	{
		if (is_admin() || ! $query->is_main_query() || ! $query->is_category('articles')) return;
		$root = self::root();
		$count = $root ? absint(Fields::get('articles_per_page', 'category_' . $root->term_id, 6)) : 6;
		$query->set('posts_per_page', min(48, max(1, $count)));
	}

	public static function category($post_id)
	{
		$root = self::root();
		foreach ((array) get_the_category($post_id) as $term) {
			if ($root && (int) $term->term_id !== (int) $root->term_id && term_is_ancestor_of($root->term_id, $term->term_id, 'category')) return $term->name;
		}
		return $root ? $root->name : 'Статьи';
	}

	public static function contents($html, $enabled)
	{
		$items = array();
		if (! $enabled) return array('html' => $html, 'items' => $items);
		preg_match_all('/\sid\s*=\s*(["\'])(.*?)\1/is', $html, $matches);
		$used = array_count_values(array_map('html_entity_decode', $matches[2]));
		$number = 0;
		$html = preg_replace_callback('/<h2\b([^>]*)>(.*?)<\/h2>/is', static function ($match) use (&$items, &$used, &$number) {
			$title = trim(wp_strip_all_tags($match[2]));
			if ($title === '') return $match[0];
			$attributes = $match[1];
			$has_id = preg_match('/(?:^|\s)id\s*=\s*(["\'])(.*?)\1/is', $attributes, $existing);
			$id = $has_id ? html_entity_decode($existing[2], ENT_QUOTES, 'UTF-8') : '';
			if ($id === '' || preg_match('/\s/u', $id) || ($used[$id] ?? 0) > 1) {
				do { $id = 'article-section-' . ++$number; } while (isset($used[$id]));
				$used[$id] = 1;
				if ($has_id) $attributes = preg_replace('/(?:^|\s)id\s*=\s*(["\'])(.*?)\1/is', '', $attributes, 1);
				$attributes .= ' id="' . esc_attr($id) . '"';
			}
			$items[] = array('id' => $id, 'title' => html_entity_decode($title, ENT_QUOTES, 'UTF-8'));
			return '<h2' . $attributes . '>' . $match[2] . '</h2>';
		}, $html);
		return array('html' => $html, 'items' => $items);
	}
}
