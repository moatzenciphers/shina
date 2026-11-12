<?php
use Edemchinim\Content\Blocks;
use Edemchinim\Support\Fields;

get_header("");
$settings_id = $args['settings_id'] ?? 'services-archive';
$title = Fields::get('catalog_title', $settings_id, 'Все услуги');
$intro = Fields::get('catalog_intro', $settings_id, 'Выездной шиномонтаж и техпомощь в Москве и Московской области. Выберите услугу, чтобы узнать условия обслуживания и посмотреть подробный прайс.');
?>
<div class="landing landing--wp-header" data-landing>
    <main class="landing__main" id="primary">
        <?php
        get_template_part('template-parts/blocks/cards-grid', null, array(
            'catalog' => true,
            'title' => $title ?: 'Все услуги',
            'intro' => $intro,
        ));
        Blocks::render($settings_id);
        ?>
    </main>
    </div>
<?php

get_footer(""); ?>
