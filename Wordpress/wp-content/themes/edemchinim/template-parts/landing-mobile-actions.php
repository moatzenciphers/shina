<?php
$mobile_actions_phone = edemchinim_get_option_field('shina_phone', '+7 (000) 000-00-00');
$mobile_actions_phone_href = edemchinim_phone_href($mobile_actions_phone);
?>
<nav class="mobile-actions" aria-label="Быстрые действия" data-mobile-actions>
  <a class="mobile-actions__phone" href="<?php echo esc_url($mobile_actions_phone_href); ?>" aria-label="<?php echo esc_attr(sprintf('Позвонить: %s', $mobile_actions_phone)); ?>">
    <span><?php echo esc_html($mobile_actions_phone); ?></span>
  </a>
  <button class="mobile-actions__calculator" type="button" aria-label="Открыть калькулятор" data-open-calculator data-mobile-calculator hidden>
    <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><rect x="4" y="2.5" width="16" height="19" rx="2" stroke="currentColor" stroke-width="1.7"/><path d="M7 7.5h10M8 12h2m4 0h2m-8 4h2m4 0h2" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg>
  </button>
</nav>
