<?php
/** Render one isolated request; loaded only by the compatibility runner in CLI. */
if (PHP_SAPI !== 'cli' || ! defined('ABSPATH')) {
    exit;
}

function theme_test_include_view($file, $args = array())
{
    global $wp_query;
    require $file;
}
function get_template_part($slug, $name = null, $args = array())
{
    $paths = $name ? array($slug . '-' . $name . '.php', $slug . '.php') : array($slug . '.php');
    foreach ($paths as $path) {
        $file = get_template_directory() . '/' . $path;
        if (is_readable($file)) {
            theme_test_include_view($file, $args);
            return;
        }
    }
    // This reference was already missing in the source theme.
    if ($slug !== 'template-parts/landing-mobile-actions') {
        throw new RuntimeException('Missing template in render fixture.');
    }
    return false;
}
function have_posts() { return $GLOBALS['wp_query']->have_posts(); }
function the_post() { $GLOBALS['wp_query']->the_post(); }
function get_queried_object_id() { return 42; }
function get_queried_object() { return (object) array('term_id' => 2); }
function get_query_var($key) { return $key === 'paged' ? 1 : ''; }
function get_post_status($post) { return 'publish'; }
function get_the_content() { return $GLOBALS['theme_test_render_post']->post_content; }
function get_the_excerpt($post = null) { return 'Короткое описание записи.'; }
function get_the_author() { return 'Автор'; }
function get_the_author_meta($key) { return 1; }
function get_author_posts_url($id) { return 'https://example.test/author/' . $id . '/'; }
function get_the_category_list($separator) { return '<a href="/articles/">Статьи</a>'; }
function get_the_tag_list($before = '', $separator = '', $after = '') { return '<a href="/tags/tires/">Шины</a>'; }
function is_attachment() { return false; }
function post_password_required() { return $GLOBALS['theme_test_render_protected']; }
function metadata_exists($type, $id, $key) { return array_key_exists($key, $GLOBALS['theme_test_fields'][$id] ?? array()); }
function get_post_thumbnail_id() { return 15; }
function has_post_thumbnail() { return ! $GLOBALS['theme_test_render_no_images']; }
function wp_get_attachment_image($id, $size, $icon = false, $attributes = array()) { $id = is_object($id) ? $id->ID : $id; return '<img data-test-image="' . esc_attr($id . ':' . $size) . '">'; }
function get_the_post_thumbnail($id, $size, $attributes = array()) { return wp_get_attachment_image($id, $size, false, $attributes); }
function wp_get_attachment_caption($id) { return 'Подпись к фотографии'; }
function wp_trim_words($text, $count, $more = null) { return $text; }
function get_category_by_slug($slug) { return (object) array('term_id' => 2, 'name' => 'Статьи'); }
function get_category_link($id) { return 'https://example.test/articles/'; }
function wp_get_post_categories($id) { return array(2); }
function get_the_category($id) { return array((object) array('term_id' => 2, 'name' => 'Статьи')); }
function term_is_ancestor_of($parent, $child, $taxonomy) { return false; }
function single_cat_title($prefix = '', $display = true) { return 'Статьи'; }
function get_post_type_archive_link($type) { return 'https://example.test/' . $type . '/'; }
function get_page_by_path($path) { return null; }
function paginate_links($args) { return $args['type'] === 'array' ? array('<a class="page-numbers" href="?paged=2">2</a>') : '<ul><li>2</li></ul>'; }
function add_query_arg($key, $value, $url) { return $url . '?' . $key . '=' . $value; }
function get_the_terms($id, $taxonomy) { return array(); }
function is_wp_error($value) { return false; }
function is_email($value) { return filter_var($value, FILTER_VALIDATE_EMAIL); }
function get_search_query() { return 'шины'; }
function the_title($before = '', $after = '') { echo $before . esc_html(get_the_title()) . $after; }
function the_permalink() { echo esc_url(get_permalink()); }
function the_ID() { echo get_the_ID(); }
function post_class() { echo 'class="fixture-post"'; }
function the_excerpt() { echo get_the_excerpt(); }
function the_content() { echo get_the_content(); }
function the_posts_navigation() { echo '<nav data-test-pagination></nav>'; }
function get_sidebar() { $GLOBALS['theme_test_layout_calls'][] = array('sidebar', null); }
function esc_html_e($text, $domain = '') { echo esc_html($text); }
function is_search() { return true; }
function is_singular($type = null) { return false; }
function is_user_logged_in() { return $GLOBALS['theme_test_scenario'] === 'single-auth-layout'; }
function is_home() { return false; }
function current_user_can($capability) { return false; }
function get_search_form() { echo '<form data-test-search></form>'; }

function language_attributes() { echo 'lang="ru"'; }
function bloginfo($key) { echo 'UTF-8'; }
function body_class() { echo 'class="fixture-page"'; }
function wp_head() { \Edemchinim\Theme\Assets::enqueue(); }
function wp_body_open() {}
function wp_footer() {}
function has_nav_menu($location) { return false; }
function wp_nav_menu($args) { echo '<div data-test-menu></div>'; }

function theme_test_forbidden_write() { throw new RuntimeException('Database mutation is forbidden in render checks.'); }
function update_post_meta(...$args) { theme_test_forbidden_write(); }
function delete_post_meta(...$args) { theme_test_forbidden_write(); }
function add_post_meta(...$args) { theme_test_forbidden_write(); }
function wp_insert_post(...$args) { theme_test_forbidden_write(); }
function wp_update_post(...$args) { theme_test_forbidden_write(); }
function update_field(...$args) { theme_test_forbidden_write(); }
function add_option(...$args) { theme_test_forbidden_write(); }
function delete_option(...$args) { theme_test_forbidden_write(); }

function have_rows($name, $id)
{
    return $GLOBALS['theme_test_render_row'] + 1 < count($GLOBALS['theme_test_render_rows']);
}
function the_row() { $GLOBALS['theme_test_render_row']++; }
function get_row_layout() { return $GLOBALS['theme_test_render_rows'][$GLOBALS['theme_test_render_row']]; }
function get_sub_field($name) { return null; }

$theme_test_empty = strpos($theme_test_scenario, '-empty') !== false;
$theme_test_forbid_writes = true;
$theme_test_full_layout = substr($theme_test_scenario, -7) === '-layout';
$theme_test_render_is_page = true;
$theme_test_render_protected = $theme_test_scenario === 'article-protected';
$theme_test_render_no_images = $theme_test_scenario === 'category-no-images';
$theme_test_layout_calls = array();
$theme_test_render_rows = $theme_test_scenario === 'blocks' ? array('features_grid', 'steps_grid', 'split_form') : array();
if ($theme_test_scenario === 'all-blocks') {
    $theme_test_render_rows = array('hero', 'features_grid', 'steps_grid', 'columns_list', 'price_table', 'cards_grid', 'media_rows', 'slider_grid', 'map_panel', 'gallery_grid', 'accordion_grid', 'split_form');
}
$theme_test_render_row = -1;
$theme_test_fields = array(
    42 => array('article_hero_image' => 15, 'article_service' => 8, 'article_form_shortcode' => '[contact-form-7 id="test"]', 'quick_call_shortcode' => '[contact-form-7 id="test"]'),
    7 => array('review_service' => 8, 'shina_review_date' => '2026-11-09', 'shina_review_rating' => 5, 'shina_review_gallery' => array('/photo.jpg'), 'review_heading' => 'Спасибо'),
    8 => array('service_card_description' => 'Описание услуги', 'service_icon' => '<svg viewBox="0 0 24 24"></svg>'),
    'options' => array('shina_phone' => '+7 (999) 123-45-67', 'shina_email' => 'test@example.test', 'shina_legal_details' => 'Реквизиты', 'shina_contact_links' => array(array('label' => 'Ссылка', 'url' => '/contact/'))),
);
if ($theme_test_scenario === 'article-no-related') {
    $theme_test_fields[42]['article_service'] = 0;
    $theme_test_fields[42]['article_show_contents'] = false;
    $theme_test_fields[42]['article_form_shortcode'] = '';
}
$theme_test_context_lead = null;
if (strpos($theme_test_scenario, '-context-') !== false) {
    $theme_test_context_id = strpos($theme_test_scenario, 'category') === 0 ? 'category_2' : (strpos($theme_test_scenario, 'reviews-archive') === 0 ? 'reviews-archive' : 42);
    if ($theme_test_acf || is_int($theme_test_context_id)) {
        $theme_test_context_lead = 'Описание формы из настроек её страницы';
        $theme_test_fields[$theme_test_context_id]['article_form_lead'] = $theme_test_context_lead;
    } else {
        $theme_test_context_lead = 'Опишите ситуацию. Оператор уточнит детали и согласует необходимую помощь.';
    }
}
$theme_test_options = array('date_format' => 'd.m.Y');
foreach ($theme_test_fields['options'] as $theme_test_key => $theme_test_value) {
    $theme_test_options['options_' . $theme_test_key] = $theme_test_value;
}
$theme_test_article = new WP_Post(42, '<h2>Раздел</h2><p>Текст статьи.</p>', 'Заголовок статьи');
$theme_test_review = new WP_Post(7, 'Текст отзыва', 'Клиент');
$theme_test_image = new WP_Post(15, '', 'Фотография');
$theme_test_service = new WP_Post(8, '', 'Шиномонтаж');
$theme_test_render_types = array(42 => 'post', 7 => 'reviews', 8 => 'services', 15 => 'attachment');
$theme_test_render_catalog = array('reviews' => $theme_test_empty ? array() : array($theme_test_review), 'services' => array($theme_test_service));
if ($theme_test_full_layout) {
    $theme_test_render_catalog['page'] = array(new WP_Post(88, '', 'Калькулятор'));
    $theme_test_render_permalinks[88] = 'https://example.test/calculator/';
}
$theme_test_template = strpos($theme_test_scenario, 'calculator') === 0 ? 'page-calculator.php' : '';
if ($theme_test_scenario === 'calculator-renamed-layout') {
    $theme_test_page = 'renamed-calculator';
    $theme_test_render_permalinks[88] = 'https://example.test/new-calculator/';
} elseif ($theme_test_scenario === 'calculator-slug-layout') {
    $theme_test_page = 'calculator';
    $theme_test_template = '';
}
if ($theme_test_scenario === 'catalog-empty') {
    $theme_test_render_catalog['services'] = array();
}
WP_Query::$fixture_posts = $theme_test_empty ? array() : array(strpos($theme_test_scenario, 'works') === 0 ? $theme_test_image : $theme_test_review);
$wp_query = new WP_Query();
$wp_query->posts = $theme_test_empty ? array() : array($theme_test_article);
$wp_query->max_num_pages = 2;
$theme_test_render_post = $theme_test_article;
$theme_test_warnings = array();
set_error_handler(static function ($severity, $message) use (&$theme_test_warnings) {
    if ($severity === E_WARNING || $severity === E_NOTICE) {
        $theme_test_warnings[] = $message;
        return true;
    }
    throw new ErrorException($message, 0, $severity);
});

$theme_test_entry = null;
if (strpos($theme_test_scenario, 'article-') === 0 || $theme_test_scenario === 'article') {
    $theme_test_entry = 'single-article.php';
} elseif (strpos($theme_test_scenario, 'category') === 0) {
    $theme_test_entry = 'category-articles.php';
} elseif (strpos($theme_test_scenario, 'catalog') === 0) {
    $theme_test_entry = 'archive-services.php';
} elseif (strpos($theme_test_scenario, 'search') === 0) {
    $theme_test_entry = 'search.php';
} elseif (strpos($theme_test_scenario, 'contacts') === 0) {
    $theme_test_entry = 'page-contacts.php';
} elseif (strpos($theme_test_scenario, 'works') === 0) {
    $theme_test_entry = 'page-works.php';
} elseif (strpos($theme_test_scenario, 'reviews-archive') === 0) {
    $theme_test_entry = 'archive-reviews.php';
    $wp_query->posts = WP_Query::$fixture_posts;
    $theme_test_render_is_page = false;
} elseif (strpos($theme_test_scenario, 'reviews') === 0) {
    $theme_test_entry = 'page-reviews.php';
} elseif ($theme_test_scenario === 'calculator-slug-layout') {
    $theme_test_entry = 'page.php';
} elseif (strpos($theme_test_scenario, 'calculator') === 0) {
    $theme_test_entry = 'page-calculator.php';
} elseif (strpos($theme_test_scenario, 'single-') === 0) {
    $theme_test_entry = 'single.php';
} elseif ($theme_test_scenario === 'archive-layout') {
    $theme_test_entry = 'archive.php';
}
$theme_test_original_fields = $theme_test_fields;
$theme_test_original_options = $theme_test_options;
ob_start();
if (in_array($theme_test_scenario, array('blocks', 'all-blocks'), true)) {
    \Edemchinim\Content\Blocks::render(42);
} elseif ($theme_test_entry) {
    theme_test_include_view(get_template_directory() . '/' . $theme_test_entry);
} else {
    throw new RuntimeException('Unknown render fixture.');
}
$theme_test_rendered = ob_get_clean();
if ($theme_test_writes !== array() || $theme_test_warnings !== array() || $theme_test_fields !== $theme_test_original_fields || $theme_test_options !== $theme_test_original_options) {
    throw new RuntimeException('Page rendering must not write data or raise PHP warnings.');
}
if ($theme_test_context_lead !== null && strpos($theme_test_rendered, esc_html($theme_test_context_lead)) === false) {
    throw new RuntimeException('The form must read its existing field from the correct page or archive context.');
}
if ($theme_test_full_layout) {
    $theme_test_expect_calculator = in_array($theme_test_scenario, array('calculator-layout', 'calculator-renamed-layout'), true);
    $theme_test_expected_dialogs = $theme_test_expect_calculator ? 0 : 1;
    $theme_test_expected_mobile = $theme_test_expect_calculator || $theme_test_scenario === 'archive-layout' ? 0 : 1;
    if (substr_count($theme_test_rendered, 'id="quick-call-dialog"') !== $theme_test_expected_dialogs || substr_count($theme_test_rendered, 'data-mobile-actions') !== $theme_test_expected_mobile) {
        throw new RuntimeException('Shared form or mobile panel rendered more than once.');
    }
    if (substr_count($theme_test_rendered, 'id="contacts"') !== ($theme_test_expect_calculator ? 0 : 1)) {
        throw new RuntimeException('Footer visibility must follow the assigned calculator template.');
    }
    preg_match_all('/\bdata-site-header(?:\s|=|>)/', $theme_test_rendered, $theme_test_header_matches);
    if (count($theme_test_header_matches[0]) !== ($theme_test_expect_calculator ? 0 : 1)) {
        throw new RuntimeException('Header visibility must follow the calculator template.');
    }
    $theme_test_calculator_script = in_array('edemchinim-calculator', array_column(array_column($theme_test_resources, 1), 0), true);
    if ($theme_test_calculator_script !== $theme_test_expect_calculator) {
        throw new RuntimeException('Calculator script must follow the assigned template.');
    }
    $theme_test_expected_url = $theme_test_render_permalinks[88];
    $theme_test_url_assignment = 'window.__CALCULATOR_URL__ = ' . wp_json_encode($theme_test_expected_url, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) . ';';
    if (strpos($theme_test_inline_code['edemchinim-main']['before'] ?? '', $theme_test_url_assignment) === false) {
        throw new RuntimeException('The frontend calculator URL must resolve from the published template page.');
    }
}
if (in_array('--render-html', $argv, true)) {
    if ($theme_test_full_layout) {
        throw new RuntimeException('Full layout diagnostics only return hashes.');
    }
    echo $theme_test_rendered;
    return;
}
echo json_encode(array(
    'html' => hash('sha256', $theme_test_rendered),
    'layout_calls' => $theme_test_layout_calls,
    'warnings' => $theme_test_warnings,
), JSON_UNESCAPED_UNICODE) . "\n";
