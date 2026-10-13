<?php
/** wp eval-file scripts/import-prices.php prices-export.json [dry-run|apply] --use-include */
if (!defined('WP_CLI') || !WP_CLI) {
    exit("Run this file through WP-CLI.\n");
}
require_once __DIR__ . '/prices-import-core.php';
$file = $args[0] ?? '';
$mode = $args[1] ?? 'dry-run';
if (!in_array($mode, ['dry-run', 'apply'], true) || !$file || !is_readable($file)) {
    WP_CLI::error('Usage: wp eval-file scripts/import-prices.php prices-export.json [dry-run|apply] --use-include');
}
try {
    $result = shina_import_prices_json(file_get_contents($file), $mode === 'apply');
    foreach ($result['log'] as $line) {
        WP_CLI::log($line);
    }
    WP_CLI::success(($mode === 'apply' ? 'Imported' : 'Dry run, no changes') . ": {$result['created']} new, {$result['updated']} existing prices.");
} catch (Throwable $error) {
    WP_CLI::error($error->getMessage());
}
