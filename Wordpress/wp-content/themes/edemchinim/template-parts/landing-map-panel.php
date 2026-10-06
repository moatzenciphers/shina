<?php $shortcode = edemchinim_landing_sub('map_form_shortcode'); ?>
<section class="map-panel landing-section" id="service-area" aria-labelledby="map-panel-title" data-map-panel>
	<div class="map-panel__inner">
		<div class="map-panel__content">
			<?php edemchinim_landing_heading('map', 'map-panel'); ?>
			<p class="map-panel__lead landing-text"><?php echo esc_html(edemchinim_landing_sub('map_lead')); ?></p>
			<?php if ($shortcode) : edemchinim_landing_cf7($shortcode); endif; ?>
		</div>
        <div class="map-panel__visual">
            <div class="map-panel__canvas" data-map-panel-canvas role="application" aria-label="Карта зоны выезда по Москве и Московской области"></div>
            <p class="map-panel__map-loading" data-map-panel-loading>Загружаем карту…</p>
            <div class="map-overlay map-panel__controls">
                <div class="map-overlay__zoom" aria-label="Масштаб карты">
                    <button class="map-overlay__control" type="button" aria-label="Приблизить карту" data-map-panel-zoom="in" disabled><svg viewBox="0 0 24 24" width="24" height="24" fill="none" aria-hidden="true"><path d="M5 12h14M12 5v14" stroke="currentColor" stroke-width="2"/></svg></button>
                    <button class="map-overlay__control" type="button" aria-label="Отдалить карту" data-map-panel-zoom="out" disabled><svg viewBox="0 0 24 24" width="24" height="24" fill="none" aria-hidden="true"><path d="M5 12h14" stroke="currentColor" stroke-width="2"/></svg></button>
                </div>
                <button class="map-overlay__location" type="button" aria-label="Использовать текущее местоположение" data-map-panel-location disabled><svg viewBox="0 0 24 24" width="24" height="24" fill="none" aria-hidden="true"><circle cx="12" cy="12" r="7" stroke="currentColor" stroke-width="1.6"/><circle cx="12" cy="12" r="2" stroke="currentColor" stroke-width="1.6"/><path d="M12 2v3M12 19v3M2 12h3M19 12h3" stroke="currentColor" stroke-width="1.6"/></svg></button>
            </div>
        </div>
	</div>
</section>
