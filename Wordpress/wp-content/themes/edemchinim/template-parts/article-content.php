<?php
/** Filtered editor content and optional automatic table of contents. */
$html = $args['html'] ?? '';
$items = $args['items'] ?? array();
?>
<section class="article-content landing-section" aria-label="Текст статьи">
    <div class="article-content__layout<?php echo $items ? '' : ' article-content__layout--full'; ?>">
        <div class="article-content__body">
            <?php echo $html; // Already passed through WordPress the_content filters in the parent template. ?>
        </div>
        <?php if ($items) : ?>
        <aside class="article-content__sidebar">
            <nav class="article-content__contents" aria-labelledby="article-contents-title">
                <h2 class="landing-subtitle" id="article-contents-title">В статье</h2>
                <ol>
                    <?php foreach ($items as $item) : ?>
                    <li><a href="#<?php echo esc_attr($item['id']); ?>"><?php echo esc_html($item['title']); ?></a></li>
                    <?php endforeach; ?>
                </ol>
            </nav>
        </aside>
        <?php endif; ?>
    </div>
    <a class="article-content__back landing-more-link" href="<?php echo esc_url(edemchinim_articles_url()); ?>">
        <?php edemchinim_landing_arrow('left'); ?>
        <span>Все статьи</span>
    </a>
</section>
