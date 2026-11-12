<?php
namespace Edemchinim\Calculator;

use Edemchinim\Calculator\Maps;

if (! defined('ABSPATH')) {
	exit;
}

/** Run the existing master point migration with its original guards. */
final class Migration
{
	public static function run()
	{
		if (get_option('edemchinim_master_points_map_migration') === '1' || ! function_exists('get_field_object')) {
			return;
		}

		$field = get_field_object('field_shina_calc_master_points', 'options', false, false);

		if (($field['type'] ?? '') !== 'yandex-map') {
			return;
		}

		$option_name = 'options_shina_calculator_config_master_points';
		$stored_value = get_option($option_name, '');
		$stored_map = is_string($stored_value) ? json_decode($stored_value, true) : $stored_value;

		if (is_array($stored_map) && isset($stored_map['marks'])) {
			update_option('edemchinim_master_points_map_migration', '1', false);
			return;
		}

		$rows_count = absint($stored_value);

		if (! $rows_count) {
			return;
		}
$map = Maps::legacy($rows_count);

		if (empty($map['marks'])) {
			return;
		}

		update_option($option_name, Maps::sanitize($map), false);
		update_option('edemchinim_master_points_map_migration', '1', false);
	}
}
