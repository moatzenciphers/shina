<?php
$settings_id = $args['settings_id'];
$has_articles = have_posts();
$title = edemchinim_get_field('articles_title', $settings_id, single_cat_title('', false));
$intro = edemchinim_get_field($has_articles ? 'articles_intro' : 'articles_empty_intro', $settings_id);
global $wp_query;
$current_page = max(1, absint(get_query_var('paged')));
?>
<section class="articles-grid landing-section" aria-labelledby="articles-title">
    <nav class="articles-grid__breadcrumbs" aria-label="Хлебные крошки">
        <a href="<?php echo esc_url(home_url('/')); ?>">Главная</a>
        <svg viewBox="0 0 16 16" fill="none" aria-hidden="true"><path d="m6 3 5 5-5 5" stroke="currentColor" stroke-width="1.5"/></svg>
        <span aria-current="page"><?php echo esc_html($title); ?></span>
    </nav>
    <header class="articles-grid__header">
        <p class="landing-eyebrow">ПОЛЕЗНО АВТОВЛАДЕЛЬЦУ</p>
        <h1 class="articles-grid__title landing-title" id="articles-title"><?php echo esc_html($title); ?></h1>
        <?php if ($intro) : ?><p class="articles-grid__intro landing-text"><?php echo esc_html($intro); ?></p><?php endif; ?>
    </header>
    <?php if ($has_articles) : ?>
    <div class="articles-grid__items">
        <?php while (have_posts()) : the_post(); ?>
        <article class="articles-grid__card">
            <div class="articles-grid__image-wrap">
                <?php if (has_post_thumbnail()) : echo get_the_post_thumbnail(get_the_ID(), 'large', array('class' => 'articles-grid__image', 'loading' => 'lazy', 'decoding' => 'async')); else : ?>
                    <svg class="articles-grid__image" viewBox="0 0 640 400" fill="none" aria-hidden="true"><path d="M250 100h100l40 40v160H250V100Zm100 0v50h40M280 190h80m-80 40h80m-80 40h40" stroke="currentColor" stroke-width="3"/></svg>
                <?php endif; ?>
            </div>
            <div class="articles-grid__body">
                <div class="articles-grid__meta"><span class="articles-grid__category"><?php echo esc_html(edemchinim_article_category(get_the_ID())); ?></span><time datetime="<?php echo esc_attr(get_the_date('c')); ?>"><?php echo esc_html(get_the_date()); ?></time></div>
                <a class="articles-grid__link articles-grid__card-title landing-subtitle" href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                <?php $excerpt = wp_trim_words(wp_strip_all_tags(get_the_excerpt()), 30, '…'); ?>
                <?php if ($excerpt) : ?><p class="articles-grid__excerpt landing-text"><?php echo esc_html($excerpt); ?></p><?php endif; ?>
                <span class="articles-grid__more landing-more-link" aria-hidden="true">Читать статью<?php edemchinim_landing_arrow(); ?></span>
            </div>
        </article>
        <?php endwhile; ?>
    </div>
    <?php if ($wp_query->max_num_pages > 1) : ?>
    <nav class="articles-grid__pagination" aria-label="Страницы статей">
        <?php
        $left_arrow = '<svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M20 12H4m6-6-6 6 6 6" stroke="currentColor" stroke-width="1.5"/></svg><span class="visually-hidden">Предыдущая страница</span>';
        $right_arrow = '<svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M4 12h16m-6-6 6 6-6 6" stroke="currentColor" stroke-width="1.5"/></svg><span class="visually-hidden">Следующая страница</span>';
        if ($current_page === 1) echo '<span class="articles-grid__page articles-grid__page--disabled" aria-disabled="true">' . $left_arrow . '</span>';
        $pagination = paginate_links(array('current' => $current_page, 'total' => (int) $wp_query->max_num_pages, 'type' => 'array', 'prev_text' => $left_arrow, 'next_text' => $right_arrow, 'aria_current' => 'page'));
        foreach ((array) $pagination as $link) {
            $classes = 'articles-grid__page';
            if (strpos($link, 'aria-current=') !== false) $classes .= ' articles-grid__page--current';
            if (strpos($link, 'dots') !== false) $classes .= ' articles-grid__page--disabled';
            echo str_replace('class="', 'class="' . esc_attr($classes) . ' ', $link); // Core-generated, escaped pagination HTML.
        }
        if ($current_page >= $wp_query->max_num_pages) echo '<span class="articles-grid__page articles-grid__page--disabled" aria-disabled="true">' . $right_arrow . '</span>';
        ?>
    </nav>
    <?php endif; ?>
    <?php else : ?>
    <div class="articles-grid__empty">
        <svg class="articles-grid__empty-icon" viewBox="0 0 64 64" fill="none" aria-hidden="true"><path d="M16 8h23l10 10v38H16V8Zm23 0v12h10M24 30h17M24 38h17M24 46h10" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
        <h2 class="landing-title landing-title--section"><?php echo esc_html(edemchinim_get_field('articles_empty_title', $settings_id, 'Здесь пока нет статей')); ?></h2>
        <p class="articles-grid__empty-text landing-text"><?php echo esc_html(edemchinim_get_field('articles_empty_text', $settings_id, 'Скоро здесь появятся первые публикации. А пока можно посмотреть доступные услуги.')); ?></p>
        <?php $services_url = get_post_type_archive_link('services'); if ($services_url) : ?><a class="landing-cta" href="<?php echo esc_url($services_url); ?>"><span>Все услуги</span><?php edemchinim_landing_arrow(); ?></a><?php endif; ?>
    </div>
    <?php endif; ?>
</section>
