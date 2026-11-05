<?php
/**
 * Template Name: Отзывы клиентов
 * Template Post Type: page
 */
get_template_part('template-parts/support-page', null, array('kind' => 'reviews', 'settings_id' => get_queried_object_id()));
