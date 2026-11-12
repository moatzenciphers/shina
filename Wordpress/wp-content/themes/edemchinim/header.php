<?php
use Edemchinim\Calculator\Config;


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
		window.__YANDEX_MAPS_URL__ = 'https://api-maps.yandex.ru/2.1/?lang=ru_RU&apikey=44e51241-2a5b-47fa-93ad-d20831059835&suggest_apikey=44e51241-2a5b-47fa-93ad-d20831059835';
        window.__YANDEX_MAPS_DISABLED__ = false;
        window.__YANDEX_SUGGEST_DISABLED__ = false;
        window.__OPENROUTESERVICE_API_KEY__ = "eyJvcmciOiI1YjNjZTM1OTc4NTExMTAwMDFjZjYyNDgiLCJpZCI6ImE2NmRjZWJkYzA0MDRiOGU5OGU3MzYxOTAwNTJiZjJhIiwiaCI6Im11cm11cjY0In0=";
	</script>
	<style>
		:root {
			--master-icon: url("/car-2/");
		}
		[data-href] {
			cursor: pointer;
		}
	</style>
<!-- Yandex.Metrika counter -->
<script type="text/javascript">
    (function(m,e,t,r,i,k,a){
        m[i]=m[i]||function(){(m[i].a=m[i].a||[]).push(arguments)};
        m[i].l=1*new Date();
        for (var j = 0; j < document.scripts.length; j++) {if (document.scripts[j].src === r) { return; }}
        k=e.createElement(t),a=e.getElementsByTagName(t)[0],k.async=1,k.src=r,a.parentNode.insertBefore(k,a)
    })(window, document,'script','https://mc.yandex.ru/metrika/tag.js?id=110756233', 'ym');

    ym(110756233, 'init', {ssr:true, webvisor:true, clickmap:true, ecommerce:"dataLayer", referrer: document.referrer, url: location.href, accurateTrackBounce:true, trackLinks:true});
</script>
<!-- /Yandex.Metrika counter -->
</head>

<body <?php body_class(); ?>>
	<?php wp_body_open(); ?>
	<div id="page" class="site">
		<div class="landing landing--wp-header" data-landing>
		    <?php if(!Config::isPage()): ?>
			<div class="site-header-shell" data-site-header-root>
				<?php get_template_part('template-parts/layout/header'); ?>
			</div>
			<?php endif; ?>
			<noscript><div><img src="https://mc.yandex.ru/watch/110756233" style="position:absolute; left:-9999px;" alt="" /></div></noscript>
