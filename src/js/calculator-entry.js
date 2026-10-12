import { initCalculator } from './calculator';
import { initYandexAddress } from './map';
import { initCookieConsent } from './cookie-consent';
import { state } from './state';

document.addEventListener('DOMContentLoaded', () => {
  const requestedService = new URLSearchParams(window.location.search).get('service');
  if (['seasonal', 'puncture', 'conditioner', 'storage'].includes(requestedService)) state.service = requestedService;
  const orderForm = document.querySelector('.app .wpcf7 form');
  const orderWrapper = orderForm?.parentElement.closest('[data-order-form]');
  if (orderForm && orderWrapper) {
    orderForm.hidden = false;
    orderForm.removeAttribute('data-order-form');
  } else if (orderForm) {
    orderForm.classList.add('order-confirm');
    orderForm.dataset.orderForm = '';
    orderForm.hidden = true;
  }
  const back = document.querySelector('[data-calculator-back]');
  let previous = '';
  try { previous = sessionStorage.getItem('calculatorPreviousPage') || ''; sessionStorage.removeItem('calculatorPreviousPage'); } catch {}
  const canGoBack = window.history.length > 1 && (window.navigation?.canGoBack ?? Boolean(document.referrer || previous));
  if (back && canGoBack) {
    back.querySelector('span').textContent = 'Назад';
    back.setAttribute('aria-label', 'Вернуться на предыдущую страницу');
    back.addEventListener('click', (event) => { event.preventDefault(); window.history.back(); });
  }
  initCalculator();
  initYandexAddress();
  initCookieConsent();
});
