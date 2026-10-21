<?php
/** The ordinary category with slug articles; preserve its main query. */
$term = get_queried_object();
$settings_id = 'category_' . $term->term_id;
get_header();
?>
<div class="landing landing--wp-header" data-landing>
    <main class="landing__main" id="primary">
        <?php
        get_template_part('template-parts/articles-grid', null, array('settings_id' => $settings_id));
        edemchinim_render_landing_blocks($settings_id);
        ?>
    </main>
    <?php get_template_part('template-parts/landing-mobile-actions'); ?>
</div>
<?php
get_template_part('template-parts/landing-quick-call', null, array('settings_id' => $settings_id));
get_footer();
