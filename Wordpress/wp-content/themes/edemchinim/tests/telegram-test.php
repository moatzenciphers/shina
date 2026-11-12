<?php
if (PHP_SAPI !== 'cli' || defined('ABSPATH')) exit;
define('ABSPATH', '/isolated/');
require dirname(__DIR__) . '/inc/autoload.php';
use Edemchinim\Testing\Telegram;
class WPCF7_Submission
{
    public static $present = true;
    public static function get_instance() { return self::$present ? new self() : null; }
}
function check($condition) { if (! $condition) throw new RuntimeException('Test routing assertion failed.'); }
$args = array('body' => array('chat_id' => -123, 'text' => 'Fixture', 'latitude' => 55.7), 'timeout' => 20);
$url = 'https://api.telegram.org/bot123:fixture/sendMessage';
check(Telegram::http($args, $url) === $args);
check(Telegram::mail(null, array()) === null);
$reflection = new ReflectionClass(Telegram::class);
$key = $reflection->getConstant('KEY');
$cookie = $reflection->getConstant('COOKIE');
$expires = time() + 600;
$_GET['telegram_test'] = '1';
check(! Telegram::active());
$_COOKIE[$cookie] = $expires . '.' . str_repeat('a', 64);
check(! Telegram::active());
$_COOKIE[$cookie] = $expires . '.' . hash_hmac('sha256', (string) $expires, $key);
check(Telegram::active());
$changed = Telegram::http($args, $url);
check($changed['body']['chat_id'] === 434875047 && strpos($changed['body']['text'], '[ТЕСТОВАЯ ЗАЯВКА]') === 0);
check($changed['timeout'] === 20 && $changed['body']['latitude'] === 55.7);
check(Telegram::http($args, 'https://api.telegram.org/bot123:fixture/sendLocation')['body']['chat_id'] === 434875047);
check(Telegram::http($args, 'https://example.test/bot123:fixture/sendMessage') === $args);
check(Telegram::http($args, 'https://api.telegram.org/bot123:fixture/getUpdates') === $args);
check(Telegram::mail(null, array()) === true);
WPCF7_Submission::$present = false;
check(Telegram::http($args, $url) === $args);
check(Telegram::mail(null, array()) === null);
ob_start(); Telegram::notice(); check(strpos(ob_get_clean(), '434875047') !== false);
$expires = time() - 1;
$_COOKIE[$cookie] = $expires . '.' . hash_hmac('sha256', (string) $expires, $key);
check(! Telegram::active());
check(Telegram::http($args, $url) === $args);
echo "PASS: test routing, signatures, expiry, geolocation and mail isolation; no network or database used.\n";
