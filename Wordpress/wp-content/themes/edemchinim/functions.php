<?php

/**
 * edemchinim functions and definitions
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package edemchinim
 */

if (! defined('_S_VERSION')) {
	// Replace the version number of the theme on each release.
	define('_S_VERSION', '1.0.0');
}

/**
 * Sets up theme defaults and registers support for various WordPress features.
 *
 * Note that this function is hooked into the after_setup_theme hook, which
 * runs before the init hook. The init hook is too late for some features, such
 * as indicating support for post thumbnails.
 */
function edemchinim_setup()
{
	/*
		* Make theme available for translation.
		* Translations can be filed in the /languages/ directory.
		* If you're building a theme based on edemchinim, use a find and replace
		* to change 'edemchinim' to the name of your theme in all the template files.
		*/
	load_theme_textdomain('edemchinim', get_template_directory() . '/languages');

	// Add default posts and comments RSS feed links to head.
	add_theme_support('automatic-feed-links');

	/*
		* Let WordPress manage the document title.
		* By adding theme support, we declare that this theme does not use a
		* hard-coded <title> tag in the document head, and expect WordPress to
		* provide it for us.
		*/
	add_theme_support('title-tag');

	/*
		* Enable support for Post Thumbnails on posts and pages.
		*
		* @link https://developer.wordpress.org/themes/functionality/featured-images-post-thumbnails/
		*/
	add_theme_support('post-thumbnails');

	// This theme uses wp_nav_menu() in one location.
	register_nav_menus(
		array(
			'menu-1' => esc_html__('Primary', 'edemchinim'),
		)
	);

	/*
		* Switch default core markup for search form, comment form, and comments
		* to output valid HTML5.
		*/
	add_theme_support(
		'html5',
		array(
			'search-form',
			'comment-form',
			'comment-list',
			'gallery',
			'caption',
			'style',
			'script',
		)
	);

	// Set up the WordPress core custom background feature.
	add_theme_support(
		'custom-background',
		apply_filters(
			'edemchinim_custom_background_args',
			array(
				'default-color' => 'ffffff',
				'default-image' => '',
			)
		)
	);

	// Add theme support for selective refresh for widgets.
	add_theme_support('customize-selective-refresh-widgets');

	/**
	 * Add support for core custom logo.
	 *
	 * @link https://codex.wordpress.org/Theme_Logo
	 */
	add_theme_support(
		'custom-logo',
		array(
			'height'      => 250,
			'width'       => 250,
			'flex-width'  => true,
			'flex-height' => true,
		)
	);
}
add_action('after_setup_theme', 'edemchinim_setup');

/**
 * Set the content width in pixels, based on the theme's design and stylesheet.
 *
 * Priority 0 to make it available to lower priority callbacks.
 *
 * @global int $content_width
 */
function edemchinim_content_width()
{
	$GLOBALS['content_width'] = apply_filters('edemchinim_content_width', 640);
}
add_action('after_setup_theme', 'edemchinim_content_width', 0);

/**
 * Register widget area.
 *
 * @link https://developer.wordpress.org/themes/functionality/sidebars/#registering-a-sidebar
 */
function edemchinim_widgets_init()
{
	register_sidebar(
		array(
			'name'          => esc_html__('Sidebar', 'edemchinim'),
			'id'            => 'sidebar-1',
			'description'   => esc_html__('Add widgets here.', 'edemchinim'),
			'before_widget' => '<section id="%1$s" class="widget %2$s">',
			'after_widget'  => '</section>',
			'before_title'  => '<h2 class="widget-title">',
			'after_title'   => '</h2>',
		)
	);
}
add_action('widgets_init', 'edemchinim_widgets_init');

if (! function_exists('edemchinim_get_field')) {
	function edemchinim_get_field($field_name, $post_id = false, $default = '')
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
}

if (! function_exists('edemchinim_get_option_field')) {
	function edemchinim_get_option_field($field_name, $default = '')
	{
		return edemchinim_get_field($field_name, 'options', $default);
	}
}

if (! function_exists('edemchinim_phone_href')) {
	function edemchinim_phone_href($phone)
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
}

if (! function_exists('edemchinim_get_image_url')) {
	function edemchinim_get_image_url($image, $size = 'medium_large')
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
}

if (! function_exists('edemchinim_relative_date')) {
	function edemchinim_relative_date($date)
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

if (! function_exists('edemchinim_rest_allowed_repair_types')) {
	function edemchinim_rest_allowed_repair_types()
	{
		return array('seasonal', 'puncture', 'storage', 'conditioner');
	}
}

if (! function_exists('edemchinim_rest_validate_positive_int')) {
	function edemchinim_rest_validate_positive_int($value, $request = null, $param = '')
	{
		return absint($value) >= 1;
	}
}

if (! function_exists('edemchinim_rest_validate_per_page')) {
	function edemchinim_rest_validate_per_page($value, $request = null, $param = '')
	{
		$value = absint($value);
		return $value >= 1 && $value <= 50;
	}
}

if (! function_exists('edemchinim_rest_sanitize_repair_type')) {
	function edemchinim_rest_sanitize_repair_type($value, $request = null, $param = '')
	{
		return is_scalar($value) ? sanitize_key((string) $value) : '';
	}
}

if (! function_exists('edemchinim_rest_validate_repair_type')) {
	function edemchinim_rest_validate_repair_type($value, $request = null, $param = '')
	{
		if ($value === null || $value === '') {
			return true;
		}

		return is_scalar($value) && in_array(sanitize_key((string) $value), edemchinim_rest_allowed_repair_types(), true);
	}
}

if (! function_exists('edemchinim_rest_sanitize_search')) {
	function edemchinim_rest_sanitize_search($value, $request = null, $param = '')
	{
		$value = is_scalar($value) ? sanitize_text_field(wp_unslash((string) $value)) : '';

		if (function_exists('mb_substr')) {
			return mb_substr($value, 0, 100);
		}

		return substr($value, 0, 100);
	}
}

if (! function_exists('edemchinim_rest_get_pagination')) {
	function edemchinim_rest_get_pagination(WP_REST_Request $request, $default_per_page)
	{
		return array(
			'page'     => max(1, absint($request->get_param('page') ?: 1)),
			'per_page' => max(1, min(50, absint($request->get_param('per_page') ?: $default_per_page))),
		);
	}
}

if (! function_exists('edemchinim_rest_paginated_response')) {
	function edemchinim_rest_paginated_response($items, WP_Query $query, $page, $per_page, $collection_key)
	{
		$total = (int) $query->found_posts;
		$total_pages = (int) $query->max_num_pages;
		$response = rest_ensure_response(
			array(
				$collection_key => $items,
				'items'         => $items,
				'page'          => $page,
				'per_page'      => $per_page,
				'total'         => $total,
				'total_pages'   => $total_pages,
				'has_more'      => $page < $total_pages,
			)
		);

		$response->header('X-WP-Total', $total);
		$response->header('X-WP-TotalPages', $total_pages);

		return $response;
	}
}

if (! function_exists('edemchinim_rest_get_repair_type')) {
	function edemchinim_rest_get_repair_type(WP_REST_Request $request)
	{
		$repair_type = sanitize_key((string) $request->get_param('repair_type'));

		return in_array($repair_type, edemchinim_rest_allowed_repair_types(), true) ? $repair_type : '';
	}
}

if (! function_exists('edemchinim_rest_review_photos')) {
	function edemchinim_rest_review_photos($gallery)
	{
		$photos = array();

		if (! is_array($gallery)) {
			return $photos;
		}

		foreach ($gallery as $image) {
			$image_url = edemchinim_get_image_url($image, 'large') ?: edemchinim_get_image_url($image, 'full');

			if ($image_url) {
				$photos[] = esc_url_raw($image_url);
			}
		}

		return array_values(array_unique($photos));
	}
}

if (! function_exists('edemchinim_rest_prepare_review')) {
	function edemchinim_rest_prepare_review(WP_Post $post)
	{
		$post_id = $post->ID;
		$type = sanitize_key(edemchinim_get_field('shina_review_repair_type', $post_id, 'seasonal'));
		$rating = max(0, min(5, (float) edemchinim_get_field('shina_review_rating', $post_id, 5)));
		$date = edemchinim_get_field('shina_review_date', $post_id, get_the_date('Y-m-d', $post_id));
		$gallery = edemchinim_get_field('shina_review_gallery', $post_id, array());
		$content = wp_strip_all_tags(apply_filters('the_content', $post->post_content));

		return array(
			'id'           => $post_id,
			'review_id'    => $post_id,
			'author'       => get_the_title($post_id),
			'author_name'  => get_the_title($post_id),
			'avatar'       => get_the_post_thumbnail_url($post_id, 'thumbnail') ?: '',
			'avatar_url'   => get_the_post_thumbnail_url($post_id, 'thumbnail') ?: '',
			'date'         => get_the_date('Y-m-d', $post_id),
			'date_label'   => edemchinim_relative_date($date),
			'rating'       => $rating,
			'text'         => $content,
			'review'       => $content,
			'photos'       => edemchinim_rest_review_photos($gallery),
			'repair_type'  => $type,
			'type'         => $type,
		);
	}
}

if (! function_exists('edemchinim_rest_get_reviews')) {
	function edemchinim_rest_get_reviews(WP_REST_Request $request)
	{
		$pagination = edemchinim_rest_get_pagination($request, 10);
		$repair_type = edemchinim_rest_get_repair_type($request);
		$meta_query = array(
			'relation' => 'AND',
			array(
				'relation' => 'OR',
				array(
					'key'     => 'shina_review_is_active',
					'compare' => 'NOT EXISTS',
				),
				array(
					'key'     => 'shina_review_is_active',
					'value'   => '1',
					'compare' => '=',
				),
			),
		);

		if ($repair_type) {
			$meta_query[] = array(
				'key'     => 'shina_review_repair_type',
				'value'   => $repair_type,
				'compare' => '=',
			);
		}

		$query = new WP_Query(
			array(
				'post_type'           => 'reviews',
				'post_status'         => 'publish',
				'posts_per_page'      => $pagination['per_page'],
				'paged'               => $pagination['page'],
				'orderby'             => 'date',
				'order'               => 'DESC',
				'ignore_sticky_posts' => true,
				'meta_query'          => $meta_query,
			)
		);

		$items = array_map('edemchinim_rest_prepare_review', $query->posts);

		return edemchinim_rest_paginated_response($items, $query, $pagination['page'], $pagination['per_page'], 'reviews');
	}
}

if (! function_exists('edemchinim_rest_prepare_photo')) {
	function edemchinim_rest_prepare_photo(WP_Post $attachment)
	{
		$attachment_id = $attachment->ID;
		$type = sanitize_key(edemchinim_get_field('shina_photo_repair_type', $attachment_id, 'seasonal'));
		$label = edemchinim_get_field('shina_photo_label', $attachment_id, get_the_title($attachment_id));
		$thumbnail_url = wp_get_attachment_image_url($attachment_id, 'medium_large');
		$full_url = wp_get_attachment_image_url($attachment_id, 'full') ?: $thumbnail_url;

		return array(
			'id'            => $attachment_id,
			'photo_id'      => $attachment_id,
			'repair_type'   => $type,
			'type'          => $type,
			'label'         => wp_strip_all_tags($label),
			'work_label'    => wp_strip_all_tags($label),
			'thumbnail'     => esc_url_raw($thumbnail_url),
			'thumbnail_url' => esc_url_raw($thumbnail_url),
			'full_url'      => esc_url_raw($full_url),
			'url'           => esc_url_raw($full_url),
		);
	}
}

if (! function_exists('edemchinim_rest_get_photos')) {
	function edemchinim_rest_get_photos(WP_REST_Request $request)
	{
		$pagination = edemchinim_rest_get_pagination($request, 10);
		$repair_type = edemchinim_rest_get_repair_type($request);
		$meta_query = array(
			array(
				'key'     => 'shina_photo_is_active',
				'value'   => '1',
				'compare' => '=',
			),
		);

		if ($repair_type) {
			$meta_query[] = array(
				'key'     => 'shina_photo_repair_type',
				'value'   => $repair_type,
				'compare' => '=',
			);
		}

		$query = new WP_Query(
			array(
				'post_type'           => 'attachment',
				'post_status'         => 'inherit',
				'post_mime_type'      => 'image',
				'posts_per_page'      => $pagination['per_page'],
				'paged'               => $pagination['page'],
				'orderby'             => 'date',
				'order'               => 'DESC',
				'ignore_sticky_posts' => true,
				'meta_query'          => $meta_query,
			)
		);

		$items = array_values(array_filter(array_map('edemchinim_rest_prepare_photo', $query->posts), function ($item) {
			return ! empty($item['full_url']);
		}));

		return edemchinim_rest_paginated_response($items, $query, $pagination['page'], $pagination['per_page'], 'photos');
	}
}

if (! function_exists('edemchinim_rest_prepare_faq')) {
	function edemchinim_rest_prepare_faq(WP_Post $post)
	{
		$content = apply_filters('the_content', $post->post_content);
		$text = trim(wp_strip_all_tags($content));
		$paragraphs = preg_split('/\R{2,}/u', $text);
		$paragraphs = array_values(array_filter(array_map('trim', is_array($paragraphs) ? $paragraphs : array($text))));

		return array(
			'id'       => $post->ID,
			'faq_id'   => $post->ID,
			'question' => get_the_title($post),
			'answer'   => $paragraphs,
			'content'  => array(
				'rendered' => wp_kses_post($content),
			),
			'title'    => array(
				'rendered' => get_the_title($post),
			),
		);
	}
}

if (! function_exists('edemchinim_rest_get_faq')) {
	function edemchinim_rest_get_faq(WP_REST_Request $request)
	{
		$pagination = edemchinim_rest_get_pagination($request, 20);
		$search = edemchinim_rest_sanitize_search($request->get_param('search'));
		$query_args = array(
			'post_type'           => 'faq',
			'post_status'         => 'publish',
			'posts_per_page'      => $pagination['per_page'],
			'paged'               => $pagination['page'],
			'orderby'             => array(
				'menu_order' => 'ASC',
				'date'       => 'DESC',
			),
			'ignore_sticky_posts' => true,
		);

		if ($search !== '') {
			$query_args['s'] = $search;
		}

		$query = new WP_Query($query_args);
		$items = array_map('edemchinim_rest_prepare_faq', $query->posts);

		return edemchinim_rest_paginated_response($items, $query, $pagination['page'], $pagination['per_page'], 'faq');
	}
}

if (! function_exists('edemchinim_register_rest_routes')) {
	function edemchinim_register_rest_routes()
	{
		$pagination_args = array(
			'page'     => array(
				'default'           => 1,
				'sanitize_callback' => 'absint',
				'validate_callback' => 'edemchinim_rest_validate_positive_int',
			),
			'per_page' => array(
				'default'           => 10,
				'sanitize_callback' => 'absint',
				'validate_callback' => 'edemchinim_rest_validate_per_page',
			),
		);

		$repair_type_arg = array(
			'repair_type' => array(
				'default'           => '',
				'sanitize_callback' => 'edemchinim_rest_sanitize_repair_type',
				'validate_callback' => 'edemchinim_rest_validate_repair_type',
			),
		);

		register_rest_route(
			'shina/v1',
			'/reviews',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => 'edemchinim_rest_get_reviews',
				'permission_callback' => '__return_true',
				'args'                => array_merge($pagination_args, $repair_type_arg),
			)
		);

		register_rest_route(
			'shina/v1',
			'/photos',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => 'edemchinim_rest_get_photos',
				'permission_callback' => '__return_true',
				'args'                => array_merge($pagination_args, $repair_type_arg),
			)
		);

		register_rest_route(
			'shina/v1',
			'/faq',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => 'edemchinim_rest_get_faq',
				'permission_callback' => '__return_true',
				'args'                => array_merge(
					$pagination_args,
					array(
						'per_page' => array(
							'default'           => 20,
							'sanitize_callback' => 'absint',
							'validate_callback' => 'edemchinim_rest_validate_per_page',
						),
						'search'   => array(
							'default'           => '',
							'sanitize_callback' => 'edemchinim_rest_sanitize_search',
						),
					)
				),
			)
		);
	}
}
add_action('rest_api_init', 'edemchinim_register_rest_routes');

if (! function_exists('edemchinim_normalize_master_points')) {
	function edemchinim_normalize_master_points($value)
	{
		if (is_string($value)) {
			$decoded = json_decode($value, true);

			if (json_last_error() === JSON_ERROR_NONE) {
				$value = $decoded;
			}
		}

		if (! is_array($value)) {
			return array();
		}

		$points = isset($value['marks']) && is_array($value['marks']) ? $value['marks'] : $value;
		$normalized = array();

		foreach ($points as $index => $point) {
			if (! is_array($point)) {
				continue;
			}

			$coords = $point['coords'] ?? $point['coordinates'] ?? array(
				$point['latitude'] ?? $point['lat'] ?? null,
				$point['longitude'] ?? $point['lng'] ?? $point['lon'] ?? null,
			);

			if (! is_array($coords) || count($coords) < 2 || ! is_numeric($coords[0]) || ! is_numeric($coords[1])) {
				continue;
			}

			$point_number = is_numeric($point['id'] ?? null) ? absint($point['id']) : $index + 1;
			$point_id = sanitize_key((string) ($point['id'] ?? ''));

			if (! $point_id || strpos($point_id, 'master-') !== 0) {
				$point_id = 'master-' . max(1, $point_number);
			}

			$normalized[] = array(
				'id'        => $point_id,
				'name'      => sanitize_text_field(wp_strip_all_tags((string) ($point['content'] ?? $point['name'] ?? 'Мастер ' . ($index + 1)))),
				'latitude'  => (float) $coords[0],
				'longitude' => (float) $coords[1],
			);
		}

		return $normalized;
	}
}

if (! function_exists('edemchinim_prepare_calculator_config')) {
	function edemchinim_prepare_calculator_config($config)
	{
		if (! is_array($config)) {
			return array();
		}

		$config['master_points'] = edemchinim_normalize_master_points($config['master_points'] ?? array());

		return $config;
	}
}

if (! function_exists('edemchinim_sanitize_yandex_map_value')) {
	function edemchinim_sanitize_yandex_map_value($value, $post_id = 0, $field = array())
	{
		$raw_value = is_string($value) ? wp_unslash($value) : $value;
		$map = is_string($raw_value) ? json_decode($raw_value, true) : $raw_value;

		if (! is_array($map)) {
			$stored_value = get_option('options_shina_calculator_config_master_points', '');
			$map = is_string($stored_value) ? json_decode($stored_value, true) : $stored_value;
		}

		if (! is_array($map)) {
			$default_map = ! empty($field['default_value']) ? json_decode((string) $field['default_value'], true) : null;
			$map = is_array($default_map) ? $default_map : edemchinim_build_legacy_master_points_map(100);
		}

		if (! is_array($map)) {
			$map = array(
				'center_lat' => 55.755864,
				'center_lng' => 37.617698,
				'zoom'       => 9,
				'type'       => 'map',
				'marks'      => array(),
			);
		}

		$marks = array();

		foreach (($map['marks'] ?? array()) as $index => $mark) {
			if (! is_array($mark) || ! isset($mark['coords'][0], $mark['coords'][1])) {
				continue;
			}

			if (! is_numeric($mark['coords'][0]) || ! is_numeric($mark['coords'][1])) {
				continue;
			}

			$marks[] = array(
				'id'          => absint($mark['id'] ?? $index + 1),
				'content'     => sanitize_text_field(wp_strip_all_tags((string) ($mark['content'] ?? ''))),
				'type'        => 'Point',
				'coords'      => array((float) $mark['coords'][0], (float) $mark['coords'][1]),
				'circle_size' => 0,
			);
		}

		$sanitized_map = array(
			'center_lat' => is_numeric($map['center_lat'] ?? null) ? (float) $map['center_lat'] : 55.755864,
			'center_lng' => is_numeric($map['center_lng'] ?? null) ? (float) $map['center_lng'] : 37.617698,
			'zoom'       => max(0, min(18, absint($map['zoom'] ?? 9))),
			'type'       => in_array(($map['type'] ?? ''), array('map', 'satellite', 'hybrid'), true) ? $map['type'] : 'map',
			'marks'      => $marks,
		);

		return wp_json_encode($sanitized_map);
	}
}
add_filter('acf/update_value/key=field_shina_calc_master_points', 'edemchinim_sanitize_yandex_map_value', 10, 3);

if (! function_exists('edemchinim_build_legacy_master_points_map')) {
	function edemchinim_build_legacy_master_points_map($rows_count)
	{
		$option_name = 'options_shina_calculator_config_master_points';
		$marks = array();

		for ($index = 0; $index < absint($rows_count); $index += 1) {
			$prefix = $option_name . '_' . $index . '_';
			$latitude = get_option($prefix . 'latitude', '');
			$longitude = get_option($prefix . 'longitude', '');

			if (! is_numeric($latitude) || ! is_numeric($longitude)) {
				continue;
			}

			$marks[] = array(
				'id'          => $index + 1,
				'content'     => sanitize_text_field((string) get_option($prefix . 'name', 'Мастер ' . ($index + 1))),
				'type'        => 'Point',
				'coords'      => array((float) $latitude, (float) $longitude),
				'circle_size' => 0,
			);
		}

		return array(
			'center_lat' => 55.755864,
			'center_lng' => 37.617698,
			'zoom'       => 9,
			'type'       => 'map',
			'marks'      => $marks,
		);
	}
}

if (! function_exists('edemchinim_load_master_points_yandex_map')) {
	function edemchinim_load_master_points_yandex_map($value, $post_id, $field)
	{
		$raw_value = is_string($value) ? wp_unslash($value) : $value;
		$decoded = is_string($raw_value) ? json_decode($raw_value, true) : $raw_value;

		if (is_array($decoded) && isset($decoded['marks'])) {
			return wp_json_encode($decoded);
		}

		$map = is_numeric($value) ? edemchinim_build_legacy_master_points_map($value) : array();

		if (empty($map['marks']) && ! empty($field['default_value'])) {
			$default_map = json_decode((string) $field['default_value'], true);

			if (is_array($default_map) && isset($default_map['marks'])) {
				$map = $default_map;
			}
		}

		if (empty($map['marks'])) {
			$legacy_map = edemchinim_build_legacy_master_points_map(100);

			if (! empty($legacy_map['marks'])) {
				$map = $legacy_map;
			}
		}

		$map_json = edemchinim_sanitize_yandex_map_value($map, $post_id, $field);
		update_option('options_shina_calculator_config_master_points', $map_json, false);
		update_option('edemchinim_master_points_map_migration', '1', false);

		return $map_json;
	}
}
add_filter('acf/load_value/key=field_shina_calc_master_points', 'edemchinim_load_master_points_yandex_map', 5, 3);

if (! function_exists('edemchinim_prepare_master_points_yandex_map_field')) {
	function edemchinim_prepare_master_points_yandex_map_field($field)
	{
		$value = $field['value'] ?? '';
		$raw_value = is_string($value) ? wp_unslash($value) : $value;
		$map = is_string($raw_value) ? json_decode($raw_value, true) : $raw_value;

		if (! is_array($map) || ! isset($map['marks'])) {
			$default_map = ! empty($field['default_value']) ? json_decode((string) $field['default_value'], true) : null;
			$map = is_array($default_map) && isset($default_map['marks'])
				? $default_map
				: edemchinim_build_legacy_master_points_map(100);
		}

		$field['value'] = edemchinim_sanitize_yandex_map_value($map, 'options', $field);

		return $field;
	}
}
add_filter('acf/prepare_field/key=field_shina_calc_master_points', 'edemchinim_prepare_master_points_yandex_map_field', 5);

if (! function_exists('edemchinim_patch_acf_yandex_map_admin_script')) {
	function edemchinim_patch_acf_yandex_map_admin_script()
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
add_action('acf/input/admin_enqueue_scripts', 'edemchinim_patch_acf_yandex_map_admin_script', 100);

if (! function_exists('edemchinim_migrate_master_points_to_yandex_map')) {
	function edemchinim_migrate_master_points_to_yandex_map()
	{
		if (get_option('edemchinim_master_points_map_migration') === '1' || ! function_exists('get_field_object')) {
			return;
		}

		$field = get_field_object('field_shina_calc_master_points', 'options', false, false);

		if (($field['type'] ?? '') !== 'yandex-map') {
			return;
		}

		$option_name = 'options_shina_calculator_config_master_points';
		$stored_value = get_option($option_name, '');
		$stored_map = is_string($stored_value) ? json_decode($stored_value, true) : $stored_value;

		if (is_array($stored_map) && isset($stored_map['marks'])) {
			update_option('edemchinim_master_points_map_migration', '1', false);
			return;
		}

		$rows_count = absint($stored_value);

		if (! $rows_count) {
			return;
		}

		$map = edemchinim_build_legacy_master_points_map($rows_count);

		if (empty($map['marks'])) {
			return;
		}

		update_option($option_name, edemchinim_sanitize_yandex_map_value($map), false);
		update_option('edemchinim_master_points_map_migration', '1', false);
	}
}
add_action('acf/init', 'edemchinim_migrate_master_points_to_yandex_map', 30);

/**
 * Enqueue scripts and styles.
 */
function edemchinim_scripts()
{
	$calculator_config = function_exists('get_field') ? get_field('shina_calculator_config', 'options') : array();
	$calculator_config = edemchinim_prepare_calculator_config($calculator_config);

	wp_enqueue_style('edemchinim-style', get_stylesheet_uri(), array(), _S_VERSION);
	wp_enqueue_script('edemchinim-main', get_template_directory_uri() . '/js/main.min.js', array(), _S_VERSION, true);
	wp_add_inline_script(
		'edemchinim-main',
		'window.shinaCalculatorConfig = ' . wp_json_encode($calculator_config) . ';',
		'before'
	);
}
add_action('wp_enqueue_scripts', 'edemchinim_scripts');

add_filter('wpcf7_autop_or_not', '__return_false');
