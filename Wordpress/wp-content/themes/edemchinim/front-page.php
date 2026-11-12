<?php
/**
 * Flexible content landing page.
 *
 * The theme header and footer remain separate from the landing blocks.
 *
 * @package edemchinim
 */
get_header("");
?>
	<main class="landing__main">
		<?php if (function_exists('have_rows') && have_rows('blocks', get_queried_object_id())) : ?>
			<?php while (have_rows('blocks', get_queried_object_id())) : the_row(); ?>
				<?php
				$layout = get_row_layout();
				get_template_part('template-parts/blocks/' . str_replace('_', '-', $layout));
				?>
			<?php endwhile; ?>
		<?php endif; ?>
	</main>
<?php
get_footer("");
