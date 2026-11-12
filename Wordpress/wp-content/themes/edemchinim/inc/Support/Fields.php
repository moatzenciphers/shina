<?php
namespace Edemchinim\Support;

if (! defined('ABSPATH')) {
	exit;
}

/** Read custom fields through ACF or the existing WordPress fallbacks. */
final class Fields
{
	public static function get($field_name, $post_id = false, $default = '')
	{
		if (function_exists('get_field')) {
			$value = get_field($field_name, $post_id);
			return $value !== null && $value !== false && $value !== '' ? $value : $default;
		}

		if ($post_id === 'options' || $post_id === 'option') {
			$value = get_option('options_' . $field_name, '');
			return $value !== '' ? $value : $default;
		}

		$value = get_post_meta($post_id ?: get_the_ID(), $field_name, true);
		return $value !== '' ? $value : $default;
	}

	public static function option($field_name, $default = '')
	{
		return self::get($field_name, 'options', $default);
	}
}
