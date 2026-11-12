<?php
/**
 * Standalone WP-CLI importer. Do not include in the theme.
 * wp eval-file scripts/import-prices.php prices-export.json
 * wp eval-file scripts/import-prices.php prices-export.json apply
 */
if (!defined('WP_CLI') || !WP_CLI) {
    exit("Run this file through WP-CLI.\n");
}
$file = $args[0] ?? '';
$mode = $args[1] ?? 'dry-run';
if (!in_array($mode, ['dry-run', 'apply'], true) || !$file || !is_readable($file)) {
    WP_CLI::error('Usage: wp eval-file scripts/import-prices.php prices-export.json [dry-run|apply]');
}
if (!post_type_exists('prices') || !post_type_exists('services') || !function_exists('update_field')) {
    WP_CLI::error('Register prices/services and enable ACF before importing.');
}
$field_keys = [
    'price_services' => 'field_shina_price_services',
    'price_type' => 'field_shina_price_type',
    'price_additional' => 'field_shina_price_additional',
    'price_display_type' => 'field_shina_price_display_type',
    'price_value' => 'field_shina_price_value',
    'price_rows' => 'field_shina_price_rows',
];
foreach (array_merge(array_values($field_keys), ['field_shina_price_row_name', 'field_shina_price_row_value']) as $key) {
    if (!acf_get_field($key)) {
        WP_CLI::error("Missing ACF field $key. Import acf-prices-import.json first.");
    }
}
$data = json_decode(file_get_contents($file), true);
if (json_last_error() !== JSON_ERROR_NONE || ($data['format'] ?? '') !== 'shina-prices-v1' || empty($data['records']) || !is_array($data['records'])) {
    WP_CLI::error('Invalid price export JSON.');
}

// Resolve every relationship and validate all records BEFORE the first database write.
$services = get_posts(['post_type' => 'services', 'post_status' => ['publish', 'draft', 'pending', 'private', 'future'], 'numberposts' => -1]);
$services_by_title = [];
foreach ($services as $service) {
    $services_by_title[trim($service->post_title)][] = (int) $service->ID;
}
$plan = [];
$seen = [];
$is_amount = static function ($value) {
    return (is_int($value) || is_float($value)) && is_finite((float) $value) && $value >= 0;
};
foreach ($data['records'] as $index => $record) {
    $label = 'Record ' . ($index + 1);
    $key = $record['source_key'] ?? '';
    $acf = $record['acf'] ?? [];
    if (!is_string($key) || !preg_match('/^[a-f0-9]{64}$/D', $key) || isset($seen[$key]) ||
        !is_string($record['post_title'] ?? null) || trim($record['post_title']) === '' ||
        !is_array($record['service_titles'] ?? null) || empty($record['service_titles']) ||
        !in_array($acf['price_type'] ?? '', ['value', 'table'], true) ||
        !in_array($acf['price_additional'] ?? null, [0, 1], true) ||
        !in_array($acf['price_display_type'] ?? '', ['rub', 'rub_from', 'rub_per_item', 'percent'], true)) {
        WP_CLI::error("$label: invalid or duplicate price record.");
    }
    $seen[$key] = true;
    if ($acf['price_type'] === 'value') {
        if (!$is_amount($acf['price_value'] ?? null)) {
            WP_CLI::error("$label: invalid price_value.");
        }
        $acf['price_rows'] = [];
    } else {
        if (empty($acf['price_rows']) || !is_array($acf['price_rows'])) {
            WP_CLI::error("$label: table has no rows.");
        }
        foreach ($acf['price_rows'] as $row) {
            if (!is_string($row['name'] ?? null) || trim($row['name']) === '' || !$is_amount($row['value'] ?? null)) {
                WP_CLI::error("$label: invalid table row.");
            }
        }
        $acf['price_value'] = '';
    }
    $service_ids = [];
    foreach ($record['service_titles'] as $title) {
        if (!is_string($title) || count($services_by_title[trim($title)] ?? []) !== 1) {
            WP_CLI::error("$label: service not found or title is ambiguous: " . (is_string($title) ? $title : '(invalid title)'));
        }
        $service_ids[] = $services_by_title[trim($title)][0];
    }
    $existing = get_posts([
        'post_type' => 'prices', 'post_status' => ['publish', 'draft', 'pending', 'private', 'future', 'trash'],
        'numberposts' => 2, 'fields' => 'ids', 'meta_key' => '_shina_price_source_key', 'meta_value' => $key,
    ]);
    if (count($existing) > 1) {
        WP_CLI::error("$label: multiple posts have the same import key.");
    }
    if ($existing && get_post_status($existing[0]) === 'trash') {
        WP_CLI::error("$label: imported post is in trash. Restore it or delete it permanently before importing.");
    }
    $acf['price_services'] = array_values(array_unique($service_ids));
    $plan[] = ['id' => (int) ($existing[0] ?? 0), 'record' => $record, 'acf' => $acf, 'menu_order' => $index];
}
$created = 0;
$updated = 0;
foreach ($plan as $item) {
    $record = $item['record'];
    WP_CLI::log(($item['id'] ? 'UPDATE' : 'CREATE') . ': ' . $record['post_title'] . ' [' . implode(', ', $record['service_titles']) . ']');
    if ($mode === 'apply') {
        $post = [
            'post_type' => 'prices', 'post_title' => $record['post_title'], 'menu_order' => $item['menu_order'],
            'meta_input' => ['_shina_price_source_key' => $record['source_key']],
        ];
        if ($item['id']) {
            $post['ID'] = $item['id'];
            $post_id = wp_update_post(wp_slash($post), true);
        } else {
            // New records remain drafts for review; existing publication status is preserved.
            $post['post_status'] = 'draft';
            $post_id = wp_insert_post(wp_slash($post), true);
        }
        if (is_wp_error($post_id)) {
            WP_CLI::error($post_id->get_error_message());
        }
        foreach ($field_keys as $name => $field_key) {
            $value = $item['acf'][$name];
            if ($name === 'price_rows') {
                // Use subfield keys for the first save of a repeater on a new post.
                $value = array_map(static function ($row) {
                    return ['field_shina_price_row_name' => $row['name'], 'field_shina_price_row_value' => $row['value']];
                }, $value);
            }
            update_field($field_key, $value, $post_id);
        }
    }
    $item['id'] ? $updated++ : $created++;
}
WP_CLI::success(($mode === 'apply' ? 'Imported' : 'Dry run, no changes') . ": $created new, $updated existing prices.");
