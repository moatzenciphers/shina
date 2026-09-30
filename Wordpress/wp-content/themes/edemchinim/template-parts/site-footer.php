<?php
/** Shared WordPress footer. Menu top-level items are collapsible groups. */
$footer_phone = edemchinim_get_option_field('shina_phone', '');
$footer_email = edemchinim_get_option_field('shina_email', '');
?>
<footer class="site-footer site-footer--wp" id="contacts">
    <div class="site-footer__panel">
        <div class="site-footer__top">
            <div class="site-footer__about">
                <a class="site-footer__brand" href="<?php echo esc_url(home_url('/')); ?>" aria-label="Едем–чиним — на главную">
                    <svg class="site-footer__brand-car" viewBox="0 0 132 31" fill="none" aria-hidden="true"><path d="M5 23c6-5 16-6 28-6 9-8 18-12 35-12 15 0 27 4 39 12 10 0 16 2 20 6M15 23h20m62 0h23M46 13c18-5 33-5 48 2" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    <span class="site-footer__brand-name">ЕДЕМ–ЧИНИМ</span>
                </a>
                <p class="site-footer__description">Выездной шиномонтаж и помощь на дороге в Москве и МО 24/7.</p>
                <p class="site-footer__contacts">
                    <?php if ($footer_phone) : ?>Телефон: <a href="<?php echo esc_url(edemchinim_phone_href($footer_phone)); ?>"><?php echo esc_html($footer_phone); ?></a><?php else : ?>Телефон: уточняется<?php endif; ?><br>
                    Круглосуточно<br>
                    <?php if ($footer_email && is_email($footer_email)) : ?>E-mail: <a href="mailto:<?php echo esc_attr($footer_email); ?>"><?php echo esc_html($footer_email); ?></a><?php else : ?>E-mail: уточняется<?php endif; ?>
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
