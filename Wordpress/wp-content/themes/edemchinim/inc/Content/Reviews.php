<?php
namespace Edemchinim\Content;

use Edemchinim\Rest\Reviews as ReviewsApi;
use Edemchinim\Support\Fields;
use DateTimeImmutable;
use WP_Post;

if (! defined('ABSPATH')) {
	exit;
}

/** Review relations, summary, visibility and support-page presentation. */
final class Reviews
{
	public static function serviceId($review_id)
	{
		$related = Fields::get('review_service', $review_id, 0);
		$service_id = $related instanceof WP_Post ? absint($related->ID) : (is_scalar($related) ? absint($related) : 0);
		if ($service_id && get_post_type($service_id) === 'services') return $service_id;
		$legacy = Fields::get('shina_review_repair_type', $review_id, '');
		$names = array('seasonal' => 'Сезонная замена шин', 'puncture' => 'Ремонт проколов', 'storage' => 'Сезонное хранение шин', 'conditioner' => 'Заправка кондиционера');
		if (! isset($names[$legacy])) return 0;
		static $services = null;
		if ($services === null) $services = get_posts(array('post_type' => 'services', 'post_status' => 'publish', 'posts_per_page' => -1));
		foreach ($services as $service) {
			if ($service->post_title === $names[$legacy] || ($legacy === 'puncture' && $service->post_title === 'Ремонт прокола') || ($legacy === 'storage' && $service->post_title === 'Хранение шин')) return (int) $service->ID;
		}
		return 0;
	}

	public static function summary()
	{
		static $summary = null;
		if ($summary !== null) return $summary;
		$reviews = get_posts(array('post_type' => 'reviews', 'post_status' => 'publish', 'posts_per_page' => -1));
		$total = 0;
		$count = 0;
		foreach ($reviews as $review) {
			if (! Fields::get('shina_review_is_active', $review->ID, 1)) continue;
			$total += max(0, min(5, (float) Fields::get('shina_review_rating', $review->ID, 5)));
			$count++;
		}
		return $summary = array('count' => $count, 'rating' => $count ? $total / $count : 0);
	}

	public static function publicMeta()
	{
		return array('relation' => 'OR', array('key' => 'shina_review_is_active', 'compare' => 'NOT EXISTS'), array('key' => 'shina_review_is_active', 'value' => '1'));
	}

	public static function archiveQuery($query)
	{
		if (is_admin() || ! $query->is_main_query() || ! $query->is_post_type_archive('reviews')) return;
		$query->set('posts_per_page', 9);
		$query->set('meta_query', self::publicMeta());
	}

	public static function prepare($post)
	{
		$id = $post->ID;
		$date = (string) Fields::get('shina_review_date', $id, get_the_date('Y-m-d', $id));
		$parsed = DateTimeImmutable::createFromFormat('!Y-m-d', $date) ?: DateTimeImmutable::createFromFormat('!Ymd', $date);
		$service = self::serviceId($id);
		return array(
			'id' => $id, 'author' => get_the_title($id),
			'heading' => Fields::get('review_heading', $id, ''),
			'text' => wp_strip_all_tags($post->post_content),
			'rating' => max(0, min(5, (float) Fields::get('shina_review_rating', $id, 5))),
			'date' => $parsed ? $parsed->format('Y-m-d') : '',
			'date_label' => $parsed ? wp_date(get_option('date_format'), $parsed->getTimestamp()) : '',
			'service_id' => $service, 'service' => $service ? get_the_title($service) : '',
			'photos' => ReviewsApi::photos(Fields::get('shina_review_gallery', $id, array())),
		);
	}
}
