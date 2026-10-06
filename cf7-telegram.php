<?php

/**
 * Contact Form 7: подготовка данных и отправка заявок в Telegram.
 * Подключается из functions.php.
 */

if (! defined('ABSPATH')) {
	exit;
}

add_filter('wpcf7_special_mail_tags', 'z_clean_coordinates_tag', 10, 3);
function z_clean_coordinates_tag($output, $name, $html) {
	if ('_clean_coordinates' == $name) {
		$submission = WPCF7_Submission::get_instance();
		if ($submission) {
			$data = $submission->get_posted_data('order_coords');
			if (!empty($data)) {
				return str_replace(' ', '', $data);
			}
		}
	}
	return $output;
}

add_filter('wpcf7_mail_components', 'z_cf7_to_tlg_action', 10, 3);

function z_cf7_to_tlg_action($components, $wpcf7_get_current_contact_form, $instance)
{
	$bot_token = '8896405895:AAGUkXIpoXLqe6qlJCBol_ALhJ5fui-Nltc';

	$receivers = [
		-1003941522972,
	];

	$submission = WPCF7_Submission::get_instance();

	if (! $submission) {
		return $components;
	}

	// Один объект отправки используется и для основного письма, и для «Письмо (2)».
	static $processed_submissions = null;

	if ($processed_submissions === null) {
		$processed_submissions = new SplObjectStorage();
	}

	if ($processed_submissions->contains($submission)) {
		return $components;
	}

	$posted_data = $submission->get_posted_data();
	$posted_data = is_array($posted_data) ? $posted_data : array();
	$body_raw = trim(wp_strip_all_tags($components['body'] ?? ''));

	// phone есть во всех формах. Для калькулятора сохраняем тело письма CF7 с order_* тегами.
	$is_calculator = array_key_exists('order_service', $posted_data)
		|| array_key_exists('order_payload', $posted_data)
		|| array_key_exists('order_details', $posted_data);

	if (! $is_calculator && array_key_exists('phone', $posted_data)) {
		if (array_key_exists('coords', $posted_data)) {
			$form_label = 'Заявка из блока карты';
		} elseif (array_key_exists('details', $posted_data)) {
			$form_label = 'Заявка на помощь';
		} else {
			$form_label = 'Быстрый вызов мастера';
		}

		$lines = array($form_label);
		$labels = array(
			'phone'         => 'Телефон',
			'address'       => 'Адрес',
			'details'       => 'Что случилось',
			'callout_price' => 'Стоимость выезда',
			'arrival_time'  => 'Время прибытия',
			'coords'        => 'Координаты',
		);

		foreach ($labels as $field => $label) {
			$value = $posted_data[$field] ?? '';

			if (! is_scalar($value)) {
				continue;
			}

			$value = sanitize_textarea_field((string) $value);

			if ($value !== '') {
				$lines[] = $label . ': ' . $value;
			}
		}

		$body_raw = implode("\n", $lines);
	}

	if (empty($body_raw)) {
		return $components;
	}

	$processed_submissions->attach($submission);

	$latitude  = '';
	$longitude = '';
	$coords = '';

	foreach (array('order_coords', 'coords') as $field) {
		$value = $posted_data[$field] ?? '';

		if (is_scalar($value) && trim((string) $value) !== '') {
			$coords = trim((string) $value);
			break;
		}
	}

	// Совместимость с прежним шаблоном письма калькулятора.
	if ($coords === '' && preg_match('/Координаты:\s*([^\r\n]+)/u', $body_raw, $matches)) {
		$coords = trim($matches[1]);
	}

	if (preg_match('/^\s*(-?\d+(?:\.\d+)?)\s*,\s*(-?\d+(?:\.\d+)?)\s*$/', $coords, $matches)) {
		if (abs((float) $matches[1]) <= 90 && abs((float) $matches[2]) <= 180) {
			$latitude  = $matches[1];
			$longitude = $matches[2];
		}
	}

	$body = esc_html($body_raw);

	if ($latitude !== '' && $longitude !== '') {
		$map_link = 'https://yandex.ru/maps/?' . http_build_query([
			'text' => $latitude . ',' . $longitude,
			'z'    => 14,
		]);

		$body .= "\n\n" . '<a href="' . esc_url($map_link) . '">Открыть координаты на Яндекс.Картах</a>';
	}

	foreach ($receivers as $receiver) {
		$chat_id = intval($receiver);

		$message_response = wp_remote_post(
			'https://api.telegram.org/bot' . $bot_token . '/sendMessage',
			[
				'body' => [
					'chat_id'                  => $chat_id,
					'text'                     => $body,
					'parse_mode'               => 'HTML',
					'disable_web_page_preview' => false,
				],
				'timeout' => 20,
			]
		);

		if (is_wp_error($message_response)) {
			error_log('Telegram Message Error: ' . $message_response->get_error_message());
			continue;
		}

		$response_body = wp_remote_retrieve_body($message_response);
		$response_data = json_decode($response_body, true);

		if (empty($response_data['ok'])) {
			error_log('Telegram Message API Error: ' . $response_body);
			continue;
		}

		$message_id = $response_data['result']['message_id'] ?? null;

		if ($message_id && $latitude !== '' && $longitude !== '') {
			$location_response = wp_remote_post(
				'https://api.telegram.org/bot' . $bot_token . '/sendLocation',
				[
					'body' => [
						'chat_id'             => $chat_id,
						'latitude'            => floatval($latitude),
						'longitude'           => floatval($longitude),
						'reply_to_message_id' => $message_id,
					],
					'timeout' => 20,
				]
			);

			if (is_wp_error($location_response)) {
				error_log('Telegram Location Error: ' . $location_response->get_error_message());
			} else {
				$location_body = wp_remote_retrieve_body($location_response);
				$location_data = json_decode($location_body, true);

				if (empty($location_data['ok'])) {
					error_log('Telegram Location API Error: ' . $location_body);
				}
			}
		}
	}

	return $components;
}

add_filter('wpcf7_mail_tag_replaced', 'theme_cf7_translate_mail_tags', 10, 4);

function theme_cf7_translate_mail_tags($replaced, $submitted, $html, $mail_tag)
{
	$field_name = $mail_tag->field_name();

	$translations = [
		'order_tariff' => [
			'day'   => 'День',
			'night' => 'Ночь',
		],
		'order_inside_mkad' => [
			'yes' => 'Да',
			'no'  => 'Нет',
		],
	];

	if (isset($translations[$field_name][$replaced])) {
		return $translations[$field_name][$replaced];
	}

	return $replaced;
}