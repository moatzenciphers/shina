<?php
use Edemchinim\Support\Fields;
use Edemchinim\Support\Formatting;

/** Shared WordPress footer. Menu top-level items are collapsible groups. */
$footer_phone = Fields::option('shina_phone', '');
$footer_email = Fields::option('shina_email', '');
?>
<footer class="site-footer site-footer--wp" id="contacts">
    <div class="site-footer__panel">
        <div class="site-footer__top">
            <div class="site-footer__about">
                <a class="site-footer__brand" href="<?php echo esc_url(home_url('/')); ?>" aria-label="Едем–чиним — на главную">
                    <img class="site-footer__brand-car" loading="lazy" src="<?=get_template_directory_uri() . "/img/logo.svg" ?>">
                </a>
                <p class="site-footer__description">Выездной шиномонтаж и помощь на дороге в Москве и МО 24/7.</p>
                <p class="site-footer__contacts">
                    <?php if ($footer_phone) : ?>Телефон: <a href="<?php echo esc_url(Formatting::phoneHref($footer_phone)); ?>"><?php echo esc_html($footer_phone); ?></a><?php else : ?>Телефон: уточняется<?php endif; ?><br>
                    Круглосуточно<br>
                </p>
            </div>
            <nav class="site-footer__nav" aria-label="Навигация в футере">
                <?php
                wp_nav_menu(array(
                    'theme_location' => 'site-footer',
                    'container' => false,
                    'items_wrap' => '%3$s',
                    'fallback_cb' => false,
                    'depth' => 2,
                    'walker' => new Edemchinim_Site_Footer_Walker(),
                ));
                ?>
            </nav>
        </div>
        <div class="site-footer__bottom">
            <span>© <?php echo esc_html(wp_date('Y')); ?> ЕДЕМ–ЧИНИМ</span>
            <p>Информация на сайте не является публичной офертой. Окончательная стоимость согласовывается до начала работ.</p>
        </div>
    </div>
</footer>
