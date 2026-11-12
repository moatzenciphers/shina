<?php
namespace Edemchinim\Rest;

use WP_Query;
use WP_REST_Request;

if (! defined('ABSPATH')) {
	exit;
}

/** Validate REST arguments and build the stable pagination response. */
final class Parameters
{
	public static function repairTypes()
	{
		return array('seasonal', 'puncture', 'storage', 'conditioner');
	}

	public static function positiveInt($value, $request = null, $param = '')
	{
		return absint($value) >= 1;
	}

	public static function perPage($value, $request = null, $param = '')
	{
		$value = absint($value);
		return $value >= 1 && $value <= 50;
	}

	public static function sanitizeRepairType($value, $request = null, $param = '')
	{
		return is_scalar($value) ? sanitize_key((string) $value) : '';
	}

	public static function validateRepairType($value, $request = null, $param = '')
	{
		if ($value === null || $value === '') {
			return true;
		}

		return is_scalar($value) && in_array(sanitize_key((string) $value), self::repairTypes(), true);
	}

	public static function sanitizeSearch($value, $request = null, $param = '')
	{
		$value = is_scalar($value) ? sanitize_text_field(wp_unslash((string) $value)) : '';

		if (function_exists('mb_substr')) {
			return mb_substr($value, 0, 100);
		}

		return substr($value, 0, 100);
	}

	public static function pagination(WP_REST_Request $request, $default_per_page)
	{
		return array(
			'page'     => max(1, absint($request->get_param('page') ?: 1)),
			'per_page' => max(1, min(50, absint($request->get_param('per_page') ?: $default_per_page))),
		);
	}

	public static function response($items, WP_Query $query, $page, $per_page, $collection_key)
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

	public static function repairType(WP_REST_Request $request)
	{
		$repair_type = sanitize_key((string) $request->get_param('repair_type'));

		return in_array($repair_type, self::repairTypes(), true) ? $repair_type : '';
	}
}
