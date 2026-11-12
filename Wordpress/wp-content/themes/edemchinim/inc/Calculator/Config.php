<?php
namespace Edemchinim\Calculator;

if (! defined('ABSPATH')) {
	exit;
}

/** Normalize calculator configuration and resolve its page URL. */
final class Config
{
	const TEMPLATE = 'page-calculator.php';

	public static function isPage()
	{
		return is_page_template(self::TEMPLATE);
	}

	public static function normalizePoints($value)
	{
		if (is_string($value)) {
			$decoded = json_decode($value, true);

			if (json_last_error() === JSON_ERROR_NONE) {
				$value = $decoded;
			}
		}

		if (! is_array($value)) {
			return array();
		}

		$points = isset($value['marks']) && is_array($value['marks']) ? $value['marks'] : $value;
		$normalized = array();

		foreach ($points as $index => $point) {
			if (! is_array($point)) {
				continue;
			}

			$coords = $point['coords'] ?? $point['coordinates'] ?? array(
				$point['latitude'] ?? $point['lat'] ?? null,
				$point['longitude'] ?? $point['lng'] ?? $point['lon'] ?? null,
			);

			if (! is_array($coords) || count($coords) < 2 || ! is_numeric($coords[0]) || ! is_numeric($coords[1])) {
				continue;
			}

			$point_number = is_numeric($point['id'] ?? null) ? absint($point['id']) : $index + 1;
			$point_id = sanitize_key((string) ($point['id'] ?? ''));

			if (! $point_id || strpos($point_id, 'master-') !== 0) {
				$point_id = 'master-' . max(1, $point_number);
			}

			$normalized[] = array(
				'id'        => $point_id,
				'name'      => sanitize_text_field(wp_strip_all_tags((string) ($point['content'] ?? $point['name'] ?? 'Мастер ' . ($index + 1)))),
				'latitude'  => (float) $coords[0],
				'longitude' => (float) $coords[1],
			);
		}

		return $normalized;
	}

	public static function prepare($config)
	{
		if (! is_array($config)) {
			return array();
		}

		$config['master_points'] = self::normalizePoints($config['master_points'] ?? array());

		return $config;
	}

	public static function url()
	{
		static $url = null;
		if ($url !== null) return $url;
		$pages = get_posts(array('post_type' => 'page', 'post_status' => 'publish', 'posts_per_page' => 1, 'meta_key' => '_wp_page_template', 'meta_value' => self::TEMPLATE));
		return $url = $pages ? get_permalink($pages[0]) : home_url('/calculator/');
	}
}
