<?php
/** Helpers shared by the flexible front page parts. */

if (! defined('ABSPATH')) {
	exit;
}

function edemchinim_landing_sub($name, $default = '')
{
	$value = function_exists('get_sub_field') ? get_sub_field($name) : null;
	return $value === null || $value === false || $value === '' ? $default : $value;
}

function edemchinim_landing_heading($prefix, $id, $fallback_eyebrow = '', $fallback_title = '')
{
	$eyebrow = edemchinim_landing_sub($prefix . '_eyebrow', $fallback_eyebrow);
	$title = edemchinim_landing_sub($prefix . '_title', $fallback_title);
	if ($eyebrow) {
		printf('<p class="%1$s__eyebrow landing-eyebrow">%2$s</p>', esc_attr($id), esc_html($eyebrow));
	}
	printf('<h2 class="%1$s__title landing-title landing-title--section" id="%1$s-title">%2$s</h2>', esc_attr($id), nl2br(esc_html($title)));
}

function edemchinim_landing_arrow($direction = 'right')
{
	$path = $direction === 'left' ? 'M27 16H5m8-8-8 8 8 8' : 'M5 16h22m-8-8 8 8-8 8';
	printf('<svg viewBox="0 0 32 32" fill="none" aria-hidden="true"><path d="%s" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>', esc_attr($path));
}

function edemchinim_landing_posts($selected, $post_type, $limit)
{
	$ids = array_values(array_filter(array_map('intval', (array) $selected)));
	$args = array(
		'post_type' => $post_type,
		'post_status' => 'publish',
		'posts_per_page' => $ids ? count($ids) : $limit,
		'no_found_rows' => true,
	);
	if ($ids) {
		$args['post__in'] = $ids;
		$args['orderby'] = 'post__in';
	}
	return get_posts($args);
}

function edemchinim_landing_image_id($image)
{
	return is_array($image) ? (int) ($image['ID'] ?? $image['id'] ?? 0) : (int) $image;
}

function edemchinim_landing_cf7($shortcode)
{
	$shortcode = trim((string) $shortcode);
	if (! preg_match('/^\[contact-form-7\s+[^\]]+\]$/', $shortcode) || ! shortcode_exists('contact-form-7')) {
		return;
	}
	echo do_shortcode($shortcode); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}
