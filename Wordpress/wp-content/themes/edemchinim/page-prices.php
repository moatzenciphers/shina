<?php
/**
 * Template Name: Все цены по услугам
 * Template Post Type: page
 *
 * The existing /prices/ page also resolves this template by its slug.
 */
use Edemchinim\Calculator\Config;
use Edemchinim\Content\Blocks;
use Edemchinim\Content\Prices;

$groups = Prices::groups();
$page_id = get_queried_object_id();
$title = get_the_title($page_id) ?: 'Цены на услуги';
$intro = (string) get_post_field('post_content', $page_id);
get_header();
?>
    <main class="prices-page support-page landing-section" id="primary">
        <header class="support-page__header">
            <nav class="support-page__breadcrumbs" aria-label="Хлебные крошки"><a href="<?php echo esc_url(home_url('/')); ?>">Главная</a><span aria-hidden="true">/</span><span aria-current="page"><?php echo esc_html($title); ?></span></nav>
            <h1 class="support-page__title landing-title"><?php echo esc_html($title); ?></h1>
            <?php if (trim($intro) !== '') : ?><div class="prices-page__intro landing-text"><?php echo apply_filters('the_content', $intro); ?></div><?php endif; ?>
        </header>
        <?php if ($groups) : ?>
        <div class="prices-page__layout">
            <nav class="prices-page__nav" aria-label="Цены по услугам"><ul>
                <?php foreach ($groups as $group) : ?><li><a href="#price-service-<?php echo esc_attr($group['service']->ID); ?>"><?php echo esc_html(get_the_title($group['service'])); ?></a></li><?php endforeach; ?>
            </ul></nav>
            <div class="prices-page__services">
                <?php foreach ($groups as $group) :
                    $service = $group['service'];
                    $service_title = get_the_title($service);
                    $section_id = 'price-service-' . $service->ID;
                ?>
                <section class="prices-page__service" id="<?php echo esc_attr($section_id); ?>" aria-labelledby="<?php echo esc_attr($section_id . '-title'); ?>">
                    <h2 class="prices-page__service-title landing-title landing-title--section" id="<?php echo esc_attr($section_id . '-title'); ?>"><?php echo esc_html($service_title); ?></h2>
                    <?php if ($group['main']) : ?><div class="prices-page__table-scroll" role="region" tabindex="0" aria-label="<?php echo esc_attr('Стоимость: ' . $service_title); ?>"><?php Prices::renderTable($group['main'], $service_title); ?></div><?php endif; ?>
                    <?php if ($group['additional']) : ?>
                    <h3 class="prices-page__additional-title landing-subtitle">Доплаты</h3>
                    <div class="prices-page__table-scroll" role="region" tabindex="0" aria-label="<?php echo esc_attr('Доплаты: ' . $service_title); ?>"><?php Prices::renderTable($group['additional'], 'Доплаты — ' . $service_title, true); ?></div>
                    <?php endif; ?>
                    <a class="prices-page__service-link landing-more-link" href="<?php echo esc_url(get_permalink($service)); ?>">Подробнее об услуге<?php Blocks::arrow(); ?></a>
                </section>
                <?php endforeach; ?>
            </div>
        </div>
        <div class="prices-page__footer"><a class="landing-cta" href="<?php echo esc_url(Config::url()); ?>" data-open-calculator><span>Рассчитать стоимость</span><?php Blocks::arrow(); ?></a></div>
        <?php else : ?><p class="support-page__empty landing-text">Цены пока не добавлены.</p><?php endif; ?>
    </main>
<?php get_footer(); ?>
