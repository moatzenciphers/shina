<?php
namespace Edemchinim\Rest;

use Edemchinim\Content\Services;
use Edemchinim\Content\Reviews as ReviewContent;
use Edemchinim\Rest\Parameters;
use Edemchinim\Support\Fields;
use Edemchinim\Support\Formatting;
use WP_Query;
use WP_Post;
use WP_REST_Request;

if (! defined('ABSPATH')) {
	exit;
}

/** Review collection endpoint and its internal record formatter. */
final class Reviews
{
	public static function photos($gallery)
	{
		$photos = array();

		if (! is_array($gallery)) {
			return $photos;
		}

		foreach ($gallery as $image) {
			$image_url = Formatting::imageUrl($image, 'large') ?: Formatting::imageUrl($image, 'full');

			if ($image_url) {
				$photos[] = esc_url_raw($image_url);
			}
		}

		return array_values(array_unique($photos));
	}

	private static function prepare(WP_Post $post)
	{
		$post_id = $post->ID;
		$service_id = ReviewContent::serviceId($post_id);
		$type = sanitize_key(Fields::get('shina_review_repair_type', $post_id, 'seasonal'));
		$rating = max(0, min(5, (float) Fields::get('shina_review_rating', $post_id, 5)));
		$date = Fields::get('shina_review_date', $post_id, get_the_date('Y-m-d', $post_id));
		$gallery = Fields::get('shina_review_gallery', $post_id, array());
		$content = wp_strip_all_tags(apply_filters('the_content', $post->post_content));

		return array(
			'id'           => $post_id,
			'review_id'    => $post_id,
			'author'       => get_the_title($post_id),
			'author_name'  => get_the_title($post_id),
			'avatar'       => get_the_post_thumbnail_url($post_id, 'thumbnail') ?: '',
			'avatar_url'   => get_the_post_thumbnail_url($post_id, 'thumbnail') ?: '',
			'date'         => get_the_date('Y-m-d', $post_id),
			'date_label'   => Formatting::relativeDate($date),
			'rating'       => $rating,
			'text'         => $content,
			'review'       => $content,
			'photos'       => self::photos($gallery),
			'service_id'   => $service_id,
			'service_name' => $service_id ? get_the_title($service_id) : '',
			'service_icon' => $service_id ? Services::icon($service_id) : '',
			'repair_type'  => $type,
			'type'         => $type,
		);
	}

	public static function get(WP_REST_Request $request)
	{
		$pagination = Parameters::pagination($request, 10);
		$repair_type = Parameters::repairType($request);
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

		$items = array_map([self::class, 'prepare'], $query->posts);

		return Parameters::response($items, $query, $pagination['page'], $pagination['per_page'], 'reviews');
	}
}
