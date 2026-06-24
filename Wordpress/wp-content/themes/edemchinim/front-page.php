<?php

/**
 * Front page template.
 *
 * @package edemchinim
 */

get_header();

if (! function_exists('edemchinim_asset_url')) {
	function edemchinim_asset_url($path)
	{
		return trailingslashit(get_template_directory_uri()) . ltrim($path, '/');
	}
}

if (! function_exists('edemchinim_option_icon')) {
	function edemchinim_option_icon($file)
	{
		$relative_path = 'img/' . ltrim($file, '/');

		if (file_exists(trailingslashit(get_template_directory()) . $relative_path)) {
			printf(
				'<img src="%s" alt="" loading="lazy" decoding="async">',
				esc_url(edemchinim_asset_url($relative_path))
			);
			return;
		}

		echo '<span class="icon-circle" aria-hidden="true"></span>';
	}
}

if (! function_exists('edemchinim_get_field')) {
	function edemchinim_get_field($field_name, $post_id = false, $default = '')
	{
		if (function_exists('get_field')) {
			$value = get_field($field_name, $post_id);
			return $value !== null && $value !== false && $value !== '' ? $value : $default;
		}

		$value = get_post_meta($post_id ?: get_the_ID(), $field_name, true);
		return $value !== '' ? $value : $default;
	}
}

if (! function_exists('edemchinim_get_image_url')) {
	function edemchinim_get_image_url($image, $size = 'medium_large')
	{
		if (is_array($image)) {
			if (! empty($image['sizes'][$size])) {
				return $image['sizes'][$size];
			}

			return $image['url'] ?? '';
		}

		if (is_numeric($image)) {
			return wp_get_attachment_image_url((int) $image, $size) ?: '';
		}

		return is_string($image) ? $image : '';
	}
}

if (! function_exists('edemchinim_relative_date')) {
	function edemchinim_relative_date($date)
	{
		if (! $date) {
			return '';
		}

		try {
			$timezone = wp_timezone();
			$day = new DateTimeImmutable($date, $timezone);
			$today = new DateTimeImmutable('today', $timezone);
			$diff = (int) $today->diff($day->setTime(0, 0))->format('%r%a');
		} catch (Exception $exception) {
			return '';
		}

		if ($diff === 0) {
			return 'Сегодня';
		}

		if ($diff === -1) {
			return 'Вчера';
		}

		$days = abs($diff);

		if ($days < 7) {
			$forms = array('день', 'дня', 'дней');
			$index = ($days % 10 === 1 && $days % 100 !== 11) ? 0 : (($days % 10 >= 2 && $days % 10 <= 4 && ($days % 100 < 10 || $days % 100 >= 20)) ? 1 : 2);
			return sprintf('%d %s назад', $days, $forms[$index]);
		}

		return wp_date('j F Y', $day->getTimestamp());
	}
}

if (! function_exists('edemchinim_review_stars')) {
	function edemchinim_review_stars($rating)
	{
		$filled_stars = max(0, min(5, (int) round((float) $rating)));
		echo '<span class="review-rating__stars" aria-hidden="true">';

		for ($index = 1; $index <= 5; $index++) {
			$class_name = $index <= $filled_stars ? 'review-rating__star' : 'review-rating__star review-rating__no-fill';
			printf('<span class="%s"></span>', esc_attr($class_name));
		}

		echo '</span>';
	}
}

if (! function_exists('edemchinim_render_review_card')) {
	function edemchinim_render_review_card($post)
	{
		$post_id = $post->ID;
		$type = edemchinim_get_field('shina_review_repair_type', $post_id, 'seasonal');
		$rating = (float) edemchinim_get_field('shina_review_rating', $post_id, 5);
		$date = edemchinim_get_field('shina_review_date', $post_id, get_the_date('Y-m-d', $post_id));
		$gallery = edemchinim_get_field('shina_review_gallery', $post_id, array());
		$avatar_url = get_the_post_thumbnail_url($post_id, 'thumbnail');
		$content = wp_strip_all_tags(apply_filters('the_content', $post->post_content));
		$author_name = get_the_title($post_id);
		$author_initial = function_exists('mb_substr') ? mb_substr($author_name, 0, 1) : substr($author_name, 0, 1);
?>
		<article class="review-card" data-review-type="<?php echo esc_attr($type); ?>">
			<header class="review-card__header">
				<?php if ($avatar_url) : ?>
					<img class="review-card__avatar" src="<?php echo esc_url($avatar_url); ?>" alt="" loading="lazy" decoding="async">
				<?php else : ?>
					<span class="review-card__avatar" aria-hidden="true"><?php echo esc_html($author_initial); ?></span>
				<?php endif; ?>
				<div class="review-card__meta">
					<h3 class="review-card__author"><?php echo esc_html($author_name); ?></h3>
					<div class="review-rating" aria-label="<?php echo esc_attr(sprintf('Оценка %.1f из 5', $rating)); ?>">
						<?php edemchinim_review_stars($rating); ?>
						<span class="review-rating__value"><?php echo esc_html(number_format_i18n($rating, 1)); ?></span>
					</div>
				</div>
				<span class="review-card__date"><?php echo esc_html(edemchinim_relative_date($date)); ?></span>
			</header>
			<?php if ($content) : ?>
				<p class="review-card__text"><?php echo esc_html($content); ?></p>
			<?php endif; ?>
			<?php if (is_array($gallery) && $gallery) : ?>
				<div class="review-card__gallery">
					<?php foreach (array_slice($gallery, 0, 3) as $image) : ?>
						<?php
						$image_url = edemchinim_get_image_url($image, 'medium_large');
						if (! $image_url) {
							continue;
						}
						?>
						<a class="review-card__photo-link glightbox" href="<?php echo esc_url(edemchinim_get_image_url($image, 'full') ?: $image_url); ?>" data-gallery="review-<?php echo esc_attr($post_id); ?>" data-type="image">
							<img class="review-card__photo" src="<?php echo esc_url($image_url); ?>" alt="" loading="lazy" decoding="async">
						</a>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
		</article>
	<?php
	}
}

if (! function_exists('edemchinim_render_photo_card')) {
	function edemchinim_render_photo_card($attachment, $index)
	{
		$attachment_id = $attachment->ID;
		$type = edemchinim_get_field('shina_photo_repair_type', $attachment_id, 'seasonal');
		$label = edemchinim_get_field('shina_photo_label', $attachment_id, get_the_title($attachment_id));
		$image_url = wp_get_attachment_image_url($attachment_id, 'medium_large');
		$full_url = wp_get_attachment_image_url($attachment_id, 'full') ?: $image_url;
		$modifier = $index % 5 === 0 ? ' photo-card--tall' : ($index % 4 === 0 ? ' photo-card--short' : '');

		if (! $image_url) {
			return;
		}
	?>
		<article class="photo-card<?php echo esc_attr($modifier); ?>" data-photo-type="<?php echo esc_attr($type); ?>">
			<a class="photo-card__link glightbox" href="<?php echo esc_url($full_url); ?>" aria-label="<?php echo esc_attr($label); ?>" data-gallery="service-photos" data-type="image" data-description="<?php echo esc_attr($label); ?>" data-desc-position="bottom">
				<img class="photo-card__image" src="<?php echo esc_url($image_url); ?>" alt="<?php echo esc_attr($label); ?>" loading="lazy" decoding="async">
			</a>
		</article>
	<?php
	}
}

if (! function_exists('edemchinim_render_faq_item')) {
	function edemchinim_render_faq_item($post, $is_open = false)
	{
		$content = apply_filters('the_content', $post->post_content);
	?>
		<details class="faq-item" data-faq-item <?php echo $is_open ? 'open' : ''; ?>>
			<summary class="faq-item__question">
				<span><?php echo esc_html(get_the_title($post)); ?></span>
				<span class="faq-item__marker" aria-hidden="true"></span>
			</summary>
			<div class="faq-item__answer">
				<?php echo wp_kses_post($content); ?>
			</div>
		</details>
<?php
	}
}

if (! function_exists('edemchinim_phone_icon')) {
	function edemchinim_phone_icon()
	{
?>
		<svg class="icon-circle icon-circle--small" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
			<path fill="currentColor" d="M4.05 21q-.45 0-.75-.3t-.3-.75V15.9q0-.325.225-.587t.575-.363l3.45-.7q.35-.05.713.063t.587.337L10.9 17q.95-.55 1.8-1.213t1.625-1.437q.825-.8 1.513-1.662t1.187-1.788L14.6 8.45q-.2-.2-.275-.475T14.3 7.3l.65-3.5q.05-.325.325-.562T15.9 3h4.05q.45 0 .75.3t.3.75q0 3.125-1.362 6.175t-3.863 5.55t-5.55 3.863T4.05 21"></path>
		</svg>
<?php
	}
}

$reviews_query = new WP_Query(
	array(
		'post_type'      => 'reviews',
		'post_status'    => 'publish',
		'posts_per_page' => 10,
		'orderby'        => 'date',
		'order'          => 'DESC',
		'meta_query'     => array(
			'relation' => 'OR',
			array(
				'key'     => 'shina_review_is_active',
				'compare' => 'NOT EXISTS',
			),
			array(
				'key'     => 'shina_review_is_active',
				'value'   => '1',
				'compare' => '=',
			),
		),
	)
);

$photos_query = new WP_Query(
	array(
		'post_type'      => 'attachment',
		'post_status'    => 'inherit',
		'post_mime_type' => 'image',
		'posts_per_page' => 10,
		'orderby'        => 'date',
		'order'          => 'DESC',
		'meta_query'     => array(
			array(
				'key'     => 'shina_photo_is_active',
				'value'   => '1',
				'compare' => '=',
			),
		),
	)
);

$faq_query = new WP_Query(
	array(
		'post_type'      => 'faq',
		'post_status'    => 'publish',
		'posts_per_page' => 20,
		'orderby'        => array(
			'menu_order' => 'ASC',
			'date'       => 'DESC',
		),
	)
);

$order_form_shortcode = '[contact-form-7 id="db84527" title="Калькулятор"]';
$average_rating = edemchinim_get_option_field('shina_average_rating', '4.9');
$successful_visits_count = edemchinim_get_option_field('shina_successful_visits_count', '3500+');
$work_schedule = edemchinim_get_option_field('shina_work_schedule', '24/7');
$yandex_reviews_count = edemchinim_get_option_field('shina_yandex_reviews_count', '420+');
$phone = edemchinim_get_option_field('shina_phone', '+7 (000) 000-00-00');
$phone_href = function_exists('edemchinim_phone_href') ? edemchinim_phone_href($phone) : 'tel:' . preg_replace('/\D+/', '', $phone);
$calculator_title_first_line = edemchinim_get_option_field('shina_calculator_title_first_line', 'Выездной');
$calculator_title_second_line = edemchinim_get_option_field('shina_calculator_title_second_line', 'шиномонтаж');
$calculator_subtitle_prefix = edemchinim_get_option_field('shina_calculator_subtitle_prefix', 'приедем за');
$calculator_arrival_time = edemchinim_get_option_field('shina_calculator_arrival_time', '30-60');
$calculator_subtitle_suffix = edemchinim_get_option_field('shina_calculator_subtitle_suffix', 'минут');
$personal_data_consent_text = edemchinim_get_option_field('shina_personal_data_consent_text', 'Я принимаю согласие на обработку персональных данных');
$cookie_consent_title = edemchinim_get_option_field('shina_cookie_consent_title', 'Мы используем cookie');
$cookie_consent_text = edemchinim_get_option_field('shina_cookie_consent_text', 'Cookie помогают сайту работать стабильно и улучшать сервис.');
$cookie_consent_button_text = edemchinim_get_option_field('shina_cookie_consent_button_text', 'Принять');
$order_success_title = edemchinim_get_option_field('shina_order_success_title', 'Заявка принята');
$order_success_text = edemchinim_get_option_field('shina_order_success_text', 'Мы получили данные и скоро свяжемся для подтверждения времени выезда.');
?>

<main class="app" id="app" data-app>
	<div class="app-menu-shell">
		<button class="map-overlay__menu-button" type="button" aria-label="Открыть меню" aria-expanded="false" data-app-menu-toggle>
			<svg class="icon-circle icon-circle--small" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
				<path fill="currentColor" d="M3 8V7h17v1zm17 4v1H3v-1zM3 17h17v1H3z"></path>
			</svg>
		</button>
		<a class="map-overlay__call" href="<?php echo esc_url($phone_href); ?>" aria-label="Позвонить">
			<?php edemchinim_phone_icon(); ?>
		</a>
		<div class="app-menu-popover" data-app-menu hidden>
			<nav class="app-menu-popover__nav" aria-label="Разделы">
				<button class="app-menu-popover__item app-menu-popover__item--active" type="button" data-screen-target="order" aria-current="page">
					<svg class="icon-circle icon-circle--small" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
						<path fill="currentColor" d="M8.308 17.692h.884v-2h2v-.884h-2v-2h-.884v2h-2v.884h2zm5-.75h4.384v-.884h-4.384zm0-2.5h4.384v-.884h-4.384zm.792-3.953l1.4-1.4l1.4 1.4l.627-.627l-1.4-1.412l1.4-1.4l-.627-.627l-1.4 1.4l-1.4-1.4l-.627.627l1.4 1.4l-1.4 1.412zM6.558 8.892h4.384v-.884H6.558zM5.616 20q-.691 0-1.153-.462T4 18.384V5.616q0-.691.463-1.153T5.616 4h12.769q.69 0 1.153.463T20 5.616v12.769q0 .69-.462 1.153T18.384 20zm0-1h12.769q.23 0 .423-.192t.192-.424V5.616q0-.231-.192-.424T18.384 5H5.616q-.231 0-.424.192T5 5.616v12.769q0 .23.192.423t.423.192M5 5v14z"></path>
					</svg>
					<span>Заказать</span>
				</button>
				<button class="app-menu-popover__item" type="button" data-screen-target="reviews">
					<svg class="icon-circle icon-circle--small" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
						<path fill="currentColor" d="m12.34 12.192l1.93-1.163l1.928 1.163l-.523-2.196l1.712-1.475l-2.24-.186l-.878-2.066l-.877 2.066l-2.24.186l1.712 1.475zm4.514 6.27h1.485q-.002.361-.245.617t-.61.317L6.182 20.817q-.671.087-1.2-.32q-.527-.407-.608-1.078l-1.23-9.713q-.08-.672.333-1.216t1.085-.606l.977-.073v1l-.823.068q-.27.019-.424.221t-.115.471l1.196 9.713q.039.27.23.424q.193.153.463.115zm-7.7-2q-.69 0-1.153-.463t-.462-1.153V4.616q0-.691.462-1.153T9.154 3h10.23q.691 0 1.153.463T21 4.616v10.23q0 .69-.463 1.153t-1.153.463zm0-1h10.23q.27 0 .443-.173t.173-.443V4.616q0-.270-.173-.443T19.385 4H9.154q-.27 0-.442.173q-.173.173-.173.443v10.23q0 .27.173.443t.442.173M5.45 19.9"></path>
					</svg>
					<span>Отзывы</span>
				</button>
				<button class="app-menu-popover__item" type="button" data-screen-target="photo">
					<svg class="icon-circle icon-circle--small" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
						<path fill="currentColor" d="M4.116 18q-.667 0-1.141-.475t-.475-1.14v-8.77q0-.666.475-1.14T4.115 6h8.77q.666 0 1.14.475t.475 1.14v8.77q0 .666-.475 1.14t-1.14.475zm13.201-7q-.357 0-.587-.23t-.23-.587V6.817q0-.357.23-.587t.587-.23h3.366q.357 0 .587.23t.23.587v3.366q0 .358-.23.587t-.587.23zm.183-1h3V7h-3zM4.116 17h8.769q.269 0 .442-.173t.173-.442v-8.77q0-.269-.173-.442T12.885 7h-8.77q-.269 0-.442.173t-.173.443v8.769q0 .269.173.442t.443.173m.576-2.096h7.616l-2.433-3.25L8 14.154l-1.375-1.825zM17.317 18q-.357 0-.587-.23t-.23-.587v-3.366q0-.357.23-.587t.587-.23h3.366q.357 0 .587.23t.23.587v3.366q0 .358-.23.587t-.587.23zm.183-1h3v-3h-3zm-14 0V7zm14-7V7zm0 7v-3z"></path>
					</svg>
					<span>Фото</span>
				</button>
				<button class="app-menu-popover__item" type="button" data-screen-target="faq">
					<svg class="icon-circle icon-circle--small" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
						<path fill="currentColor" d="M12 20.962q-3.014-.895-5.007-3.651T5 11.1V5.692l7-2.615l7 2.615V11.1q0 3.454-1.993 6.21T12 20.963m0-1.062q2.6-.825 4.3-3.3t1.7-5.5V6.375l-6-2.23l-6 2.23V11.1q0 3.025 1.7 5.5t4.3 3.3m.43-3.559q.197-.197.197-.468t-.197-.468t-.468-.197t-.469.197t-.197.468t.197.468q.198.198.469.198t.468-.198m-.905-2.58h.885q.019-.255.057-.511t.164-.467q.169-.273.371-.501t.42-.44q.424-.444.756-.94t.332-1.16q0-.97-.738-1.607q-.737-.635-1.733-.635q-.847 0-1.538.45T9.496 9.177l.812.339q.227-.52.697-.832t1.034-.311q.627 0 1.106.383t.48.998q0 .483-.291.893q-.292.41-.636.75q-.28.255-.523.533q-.242.278-.417.614q-.137.292-.185.593t-.048.624"></path>
					</svg>
					<span>FAQ</span>
				</button>
			</nav>
		</div>
	</div>
	<div class="app__screens">
		<section class="screen screen--active order-screen" data-screen="order" aria-label="Заказ шиномонтажа">
			<header class="hero">
				<div class="hero__map-placeholder" data-service-map role="img" aria-label="Место для карты"></div>
				<div class="map-overlay">
					<button class="map-overlay__menu-button" type="button" aria-label="Открыть меню" aria-expanded="false" data-app-menu-toggle>
						<svg class="icon-circle icon-circle--small" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
							<path fill="currentColor" d="M3 8V7h17v1zm17 4v1H3v-1zM3 17h17v1H3z"></path>
						</svg>
					</button>
					<div class="map-overlay__zoom" aria-label="Масштаб карты">
						<button class="map-overlay__control" type="button" aria-label="Приблизить карту" data-service-map-zoom="in">
							<svg class="icon-circle icon-circle--small" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
								<path fill="currentColor" d="M5 13v-1h6V6h1v6h6v1h-6v6h-1v-6z"></path>
							</svg>
						</button>
						<button class="map-overlay__control" type="button" aria-label="Отдалить карту" data-service-map-zoom="out">
							<svg class="icon-circle icon-circle--small" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
								<path fill="currentColor" d="M5 13v-1h13v1z"></path>
							</svg>
						</button>
					</div>
					<button class="map-overlay__location" type="button" aria-label="Использовать текущее местоположение" data-location-button>
						<svg class="icon-circle icon-circle--small" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
							<path fill="currentColor" d="M11.5 21.45v-1.461q-3.125-.293-5.16-2.328q-2.036-2.036-2.328-5.161H2.55v-1h1.462q.292-3.125 2.328-5.16t5.16-2.328V2.55h1v1.462q3.125.292 5.16 2.328t2.329 5.16h1.461v1h-1.461q-.293 3.125-2.328 5.16q-2.036 2.036-5.161 2.329v1.461zm5.45-4.5Q19 14.9 19 12t-2.05-4.95T12 5T7.05 7.05T5 12t2.05 4.95T12 19t4.95-2.05m-7.073-2.827Q9 13.246 9 12t.877-2.123T12 9t2.123.877T15 12t-.877 2.123T12 15t-2.123-.877m3.536-.71Q14 12.825 14 12t-.587-1.412T12 10t-1.412.588T10 12t.588 1.413T12 14t1.413-.587M12 12"></path>
						</svg>
					</button>
					<div class="app-menu-popover" data-app-menu hidden>
						<nav class="app-menu-popover__nav" aria-label="Разделы">
							<button class="app-menu-popover__item app-menu-popover__item--active" type="button" data-screen-target="order" aria-current="page"><span>Заказать</span></button>
							<button class="app-menu-popover__item" type="button" data-screen-target="reviews"><span>Отзывы</span></button>
							<button class="app-menu-popover__item" type="button" data-screen-target="photo"><span>Фото</span></button>
							<button class="app-menu-popover__item" type="button" data-screen-target="faq"><span>FAQ</span></button>
						</nav>
					</div>
				</div>
			</header>

			<section class="calculator" data-calculator>
				<button class="calculator__handle" type="button" aria-label="Развернуть калькулятор" aria-expanded="false" data-calculator-toggle>
					<span aria-hidden="true"></span>
				</button>
				<header class="calculator__intro">
					<div class="calculator__intro-copy">
						<h1 class="calculator__title"><?php echo esc_html($calculator_title_first_line); ?><br><?php echo esc_html($calculator_title_second_line); ?> <span><?php echo esc_html($work_schedule); ?></span></h1>
						<p class="calculator__subtitle"><?php echo esc_html($calculator_subtitle_prefix); ?> <span><?php echo esc_html($calculator_arrival_time); ?></span> <?php echo esc_html($calculator_subtitle_suffix); ?></p>
					</div>
					<a class="calculator__call" href="<?php echo esc_url($phone_href); ?>" aria-label="Позвонить">
						<?php edemchinim_phone_icon(); ?>
					</a>
				</header>
				<form class="calculator__form" action="#" data-calculator-form>
					<fieldset class="calculator__section">
						<div class="address-row">
							<label class="address-field" for="address-input">
								<svg
									class="icon-circle icon-circle--small"
									xmlns="http://www.w3.org/2000/svg"
									viewBox="0 0 24 24">
									<path
										fill="currentColor"
										d="M13.143 11.259q.473-.472.473-1.143q0-.672-.473-1.144Q12.671 8.5 12 8.5t-1.143.472t-.472 1.144q0 .67.472 1.143q.472.472 1.143.472t1.143-.472M12 19.677q2.82-2.454 4.458-4.991t1.638-4.39q0-2.744-1.737-4.53Q14.62 3.981 12 3.981T7.641 5.766t-1.737 4.53q0 1.852 1.638 4.39T12 19.677m0 1.342q-3.525-3.117-5.31-5.814q-1.786-2.697-1.786-4.909q0-3.173 2.066-5.234Q9.037 3 12 3t5.03 2.062q2.066 2.061 2.066 5.234q0 2.212-1.785 4.909q-1.786 2.697-5.311 5.814m0-10.903"></path>
								</svg>
								<input class="address-field__input" id="address-input" type="text" name="address" placeholder="Введите адрес" autocomplete="street-address" data-address-input>
							</label>
						</div>
						<p class="address-status" data-location-status aria-live="polite"></p>
					</fieldset>

					<fieldset class="calculator__section calculator__section--details">
						<legend class="calculator__legend">Услуга</legend>
						<div class="option-grid option-grid--services" data-option-group="service">
							<button class="option-card option-card--active" type="button" data-option-name="service" data-option-value="seasonal" aria-pressed="true"><?php edemchinim_option_icon('season.svg'); ?><span class="option-card__label">Сезонная замена</span></button>
							<button class="option-card" type="button" data-option-name="service" data-option-value="puncture" aria-pressed="false"><?php edemchinim_option_icon('prokol.svg'); ?><span class="option-card__label">Ремонт прокола</span></button>
							<button class="option-card" type="button" data-option-name="service" data-option-value="conditioner" aria-pressed="false"><?php edemchinim_option_icon('cond.svg'); ?><span class="option-card__label">Заправка кондиционера</span></button>
							<button class="option-card" type="button" data-option-name="service" data-option-value="storage" aria-pressed="false"><?php edemchinim_option_icon('storage.svg'); ?><span class="option-card__label">Хранение шин</span></button>
						</div>
					</fieldset>

					<fieldset class="calculator__section calculator__section--details" data-service-section="vehicle">
						<legend class="calculator__legend">Тип автомобиля</legend>
						<div class="option-grid option-grid--cars" data-option-group="carType">
							<button class="option-card option-card--active" type="button" data-option-name="carType" data-option-value="passenger" aria-pressed="true"><?php edemchinim_option_icon('light.svg'); ?><span class="option-card__label">Легковой</span></button>
							<button class="option-card" type="button" data-option-name="carType" data-option-value="crossover" aria-pressed="false"><?php edemchinim_option_icon('cross.svg'); ?><span class="option-card__label">Кроссовер</span></button>
							<button class="option-card" type="button" data-option-name="carType" data-option-value="suv" aria-pressed="false"><?php edemchinim_option_icon('offroad.svg'); ?><span class="option-card__label">Внедорожник</span></button>
							<button class="option-card" type="button" data-option-name="carType" data-option-value="commercial" data-service-only="conditioner" aria-pressed="false" hidden><?php edemchinim_option_icon('fura.svg'); ?><span class="option-card__label">Спецтехника, грузовое</span></button>
						</div>
					</fieldset>

					<fieldset class="calculator__section calculator__section--details" data-service-section="diameter">
						<legend class="calculator__legend">Диаметр колес</legend>
						<div class="diameter-slider" data-diameter-slider>
							<div class="diameter-slider__header"><output class="diameter-slider__value" id="diameter-output" for="diameter-range" data-diameter-output>R12</output></div>
							<input type="hidden" name="diameter" value="r12" data-diameter-value>
							<input class="diameter-slider__control" id="diameter-range" type="range" min="0" max="12" step="1" value="0" aria-labelledby="diameter-output" data-diameter-range>
							<div class="diameter-slider__marks" aria-hidden="true"><span>R12</span><span>R24</span></div>
						</div>
					</fieldset>

					<fieldset class="calculator__section calculator__section--details" data-service-section="seasonal-addons">
						<legend class="calculator__legend">Дополнительные услуги</legend>
						<div class="checkbox-grid">
							<label class="checkbox-card"><input class="checkbox-card__control" type="checkbox" name="copperGrease" data-addon-name="copperGrease"><span class="checkbox-card__label">Медная смазка</span><span class="checkbox-card__price">+700 ₽</span></label>
							<label class="checkbox-card"><input class="checkbox-card__control" type="checkbox" name="hubCleaning" data-addon-name="hubCleaning"><span class="checkbox-card__label">Механическая чистка ступиц</span><span class="checkbox-card__price">+800 ₽</span></label>
						</div>
					</fieldset>

					<fieldset class="calculator__section calculator__section--details" data-service-section="vehicle-addons">
						<legend class="calculator__legend">Особенности шин</legend>
						<div class="checkbox-grid">
							<label class="checkbox-card"><input class="checkbox-card__control" type="checkbox" name="lowProfile" data-addon-name="lowProfile"><span class="checkbox-card__label">Низкий профиль</span><span class="checkbox-card__price">+20%</span></label>
							<label class="checkbox-card"><input class="checkbox-card__control" type="checkbox" name="reinforcedTire" data-addon-name="reinforcedTire"><span class="checkbox-card__label">Run Flat / C / AT</span><span class="checkbox-card__price">+40%</span></label>
						</div>
					</fieldset>

					<fieldset class="calculator__section calculator__section--details" data-service-section="conditioner-addons" hidden>
						<legend class="calculator__legend">Дополнительно</legend>
						<div class="checkbox-grid">
							<label class="checkbox-card"><input class="checkbox-card__control" type="checkbox" name="freon134" data-addon-name="freon134"><span class="checkbox-card__label">Дополнительно фреон 134А 100 г</span><span class="checkbox-card__price">+500 ₽</span></label>
							<label class="checkbox-card"><input class="checkbox-card__control" type="checkbox" name="antibacterial" data-addon-name="antibacterial"><span class="checkbox-card__label">Антибактериальная обработка системы</span><span class="checkbox-card__price">+500 ₽</span></label>
							<label class="checkbox-card"><input class="checkbox-card__control" type="checkbox" name="extraCircuit" data-addon-name="extraCircuit"><span class="checkbox-card__label">Дополнительный контур</span><span class="checkbox-card__price">+70%</span></label>
						</div>
					</fieldset>

					<fieldset class="calculator__section calculator__section--details" data-service-section="storage-addons" hidden>
						<legend class="calculator__legend">Дополнительно</legend>
						<div class="checkbox-grid">
							<label class="checkbox-card"><input class="checkbox-card__control" type="checkbox" name="storageDelivery" data-addon-name="storageDelivery"><span class="checkbox-card__label">Доставка</span><span class="checkbox-card__price">+3 500 ₽</span></label>
						</div>
					</fieldset>

					<button class="calculator__footer calculator__section--details" type="submit" data-order-submit>
						<span class="order-summary" aria-live="polite">
							<span class="order-summary__item"><span class="order-summary__label">Стоимость</span><span class="order-summary__line"><span data-price-prefix>от</span><output class="order-summary__value" data-price-output for="diameter-range">2 700</output><span data-price-unit>₽</span></span></span>
							<span class="order-summary__item"><span class="order-summary__label">Время прибытия</span><span class="order-summary__line"><output class="order-summary__value" data-time-output>—</output><span data-time-unit>мин</span></span></span>
						</span>
						<svg class="icon-circle icon-circle--small" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
							<path fill="currentColor" d="M4 12h12.25L11 6.75l.66-.75l6.5 6.5l-6.5 6.5l-.66-.75L16.25 13H4z"></path>
						</svg>
					</button>
				</form>

				<?php if ($order_form_shortcode) : ?>
					<div class="order-confirm" data-order-form hidden>
						<h2 class="order-confirm__title">Подтверждение заявки</h2>
						<dl class="order-details" data-order-details></dl>
						<label class="personal-consent">
							<input class="personal-consent__control" type="checkbox" name="personal_data_consent" value="yes" required data-personal-data-consent>
							<span class="personal-consent__text"><?php echo esc_html($personal_data_consent_text); ?></span>
						</label>
						<?php echo do_shortcode($order_form_shortcode); ?>
					</div>
				<?php else : ?>
					<form class="order-confirm" action="#" data-order-form hidden>
						<h2 class="order-confirm__title">Подтверждение заявки</h2>
						<dl class="order-details" data-order-details></dl>
						<input type="hidden" name="order_service" data-order-field="service_label">
						<input type="hidden" name="order_service_key" data-order-field="service">
						<input type="hidden" name="order_address" data-order-field="address">
						<input type="hidden" name="order_car_type" data-order-field="car_type_label">
						<input type="hidden" name="order_car_type_key" data-order-field="car_type">
						<input type="hidden" name="order_diameter" data-order-field="diameter_label">
						<input type="hidden" name="order_diameter_key" data-order-field="diameter">
						<input type="hidden" name="order_addons" data-order-field="addons">
						<input type="hidden" name="order_addon_keys" data-order-field="addon_keys">
						<input type="hidden" name="order_price" data-order-field="price_text">
						<input type="hidden" name="order_price_value" data-order-field="price">
						<input type="hidden" name="order_callout_price_value" data-order-field="callout_price">
						<input type="hidden" name="order_service_price_value" data-order-field="service_price">
						<input type="hidden" name="order_arrival_time" data-order-field="arrival_time">
						<input type="hidden" name="order_tariff" data-order-field="tariff">
						<input type="hidden" name="order_is_night_tariff" data-order-field="is_night_tariff">
						<input type="hidden" name="order_location_status" data-order-field="location_status">
						<input type="hidden" name="order_inside_mkad" data-order-field="inside_mkad">
						<input type="hidden" name="order_distance_outside_mkad_km" data-order-field="distance_outside_mkad_km">
						<input type="hidden" name="order_coords" data-order-field="coords">
						<input type="hidden" name="order_phone" data-order-field="phone">
						<input type="hidden" name="order_details" data-order-field="details">
						<input type="hidden" name="order_payload" data-order-field="payload">
						<label class="phone-field" for="order-phone"><span class="phone-field__label">Телефон для связи</span><input class="phone-field__input" id="order-phone" type="tel" name="phone" inputmode="tel" autocomplete="tel" placeholder="+7 (___) ___-__-__"></label>
						<label class="personal-consent">
							<input class="personal-consent__control" type="checkbox" name="personal_data_consent" value="yes" required data-personal-data-consent>
							<span class="personal-consent__text"><?php echo esc_html($personal_data_consent_text); ?></span>
						</label>
						<div class="order-confirm__footer"><button class="secondary-button" type="button" data-order-back>Назад</button><button class="primary-button" type="submit" data-order-send disabled>Отправить</button></div>
					</form>
				<?php endif; ?>

				<div class="order-success" data-order-success hidden>
					<span class="order-success__icon" aria-hidden="true"></span>
					<h2 class="order-success__title"><?php echo esc_html($order_success_title); ?></h2>
					<p class="order-success__text"><?php echo esc_html($order_success_text); ?></p>
					<div class="order-success__summary" data-success-summary></div>
					<button class="primary-button" type="button" data-order-success-back>Вернуться к заказу</button>
				</div>
			</section>

			<div class="map-picker" data-map-picker hidden>
				<div class="map-picker__panel" role="dialog" aria-modal="true" aria-labelledby="map-picker-title">
					<div class="map-picker__header">
						<h2 class="map-picker__title" id="map-picker-title">Выберите точку на карте</h2><button class="map-picker__close" type="button" aria-label="Закрыть карту" data-map-picker-close><span aria-hidden="true">×</span></button>
					</div>
					<div class="map-picker__map">
						<div class="map-picker__canvas" data-map-picker-canvas></div>
						<div class="map-picker__zoom-controls" aria-label="Масштаб карты"><button class="map-picker__zoom-button" type="button" aria-label="Приблизить карту" data-map-picker-zoom="in">+</button><button class="map-picker__zoom-button" type="button" aria-label="Отдалить карту" data-map-picker-zoom="out">-</button></div>
					</div>
					<p class="map-picker__status" data-map-picker-status aria-live="polite">Нажмите на карту, чтобы выбрать адрес</p>
					<div class="map-picker__footer"><button class="secondary-button" type="button" data-map-picker-cancel>Отмена</button><button class="primary-button" type="button" data-map-picker-apply disabled>Выбрать адрес</button></div>
				</div>
			</div>

			<aside class="trust-panel" aria-label="Показатели сервиса">
				<div class="trust-panel__item"> <svg
						class="icon-circle icon-circle--medium"
						xmlns="http://www.w3.org/2000/svg"
						viewBox="0 0 24 24">
						<path
							fill="currentColor"
							d="m8.125 7.092l2.608-3.47q.242-.324.568-.473T12 3t.699.149t.568.472l2.608 3.471l4.02 1.368q.534.18.822.602t.289.938q0 .242-.069.475t-.23.45l-2.634 3.573l.1 3.83q.025.703-.47 1.188q-.493.484-1.14.484l-.453-.056L12 18.733l-4.11 1.211q-.124.05-.236.053T7.437 20q-.666 0-1.15-.484q-.485-.485-.46-1.187l.1-3.856l-2.629-3.548q-.161-.217-.23-.45T3 10q0-.51.295-.941t.83-.618zm.629.86L4.462 9.398q-.289.096-.395.394t.087.548l2.792 3.84l-.119 4.16q-.02.327.23.52q.25.192.559.096L12 17.696l4.385 1.285q.307.096.557-.096q.25-.193.231-.52l-.12-4.184l2.793-3.79q.192-.25.087-.549q-.106-.298-.395-.394l-4.292-1.496l-2.765-3.683q-.173-.25-.481-.25t-.48.25zM12 11.519"></path>
					</svg><strong><?php echo esc_html($average_rating); ?></strong><span class="trust-panel__text">рейтинг</span></div>
				<div class="trust-panel__item"><svg
						class="icon-circle icon-circle--medium"
						xmlns="http://www.w3.org/2000/svg"
						viewBox="0 0 24 24">
						<path
							fill="currentColor"
							d="M12 20.962q-3.014-.895-5.007-3.651T5 11.1V5.692l7-2.615l7 2.615V11.1q0 3.454-1.993 6.21T12 20.963m0-1.062q2.6-.825 4.3-3.3t1.7-5.5V6.375l-6-2.23l-6 2.23V11.1q0 3.025 1.7 5.5t4.3 3.3m0-7.88"></path>
					</svg><strong><?php echo esc_html($successful_visits_count); ?></strong><span class="trust-panel__text">успешных выездов</span></div>
				<div class="trust-panel__item"><svg
						class="icon-circle icon-circle--medium"
						xmlns="http://www.w3.org/2000/svg"
						viewBox="0 0 24 24">
						<path
							fill="currentColor"
							d="M14.935 16.223L11.5 12.789V7.923h1v4.464l3.123 3.123zM11.5 6V4h1v2zm6.5 6.5v-1h2v1zM11.5 20v-2h1v2zM4 12.5v-1h2v1zm8.003 8.5q-1.867 0-3.51-.708q-1.643-.709-2.859-1.924t-1.925-2.856T3 12.003t.709-3.51Q4.417 6.85 5.63 5.634t2.857-1.925T11.997 3t3.51.709q1.643.708 2.859 1.922t1.925 2.857t.709 3.509t-.708 3.51t-1.924 2.859t-2.856 1.925t-3.509.709M12 20q3.35 0 5.675-2.325T20 12t-2.325-5.675T12 4T6.325 6.325T4 12t2.325 5.675T12 20m0-8"></path>
					</svg><strong><?php echo esc_html($work_schedule); ?></strong><span class="trust-panel__text">работаем круглосуточно</span></div>
			</aside>

			<div class="cookie-consent" data-cookie-consent role="dialog" aria-live="polite" aria-label="Согласие на использование cookie" hidden>
				<div class="cookie-consent__content">
					<h2 class="cookie-consent__title"><?php echo esc_html($cookie_consent_title); ?></h2>
					<p class="cookie-consent__text"><?php echo esc_html($cookie_consent_text); ?></p>
				</div>
				<button class="primary-button cookie-consent__button" type="button" data-cookie-consent-accept><?php echo esc_html($cookie_consent_button_text); ?></button>
			</div>
		</section>

		<section class="screen reviews-screen" data-screen="reviews" aria-label="Отзывы">
			<header class="reviews-hero">
				<h2 class="reviews-title">Отзывы</h2>
				<div class="reviews-overview">
					<div class="reviews-overview__rating" aria-label="<?php echo esc_attr(sprintf('Рейтинг %s из 5', $average_rating)); ?>"><svg
							class="icon-circle icon-circle--medium"
							xmlns="http://www.w3.org/2000/svg"
							viewBox="0 0 8 8">
							<path
								fill="currentColor"
								d="M1.6622 7.46132L2.2822 4.80482L0.220703 3.01882L2.9362 2.78382L3.9997 0.27832L5.0632 2.78332L7.7782 3.01832L5.7167 4.80432L6.3372 7.46082L3.9997 6.05082L1.6622 7.46132Z"></path>
						</svg><span class="reviews-overview__score"><?php echo esc_html($average_rating); ?></span><span class="reviews-overview__caption">из 5</span></div>
					<div class="reviews-overview__count"><strong><?php echo esc_html($yandex_reviews_count); ?></strong><span>отзывов</span><small>на Яндекс Картах</small></div>
				</div>
			</header>
			<div class="review-filters" data-review-filters aria-label="Фильтр отзывов по типу ремонта"><button class="review-filter-card review-filter-card--active" type="button" data-review-filter="all" aria-pressed="true">Все отзывы</button><button class="review-filter-card" type="button" data-review-filter="seasonal" aria-pressed="false">Сезонная замена</button><button class="review-filter-card" type="button" data-review-filter="puncture" aria-pressed="false">Ремонт прокола</button><button class="review-filter-card" type="button" data-review-filter="storage" aria-pressed="false">Хранение шин</button><button class="review-filter-card" type="button" data-review-filter="conditioner" aria-pressed="false">Заправка кондиционера</button></div>
			<section class="reviews-feed" aria-label="Отзывы клиентов">
				<div class="reviews-list" data-reviews-list><?php if ($reviews_query->have_posts()) : while ($reviews_query->have_posts()) : $reviews_query->the_post();
																	edemchinim_render_review_card(get_post());
																endwhile;
																wp_reset_postdata();
															endif; ?></div>
				<div class="reviews-loader" data-reviews-status role="status" aria-live="polite"></div>
				<div class="reviews-sentinel" data-reviews-sentinel aria-hidden="true"></div>
			</section>
		</section>

		<section class="screen photo-screen" data-screen="photo" aria-label="Фото работ">
			<header class="photo-hero">
				<h2 class="photo-title">Фото</h2>
				<p class="photo-subtitle">Реальные выезды и выполненные работы</p>
			</header>
			<div class="review-filters" data-photo-filters aria-label="Фильтр фото по типу ремонта"><button class="review-filter-card review-filter-card--active" type="button" data-photo-filter="all" aria-pressed="true">Все фото</button><button class="review-filter-card" type="button" data-photo-filter="seasonal" aria-pressed="false">Сезонная замена</button><button class="review-filter-card" type="button" data-photo-filter="puncture" aria-pressed="false">Ремонт прокола</button><button class="review-filter-card" type="button" data-photo-filter="storage" aria-pressed="false">Хранение шин</button><button class="review-filter-card" type="button" data-photo-filter="conditioner" aria-pressed="false">Заправка кондиционера</button></div>
			<section class="photo-feed" aria-label="Фото выполненных работ">
				<div class="photo-grid" data-photo-list><?php if ($photos_query->have_posts()) : $photo_index = 1;
															while ($photos_query->have_posts()) : $photos_query->the_post();
																edemchinim_render_photo_card(get_post(), $photo_index);
																$photo_index++;
															endwhile;
															wp_reset_postdata();
														endif; ?></div>
				<div class="photo-loader" data-photo-status role="status" aria-live="polite"></div>
				<div class="photo-sentinel" data-photo-sentinel aria-hidden="true"></div>
			</section>
		</section>

		<section class="screen faq-screen" data-screen="faq" aria-label="FAQ">
			<header class="faq-hero">
				<h2 class="faq-title">FAQ</h2>
				<p class="faq-subtitle">Ответы на популярные вопросы</p><label class="faq-search" for="faq-search"><svg
						class="icon-circle icon-circle--small"
						xmlns="http://www.w3.org/2000/svg"
						viewBox="0 0 24 24">
						<path
							fill="currentColor"
							d="m19.485 20.154l-6.262-6.262q-.75.639-1.725.989t-1.96.35q-2.398 0-4.064-1.666Q3.808 11.898 3.808 9.5t1.666-4.064t4.064-1.667t4.065 1.667T15.269 9.5q0 1.042-.369 2.017t-.97 1.668l6.262 6.261zM9.539 14.23q1.99 0 3.36-1.37t1.37-3.361t-1.37-3.36t-3.36-1.37t-3.361 1.37t-1.37 3.36t1.37 3.36t3.36 1.37"></path>
					</svg><input class="faq-search__input" id="faq-search" type="search" name="faq-search" placeholder="Поиск по вопросам и ответам" autocomplete="off" data-faq-search></label>
			</header>
			<section class="faq-feed" aria-label="Вопросы и ответы">
				<div class="faq-list" data-faq-list><?php if ($faq_query->have_posts()) : $faq_index = 0;
														while ($faq_query->have_posts()) : $faq_query->the_post();
															edemchinim_render_faq_item(get_post(), $faq_index === 0);
															$faq_index++;
														endwhile;
														wp_reset_postdata();
													endif; ?></div>
				<div class="faq-loader" data-faq-status role="status" aria-live="polite"></div>
				<div class="faq-sentinel" data-faq-sentinel aria-hidden="true"></div>
			</section>
		</section>
	</div>
</main>

<?php
get_footer();
