<?php
/**
 * Flexible content landing page.
 *
 * The theme header and footer remain separate from the landing blocks.
 *
 * @package edemchinim
 */
get_header();
require_once get_template_directory() . '/inc/landing.php';
$landing_layouts = array(
	'hero', 'features_grid', 'steps_grid', 'cards_grid', 'media_rows',
	'slider_grid', 'map_panel', 'gallery_grid', 'accordion_grid', 'split_form',
);
?>
<div class="landing" data-landing>
	<main class="landing__main">
		<?php if (function_exists('have_rows') && have_rows('blocks', get_queried_object_id())) : ?>
			<?php while (have_rows('blocks', get_queried_object_id())) : the_row(); ?>
				<?php
				$layout = get_row_layout();
				if (in_array($layout, $landing_layouts, true)) {
					get_template_part('template-parts/landing-' . str_replace('_', '-', $layout));
				}
				?>
			<?php endwhile; ?>
		<?php endif; ?>
	</main>
	<?php get_template_part('template-parts/landing-mobile-actions'); ?>
</div>
<?php
get_template_part('template-parts/landing-quick-call');
get_template_part('template-parts/landing-calculator');
get_footer();
