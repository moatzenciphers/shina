<?php
namespace Edemchinim\Support;

if (! defined('ABSPATH')) {
	exit;
}

/** Read-only WordPress post metadata and thumbnail output for search results. */
final class PostTags
{
	public static function postedOn()
	{
		printf(
			'<span class="posted-on"><a href="%1$s" rel="bookmark"><time class="entry-date published" datetime="%2$s">%3$s</time></a></span>',
			esc_url(get_permalink()),
			esc_attr(get_the_date('c')),
			esc_html(get_the_date())
		);
	}

	public static function postedBy()
	{
		printf(
			'<span class="byline"><span class="author vcard"><a class="url fn n" href="%1$s">%2$s</a></span></span>',
			esc_url(get_author_posts_url((int) get_the_author_meta('ID'))),
			esc_html(get_the_author())
		);
	}

	public static function thumbnail()
	{
		if (post_password_required() || is_attachment() || ! has_post_thumbnail()) {
			return;
		}

		printf('<a class="post-thumbnail" href="%s" aria-hidden="true" tabindex="-1">', esc_url(get_permalink()));
		echo get_the_post_thumbnail(get_the_ID(), 'post-thumbnail', array('alt' => get_the_title(), 'loading' => 'lazy', 'decoding' => 'async')); // Core-generated image markup.
		echo '</a>';
	}

	public static function entryFooter()
	{
		if (get_post_type() !== 'post') {
			return;
		}

		$categories = get_the_category_list(', ');
		if ($categories) {
			printf('<span class="cat-links">%s</span>', sprintf(esc_html__('Рубрики: %s', 'edemchinim'), $categories)); // Core-generated term links.
		}
		$tags = get_the_tag_list('', ', ');
		if ($tags && ! is_wp_error($tags)) {
			printf('<span class="tags-links">%s</span>', sprintf(esc_html__('Метки: %s', 'edemchinim'), $tags)); // Core-generated term links.
		}
	}
}
