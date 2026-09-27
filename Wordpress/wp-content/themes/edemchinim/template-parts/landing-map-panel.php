<?php $shortcode = edemchinim_landing_sub('map_form_shortcode'); ?>
<section class="map-panel landing-section" id="service-area" aria-labelledby="map-panel-title" data-map-panel>
	<div class="map-panel__inner">
		<div class="map-panel__content">
			<?php edemchinim_landing_heading('map', 'map-panel'); ?>
			<p class="map-panel__lead landing-text"><?php echo esc_html(edemchinim_landing_sub('map_lead')); ?></p>
			<?php if ($shortcode) : edemchinim_landing_cf7($shortcode); endif; ?>
		</div>
		<div class="map-panel__visual"><div class="map-panel__canvas" data-map-panel-canvas role="application" aria-label="Карта зоны выезда по Москве и Московской области"></div><p class="map-panel__map-loading" data-map-panel-loading>Загружаем карту…</p></div>
	</div>
</section>
