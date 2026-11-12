<?php
use Edemchinim\Content\Articles;
use Edemchinim\Content\Blocks;
use Edemchinim\Support\Fields;

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
            $content = Articles::contents($html, ! $protected && $show_contents);
            $hero_image = Blocks::imageId(Fields::get('article_hero_image', $article_id, get_post_thumbnail_id()));
            $lead = $protected ? '' : Fields::get('article_lead', $article_id, wp_trim_words(wp_strip_all_tags(get_the_excerpt()), 32, '…'));
            $author_label = Fields::get('article_author_label', $article_id, get_the_author());
            preg_match_all('/[\p{L}\p{N}]+/u', wp_strip_all_tags($html), $words);
            $reading_minutes = max(1, (int) ceil(count($words[0]) / 180));
        ?>
        <article aria-labelledby="article-title">
            <?php 

 $hero_image = absint($hero_image); ?>
<header class="article-hero" aria-labelledby="article-title">
    <?php if ($hero_image) : ?>
    <div class="article-hero__background" aria-hidden="true"><?php echo wp_get_attachment_image($hero_image, 'full', false, array('alt' => '', 'loading' => 'eager', 'fetchpriority' => 'high')); ?></div>
    <?php endif; ?>
    <div class="article-hero__inner landing-section">
        <nav class="article-hero__breadcrumbs" aria-label="Хлебные крошки">
            <a href="<?php echo esc_url(home_url('/')); ?>">Главная</a>
            <svg viewBox="0 0 16 16" fill="none" aria-hidden="true"><path d="m6 3 5 5-5 5" stroke="currentColor" stroke-width="1.5"/></svg>
            <a href="<?php echo esc_url(Articles::url()); ?>">Статьи</a>
            <svg viewBox="0 0 16 16" fill="none" aria-hidden="true"><path d="m6 3 5 5-5 5" stroke="currentColor" stroke-width="1.5"/></svg>
            <span aria-current="page"><?php the_title(); ?></span>
        </nav>
        <div class="article-hero__copy">
            <p class="landing-eyebrow"><?php echo esc_html(Articles::category(get_the_ID())); ?></p>
            <h1 class="article-hero__title landing-title landing-title--hero" id="article-title"><?php the_title(); ?></h1>
            <div class="article-hero__meta">
                <time datetime="<?php echo esc_attr(get_the_date('c')); ?>"><?php echo esc_html(get_the_date()); ?></time>
                <?php if (! $protected) : ?><span><?php echo esc_html(sprintf('Около %d мин на чтение', $reading_minutes)); ?></span><?php endif; ?>
            </div>
        </div>
    </div>
</header>
            <section class="article-content landing-section" aria-label="Текст статьи">
                <div class="article-content__layout<?php echo $content['items'] ? '' : ' article-content__layout--full'; ?>">
                    <div class="article-content__body"><?php echo $content['html']; // Standard filtered post content. ?></div>
                    <?php if ($content['items']) : ?>
                    <aside class="article-content__sidebar">
                        <nav class="article-content__contents" aria-labelledby="article-contents-title">
                            <div class="landing-subtitle" id="article-contents-title">В статье</div>
                            <ol><?php foreach ($content['items'] as $item) : ?><li><a href="#<?php echo esc_attr($item['id']); ?>"><?php echo esc_html($item['title']); ?></a></li><?php endforeach; ?></ol>
                        </nav>
                    </aside>
                    <?php endif; ?>
                </div>
                <a class="article-content__back landing-more-link" href="<?php echo esc_url(Articles::url()); ?>"><?php Blocks::arrow('left'); ?><span>Все статьи</span></a>
            </section>
        </article>
        <?php if (! $protected) :
            $related = Fields::get('article_service', $article_id, 0);
            $service_id = $related instanceof WP_Post ? $related->ID : absint($related);
            if ($service_id && get_post_type($service_id) === 'services' && get_post_status($service_id) === 'publish') {

$related_service_id = absint($service_id);
$related_service_description = Fields::get('service_card_description', $related_service_id, wp_trim_words(wp_strip_all_tags(get_the_excerpt($related_service_id)), 30, '…'));
?>
<section class="related-service landing-section" aria-labelledby="related-service-title">
    <div class="related-service__inner">
        <div class="related-service__copy">
            <p class="landing-eyebrow">УСЛУГА ПО ТЕМЕ СТАТЬИ</p>
            <h2 class="related-service__title landing-title landing-title--section" id="related-service-title"><?php echo esc_html(get_the_title($related_service_id)); ?></h2>
            <?php if ($related_service_description) : ?><p class="related-service__text landing-text"><?php echo esc_html($related_service_description); ?></p><?php endif; ?>
        </div>
        <a class="related-service__cta landing-cta" href="<?php echo esc_url(get_permalink($related_service_id)); ?>"><span>Подробнее об услуге</span><?php Blocks::arrow(); ?></a>
    </div>
</section>
<?php
            }
            $root = Articles::root();
            $root_settings = $root ? 'category_' . $root->term_id : false;
            $shortcode = Fields::get('article_form_shortcode', $article_id);
            if (! $shortcode && $root_settings) $shortcode = Fields::get('articles_form_shortcode', $root_settings);
            if ($shortcode) get_template_part('template-parts/blocks/split-form', null, array('form' => array(
                'title' => Fields::get('article_form_title', $article_id, 'Нужна помощь с автомобилем?'),
                'lead' => Fields::get('article_form_lead', $article_id, 'Опишите ситуацию. Оператор уточнит детали и согласует необходимую помощь.'),
                'shortcode' => $shortcode,
            )));
        endif;
        endwhile; ?>
    </main>
</div>
<?php get_footer();
