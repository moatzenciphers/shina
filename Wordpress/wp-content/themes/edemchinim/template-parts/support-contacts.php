<?php
$phone = edemchinim_get_option_field('shina_phone', '+7 (499) 648-19-90');
$email = edemchinim_get_option_field('shina_email', '');
$schedule = edemchinim_get_option_field('shina_work_schedule', '24/7');
$details = edemchinim_get_option_field('shina_legal_details', '');
$links = edemchinim_get_option_field('shina_contact_links', array());
?>
<div class="contacts-page__layout">
    <div class="contacts-page__details">
        <?php if ($phone) : ?><a class="contacts-page__phone" href="<?php echo esc_url(edemchinim_phone_href($phone)); ?>"><?php echo esc_html($phone); ?></a><?php endif; ?>
        <dl class="contacts-page__facts"><div><dt>График работы</dt><dd><?php echo esc_html($schedule); ?></dd></div><div><dt>Зона выезда</dt><dd>Москва и Московская область</dd></div><div><dt>Формат работы</dt><dd>Только выезд к автомобилю</dd></div><?php if ($email && is_email($email)) : ?><div><dt>Электронная почта</dt><dd><a href="mailto:<?php echo esc_attr($email); ?>"><?php echo esc_html($email); ?></a></dd></div><?php endif; ?></dl>
        <?php if (is_array($links)) : ?><div class="contacts-page__links"><?php foreach ($links as $link) : if (empty($link['label']) || empty($link['url'])) continue; ?><a class="landing-more-link" href="<?php echo esc_url($link['url']); ?>"><?php echo esc_html($link['label']); ?><?php edemchinim_landing_arrow(); ?></a><?php endforeach; ?></div><?php endif; ?>
        <a class="landing-cta contacts-page__cta" href="<?php echo esc_url(edemchinim_calculator_url()); ?>" data-open-calculator><span>Рассчитать стоимость</span><?php edemchinim_landing_arrow(); ?></a>
    </div>
    <section class="contacts-page__coverage" aria-labelledby="coverage-title"><h2 class="landing-subtitle" id="coverage-title">Зона выезда</h2><?php get_template_part('template-parts/coverage-map'); ?></section>
</div>
<?php if ($details) : ?><section class="contacts-page__legal" aria-labelledby="legal-title"><h2 class="landing-title landing-title--section" id="legal-title">Реквизиты</h2><p class="landing-text"><?php echo nl2br(esc_html($details)); ?></p></section><?php endif; ?>
