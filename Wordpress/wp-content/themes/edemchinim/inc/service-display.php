<?php
/** Shared service icons, area terms and review relations. */
function edemchinim_sanitize_icon($raw)
{
    $attributes = array_fill_keys(array('viewbox', 'width', 'height', 'fill', 'stroke', 'stroke-width', 'stroke-linecap', 'stroke-linejoin', 'fill-rule', 'clip-rule', 'opacity', 'd', 'points', 'x', 'y', 'x1', 'y1', 'x2', 'y2', 'cx', 'cy', 'r', 'rx', 'ry', 'transform'), true);
    $allowed = array();
    foreach (array('svg', 'g', 'path', 'circle', 'ellipse', 'rect', 'line', 'polyline', 'polygon') as $tag) $allowed[$tag] = $attributes;
    return wp_kses((string) $raw, $allowed);
}

function edemchinim_service_icon($service_id, $class = '')
{
    $svg = edemchinim_sanitize_icon(edemchinim_get_field('service_icon', $service_id, ''));
    if (stripos($svg, '<svg') === false) $svg = '<svg viewBox="0 0 24 24" fill="none"><path d="M20 12a8 8 0 1 1-16 0 8 8 0 0 1 16 0Z M12 4v16 M4 12h16 M6.3 6.3l11.4 11.4 M6.3 17.7 17.7 6.3" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg>';
    return '<span class="service-icon ' . esc_attr($class) . '" aria-hidden="true">' . $svg . '</span>';
}

function edemchinim_service_areas($service_id)
{
    $terms = get_the_terms($service_id, 'area');
    return ! $terms || is_wp_error($terms) ? array() : $terms;
}

function edemchinim_review_service_id($review_id)
{
    $service_id = (int) edemchinim_get_field('review_service', $review_id, 0);
    if ($service_id && get_post_type($service_id) === 'services') return $service_id;
    $legacy = edemchinim_get_field('shina_review_repair_type', $review_id, '');
    $names = array('seasonal' => 'Сезонная замена шин', 'puncture' => 'Ремонт проколов', 'storage' => 'Сезонное хранение шин', 'conditioner' => 'Заправка кондиционера');
    if (! isset($names[$legacy])) return 0;
    static $services = null;
    if ($services === null) $services = get_posts(array('post_type' => 'services', 'post_status' => 'publish', 'posts_per_page' => -1));
    foreach ($services as $service) {
        if ($service->post_title === $names[$legacy] || ($legacy === 'puncture' && $service->post_title === 'Ремонт прокола') || ($legacy === 'storage' && $service->post_title === 'Хранение шин')) return (int) $service->ID;
    }
    return 0;
}

function edemchinim_review_summary()
{
    static $summary = null;
    if ($summary !== null) return $summary;
    $reviews = get_posts(array('post_type' => 'reviews', 'post_status' => 'publish', 'posts_per_page' => -1));
    $total = 0;
    $count = 0;
    foreach ($reviews as $review) {
        if (! edemchinim_get_field('shina_review_is_active', $review->ID, 1)) continue;
        $total += max(0, min(5, (float) edemchinim_get_field('shina_review_rating', $review->ID, 5)));
        $count++;
    }
    return $summary = array('count' => $count, 'rating' => $count ? $total / $count : 0);
}

// Extend existing reviews directly in their edit screen, without creating a new type.
add_action('acf/init', static function () {
    if (! function_exists('acf_add_local_field_group')) return;
    foreach (array('acf-review-fields.json', 'acf-review-caption.json') as $filename) {
        $schema = get_template_directory() . '/inc/' . $filename;
        if (! is_readable($schema)) continue;
        $group = json_decode(file_get_contents($schema), true);
        if (is_array($group)) acf_add_local_field_group($group);
    }
});
