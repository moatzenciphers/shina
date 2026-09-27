<?php $shortcode = function_exists('get_field') ? get_field('quick_call_shortcode', get_queried_object_id()) : ''; ?>
<?php if ($shortcode) : ?>
<div class="quick-call-backdrop" data-quick-call-dialog hidden>
	<div class="quick-call-dialog" id="quick-call-dialog" role="dialog" aria-modal="true" aria-labelledby="quick-call-title" aria-describedby="quick-call-description">
		<button class="quick-call-dialog__close" type="button" data-quick-call-close aria-label="Закрыть форму">×</button>
		<p class="quick-call-dialog__eyebrow landing-eyebrow">ПОМОЩЬ РЯДОМ</p>
		<h2 class="quick-call-dialog__title landing-title landing-title--dialog" id="quick-call-title">Быстрый вызов</h2>
		<p class="quick-call-dialog__description landing-text landing-text--dialog" id="quick-call-description">Оставьте номер телефона, чтобы оператор мог вам перезвонить.</p>
		<?php edemchinim_landing_cf7($shortcode); ?>
		<p class="quick-call-dialog__status" role="status" aria-live="polite" data-quick-call-status hidden></p>
	</div>
</div>
<?php endif; ?>
