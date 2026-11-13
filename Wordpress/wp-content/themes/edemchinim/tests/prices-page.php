<?php
/** Isolated price-page checks: no WordPress database, network or data writes. */
if (PHP_SAPI !== 'cli' || defined('ABSPATH')) exit;
define('ABSPATH', '/isolated/');
error_reporting(E_ALL);
set_error_handler(static function ($severity, $message, $file, $line) { throw new ErrorException($message, 0, $severity, $file, $line); });
require dirname(__DIR__) . '/inc/autoload.php';

class WP_Post {
    public $ID;
    public $post_title;
    public function __construct($id, $title) { $this->ID = $id; $this->post_title = $title; }
}
$services = array(new WP_Post(11, 'Ремонт проколов'), new WP_Post(12, 'Шиномонтаж'), new WP_Post(13, 'Без цен'));
$prices = array(21 => new WP_Post(21, 'Ремонт'), 22 => new WP_Post(22, 'Дополнительная работа'), 23 => new WP_Post(23, 'Комплекс'), 24 => new WP_Post(24, 'Черновик'));
$fields = array(
    11 => array('services_prices' => array(21, 22, 24)),
    12 => array('services_prices' => array(new WP_Post(23, 'Комплекс'))),
    13 => array('services_prices' => array()),
    21 => array('price_type' => 'value', 'price_value' => 1500, 'price_display_type' => 'rub_from'),
    22 => array('price_type' => 'value', 'price_value' => 20, 'price_display_type' => 'percent', 'price_additional' => true),
    23 => array('price_type' => 'table', 'price_display_type' => 'rub_per_item', 'price_rows' => array(array('name' => 'R16', 'value' => 700), array('name' => 'R17', 'value' => 800))),
);
$queries = array();
function get_field($name, $id = false) { return $GLOBALS['fields'][$id][$name] ?? null; }
function get_posts($args) {
    $GLOBALS['queries'][] = $args;
    if ($args['post_type'] === 'services') return $GLOBALS['services'];
    if ($args['post_type'] === 'page') return array();
    $result = array();
    foreach ($args['post__in'] as $id) if ($id !== 24 && isset($GLOBALS['prices'][$id])) $result[] = $GLOBALS['prices'][$id];
    return $result;
}
function get_the_title($value) { return $value instanceof WP_Post ? $value->post_title : 'Цены'; }
function number_format_i18n($value, $decimals = 0) { return number_format($value, $decimals, ',', ' '); }
function esc_html($value) { return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8'); }
function esc_attr($value) { return esc_html($value); }
function esc_url($value) { return esc_html($value); }
function get_queried_object_id() { return 1; }
function get_post_field($field, $id) { return ''; }
function get_permalink($post) { return '/services/' . $post->ID . '/'; }
function home_url($path) { return $path; }
function apply_filters($name, $value) { return $value; }
function get_template_directory_uri() { return 'file://' . dirname(__DIR__); }
function get_template_part($slug, $name = null, $args = array()) { require dirname(__DIR__) . '/' . $slug . '.php'; }
function shortcode_exists($tag) { return $tag === 'contact-form-7'; }
function do_shortcode($shortcode) { return '<form class="wpcf7-form"><input type="tel" name="phone" aria-label="Телефон"><button type="submit">Получить помощь</button></form>'; }
function get_header() {
    $styles = glob(dirname(__DIR__, 5) . '/dist/css/main.*.css');
    $css = $styles ? 'file://' . $styles[0] : get_template_directory_uri() . '/css/landing.css';
    echo '<!doctype html><html lang="ru"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><link rel="stylesheet" href="' . $css . '"></head><body><div class="landing landing--wp-header">';
}
function get_footer() {
    $module = dirname(__DIR__, 5) . '/src/js/prices-page.js';
    if (is_readable($module)) echo '<script>' . str_replace('export const ', 'const ', file_get_contents($module)) . PHP_EOL . 'initPricesPage();</script>';
    echo '</div></body></html>';
}
function check($condition, $message) { if (! $condition) throw new RuntimeException($message); }

$groups = \Edemchinim\Content\Prices::groups();
check(count($groups) === 2, 'Services without published related prices must be omitted.');
check($groups[0]['main'][0]->ID === 21 && $groups[0]['additional'][0]->ID === 22, 'Partition main prices and surcharges in service relation order.');
check($groups[1]['main'][0]->ID === 23, 'ACF post objects must be supported.');
foreach ($queries as $query) check($query['post_status'] === 'publish', 'Query published records only.');
ob_start(); require dirname(__DIR__) . '/page-prices.php'; $html = ob_get_clean();
check(substr_count($html, '<h1 ') === 1, 'Exactly one H1.');
check(strpos($html, 'Черновик') === false && strpos($html, 'Без цен') === false, 'No draft tariffs or empty service sections.');
check(strpos($html, 'от 1 500 ₽') !== false && strpos($html, '+20%') !== false, 'Use existing money/from/percentage formatting.');
check(strpos($html, '700 ₽/шт.') !== false && strpos($html, 'R17') !== false, 'Preserve table headings and per-item format.');
check(substr_count($html, 'id="price-service-11"') === 1 && strpos($html, 'href="#price-service-11"') !== false, 'Navigation anchors must resolve to unique sections.');
check(strpos($html, 'tabindex="0"') !== false, 'Wide tables must support keyboard scrolling.');
check(substr_count($html, '<details class="prices-page__service"') === 2 && strpos($html, '<details class="prices-page__service" open') === false, 'Each service starts collapsed.');
check(substr_count($html, 'class="split-form landing-section"') === 1 && substr_count($html, '<form ') === 1, 'Reuse the existing form once after the prices.');
check(strpos($html, 'class="split-form landing-section"') > strrpos($html, '</details>'), 'Form follows all price groups.');
if (in_array('--render', $argv, true)) { echo $html; exit; }
$services = array();
ob_start(); require dirname(__DIR__) . '/page-prices.php'; $empty = ob_get_clean();
check(strpos($empty, 'Цены пока не добавлены.') !== false && strpos($empty, '<table') === false, 'Honest empty state without fabricated prices.');
echo "PASS: service relations, published prices, ACF IDs/objects, surcharges, formats, table rows, page navigation and empty state.\n";
