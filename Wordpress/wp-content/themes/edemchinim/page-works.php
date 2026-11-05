<?php
/**
 * Template Name: Фотографии работ
 * Template Post Type: page
 */
get_template_part('template-parts/support-page', null, array('kind' => 'works', 'settings_id' => get_queried_object_id()));
