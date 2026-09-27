<?php $shortcode = edemchinim_landing_sub('help_form_shortcode'); ?>
<section class="split-form landing-section" id="help" aria-labelledby="split-form-title">
	<div class="split-form__inner">
		<header class="split-form__intro">
			<?php edemchinim_landing_heading('help', 'split-form'); ?>
			<p class="split-form__lead landing-text"><?php echo esc_html(edemchinim_landing_sub('help_lead')); ?></p>
		</header>
		<div class="split-form__form-wrap">
			<?php if ($shortcode) : edemchinim_landing_cf7($shortcode); endif; ?>
			<p class="split-form__note landing-text"><?php echo esc_html(edemchinim_landing_sub('help_note')); ?></p>
			<p class="split-form__status" role="status" aria-live="polite" data-split-form-status hidden></p>
		</div>
	</div>
</section>
