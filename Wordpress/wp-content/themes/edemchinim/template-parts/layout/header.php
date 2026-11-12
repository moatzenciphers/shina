<?php
use Edemchinim\Calculator\Config;
use Edemchinim\Support\Fields;
use Edemchinim\Support\Formatting;

/** Shared header and WordPress menu. */
$header_phone = Fields::option('shina_phone', '+7 (000) 000-00-00');
$header_phone_href = Formatting::phoneHref($header_phone);
$menu_location = has_nav_menu('landing-primary') ? 'landing-primary' : 'menu-1';
$calculator_href = Config::url();
$calculator_config = Config::prepare(function_exists('get_field') ? get_field('shina_calculator_config', 'options') : array());
$crew_count = count($calculator_config['master_points'] ?? array());
$last_digits = $crew_count % 100;
$crew_word = $last_digits >= 11 && $last_digits <= 14 ? 'экипажей' : ($crew_count % 10 === 1 ? 'экипаж' : (in_array($crew_count % 10, array(2, 3, 4), true) ? 'экипажа' : 'экипажей'));
?>
<header class="landing-hero__header" data-site-header>
    <a class="landing-hero__brand" href="<?php echo esc_url(home_url('/')); ?>" aria-label="Едем-чиним — на главную">
		 <img class="landing-hero__brand-car" src="/wp-content/themes/edemchinim/img/logo.svg">
    </a>
    <a class="landing-hero__crews" href="<?php echo esc_url($calculator_href); ?>" data-open-calculator>
        <span class="landing-hero__activity" aria-hidden="true"></span><span><strong><?php echo esc_html($crew_count . ' ' . $crew_word); ?></strong> <span class="landing-hero__crews-note">на линии</span></span>
    </a>
    <nav class="landing-hero__nav" id="landing-hero-menu" aria-label="Разделы сайта" data-landing-menu>
        <?php
        wp_nav_menu(array(
            'theme_location' => $menu_location,
            'container' => false,
            'menu_class' => 'landing-hero__nav-list',
            'fallback_cb' => false,
            'depth' => 3,
            'walker' => new Edemchinim_Site_Nav_Walker(),
        ));
        ?>
        <div class="landing-hero__menu-footer">
            <p>Выездной шиномонтаж в Москве и МО. Работаем 24/7</p>
            <button class="landing-hero__cta landing-cta" data-quick-call-open>
                <span>Вызвать мастера</span>
                <svg viewBox="0 0 32 32" fill="none" aria-hidden="true"><path d="M5 16h21m-8-8 8 8-8 8" stroke="currentColor" stroke-width="2" stroke-linecap="square"/></svg>
            </button>
        </div>
    </nav>
    <div class="landing-hero__header-actions">
        <a class="landing-hero__phone" href="<?php echo esc_url($header_phone_href); ?>" aria-label="<?php echo esc_attr(sprintf('Позвонить: %s', $header_phone)); ?>">
            <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M6.6 2.5a2 2 0 0 0-2.1.5L2.9 4.6a3 3 0 0 0-.7 3.1c2.2 6.4 7.4 11.6 13.8 13.8a3 3 0 0 0 3.1-.7l1.6-1.6a2 2 0 0 0 .5-2.1l-.9-2.7a2 2 0 0 0-2.1-1.3l-2.8.3a2 2 0 0 0-1.2.6l-1 1a15 15 0 0 1-4.2-4.2l1-1a2 2 0 0 0 .6-1.2l.3-2.8a2 2 0 0 0-1.3-2.1z"/></svg>
            <span><?php echo esc_html($header_phone); ?></span>
        </a>
        <button class="landing-hero__header-cta" data-quick-call-open><span>Вызвать мастера</span></button>
        <button class="landing-hero__menu-toggle" type="button" aria-label="Открыть меню" aria-expanded="false" aria-controls="landing-hero-menu" data-landing-menu-toggle><span></span><span></span><span></span></button>
    </div>
</header>
