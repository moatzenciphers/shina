<?php
/**
 * Template Name: О компании
 * Template Post Type: page
 */
use Edemchinim\Content\About;
use Edemchinim\Content\Blocks;

$page_id = get_queried_object_id();
get_header();
?>
<main class="landing__main" id="primary">
    <?php if (function_exists('have_rows') && have_rows('blocks', $page_id)) :
        Blocks::render($page_id);
    else :
        get_template_part('template-parts/pages/about', null, array('content' => About::content(), 'images' => About::images(), 'page_id' => $page_id));
    endif; ?>
</main>
<?php get_footer(); ?>
