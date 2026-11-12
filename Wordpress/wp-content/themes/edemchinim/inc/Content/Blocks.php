<?php
namespace Edemchinim\Content;

if (! defined('ABSPATH')) {
	exit;
}

/** Render flexible landing content and its shared presentation helpers. */
final class Blocks
{
	public static function sub($name, $default = '')
	{
		$value = function_exists('get_sub_field') ? get_sub_field($name) : null;
		return $value === null || $value === false || $value === '' ? $default : $value;
	}

	public static function heading($prefix, $id, $fallback_eyebrow = '', $fallback_title = '')
	{
		$eyebrow = self::sub($prefix . '_eyebrow', $fallback_eyebrow);
		$title = self::sub($prefix . '_title', $fallback_title);
		if ($eyebrow) {
			printf('<p class="%1$s__eyebrow landing-eyebrow">%2$s</p>', esc_attr($id), esc_html($eyebrow));
		}
		printf('<h2 class="%1$s__title landing-title landing-title--section" id="%1$s-title">%2$s</h2>', esc_attr($id), nl2br(esc_html($title)));
	}

	public static function arrow($direction = 'right')
	{
		$path = $direction === 'left' ? 'M27 16H5m8-8-8 8 8 8' : 'M5 16h22m-8-8 8 8-8 8';
		printf('<svg viewBox="0 0 32 32" fill="none" aria-hidden="true"><path d="%s" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>', esc_attr($path));
	}

	public static function posts($selected, $post_type, $limit)
	{
		$ids = array_values(array_filter(array_map('intval', (array) $selected)));
		$args = array(
			'post_type' => $post_type,
			'post_status' => 'publish',
			'posts_per_page' => $ids ? count($ids) : $limit,
			'no_found_rows' => true,
		);
		if ($ids) {
			$args['post__in'] = $ids;
			$args['orderby'] = 'post__in';
		}
		return get_posts($args);
	}

	public static function imageId($image)
	{
		return is_array($image) ? (int) ($image['ID'] ?? $image['id'] ?? 0) : (int) $image;
	}

	public static function form($shortcode)
	{
		$shortcode = trim((string) $shortcode);
		if (! preg_match('/^\[contact-form-7\s+[^\]]+\]$/', $shortcode) || ! shortcode_exists('contact-form-7')) {
			return;
		}
		echo do_shortcode($shortcode); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	private static function layouts()
	{
		return array('hero', 'features_grid', 'steps_grid', 'columns_list', 'price_table', 'cards_grid', 'media_rows', 'slider_grid', 'map_panel', 'gallery_grid', 'accordion_grid', 'split_form');
	}

	public static function render($post_id)
	{
		if (! function_exists('have_rows')) return;
		while (have_rows('blocks', $post_id)) {
			the_row();
			$layout = get_row_layout();
			if (in_array($layout, self::layouts(), true)) {
				get_template_part('template-parts/blocks/' . str_replace('_', '-', $layout));
			}
		}
	}
}
