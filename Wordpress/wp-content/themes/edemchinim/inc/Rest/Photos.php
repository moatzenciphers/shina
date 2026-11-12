<?php
namespace Edemchinim\Rest;

use Edemchinim\Rest\Parameters;
use Edemchinim\Support\Fields;
use WP_Query;
use WP_Post;
use WP_REST_Request;

if (! defined('ABSPATH')) {
	exit;
}

/** Work photo collection endpoint and its internal record formatter. */
final class Photos
{
	private static function prepare(WP_Post $attachment)
	{
		$attachment_id = $attachment->ID;
		$type = sanitize_key(Fields::get('shina_photo_repair_type', $attachment_id, 'seasonal'));
		$label = Fields::get('shina_photo_label', $attachment_id, get_the_title($attachment_id));
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

	public static function get(WP_REST_Request $request)
	{
		$pagination = Parameters::pagination($request, 10);
		$repair_type = Parameters::repairType($request);
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

		$items = array_values(array_filter(array_map([self::class, 'prepare'], $query->posts), function ($item) {
			return ! empty($item['full_url']);
		}));

		return Parameters::response($items, $query, $pagination['page'], $pagination['per_page'], 'photos');
	}
}
