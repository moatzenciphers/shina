<?php
global $wp_query;
get_template_part('template-parts/pages/support', null, array('kind' => 'reviews', 'settings_id' => 'reviews-archive', 'query' => $wp_query));
