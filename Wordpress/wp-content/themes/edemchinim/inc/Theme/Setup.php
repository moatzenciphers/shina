<?php
namespace Edemchinim\Theme;

if (! defined('ABSPATH')) {
	exit;
}

/** WordPress theme support, document cleanup and widget registration. */
final class Setup
{
	public static function support()
	{
		/*
			* Make theme available for translation.
			* Translations can be filed in the /languages/ directory.
			* If you're building a theme based on edemchinim, use a find and replace
			* to change 'edemchinim' to the name of your theme in all the template files.
			*/
		load_theme_textdomain('edemchinim', get_template_directory() . '/languages');

		// Add default posts and comments RSS feed links to head.
		add_theme_support('automatic-feed-links');

		/*
			* Let WordPress manage the document title.
			* By adding theme support, we declare that this theme does not use a
			* hard-coded <title> tag in the document head, and expect WordPress to
			* provide it for us.
			*/
		add_theme_support('title-tag');

		/*
			* Enable support for Post Thumbnails on posts and pages.
			*
			* @link https://developer.wordpress.org/themes/functionality/featured-images-post-thumbnails/
			*/
		add_theme_support('post-thumbnails');

		// This theme uses wp_nav_menu() in one location.

		register_nav_menus(
			array(
				'menu-1' => esc_html__('Primary', 'edemchinim'),
				'landing-primary' => esc_html__('Шапка лендинга', 'edemchinim'),
				'site-footer' => esc_html__('Футер сайта', 'edemchinim'),
			)
		);

		/*
			* Switch default core markup for search form, comment form, and comments
			* to output valid HTML5.
			*/
		add_theme_support(
			'html5',
			array(
				'search-form',
				'comment-form',
				'comment-list',
				'gallery',
				'caption',
				'style',
				'script',
			)
		);

		// Set up the WordPress core custom background feature.
		add_theme_support(
			'custom-background',
			apply_filters(
				'edemchinim_custom_background_args',
				array(
					'default-color' => 'ffffff',
					'default-image' => '',
				)
			)
		);

		// Add theme support for selective refresh for widgets.
		add_theme_support('customize-selective-refresh-widgets');

		/**
		 * Add support for core custom logo.
		 *
		 * @link https://codex.wordpress.org/Theme_Logo
		 */
		add_theme_support(
			'custom-logo',
			array(
				'height'      => 250,
				'width'       => 250,
				'flex-width'  => true,
				'flex-height' => true,
			)
		);
	}

	public static function cleanup()
	{
		remove_action('wp_head', 'wp_generator');
		remove_action('wp_head', 'feed_links_extra', 3);
		remove_action('wp_head', 'feed_links', 2);
		remove_action('wp_head', 'rsd_link');
		remove_action('wp_head', 'wlwmanifest_link');
		remove_action('wp_head', 'wp_shortlink_wp_head');
		remove_action('wp_head', 'adjacent_posts_rel_link_wp_head');
		remove_action('wp_head', 'start_post_rel_link', 10, 0);
		remove_action('wp_head', 'adjacent_post_rel_link_wp_head', 10, 0);
		remove_action('wp_head', 'print_emoji_detection_script', 7);
		remove_action('wp_print_styles', 'print_emoji_styles');

		if (!is_admin()) {
			// remove_action('wp_head', 'rest_output_link_wp_head');
			remove_action('wp_head', 'wp_oembed_add_discovery_links', 10);
			remove_action('wp_head', 'wp_oembed_add_host_js');
		}
	}

	public static function contentWidth()
	{
		$GLOBALS['content_width'] = apply_filters('edemchinim_content_width', 640);
	}

	public static function widgets()
	{
		register_sidebar(
			array(
				'name'          => esc_html__('Sidebar', 'edemchinim'),
				'id'            => 'sidebar-1',
				'description'   => esc_html__('Add widgets here.', 'edemchinim'),
				'before_widget' => '<section id="%1$s" class="widget %2$s">',
				'after_widget'  => '</section>',
				'before_title'  => '<h2 class="widget-title">',
				'after_title'   => '</h2>',
			)
		);
	}
}
