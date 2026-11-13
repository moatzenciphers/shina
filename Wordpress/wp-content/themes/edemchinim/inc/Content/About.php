<?php
namespace Edemchinim\Content;

use Edemchinim\Support\Fields;

if (! defined('ABSPATH')) exit;

/** Confirmed company copy and photographs from the existing works gallery. */
final class About
{
    public static function content()
    {
        return json_decode(file_get_contents(__DIR__ . '/about-content.json'), true);
    }

    public static function images()
    {
        $pages = get_posts(array('post_type' => 'page', 'post_status' => 'publish', 'posts_per_page' => 1, 'meta_key' => '_wp_page_template', 'meta_value' => 'page-works.php', 'no_found_rows' => true));
        $page = $pages ? $pages[0] : (get_page_by_path('works') ?: get_page_by_path('vyezdy'));
        $chosen = $page ? array_values(array_filter(array_map(array(Blocks::class, 'imageId'), (array) Fields::get('works_images', $page->ID, array())))) : array();
        $query = array('post_type' => 'attachment', 'post_status' => 'inherit', 'post_mime_type' => 'image', 'posts_per_page' => 3, 'no_found_rows' => true);
        if ($chosen) {
            $query['post__in'] = $chosen;
            $query['orderby'] = 'post__in';
        } else {
            $query['meta_query'] = array(array('key' => 'shina_photo_is_active', 'value' => '1'));
        }
        return array_values(array_filter(array_map(static function ($image) { return (int) $image->ID; }, get_posts($query)), static function ($id) { return (bool) wp_get_attachment_image_url($id, 'full'); }));
    }
}
