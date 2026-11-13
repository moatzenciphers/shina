<?php
/**
 * Template Name: Все цены по услугам
 * Template Post Type: page
 *
 * The existing /prices/ page also resolves this template by its slug.
 */
use Edemchinim\Content\Blocks;
use Edemchinim\Content\Prices;
use Edemchinim\Support\Fields;

$groups = Prices::groups();
$page_id = get_queried_object_id();
$title = get_the_title($page_id) ?: 'Цены на услуги';
$intro = (string) get_post_field('post_content', $page_id);
get_header();
?>
    <main class="landing__main" id="primary">
    <section class="prices-page support-page landing-section" aria-labelledby="prices-page-title">
        <header class="support-page__header">
            <nav class="support-page__breadcrumbs" aria-label="Хлебные крошки"><a href="<?php echo esc_url(home_url('/')); ?>">Главная</a><span aria-hidden="true">/</span><span aria-current="page"><?php echo esc_html($title); ?></span></nav>
            <h1 class="support-page__title landing-title" id="prices-page-title"><?php echo esc_html($title); ?></h1>
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
                <details class="prices-page__service" id="<?php echo esc_attr($section_id); ?>">
                    <summary class="prices-page__summary">
                        <h2 class="prices-page__service-title landing-title" id="<?php echo esc_attr($section_id . '-title'); ?>"><?php echo esc_html($service_title); ?></h2>
                        <svg class="prices-page__chevron" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="m5 9 7 7 7-7" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </summary>
                    <div class="prices-page__body">
                    <div class="prices-page__panel">
                    <?php if ($group['main']) : ?><div class="prices-page__table-scroll" role="region" tabindex="0" aria-label="<?php echo esc_attr('Стоимость: ' . $service_title); ?>"><?php Prices::renderTable($group['main'], $service_title); ?></div><?php endif; ?>
                    <?php if ($group['additional']) : ?>
                    <h3 class="prices-page__additional-title landing-subtitle">Доплаты</h3>
                    <div class="prices-page__table-scroll" role="region" tabindex="0" aria-label="<?php echo esc_attr('Доплаты: ' . $service_title); ?>"><?php Prices::renderTable($group['additional'], 'Доплаты — ' . $service_title, true); ?></div>
                    <?php endif; ?>
                    <a class="prices-page__service-link landing-more-link" href="<?php echo esc_url(get_permalink($service)); ?>">Подробнее об услуге<?php Blocks::arrow(); ?></a>
                    </div>
                    </div>
                </details>
                <?php endforeach; ?>
            </div>
        </div>
        <?php else : ?><p class="support-page__empty landing-text">Цены пока не добавлены.</p><?php endif; ?>
    </section>
    <?php get_template_part('template-parts/blocks/split-form', null, array('form' => array(
        'title' => 'Нужна помощь с автомобилем?',
        'lead' => 'Опишите ситуацию. Оператор уточнит детали и согласует необходимую помощь.',
        'shortcode' => Fields::get('support_form_shortcode', $page_id, '[contact-form-7 id="93c12f0" title="Форма в CTA"]'),
    ))); ?>
    </main>
<?php get_footer(); ?>
