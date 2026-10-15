<?php
/** Shared flexible-content output for pages and services. */
get_header();
?>
<div class="landing landing--wp-header" data-landing>
	<main class="landing__main">
		<?php edemchinim_render_landing_blocks(get_queried_object_id()); ?>
	</main>
	<?php get_template_part('template-parts/landing-mobile-actions'); ?>
</div>
<?php
get_template_part('template-parts/landing-quick-call');
get_footer();
