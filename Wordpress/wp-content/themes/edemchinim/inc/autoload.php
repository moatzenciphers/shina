<?php
/** Load only this theme's namespace; Composer is not required on the server. */
if (! defined('ABSPATH')) {
	exit;
}

spl_autoload_register(static function ($class) {
	$prefix = 'Edemchinim\\';
	if (strpos($class, $prefix) !== 0) {
		return;
	}

	$relative = substr($class, strlen($prefix));
	if (! preg_match('/^[A-Za-z_][A-Za-z0-9_\\\\]*$/D', $relative)) {
		return;
	}

	$file = __DIR__ . '/' . str_replace('\\', '/', $relative) . '.php';
	if (is_readable($file)) {
		require_once $file;
	}
});
