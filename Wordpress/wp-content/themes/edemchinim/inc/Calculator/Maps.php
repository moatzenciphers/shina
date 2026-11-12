<?php
namespace Edemchinim\Calculator;

if (! defined('ABSPATH')) {
	exit;
}

/** Read and sanitize current and legacy master point storage formats. */
final class Maps
{
	public static function sanitize($value, $post_id = 0, $field = array())
	{
		$raw_value = is_string($value) ? wp_unslash($value) : $value;
		$map = is_string($raw_value) ? json_decode($raw_value, true) : $raw_value;

		if (! is_array($map)) {
			$stored_value = get_option('options_shina_calculator_config_master_points', '');
			$map = is_string($stored_value) ? json_decode($stored_value, true) : $stored_value;
		}

		if (! is_array($map)) {
			$default_map = ! empty($field['default_value']) ? json_decode((string) $field['default_value'], true) : null;
			$map = is_array($default_map) ? $default_map : self::legacy(100);
		}

		if (! is_array($map)) {
			$map = array(
				'center_lat' => 55.755864,
				'center_lng' => 37.617698,
				'zoom'       => 9,
				'type'       => 'map',
				'marks'      => array(),
			);
		}

		$marks = array();

		foreach (($map['marks'] ?? array()) as $index => $mark) {
			if (! is_array($mark) || ! isset($mark['coords'][0], $mark['coords'][1])) {
				continue;
			}

			if (! is_numeric($mark['coords'][0]) || ! is_numeric($mark['coords'][1])) {
				continue;
			}

			$marks[] = array(
				'id'          => absint($mark['id'] ?? $index + 1),
				'content'     => sanitize_text_field(wp_strip_all_tags((string) ($mark['content'] ?? ''))),
				'type'        => 'Point',
				'coords'      => array((float) $mark['coords'][0], (float) $mark['coords'][1]),
				'circle_size' => 0,
			);
		}

		$sanitized_map = array(
			'center_lat' => is_numeric($map['center_lat'] ?? null) ? (float) $map['center_lat'] : 55.755864,
			'center_lng' => is_numeric($map['center_lng'] ?? null) ? (float) $map['center_lng'] : 37.617698,
			'zoom'       => max(0, min(18, absint($map['zoom'] ?? 9))),
			'type'       => in_array(($map['type'] ?? ''), array('map', 'satellite', 'hybrid'), true) ? $map['type'] : 'map',
			'marks'      => $marks,
		);

		return wp_json_encode($sanitized_map);
	}

	public static function legacy($rows_count)
	{
		$option_name = 'options_shina_calculator_config_master_points';
		$marks = array();

		for ($index = 0; $index < absint($rows_count); $index += 1) {
			$prefix = $option_name . '_' . $index . '_';
			$latitude = get_option($prefix . 'latitude', '');
			$longitude = get_option($prefix . 'longitude', '');

			if (! is_numeric($latitude) || ! is_numeric($longitude)) {
				continue;
			}

			$marks[] = array(
				'id'          => $index + 1,
				'content'     => sanitize_text_field((string) get_option($prefix . 'name', 'Мастер ' . ($index + 1))),
				'type'        => 'Point',
				'coords'      => array((float) $latitude, (float) $longitude),
				'circle_size' => 0,
			);
		}

		return array(
			'center_lat' => 55.755864,
			'center_lng' => 37.617698,
			'zoom'       => 9,
			'type'       => 'map',
			'marks'      => $marks,
		);
	}

	public static function load($value, $post_id, $field)
	{
		$raw_value = is_string($value) ? wp_unslash($value) : $value;
		$decoded = is_string($raw_value) ? json_decode($raw_value, true) : $raw_value;

		if (is_array($decoded) && isset($decoded['marks'])) {
			return wp_json_encode($decoded);
		}

		$map = is_numeric($value) ? self::legacy($value) : array();

		if (empty($map['marks']) && ! empty($field['default_value'])) {
			$default_map = json_decode((string) $field['default_value'], true);

			if (is_array($default_map) && isset($default_map['marks'])) {
				$map = $default_map;
			}
		}

		if (empty($map['marks'])) {
			$legacy_map = self::legacy(100);

			if (! empty($legacy_map['marks'])) {
				$map = $legacy_map;
			}
		}

		$map_json = self::sanitize($map, $post_id, $field);
		update_option('options_shina_calculator_config_master_points', $map_json, false);
		update_option('edemchinim_master_points_map_migration', '1', false);

		return $map_json;
	}
}
