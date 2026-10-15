<?php
/** Helpers shared by flexible components on pages and services. */

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

function edemchinim_landing_layouts()
{
	return array('hero', 'features_grid', 'steps_grid', 'columns_list', 'price_table', 'cards_grid', 'media_rows', 'slider_grid', 'map_panel', 'gallery_grid', 'accordion_grid', 'split_form');
}

function edemchinim_landing_price_posts($service_id)
{
	$related = (array) edemchinim_get_field('services_prices', $service_id, array());
	$ids = array_values(array_filter(array_map(static function ($price) {
		return $price instanceof WP_Post ? (int) $price->ID : (int) $price;
	}, $related)));
	if (! $ids) return array();
	return get_posts(array(
		'post_type' => 'prices', 'post_status' => 'publish', 'posts_per_page' => count($ids),
		'post__in' => $ids, 'orderby' => 'post__in', 'no_found_rows' => true,
	));
}

function edemchinim_landing_price_text($value, $format, $additional = false)
{
	if (! is_numeric($value)) return '—';
	$value = (float) $value;
	$number = number_format_i18n($value, floor($value) === $value ? 0 : 2);
	if ($format === 'percent') return ($additional ? '+' : '') . $number . '%';
	return ($format === 'rub_from' ? 'от ' : '') . $number . ' ₽' . ($format === 'rub_per_item' ? '/шт.' : '');
}

/** Keep all repeater columns, including tables with a single populated value. */
function edemchinim_landing_price_table($posts, $caption, $additional = false)
{
	if (! $posts) return;
	$label_heading = 'Наименование';
	$value_heading = 'Стоимость';
	$columns = array();
	$rows = array();
	foreach ($posts as $post) {
		$format = edemchinim_get_field('price_display_type', $post->ID, 'rub');
		$values = array();
		if (edemchinim_get_field('price_type', $post->ID, 'value') === 'table') {
			foreach ((array) edemchinim_get_field('price_rows', $post->ID, array()) as $row) {
				$name = (string) ($row['name'] ?? '');
				if ($name === '') continue;
				$values[$name] = edemchinim_landing_price_text($row['value'] ?? null, $format, $additional);
			}
		} else {
			$values[$value_heading] = edemchinim_landing_price_text(edemchinim_get_field('price_value', $post->ID, null), $format, $additional);
		}
		foreach (array_keys($values) as $column) {
			if (! in_array($column, $columns, true)) $columns[] = $column;
		}
		$rows[] = array('title' => get_the_title($post), 'values' => $values);
	}
	if (! $columns) return;
	$compact = count($columns) === 1 ? ' price-table__table--compact' : '';
	if ($additional) {
	    echo '<table class="price-table__table price-table__table--details' . esc_attr($compact) . '">';
	} else {
	    echo '<table class="price-table__table price-table__table--main' . esc_attr($compact) . '">';
	}
	echo '<caption class="visually-hidden">' . esc_html($caption) . '</caption><colgroup><col class="price-table__label-column">';
	foreach ($columns as $column) echo '<col>';
	echo '</colgroup><thead><tr><th scope="col">' . esc_html($label_heading) . '</th>';
	foreach ($columns as $column) echo '<th scope="col">' . esc_html($column) . '</th>';
	echo '</tr></thead><tbody>';
	foreach ($rows as $row) {
		echo '<tr><th scope="row">' . esc_html($row['title']) . '</th>';
		foreach ($columns as $column) echo '<td class="price-table__price">' . esc_html($row['values'][$column] ?? '—') . '</td>';
		echo '</tr>';
	}
	echo '</tbody></table>';
}

function edemchinim_render_landing_blocks($post_id)
{
	if (! function_exists('have_rows')) return;
	while (have_rows('blocks', $post_id)) {
		the_row();
		$layout = get_row_layout();
		if (in_array($layout, edemchinim_landing_layouts(), true)) {
			get_template_part('template-parts/landing-' . str_replace('_', '-', $layout));
		}
	}
}

