<?php
/** Read-only About page checks with isolated WordPress data. */
if (PHP_SAPI !== 'cli' || defined('ABSPATH')) exit;
define('ABSPATH', '/isolated/');
error_reporting(E_ALL);
set_error_handler(static function ($severity, $message, $file, $line) { throw new ErrorException($message, 0, $severity, $file, $line); });
require dirname(__DIR__) . '/inc/autoload.php';
$gallery = array(array('ID' => 101), 102, 103);
$queries = array();
$custom_blocks = false;
function get_posts($args) {
    $GLOBALS['queries'][] = $args;
    if ($args['post_type'] === 'page') return ($args['meta_value'] ?? '') === 'page-works.php' ? array((object) array('ID' => 9)) : array();
    return array_map(static function ($id) { return (object) array('ID' => $id); }, $args['post__in'] ?? array(104));
}
function get_field($name, $id = false) { return $name === 'works_images' ? $GLOBALS['gallery'] : null; }
function get_page_by_path($slug) { return null; }
function get_queried_object_id() { return 1; }
function get_post_thumbnail_id($id) { return 0; }
function get_post_meta($id, $key, $single) { return ''; }
function get_template_directory_uri() { return 'file://' . dirname(__DIR__); }
function get_template_part($slug, $name = null, $args = array()) { require dirname(__DIR__) . '/' . $slug . '.php'; }
function home_url($path) { return $path; }
function get_permalink($post) { return '/calculator/'; }
function esc_html($value) { return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8'); }
function esc_attr($value) { return esc_html($value); }
function esc_url($value) { return esc_html($value); }
function wp_get_attachment_image_url($id, $size) {
    $photos = array(101 => 'visit-home.jpg', 102 => 'visit-evening.jpg', 103 => 'visit-puncture.jpg', 104 => 'visit-battery.jpg');
    return isset($photos[$id]) ? 'file://' . dirname(__DIR__, 5) . '/src/img/' . $photos[$id] : '';
}
function wp_get_attachment_image($id, $size, $icon, $attrs) {
    return '<img class="' . esc_attr($attrs['class']) . '" src="' . esc_url(wp_get_attachment_image_url($id, $size)) . '" alt="Выездное обслуживание автомобиля" loading="lazy">';
}
function shortcode_exists($tag) { return $tag === 'contact-form-7'; }
function do_shortcode($shortcode) { return '<form class="wpcf7-form"><input type="tel" aria-label="Телефон"><button type="submit">Получить помощь</button></form>'; }
function have_rows($name, $id = false) {
    if (! $GLOBALS['custom_blocks']) return false;
    if ($GLOBALS['custom_blocks'] === true) { $GLOBALS['custom_blocks'] = 1; return true; }
    return $GLOBALS['custom_blocks']++ === 1;
}
function the_row() {}
function get_row_layout() { return 'split_form'; }
function get_sub_field($name) { return array('help_title' => 'Редакторский заголовок', 'help_lead' => 'Текст из ACF')[$name] ?? ''; }
function get_header() {
    $styles = glob(dirname(__DIR__, 5) . '/dist/css/main.*.css');
    $css = in_array('--theme-css', $GLOBALS['argv'], true) ? get_template_directory_uri() . '/css/landing.css' : 'file://' . $styles[0];
    echo '<!doctype html><html lang="ru"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><link rel="stylesheet" href="' . $css . '"></head><body><div class="landing">';
}
function get_footer() { echo '</div></body></html>'; }
function check($condition, $message) { if (! $condition) throw new RuntimeException($message); }

$images = \Edemchinim\Content\About::images();
check($images === array(101, 102, 103), 'Preserve selected works gallery order and ACF image arrays.');
$gallery = array();
check(\Edemchinim\Content\About::images() === array(104), 'Use active gallery photographs if no selection exists.');
$last_query = end($queries);
check($last_query['meta_query'][0]['key'] === 'shina_photo_is_active', 'Never use unrelated media-library images.');
$gallery = array(array('ID' => 101), 102, 103);
ob_start(); require dirname(__DIR__) . '/page-about.php'; $html = ob_get_clean();
check(substr_count($html, '<h1 ') === 1, 'Exactly one H1.');
foreach (array('15+ лет', 'Более 20 экипажей', 'банковские карты', 'Оформляем договор', '/prices/', '/calculator/', 'visit-evening.jpg', 'visit-puncture.jpg', 'wpcf7-form') as $text) check(strpos($html, $text) !== false, 'Missing confirmed content or destination: ' . $text);
check(strpos($html, 'заглушка') === false && strpos($html, 'href="#reviews"') === false, 'No placeholders or missing review anchors.');
ob_start(); get_template_part('template-parts/pages/about', null, array('content' => \Edemchinim\Content\About::content(), 'images' => array(), 'page_id' => 1)); $empty = ob_get_clean();
check(strpos($empty, '<img') === false && strpos($empty, 'Безопасное место') !== false, 'Without gallery retain real text without fake imagery.');
$custom_blocks = true;
ob_start(); require dirname(__DIR__) . '/page-about.php'; $edited = ob_get_clean();
check(strpos($edited, 'Редакторский заголовок') !== false && strpos($edited, '15+ лет') === false, 'Existing ACF content takes precedence.');
echo in_array('--render', $argv, true) ? $html : "PASS: About facts, gallery selection, empty photos, destinations, form and ACF override.\n";
