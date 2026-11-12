<?php
use Edemchinim\Calculator\Admin as MapAdmin;
use Edemchinim\Calculator\Config;
use Edemchinim\Calculator\Maps;
use Edemchinim\Calculator\Migration;
use Edemchinim\Content\Prices;
use Edemchinim\Rest\Parameters;
use Edemchinim\Rest\Reviews as ReviewsApi;
use Edemchinim\Rest\Routes;
use Edemchinim\Support\Fields;
use Edemchinim\Support\Formatting;
use Edemchinim\Theme\Assets;

/**
 * Isolated compatibility checks. No WordPress installation or network is used.
 * php tests/theme-contract.php [--without-acf]
 * --snapshot and --theme-dir=... are for reviewing a compatibility baseline.
 */
if (PHP_SAPI !== 'cli' || defined('ABSPATH')) {
    exit;
}

error_reporting(E_ALL);
ini_set('display_errors', '0');
set_error_handler(static function ($severity, $message, $file, $line) {
    throw new ErrorException($message, 0, $severity, $file, $line);
});

$theme_test_root = dirname(__DIR__);
$theme_test_snapshot = in_array('--snapshot', $argv, true);
$theme_test_acf = ! in_array('--without-acf', $argv, true);
foreach ($argv as $theme_test_arg) {
    if (strpos($theme_test_arg, '--theme-dir=') === 0) {
        $theme_test_root = substr($theme_test_arg, strlen('--theme-dir='));
    }
}
define('ABSPATH', '/isolated-wordpress/');
require __DIR__ . '/compatibility.php';
$theme_test_hooks = array();
$theme_test_options = array();
$theme_test_fields = array();
$theme_test_writes = array();
$theme_test_resources = array();
$theme_test_routes = array();
$theme_test_page = false;
$theme_test_template = '';
$theme_test_registered = false;

class Walker_Nav_Menu {}
class WP_REST_Server { const READABLE = 'GET'; }
class WP_REST_Request
{
    private $params;
    public function __construct($params) { $this->params = $params; }
    public function get_param($key) { return $this->params[$key] ?? null; }
}
class WP_Query
{
    public static $fixture_posts = array();
    public $posts = array();
    public $found_posts = 23;
    public $max_num_pages = 3;
    public $current_post = -1;
    public function __construct($args = null)
    {
        if ($args !== null) {
            $this->posts = self::$fixture_posts;
            $this->found_posts = count($this->posts);
            $this->max_num_pages = (int) ceil($this->found_posts / ($args['posts_per_page'] ?? 10));
        }
    }
    public function have_posts() { return $this->current_post + 1 < count($this->posts); }
    public function the_post()
    {
        $GLOBALS['theme_test_render_post'] = $this->posts[++$this->current_post];
    }
}
class WP_Post
{
    public $ID;
    public $post_content;
    public $post_title;
    public $post_name = 'fixture-post';
    public function __construct($id, $content, $title = 'Fixture')
    {
        $this->ID = $id;
        $this->post_content = $content;
        $this->post_title = $title;
    }
}
class Theme_Test_Response
{
    public $data;
    public $headers = array();
    public function __construct($data) { $this->data = $data; }
    public function header($name, $value) { $this->headers[$name] = $value; }
}

function theme_test_hook($kind, $name, $callback, $priority, $accepted)
{
    global $theme_test_hooks;
    if (is_array($callback) && is_string($callback[0]) && strpos($callback[0], 'Edemchinim\\') === 0 && ! is_callable($callback)) {
        throw new RuntimeException('A registered theme callback is not callable.');
    }
    $callback = theme_test_legacy_callback($callback);
    $theme_test_hooks[] = array($kind, $name, $callback, $priority, $accepted);
}
function add_action($name, $callback, $priority = 10, $accepted = 1) { theme_test_hook('action', $name, $callback, $priority, $accepted); }
function add_filter($name, $callback, $priority = 10, $accepted = 1) { theme_test_hook('filter', $name, $callback, $priority, $accepted); }
function remove_action($name, $callback, $priority = 10) { theme_test_hook('remove', $name, $callback, $priority, 1); }
function get_template_directory() { global $theme_test_root; return $theme_test_root; }
function get_template_directory_uri() { return 'https://example.test/wp-content/themes/edemchinim'; }
function get_option($name, $default = false) { global $theme_test_options; return $theme_test_options[$name] ?? $default; }
function update_option($name, $value, $autoload = null)
{
    global $theme_test_options, $theme_test_writes;
    if (! empty($GLOBALS['theme_test_forbid_writes'])) {
        throw new RuntimeException('Database writes are forbidden in page and behavior checks.');
    }
    $theme_test_options[$name] = $value;
    $theme_test_writes[] = array($name, $value, $autoload);
    return true;
}
if ($theme_test_acf) {
    function get_field($name, $post_id = false) { global $theme_test_fields; return $theme_test_fields[$post_id ?: 42][$name] ?? null; }
    function get_field_object($key, $post_id, $format, $load) { return array('type' => 'yandex-map'); }
    function acf_add_options_sub_page($args) { $GLOBALS['theme_test_acf_pages'][] = $args; }
    function acf_add_local_field_group($group) { $GLOBALS['theme_test_acf_groups'][] = $group; }
}
function get_post_meta($post_id, $name, $single) { global $theme_test_fields; return $theme_test_fields[$post_id][$name] ?? ''; }
function get_the_ID() { return isset($GLOBALS['theme_test_render_post']) ? $GLOBALS['theme_test_render_post']->ID : 42; }
function get_the_title($post = null) { $post = $post ?? ($GLOBALS['theme_test_render_post'] ?? 42); return is_object($post) ? $post->post_title : 'Fixture ' . $post; }
function get_the_date($format = '', $post = null) { return '2026-11-12'; }
function get_post_type($post = null) { $id = is_object($post) ? $post->ID : ($post ?? get_the_ID()); return $GLOBALS['theme_test_render_types'][$id] ?? 'post'; }
function get_the_post_thumbnail_url($id, $size) { return wp_get_attachment_image_url($id, $size); }
function apply_filters($name, $value) { $GLOBALS['theme_test_last_filter'] = $name; return $value; }
function wp_kses_post($value) { return $value; }
function absint($value) { return abs((int) $value); }
function sanitize_key($value) { return preg_replace('/[^a-z0-9_\-]/', '', strtolower($value)); }
function sanitize_text_field($value) { return trim(strip_tags($value)); }
function wp_strip_all_tags($value) { return strip_tags($value); }
function wp_unslash($value) { return stripslashes($value); }
function wp_json_encode($value, $flags = 0, $depth = 512) { return json_encode($value, $flags, $depth); }
function wp_kses($html, $allowed) { return $html; }
function esc_url_raw($value) { return (string) $value; }
function esc_url($value) { return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8'); }
function esc_attr($value) { return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8'); }
function esc_html($value) { return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8'); }
function __($value, $domain = '') { return $value; }
function esc_html__($value, $domain = '') { return esc_html($value); }
function trailingslashit($value) { return rtrim($value, '/') . '/'; }
function home_url($path = '') { return 'https://example.test' . $path; }
function get_posts($args) { return $GLOBALS['theme_test_render_catalog'][$args['post_type'] ?? 'post'] ?? array(); }
function get_permalink($post = null) { $post = $post ?? ($GLOBALS['theme_test_render_post'] ?? 42); $id = is_object($post) ? $post->ID : $post; return $GLOBALS['theme_test_render_permalinks'][$id] ?? 'https://example.test/page/' . $id; }
function wp_get_attachment_image_url($id, $size) { return 'https://example.test/images/' . $id . '-' . $size . '.jpg'; }
function number_format_i18n($value, $decimals = 0) { return number_format($value, $decimals, ',', ' '); }
function wp_timezone() { return new DateTimeZone('Europe/Moscow'); }
function wp_date($format, $timestamp = null) { $timestamp = $timestamp ?? strtotime('2026-11-12 00:00:00 UTC'); return (new DateTimeImmutable('@' . $timestamp))->setTimezone(wp_timezone())->format($format); }
function shortcode_exists($name) { return $name === 'contact-form-7'; }
function do_shortcode($shortcode) { return '<form data-test-cf7></form>'; }
function is_page($slug = null) { global $theme_test_page; return $slug === null ? ($GLOBALS['theme_test_render_is_page'] ?? false) : $theme_test_page === $slug; }
function is_page_template($template) { return ($GLOBALS['theme_test_template'] ?? '') === $template; }
function get_header($name = null)
{
    $GLOBALS['theme_test_layout_calls'][] = array('header', $name);
    if (! empty($GLOBALS['theme_test_full_layout'])) {
        theme_test_include_view(get_template_directory() . '/header' . ($name ? '-' . $name : '') . '.php');
    }
}
function get_footer($name = null)
{
    $GLOBALS['theme_test_layout_calls'][] = array('footer', $name);
    if (! empty($GLOBALS['theme_test_full_layout'])) {
        theme_test_include_view(get_template_directory() . '/footer' . ($name ? '-' . $name : '') . '.php');
    }
}
function wp_script_is($name, $state) { global $theme_test_registered; return $theme_test_registered; }
function rest_ensure_response($data) { return new Theme_Test_Response($data); }
function register_rest_route($namespace, $path, $args) { global $theme_test_routes; $theme_test_routes[$namespace . $path] = $args; }
function theme_test_resource($kind, $args)
{
    global $theme_test_resources;
    if (in_array($kind, array('script', 'style', 'register'), true) && isset($args[3]) && ctype_digit((string) $args[3])) {
        $args[3] = '[filemtime]';
    }
    $theme_test_resources[] = array($kind, $args);
}
function wp_enqueue_script(...$args) { theme_test_resource('script', $args); }
function wp_enqueue_style(...$args) { theme_test_resource('style', $args); }
function wp_register_script(...$args) { theme_test_resource('register', $args); }
function wp_dequeue_script(...$args) { theme_test_resource('dequeue', $args); }
function wp_deregister_script(...$args) { theme_test_resource('deregister', $args); }
function wp_localize_script(...$args) { theme_test_resource('localize', $args); }
function wp_add_inline_script($handle, $code, $position = 'after') { $GLOBALS['theme_test_inline_code'][$handle][$position] = $code; theme_test_resource('inline', array($handle, hash('sha256', $code), $position)); }
function wp_remote_post(...$args) { throw new RuntimeException('Network is disabled in compatibility checks.'); }

function theme_test_function_hash($name)
{
    global $theme_test_method_map;
    $method = $theme_test_method_map[$name] ?? null;
    $function = $method ? new ReflectionMethod($method[0], $method[1]) : new ReflectionFunction($name);
    $file = $function->getFileName();
    $lines = file($file);
    $source = implode('', array_slice($lines, $function->getStartLine() - 1, $function->getEndLine() - $function->getStartLine() + 1));
    if ($method) {
        $source = preg_replace('/(?:public|private|protected)\s+static\s+function\s+' . preg_quote($method[1], '/') . '\b/', 'function ' . $name, $source, 1);
        $source = theme_test_original_source($source, $file, $method[0]);
    }
    return theme_test_code_hash('<?php ' . $source);
}

try {
    ob_start();
    require get_template_directory() . '/functions.php';
    if (ob_get_clean() !== '') {
        throw new RuntimeException('Unexpected output during theme bootstrap.');
    }
    if ($theme_test_writes !== array()) {
        throw new RuntimeException('Unexpected option write during theme bootstrap.');
    }
    foreach ($argv as $theme_test_arg) {
        if (strpos($theme_test_arg, '--render-fixture=') === 0) {
            $theme_test_scenario = substr($theme_test_arg, strlen('--render-fixture='));
            require __DIR__ . '/render-fixture.php';
            exit;
        }
    }
    // Older themes loaded these helpers from their page templates.
    if (! $theme_test_snapshot) {
        foreach (array('edemchinim_landing_cf7', 'edemchinim_asset_url', 'edemchinim_option_icon', 'edemchinim_phone_icon') as $theme_test_helper) {
            if (! is_callable($theme_test_method_map[$theme_test_helper])) {
                throw new RuntimeException('Shared helpers must be available immediately after bootstrap.');
            }
        }
        $theme_test_registered_hooks = $theme_test_hooks;
        \Edemchinim\Bootstrap::boot();
        if ($theme_test_hooks !== $theme_test_registered_hooks) {
            throw new RuntimeException('Repeated bootstrap must not register hooks twice.');
        }
    }
    ob_start();
    require get_template_directory() . '/page-calculator.php';
    $theme_test_calculator_html = hash('sha256', ob_get_clean());

    $theme_test_result = array('bootstrap_hooks' => $theme_test_hooks, 'calculator_html' => $theme_test_calculator_html);
    $theme_test_functions = array_filter(get_defined_functions()['user'], static function ($name) {
        return strpos($name, 'edemchinim_') === 0 || strpos($name, 'z_') === 0 || in_array($name, array('roots_head_cleanup', 'theme_cf7_translate_mail_tags'), true);
    });
    $theme_test_functions = array_unique(array_merge($theme_test_functions, array_keys($theme_test_method_map)));
    sort($theme_test_functions);
    foreach ($theme_test_functions as $theme_test_function) {
        $theme_test_result['function_bodies'][$theme_test_function] = theme_test_function_hash($theme_test_function);
    }
    Routes::register();
    foreach ($theme_test_routes as $theme_test_route) {
        if (! is_callable($theme_test_route['callback'])) {
            throw new RuntimeException('A route controller is not callable.');
        }
        foreach ($theme_test_route['args'] as $theme_test_argument) {
            foreach (array('validate_callback', 'sanitize_callback') as $theme_test_callback_key) {
                if (isset($theme_test_argument[$theme_test_callback_key]) && ! is_callable($theme_test_argument[$theme_test_callback_key])) {
                    throw new RuntimeException('A route argument callback is not callable.');
                }
            }
        }
    }
    $theme_test_result['routes'] = theme_test_legacy_callback($theme_test_routes);

    $theme_test_fields = array(42 => array('zero' => 0, 'empty' => '', 'false' => false, 'array' => array('one')), 'options' => array('phone' => 'fixture'));
    $theme_test_options['options_phone'] = 'fixture';
    foreach (array('zero', 'empty', 'false', 'array', 'missing') as $theme_test_field) {
        $theme_test_result['fields'][$theme_test_field] = Fields::get($theme_test_field, 42, 'fallback');
    }
    $theme_test_result['option_field'] = Fields::option('phone', 'fallback');
    foreach (array('+7 (999) 123-45-67', '8 999 123 45 67', '9991234567', '') as $theme_test_phone) {
        $theme_test_result['phones'][] = Formatting::phoneHref($theme_test_phone);
    }
    $theme_test_result['images'] = array(
        Formatting::imageUrl(array('sizes' => array('large' => '/large.jpg'), 'url' => '/full.jpg'), 'large'),
        Formatting::imageUrl(15), Formatting::imageUrl('/image.jpg'), Formatting::imageUrl(null),
    );
    $theme_test_result['review_photos'] = ReviewsApi::photos(array('/one.jpg', '/one.jpg', array('url' => '/two.jpg'), null));
    $theme_test_result['pagination'] = Parameters::pagination(new WP_REST_Request(array('page' => -2, 'per_page' => 100)), 10);
    $theme_test_result['response'] = Parameters::response(array(array('id' => 42)), new WP_Query(), 2, 10, 'reviews');
    foreach (array(0, 1, 50, 51) as $theme_test_number) {
        $theme_test_result['validation'][] = array(Parameters::positiveInt($theme_test_number), Parameters::perPage($theme_test_number));
    }
    foreach (array('', 'seasonal', 'puncture', 'invalid', array()) as $theme_test_type) {
        $theme_test_result['repair_types'][] = Parameters::validateRepairType($theme_test_type);
    }
    $theme_test_result['prices'] = array(
        Prices::format(1500, 'rub'), Prices::format(12.5, 'percent', true),
        Prices::format(250, 'rub_per_item'), Prices::format(null, 'rub'),
    );
    $theme_test_points = array(
        array('id' => 2, 'coords' => array('55.7', '37.6'), 'content' => '<b>Мастер</b>'),
        array('id' => 'master-3', 'latitude' => 55.8, 'longitude' => 37.7, 'name' => 'Второй'),
        array('coords' => array('invalid', 37)),
    );
    $theme_test_result['points'] = array(
        Config::normalizePoints($theme_test_points),
        Config::normalizePoints(wp_json_encode(array('marks' => $theme_test_points))),
        Config::normalizePoints('invalid'),
        Config::prepare(array('master_points' => $theme_test_points, 'custom' => 42)),
    );
    $theme_test_result['sanitized_map'] = Maps::sanitize(array('zoom' => 99, 'type' => 'invalid', 'marks' => $theme_test_points));

    $theme_test_options = array(
        'options_shina_calculator_config_master_points' => 1,
        'options_shina_calculator_config_master_points_0_latitude' => '55.7',
        'options_shina_calculator_config_master_points_0_longitude' => '37.6',
        'options_shina_calculator_config_master_points_0_name' => 'Мастер',
    );
    $theme_test_writes = array();
    Migration::run();
    $theme_test_result['migration_once'] = array($theme_test_options, $theme_test_writes);
    Migration::run();
    $theme_test_result['migration_twice'] = array($theme_test_options, $theme_test_writes);
    $theme_test_result['load_legacy_map'] = Maps::load(1, 'options', array());
    $theme_test_result['prepared_map'] = MapAdmin::prepareField(array('value' => $theme_test_result['load_legacy_map']));

    $theme_test_fields['options']['shina_calculator_config'] = array('master_points' => $theme_test_points);
    $theme_test_resources = array();
    Assets::enqueue();
    $theme_test_result['landing_resources'] = $theme_test_resources;
    $theme_test_resources = array();
    $theme_test_page = 'calculator';
    $theme_test_template = 'page-calculator.php';
    Assets::enqueue();
    $theme_test_result['calculator_resources'] = $theme_test_resources;
    $theme_test_resources = array();
    MapAdmin::enqueue();
    $theme_test_result['admin_without_map_plugin'] = $theme_test_resources;
    $theme_test_registered = true;
    MapAdmin::enqueue();
    $theme_test_result['admin_with_map_plugin'] = $theme_test_resources;

    $theme_test_templates = json_decode(file_get_contents(__DIR__ . '/fixtures/templates.json'), true);
    foreach ($theme_test_templates as $theme_test_path => $theme_test_hash) {
        $theme_test_file = get_template_directory() . '/' . $theme_test_path;
        $theme_test_source = theme_test_original_source(file_get_contents($theme_test_file), $theme_test_file);
        if (theme_test_code_hash($theme_test_source) !== $theme_test_hash) {
            throw new RuntimeException('Template contract changed: ' . $theme_test_path);
        }
    }
    foreach (array_keys($theme_test_method_map) as $theme_test_old_function) {
        if (function_exists($theme_test_old_function)) {
            throw new RuntimeException('A migrated function still pollutes the global namespace.');
        }
    }

    // Exercise the private record formatters through the real public entry points.
    WP_Query::$fixture_posts = array(new WP_Post(7, '<p>Ответ</p>', 'Вопрос'));
    $theme_test_photo_response = \Edemchinim\Rest\Photos::get(new WP_REST_Request(array()));
    $theme_test_faq_response = \Edemchinim\Rest\Faq::get(new WP_REST_Request(array()));
    if (($theme_test_photo_response->data['photos'][0]['id'] ?? null) !== 7 || ($theme_test_faq_response->data['faq'][0]['answer'] ?? null) !== array('Ответ')) {
        throw new RuntimeException('Private REST formatters must work through their controllers.');
    }
    WP_Query::$fixture_posts = array();
    $theme_test_review_response = \Edemchinim\Rest\Reviews::get(new WP_REST_Request(array()));
    if ($theme_test_review_response->data['reviews'] !== array()) {
        throw new RuntimeException('Empty review collection contract changed.');
    }
    $theme_test_write_count = count($theme_test_writes);
    $theme_test_render_types[8] = 'services';
    $theme_test_fields[7] = array('review_service' => 8, 'shina_review_date' => '2020-01-01');
    WP_Query::$fixture_posts = array(new WP_Post(7, 'Текст отзыва', 'Клиент'));
    foreach (array(8, new WP_Post(8, '', 'Услуга')) as $theme_test_related_value) {
        $theme_test_fields[7]['review_service'] = $theme_test_related_value;
        $theme_test_review_response = ReviewsApi::get(new WP_REST_Request(array()));
        $theme_test_review_item = $theme_test_review_response->data['reviews'][0];
        if ($theme_test_review_item['service_id'] !== 8 || $theme_test_review_item['service_name'] !== 'Fixture 8' || $theme_test_review_item['service_icon'] === '') {
            throw new RuntimeException('Review service metadata must support existing ID and post-object formats.');
        }
    }
    WP_Query::$fixture_posts = array();
    $theme_test_page = 'renamed-calculator';
    $theme_test_template = Config::TEMPLATE;
    $theme_test_resources = array();
    Assets::enqueue();
    if (! Config::isPage() || ! in_array('edemchinim-calculator', array_column(array_column($theme_test_resources, 1), 0), true)) {
        throw new RuntimeException('A renamed calculator page must load its script by template.');
    }
    $theme_test_page = 'calculator';
    $theme_test_template = '';
    $theme_test_resources = array();
    Assets::enqueue();
    if (Config::isPage() || in_array('edemchinim-calculator', array_column(array_column($theme_test_resources, 1), 0), true)) {
        throw new RuntimeException('A slug alone must not turn a regular page into the calculator.');
    }
    if (count($theme_test_writes) !== $theme_test_write_count) {
        throw new RuntimeException('Review and calculator fixes must not write options.');
    }
    \Edemchinim\Theme\Setup::contentWidth();
    if ($GLOBALS['content_width'] !== 640 || $GLOBALS['theme_test_last_filter'] !== 'edemchinim_content_width') {
        throw new RuntimeException('The public content width filter name must stay unchanged.');
    }

    $theme_test_json = json_encode($theme_test_result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . "\n";
    if ($theme_test_snapshot) {
        echo $theme_test_json;
    } else {
        $theme_test_fixture = __DIR__ . '/fixtures/contract-' . ($theme_test_acf ? 'acf' : 'no-acf') . '.json';
        $theme_test_expected = json_decode(file_get_contents($theme_test_fixture), true);
        $theme_test_actual = json_decode($theme_test_json, true);
        $theme_test_failed = array();
        foreach (array_unique(array_merge(array_keys($theme_test_expected), array_keys($theme_test_actual))) as $theme_test_check) {
            if (($theme_test_expected[$theme_test_check] ?? null) !== ($theme_test_actual[$theme_test_check] ?? null)) {
                $theme_test_failed[] = $theme_test_check;
            }
        }
        if ($theme_test_failed) {
            // Only check names are printed; never configuration values or request URLs.
            fwrite(STDERR, 'FAIL: ' . implode(', ', $theme_test_failed) . "\n");
            exit(1);
        }
        echo 'PASS: ' . count($theme_test_actual) . ' compatibility groups, ' . count($theme_test_templates) . ' template contracts, ' . count($theme_test_method_map) . ' migrated methods (' . ($theme_test_acf ? 'ACF' : 'no ACF') . ").\n";
    }
} catch (Throwable $theme_test_error) {
    // Avoid echoing exception arguments that might contain integration credentials.
    fwrite(STDERR, 'FAIL: ' . get_class($theme_test_error) . ' in ' . basename($theme_test_error->getFile()) . ':' . $theme_test_error->getLine() . "\n");
    exit(1);
}
