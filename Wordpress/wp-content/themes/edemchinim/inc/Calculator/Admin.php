<?php
namespace Edemchinim\Calculator;

use Edemchinim\Calculator\Maps;

if (! defined('ABSPATH')) {
	exit;
}

/** Prepare the ACF map field and enqueue its admin adapter. */
final class Admin
{
	public static function prepareField($field)
	{
		$value = $field['value'] ?? '';
		$raw_value = is_string($value) ? wp_unslash($value) : $value;
		$map = is_string($raw_value) ? json_decode($raw_value, true) : $raw_value;

		if (! is_array($map) || ! isset($map['marks'])) {
			$default_map = ! empty($field['default_value']) ? json_decode((string) $field['default_value'], true) : null;
			$map = is_array($default_map) && isset($default_map['marks'])
				? $default_map
				: Maps::legacy(100);
		}

		$field['value'] = Maps::sanitize($map, 'options', $field);

		return $field;
	}

	public static function enqueue()
	{
		if (! wp_script_is('acf-yandex', 'registered')) {
			return;
		}

		$relative_path = '/js/acf-yandex-map-admin.js';
		$script_path = get_template_directory() . $relative_path;

		wp_dequeue_script('acf-yandex');
		wp_deregister_script('acf-yandex');
		wp_register_script(
			'acf-yandex',
			get_template_directory_uri() . $relative_path,
			array('jquery', 'yandex-map-api'),
			file_exists($script_path) ? (string) filemtime($script_path) : _S_VERSION,
			true
		);
		wp_localize_script(
			'acf-yandex',
			'acf_yandex_locale',
			array(
				'map_init_fail'      => __('Не удалось инициализировать поле Яндекс Карты.', 'edemchinim'),
				'mark_hint'          => __('Перетащите точку. Нажмите правой кнопкой, чтобы удалить.', 'edemchinim'),
				'btn_clear_all'      => __('Очистить', 'edemchinim'),
				'btn_clear_all_hint' => __('Удалить все точки', 'edemchinim'),
				'mark_save'          => __('Сохранить', 'edemchinim'),
				'mark_remove'        => __('Удалить', 'edemchinim'),
			)
		);
		wp_enqueue_script('acf-yandex');
	}
}
