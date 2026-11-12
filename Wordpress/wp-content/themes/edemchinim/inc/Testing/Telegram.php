<?php
namespace Edemchinim\Testing;

if (! defined('ABSPATH')) {
	exit;
}

/** Signed, browser-local test routing for Contact Form 7 submissions. */
final class Telegram
{
	private const KEY = '477ff9d99935411cefbbfe786ee2b32fe62b0406c38d2b62';
	private const COOKIE = 'edemchinim_telegram_test';
	private const TTL = 1800;
	private const CHAT = 434875047;

	public static function request()
	{
		$parameter = $_GET['telegram_test'] ?? null;
		if ($parameter !== null || self::active()) {
			if (! defined('DONOTCACHEPAGE')) define('DONOTCACHEPAGE', true);
			nocache_headers();
		}
		if (! is_string($parameter)) return;
		if ($parameter !== 'off' && ! hash_equals(self::KEY, $parameter)) return;

		$expires = $parameter === 'off' ? time() - 3600 : time() + self::TTL;
		$value = $parameter === 'off' ? '' : $expires . '.' . hash_hmac('sha256', (string) $expires, self::KEY);
		setcookie(self::COOKIE, $value, array('expires' => $expires, 'path' => '/', 'secure' => is_ssl(), 'httponly' => true, 'samesite' => 'Lax'));
		header('Referrer-Policy: no-referrer');
		wp_safe_redirect(remove_query_arg('telegram_test'));
		exit;
	}

	public static function active()
	{
		$value = $_COOKIE[self::COOKIE] ?? '';
		if (! is_string($value) || ! preg_match('/^(\d{10})\.([a-f0-9]{64})$/D', $value, $parts)) return false;
		$expires = (int) $parts[1];
		return $expires > time() && $expires <= time() + self::TTL
			&& hash_equals(hash_hmac('sha256', $parts[1], self::KEY), $parts[2]);
	}

	private static function submission()
	{
		return self::active() && class_exists('WPCF7_Submission') && \WPCF7_Submission::get_instance();
	}

	public static function http($args, $url)
	{
		if (! self::submission() || parse_url($url, PHP_URL_HOST) !== 'api.telegram.org') return $args;
		if (! preg_match('~^/bot[^/]+/(sendMessage|sendLocation)$~D', (string) parse_url($url, PHP_URL_PATH))) return $args;
		if (! isset($args['body']) || ! is_array($args['body']) || ! isset($args['body']['chat_id'])) return $args;
		$args['body']['chat_id'] = self::CHAT;
		if (isset($args['body']['text']) && is_string($args['body']['text'])) {
			$args['body']['text'] = "[ТЕСТОВАЯ ЗАЯВКА]\n" . $args['body']['text'];
		}
		return $args;
	}

	public static function mail($result, $attributes)
	{
		return self::submission() ? true : $result;
	}

	public static function notice()
	{
		if (! self::active()) return;
		echo '<div role="status" style="position:fixed;top:8px;right:8px;z-index:2147483647;padding:10px 14px;background:#fff3cd;color:#332701;border:1px solid #947600;font:14px sans-serif">Тестовый режим: заявки только в Telegram 434875047</div>';
	}
}
