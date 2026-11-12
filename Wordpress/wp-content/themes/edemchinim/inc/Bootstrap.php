<?php
namespace Edemchinim;

use Edemchinim\Calculator\Admin as MapAdmin;
use Edemchinim\Calculator\Maps;
use Edemchinim\Calculator\Migration;
use Edemchinim\Content\Articles;
use Edemchinim\Content\Pages;
use Edemchinim\Content\Reviews;
use Edemchinim\Content\Services;
use Edemchinim\Rest\Routes;
use Edemchinim\Theme\Assets;
use Edemchinim\Theme\Setup;

if (! defined('ABSPATH')) {
	exit;
}

/** Register feature callbacks once, in their original order. */
final class Bootstrap
{
	private static $booted = false;

	public static function boot()
	{
		if (self::$booted) {
			return;
		}

		add_action('after_setup_theme', [Setup::class, 'support']);
		add_action('acf/init', [Services::class, 'registerFields']);
		require_once __DIR__ . '/comments.php';
		require_once __DIR__ . '/walker.php';
		add_filter('single_template', [Articles::class, 'template']);
		add_action('pre_get_posts', [Articles::class, 'query']);
		add_action('pre_get_posts', [Reviews::class, 'archiveQuery']);
		add_action('acf/init', [Pages::class, 'registerSettings']);
		add_filter('wp_img_tag_add_auto_sizes', '__return_false');
		remove_action('wp_footer', 'wp_enqueue_global_styles', 1);
		remove_action('wp_body_open', 'wp_global_styles_render_svg_filters');
		add_action('init', [Setup::class, 'cleanup']);
		add_filter('wpcf7_autop_or_not', '__return_false');
		add_action('after_setup_theme', [Setup::class, 'contentWidth'], 0);
		add_action('widgets_init', [Setup::class, 'widgets']);
		add_action('rest_api_init', [Routes::class, 'register']);
		add_filter('acf/update_value/key=field_shina_calc_master_points', [Maps::class, 'sanitize'], 10, 3);
		add_filter('acf/load_value/key=field_shina_calc_master_points', [Maps::class, 'load'], 5, 3);
		add_action('wp_enqueue_scripts', [Assets::class, 'enqueueLinks']);
		add_filter('acf/prepare_field/key=field_shina_calc_master_points', [MapAdmin::class, 'prepareField'], 5);
		add_action('acf/input/admin_enqueue_scripts', [MapAdmin::class, 'enqueue'], 100);
		add_action('acf/init', [Migration::class, 'run'], 30);
		add_action('wp_enqueue_scripts', [Assets::class, 'enqueue']);

		// Environment-specific credentials stay in the existing integration file.
		require_once __DIR__ . '/telegram-forms.php';
		add_action('acf/init', [Services::class, 'registerArchiveSettings']);
		add_action('init', [\Edemchinim\Testing\Telegram::class, 'request'], 0);
		add_filter('http_request_args', [\Edemchinim\Testing\Telegram::class, 'http'], 10, 2);
		add_filter('pre_wp_mail', [\Edemchinim\Testing\Telegram::class, 'mail'], 10, 2);
		add_action('wp_footer', [\Edemchinim\Testing\Telegram::class, 'notice'], 99);
		self::$booted = true;
	}
}
