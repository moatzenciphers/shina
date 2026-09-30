import { expandCalculatorSheet } from './calculator';

export const initLanding = () => {
  const landing = document.querySelector('[data-landing]');
  const app = document.querySelector('[data-app]');
  const closeButton = document.querySelector('[data-close-calculator]');
  const menuToggle = document.querySelector('[data-landing-menu-toggle]');
  const headerShell = document.querySelector('[data-site-header-root]');
  let returnFocus = null;
  let landingScrollY = 0;

  if (!landing || !app) return;
  document.body.classList.add('landing-page');

  const openCalculator = (trigger) => {
    returnFocus = trigger;
    landingScrollY = window.scrollY;
    document.dispatchEvent(new Event('site-header:close-menu'));
    landing.hidden = true;
    if (headerShell) headerShell.hidden = true;
    app.hidden = false;
    window.scrollTo({ top: 0 });
    closeButton?.focus();
    expandCalculatorSheet();
    document.dispatchEvent(new Event('app:calculator-open'));
  };

  document.querySelectorAll('[data-open-calculator]').forEach((button) => {
    button.addEventListener('click', (event) => {
      event.preventDefault();
      openCalculator(button);
    });
  });

  closeButton?.addEventListener('click', () => {
    app.hidden = true;
    landing.hidden = false;
    if (headerShell) headerShell.hidden = false;
    window.scrollTo({ top: landingScrollY });
    document.dispatchEvent(new Event('landing:shown'));
    if (returnFocus?.isConnected && !returnFocus.hidden
      && returnFocus.getClientRects().length
      && getComputedStyle(returnFocus).visibility === 'visible') {
      returnFocus.focus({ preventScroll: true });
    } else {
      menuToggle?.focus({ preventScroll: true });
    }
  });

  if (window.location.hash === '#calculator') {
    const trigger = Array.from(landing.querySelectorAll('[data-open-calculator]'))
      .find((item) => item.getClientRects().length && getComputedStyle(item).visibility === 'visible');
    if (trigger) {
      window.history.replaceState(null, '', window.location.pathname + window.location.search);
      openCalculator(trigger);
    }
  }
};
