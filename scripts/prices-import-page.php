<?php
/**
 * Plugin Name: Импорт цен из Excel
 * Description: Импорт prices-export.json через [shina_prices_import] или Инструменты → Импорт цен.
 * Version: 1.0.0
 */
if (!defined('ABSPATH')) {
    exit;
}
require_once __DIR__ . '/prices-import-core.php';

function shina_prices_import_page() {
    if (!current_user_can('manage_options')) {
        return '<p>Импорт цен доступен только администратору сайта.</p>';
    }
    ob_start();
    echo '<div class="shina-prices-import"><h2>Импорт цен</h2>';
    echo '<p>Выберите prices-export.json. Новые записи создаются черновиками, повторный импорт обновляет ранее загруженные цены.</p>';
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['shina_prices_action'])) {
        try {
            $nonce = isset($_POST['shina_prices_nonce']) && is_string($_POST['shina_prices_nonce'])
                ? sanitize_text_field(wp_unslash($_POST['shina_prices_nonce'])) : '';
            if (!wp_verify_nonce($nonce, 'shina_prices_import')) {
                throw new RuntimeException('Сессия устарела. Обновите страницу и повторите загрузку.');
            }
            $action = is_string($_POST['shina_prices_action']) ? wp_unslash($_POST['shina_prices_action']) : '';
            if (!in_array($action, ['check', 'apply'], true)) {
                throw new RuntimeException('Неизвестное действие.');
            }
            $upload = $_FILES['shina_prices_file'] ?? null;
            if (!is_array($upload) || ($upload['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK ||
                !is_string($upload['tmp_name'] ?? null) || !is_uploaded_file($upload['tmp_name'])) {
                throw new RuntimeException('Не удалось загрузить файл. Выберите JSON и проверьте лимит загрузки на сервере.');
            }
            if (filesize($upload['tmp_name']) > 5 * 1024 * 1024) {
                throw new RuntimeException('Файл превышает 5 МБ.');
            }
            $json = file_get_contents($upload['tmp_name']);
            if ($json === false) {
                throw new RuntimeException('Не удалось прочитать загруженный файл.');
            }
            $result = shina_import_prices_json($json, $action === 'apply');
            $message = $action === 'apply' ? 'Импорт выполнен.' : 'Проверка пройдена, записи не изменены.';
            echo '<p role="status">' . esc_html("$message Новых: {$result['created']}, обновляемых: {$result['updated']}.") . '</p>';
            echo '<details><summary>Список позиций</summary><ul>';
            foreach ($result['log'] as $line) {
                echo '<li>' . esc_html($line) . '</li>';
            }
            echo '</ul></details>';
        } catch (Throwable $error) {
            echo '<p role="alert">' . esc_html($error->getMessage()) . '</p>';
        }
    }
    ?>
    <form method="post" enctype="multipart/form-data">
        <?php wp_nonce_field('shina_prices_import', 'shina_prices_nonce'); ?>
        <p><label>Файл цен (JSON, до 5 МБ)<br>
            <input type="file" name="shina_prices_file" accept=".json,application/json" required>
        </label></p>
        <p>
            <button type="submit" class="button" name="shina_prices_action" value="check">Проверить без импорта</button>
            <button type="submit" class="button button-primary" name="shina_prices_action" value="apply">Импортировать цены</button>
        </p>
    </form>
    <?php
    echo '</div>';
    return ob_get_clean();
}
add_shortcode('shina_prices_import', 'shina_prices_import_page');
add_action('admin_menu', static function () {
    add_management_page('Импорт цен', 'Импорт цен', 'manage_options', 'shina-prices-import', static function () {
        echo '<div class="wrap">' . shina_prices_import_page() . '</div>';
    });
});
