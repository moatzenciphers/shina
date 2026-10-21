<?php $hero_image = absint($args['hero_image'] ?? 0); ?>
<header class="article-hero" aria-labelledby="article-title">
    <?php if ($hero_image) : ?>
    <div class="article-hero__background" aria-hidden="true"><?php echo wp_get_attachment_image($hero_image, 'full', false, array('alt' => '', 'loading' => 'eager', 'fetchpriority' => 'high')); ?></div>
    <?php endif; ?>
    <div class="article-hero__inner landing-section">
        <nav class="article-hero__breadcrumbs" aria-label="Хлебные крошки">
            <a href="<?php echo esc_url(home_url('/')); ?>">Главная</a>
            <svg viewBox="0 0 16 16" fill="none" aria-hidden="true"><path d="m6 3 5 5-5 5" stroke="currentColor" stroke-width="1.5"/></svg>
            <a href="<?php echo esc_url(edemchinim_articles_url()); ?>">Статьи</a>
            <svg viewBox="0 0 16 16" fill="none" aria-hidden="true"><path d="m6 3 5 5-5 5" stroke="currentColor" stroke-width="1.5"/></svg>
            <span aria-current="page"><?php the_title(); ?></span>
        </nav>
        <div class="article-hero__copy">
            <p class="landing-eyebrow"><?php echo esc_html(edemchinim_article_category(get_the_ID())); ?></p>
            <h1 class="article-hero__title landing-title landing-title--hero" id="article-title"><?php the_title(); ?></h1>
            <?php if ($args['lead']) : ?><p class="article-hero__lead landing-text"><?php echo esc_html($args['lead']); ?></p><?php endif; ?>
            <div class="article-hero__meta">
                <span><?php echo esc_html($args['author_label']); ?></span>
                <time datetime="<?php echo esc_attr(get_the_date('c')); ?>"><?php echo esc_html(get_the_date()); ?></time>
                <?php if (! $args['protected']) : ?><span><?php echo esc_html(sprintf('Около %d мин на чтение', $args['reading_minutes'])); ?></span><?php endif; ?>
            </div>
        </div>
    </div>
</header>
