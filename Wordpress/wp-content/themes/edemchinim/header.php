<?php

/**
 * The header for our theme
 *
 * This is the template that displays all of the <head> section and everything up until <div id="content">
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package edemchinim
 */

?>
<!doctype html>
<html <?php language_attributes(); ?>>

<head>
	<meta charset="<?php bloginfo('charset'); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">

	<?php wp_head(); ?>
	<script>
		window.__YANDEX_MAPS_URL__ = 'https://api-maps.yandex.ru/2.1/?lang=ru_RU&load=Map%2CPlacemark%2CGeoObjectCollection%2CSuggestView%2Cgeocode%2Cgeolocation%2Croute%2CtemplateLayoutFactory&apikey=583522a2-deda-4602-bdaa-560cf1c6d16a&suggest_apikey=583522a2-deda-4602-bdaa-560cf1c6d16a';
		window.__YANDEX_MAPS_DISABLED__ = false;
		window.__YANDEX_SUGGEST_DISABLED__ = false;
	</script>
</head>

<body <?php body_class(); ?>>
	<?php wp_body_open(); ?>
	<div id="page" class="site">
