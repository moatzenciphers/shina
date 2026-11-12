<?php
namespace Edemchinim\Support;

use DateTimeImmutable;
use Exception;

if (! defined('ABSPATH')) {
	exit;
}

/** Format phone links, image URLs and relative dates. */
final class Formatting
{
	public static function phoneHref($phone)
	{
		$digits = preg_replace('/\D+/', '', (string) $phone);

		if (strlen($digits) === 10) {
			$digits = '7' . $digits;
		}

		if (strlen($digits) === 11 && $digits[0] === '8') {
			$digits = '7' . substr($digits, 1);
		}

		return $digits ? 'tel:+' . $digits : '#';
	}

	public static function imageUrl($image, $size = 'medium_large')
	{
		if (is_array($image)) {
			if (! empty($image['sizes'][$size])) {
				return $image['sizes'][$size];
			}

			return $image['url'] ?? '';
		}

		if (is_numeric($image)) {
			return wp_get_attachment_image_url((int) $image, $size) ?: '';
		}

		return is_string($image) ? $image : '';
	}

	public static function relativeDate($date)
	{
		if (! $date) {
			return '';
		}

		try {
			$timezone = wp_timezone();
			$day = new DateTimeImmutable($date, $timezone);
			$today = new DateTimeImmutable('today', $timezone);
			$diff = (int) $today->diff($day->setTime(0, 0))->format('%r%a');
		} catch (Exception $exception) {
			return '';
		}

		if ($diff === 0) {
			return 'Сегодня';
		}

		if ($diff === -1) {
			return 'Вчера';
		}

		$days = abs($diff);

		if ($days < 7) {
			$forms = array('день', 'дня', 'дней');
			$index = ($days % 10 === 1 && $days % 100 !== 11) ? 0 : (($days % 10 >= 2 && $days % 10 <= 4 && ($days % 100 < 10 || $days % 100 >= 20)) ? 1 : 2);
			return sprintf('%d %s назад', $days, $forms[$index]);
		}

		return wp_date('j F Y', $day->getTimestamp());
	}
}
