<?php
/** Published reviews archive, with the native WordPress query. */
global $wp_query;
get_template_part('template-parts/support-page', null, array('kind' => 'reviews', 'settings_id' => 'reviews-archive', 'query' => $wp_query));
