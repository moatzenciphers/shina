<?php
/**
 * Theme entry point. Module dependencies and load order live in inc/Bootstrap.php.
 *
 * @package edemchinim
 */

if (! defined('_S_VERSION')) {
	define('_S_VERSION', '1.0.8');
}

require_once get_template_directory() . '/inc/autoload.php';

\Edemchinim\Bootstrap::boot();
