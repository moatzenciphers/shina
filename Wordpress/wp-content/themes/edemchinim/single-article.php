<?php
/** Detail template selected only for posts in category articles or its children. */
get_header();
?>
<div class="landing landing--wp-header" data-landing>
    <main class="landing__main" id="primary">
        <?php while (have_posts()) : the_post();
            $article_id = get_the_ID();
            $protected = post_password_required();
            $html = apply_filters('the_content', get_the_content());
            $show_contents = ! metadata_exists('post', $article_id, 'article_show_contents') || (bool) get_post_meta($article_id, 'article_show_contents', true);
            $content = edemchinim_article_contents($html, ! $protected && $show_contents);
            $hero_image = edemchinim_landing_image_id(edemchinim_get_field('article_hero_image', $article_id, get_post_thumbnail_id()));
            $lead = $protected ? '' : edemchinim_get_field('article_lead', $article_id, wp_trim_words(wp_strip_all_tags(get_the_excerpt()), 32, '…'));
            $author_label = edemchinim_get_field('article_author_label', $article_id, get_the_author());
            preg_match_all('/[\p{L}\p{N}]+/u', wp_strip_all_tags($html), $words);
            $reading_minutes = max(1, (int) ceil(count($words[0]) / 180));
        ?>
        <article aria-labelledby="article-title">
            <?php get_template_part('template-parts/article-hero', null, compact('hero_image', 'lead', 'author_label', 'reading_minutes', 'protected')); ?>
            <section class="article-content landing-section" aria-label="Текст статьи">
                <div class="article-content__layout<?php echo $content['items'] ? '' : ' article-content__layout--full'; ?>">
                    <div class="article-content__body"><?php echo $content['html']; // Standard filtered post content. ?></div>
                    <?php if ($content['items']) : ?>
                    <aside class="article-content__sidebar">
                        <nav class="article-content__contents" aria-labelledby="article-contents-title">
                            <h2 class="landing-subtitle" id="article-contents-title">В статье</h2>
                            <ol><?php foreach ($content['items'] as $item) : ?><li><a href="#<?php echo esc_attr($item['id']); ?>"><?php echo esc_html($item['title']); ?></a></li><?php endforeach; ?></ol>
                        </nav>
                    </aside>
                    <?php endif; ?>
                </div>
                <a class="article-content__back landing-more-link" href="<?php echo esc_url(edemchinim_articles_url()); ?>"><?php edemchinim_landing_arrow('left'); ?><span>Все статьи</span></a>
            </section>
        </article>
        <?php if (! $protected) :
            $related = edemchinim_get_field('article_service', $article_id, 0);
            $service_id = $related instanceof WP_Post ? $related->ID : absint($related);
            if ($service_id && get_post_type($service_id) === 'services' && get_post_status($service_id) === 'publish') get_template_part('template-parts/related-service', null, array('service_id' => $service_id));
            $root = edemchinim_articles_root();
            $root_settings = $root ? 'category_' . $root->term_id : false;
            $shortcode = edemchinim_get_field('article_form_shortcode', $article_id);
            if (! $shortcode && $root_settings) $shortcode = edemchinim_get_field('articles_form_shortcode', $root_settings);
            if ($shortcode) get_template_part('template-parts/landing-split-form', null, array('form' => array(
                'title' => edemchinim_get_field('article_form_title', $article_id, 'Нужна помощь с автомобилем?'),
                'lead' => edemchinim_get_field('article_form_lead', $article_id, 'Опишите ситуацию. Оператор уточнит детали и согласует необходимую помощь.'),
                'shortcode' => $shortcode,
            )));
        endif;
        endwhile; ?>
    </main>
    <?php get_template_part('template-parts/landing-mobile-actions'); ?>
</div>
<?php get_template_part('template-parts/landing-quick-call'); get_footer();
