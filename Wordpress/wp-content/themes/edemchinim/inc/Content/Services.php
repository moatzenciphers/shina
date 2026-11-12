<?php
namespace Edemchinim\Content;

use Edemchinim\Support\Fields;

if (! defined('ABSPATH')) {
	exit;
}

/** Service icons, area terms and archive options. */
final class Services
{
	public static function sanitizeIcon($raw)
	{
		$attributes = array_fill_keys(array('viewbox', 'width', 'height', 'fill', 'stroke', 'stroke-width', 'stroke-linecap', 'stroke-linejoin', 'fill-rule', 'clip-rule', 'opacity', 'd', 'points', 'x', 'y', 'x1', 'y1', 'x2', 'y2', 'cx', 'cy', 'r', 'rx', 'ry', 'transform'), true);
		$allowed = array();
		foreach (array('svg', 'g', 'path', 'circle', 'ellipse', 'rect', 'line', 'polyline', 'polygon') as $tag) $allowed[$tag] = $attributes;
		return wp_kses((string) $raw, $allowed);
	}

	public static function icon($service_id, $class = '')
	{
		$svg = self::sanitizeIcon(Fields::get('service_icon', $service_id, ''));
		if (stripos($svg, '<svg') === false) $svg = '<svg viewBox="0 0 24 24" fill="none"><path d="M20 12a8 8 0 1 1-16 0 8 8 0 0 1 16 0Z M12 4v16 M4 12h16 M6.3 6.3l11.4 11.4 M6.3 17.7 17.7 6.3" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg>';
		return '<span class="service-icon ' . esc_attr($class) . '" aria-hidden="true">' . $svg . '</span>';
	}

	public static function areas($service_id)
	{
		$terms = get_the_terms($service_id, 'area');
		return ! $terms || is_wp_error($terms) ? array() : $terms;
	}

	public static function registerArchiveSettings()
	{
		if (! function_exists('acf_add_options_sub_page')) return;
		acf_add_options_sub_page(array(
			'page_title' => 'Каталог услуг',
			'menu_title' => 'Настройки каталога',
			'menu_slug' => 'services-archive',
			'parent_slug' => 'edit.php?post_type=services',
			'post_id' => 'services-archive',
			'capability' => 'manage_options',
			'redirect' => false,
		));
	}

	public static function registerFields()
	{
		if (! function_exists('acf_add_local_field_group')) return;
		foreach (array('acf-review-fields.json', 'acf-review-caption.json') as $filename) {
			$schema = get_template_directory() . '/inc/' . $filename;
			if (! is_readable($schema)) continue;
			$group = json_decode(file_get_contents($schema), true);
			if (is_array($group)) acf_add_local_field_group($group);
		}
	}
}
