<?php
namespace Edemchinim\Rest;

use Edemchinim\Rest\Parameters;
use WP_Query;
use WP_Post;
use WP_REST_Request;

if (! defined('ABSPATH')) {
	exit;
}

/** FAQ collection endpoint and its internal record formatter. */
final class Faq
{
	private static function prepare(WP_Post $post)
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

	public static function get(WP_REST_Request $request)
	{
		$pagination = Parameters::pagination($request, 20);
		$search = Parameters::sanitizeSearch($request->get_param('search'));
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
		$items = array_map([self::class, 'prepare'], $query->posts);

		return Parameters::response($items, $query, $pagination['page'], $pagination['per_page'], 'faq');
	}
}
