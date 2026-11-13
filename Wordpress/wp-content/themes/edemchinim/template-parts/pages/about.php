<?php
use Edemchinim\Calculator\Config;
use Edemchinim\Content\Blocks;
use Edemchinim\Support\Fields;

$content = $args['content'];
$images = $args['images'];
$page_id = $args['page_id'];
$hero = $content['hero'];
$hero_image = get_post_thumbnail_id($page_id) ?: ($images[0] ?? 0);
$hero_url = $hero_image ? wp_get_attachment_image_url($hero_image, 'full') : '';
?>
<section class="landing-hero landing-hero--service" aria-labelledby="landing-hero-title">
    <?php if ($hero_url) : ?><picture class="landing-hero__picture" aria-hidden="true"><img src="<?php echo esc_url($hero_url); ?>" alt="" decoding="async" fetchpriority="high"></picture><?php endif; ?>
    <div class="landing-hero__inner"><div class="landing-hero__content" id="top">
        <p class="landing-eyebrow"><?php echo esc_html($hero['eyebrow']); ?></p>
        <h1 class="landing-title landing-title--hero" id="landing-hero-title"><?php echo esc_html($hero['title']); ?></h1>
        <p class="landing-text landing-text--hero"><?php echo esc_html($hero['lead']); ?></p>
        <ul class="landing-hero__benefits"><?php foreach ($hero['benefits'] as $benefit) : ?><li><div><strong><?php echo esc_html($benefit['title']); ?></strong><span><?php echo esc_html($benefit['text']); ?></span></div></li><?php endforeach; ?></ul>
        <div class="landing-hero__visual-space" aria-hidden="true"></div>
        <a class="landing-cta" href="<?php echo esc_url(Config::url()); ?>" data-open-calculator><span><?php echo esc_html($hero['button']); ?></span><?php Blocks::arrow(); ?></a>
        <p class="service-page__price-note landing-text"><?php echo esc_html($hero['note']); ?></p>
    </div></div>
</section>
<?php $columns = $content['columnsList']; ?>
<section class="columns-list landing-section" aria-labelledby="columns-list-title">
    <header class="columns-list__header">
        <p class="columns-list__eyebrow landing-eyebrow"><?php echo esc_html($columns['eyebrow']); ?></p>
        <h2 class="columns-list__title landing-title landing-title--section" id="columns-list-title"><?php echo esc_html($columns['title']); ?></h2>
        <p class="columns-list__description landing-text"><?php echo esc_html($columns['description']); ?></p>
    </header>
    <div class="columns-list__columns"><?php foreach ($columns['columns'] as $column) : ?>
        <div class="columns-list__column"><h3 class="columns-list__subtitle landing-subtitle"><?php echo esc_html($column['title']); ?></h3><ul class="columns-list__items"><?php foreach ($column['items'] as $item) : ?><li class="columns-list__item"><p class="landing-text"><?php echo esc_html($item); ?></p></li><?php endforeach; ?></ul></div>
    <?php endforeach; ?></div>
    <div class="columns-list__footer"><p class="columns-list__note landing-text"><?php echo esc_html($columns['note']); ?></p><a class="landing-more-link" href="<?php echo esc_url(home_url('/prices/')); ?>">Все цены на услуги<?php Blocks::arrow(); ?></a></div>
</section>
<?php $media = $content['mediaRows']; ?>
<section class="media-rows landing-section" aria-labelledby="media-rows-title">
    <header class="media-rows__header"><p class="media-rows__eyebrow landing-eyebrow"><?php echo esc_html($media['eyebrow']); ?></p><h2 class="media-rows__title landing-title landing-title--section" id="media-rows-title"><?php echo esc_html($media['title']); ?></h2></header>
    <div class="media-rows__items"><?php foreach ($media['rows'] as $index => $row) : $image_id = $images[$index + 1] ?? ($images[$index] ?? 0); ?>
        <div class="<?php echo $image_id ? 'media-rows__row' : 'columns-list__column'; ?>">
            <?php if ($image_id) echo wp_get_attachment_image($image_id, 'large', false, array('class' => 'media-rows__image', 'loading' => 'lazy', 'decoding' => 'async')); ?>
            <ul class="media-rows__list"><?php foreach ($row['points'] as $point) : ?><li class="media-rows__point"><h3 class="media-rows__point-title landing-subtitle"><?php echo esc_html($point['title']); ?></h3><p class="media-rows__point-text landing-text"><?php echo esc_html($point['text']); ?></p></li><?php endforeach; ?></ul>
        </div>
    <?php endforeach; ?></div>
</section>
<?php get_template_part('template-parts/blocks/split-form', null, array('form' => array(
    'title' => $content['form']['title'],
    'lead' => $content['form']['lead'],
    'shortcode' => Fields::get('support_form_shortcode', $page_id, '[contact-form-7 id="93c12f0" title="Форма в CTA"]'),
))); ?>
