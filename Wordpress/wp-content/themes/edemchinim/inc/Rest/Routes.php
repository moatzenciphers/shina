<?php
namespace Edemchinim\Rest;

use Edemchinim\Rest\Faq as FaqApi;
use Edemchinim\Rest\Parameters;
use Edemchinim\Rest\Photos as PhotosApi;
use Edemchinim\Rest\Reviews as ReviewsApi;
use WP_REST_Server;

if (! defined('ABSPATH')) {
	exit;
}

/** Register the existing public REST namespace and argument contracts. */
final class Routes
{
	public static function register()
	{
		$pagination_args = array(
			'page'     => array(
				'default'           => 1,
				'sanitize_callback' => 'absint',
				'validate_callback' => [Parameters::class, 'positiveInt'],
			),
			'per_page' => array(
				'default'           => 10,
				'sanitize_callback' => 'absint',
				'validate_callback' => [Parameters::class, 'perPage'],
			),
		);

		$repair_type_arg = array(
			'repair_type' => array(
				'default'           => '',
				'sanitize_callback' => [Parameters::class, 'sanitizeRepairType'],
				'validate_callback' => [Parameters::class, 'validateRepairType'],
			),
		);

		register_rest_route(
			'shina/v1',
			'/reviews',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => [ReviewsApi::class, 'get'],
				'permission_callback' => '__return_true',
				'args'                => array_merge($pagination_args, $repair_type_arg),
			)
		);

		register_rest_route(
			'shina/v1',
			'/photos',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => [PhotosApi::class, 'get'],
				'permission_callback' => '__return_true',
				'args'                => array_merge($pagination_args, $repair_type_arg),
			)
		);

		register_rest_route(
			'shina/v1',
			'/faq',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => [FaqApi::class, 'get'],
				'permission_callback' => '__return_true',
				'args'                => array_merge(
					$pagination_args,
					array(
						'per_page' => array(
							'default'           => 20,
							'sanitize_callback' => 'absint',
							'validate_callback' => [Parameters::class, 'perPage'],
						),
						'search'   => array(
							'default'           => '',
							'sanitize_callback' => [Parameters::class, 'sanitizeSearch'],
						),
					)
				),
			)
		);
	}
}
