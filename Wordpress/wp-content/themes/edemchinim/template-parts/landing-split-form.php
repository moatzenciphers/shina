<?php
$form = $args['form'] ?? null;
$shortcode = $form ? ($form['shortcode'] ?? '') : edemchinim_landing_sub('help_form_shortcode');
?>
<section class="split-form landing-section" id="help" aria-labelledby="split-form-title">
	<div class="split-form__inner">
		<header class="split-form__intro">
			<?php if ($form) : ?>
                <p class="split-form__eyebrow landing-eyebrow">НУЖНА ПОМОЩЬ?</p>
                <h2 class="split-form__title landing-title landing-title--section" id="split-form-title"><?php echo esc_html($form['title'] ?? 'Нужна помощь с автомобилем?'); ?></h2>
            <?php else : edemchinim_landing_heading('help', 'split-form'); endif; ?>
			<p class="split-form__lead landing-text"><?php echo esc_html($form ? ($form['lead'] ?? '') : edemchinim_landing_sub('help_lead')); ?></p>
		</header>
		<div class="split-form__form-wrap">
			<?php if ($shortcode) : edemchinim_landing_cf7($shortcode); endif; ?>
			<?php $note = $form ? ($form['note'] ?? '') : edemchinim_landing_sub('help_note'); if ($note) : ?><p class="split-form__note landing-text"><?php echo esc_html($note); ?></p><?php endif; ?>
			<p class="split-form__status" role="status" aria-live="polite" data-split-form-status hidden></p>
		</div>
	</div>
</section>
