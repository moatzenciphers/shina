<?php
namespace Edemchinim\Content;

use Edemchinim\Support\Fields;
use WP_Post;

if (! defined('ABSPATH')) {
	exit;
}

/** Related price queries, tariff formatting and price table rendering. */
final class Prices
{
	public static function posts($service_id)
	{
		$related = (array) Fields::get('services_prices', $service_id, array());
		$ids = array_values(array_filter(array_map(static function ($price) {
			return $price instanceof WP_Post ? (int) $price->ID : (int) $price;
		}, $related)));
		if (! $ids) return array();
		return get_posts(array(
			'post_type' => 'prices', 'post_status' => 'publish', 'posts_per_page' => count($ids),
			'post__in' => $ids, 'orderby' => 'post__in', 'no_found_rows' => true,
		));
	}

	/** Published tariffs grouped by their service, preserving the ACF relation order. */
	public static function groups()
	{
		$groups = array();
		$services = get_posts(array(
			'post_type' => 'services', 'post_status' => 'publish', 'posts_per_page' => -1,
			'orderby' => array('menu_order' => 'ASC', 'title' => 'ASC'), 'no_found_rows' => true,
		));
		foreach ($services as $service) {
			$main = array();
			$additional = array();
			foreach (self::posts($service->ID) as $price) {
				if (Fields::get('price_additional', $price->ID, false)) $additional[] = $price;
				else $main[] = $price;
			}
			if ($main || $additional) $groups[] = array('service' => $service, 'main' => $main, 'additional' => $additional);
		}
		return $groups;
	}

	public static function format($value, $format, $additional = false)
	{
		if (! is_numeric($value)) return '—';
		$value = (float) $value;
		$number = number_format_i18n($value, floor($value) === $value ? 0 : 2);
		if ($format === 'percent') return ($additional ? '+' : '') . $number . '%';
		return ($format === 'rub_from' ? 'от ' : '') . $number . ' ₽' . ($format === 'rub_per_item' ? '/шт.' : '');
	}

	public static function renderTable($posts, $caption, $additional = false)
	{
		if (! $posts) return;
		$label_heading = 'Наименование';
		$value_heading = 'Стоимость';
		$columns = array();
		$rows = array();
		foreach ($posts as $post) {
			$format = Fields::get('price_display_type', $post->ID, 'rub');
			$values = array();
			if (Fields::get('price_type', $post->ID, 'value') === 'table') {
				foreach ((array) Fields::get('price_rows', $post->ID, array()) as $row) {
					$name = (string) ($row['name'] ?? '');
					if ($name === '') continue;
					$values[$name] = self::format($row['value'] ?? null, $format, $additional);
				}
			} else {
				$values[$value_heading] = self::format(Fields::get('price_value', $post->ID, null), $format, $additional);
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

	public static function catalogPrice($service_id)
	{
		foreach (self::posts($service_id) as $price) {
			if (Fields::get('price_additional', $price->ID, false)) continue;
			$format = Fields::get('price_display_type', $price->ID, 'rub');
			if ($format === 'percent') continue;
			if (Fields::get('price_type', $price->ID, 'value') === 'table') {
				$values = array();
				foreach ((array) Fields::get('price_rows', $price->ID, array()) as $row) {
					if (isset($row['value']) && is_numeric($row['value'])) $values[] = (float) $row['value'];
				}
				if ($values) return self::format(min($values), $format === 'rub_per_item' ? $format : 'rub_from');
			} else {
				$value = Fields::get('price_value', $price->ID, null);
				if (is_numeric($value)) return self::format($value, $format);
			}
		}
		$value = Fields::get('service_price_from', $service_id, null);
		return is_numeric($value) ? self::format($value, 'rub_from') : '';
	}
}
