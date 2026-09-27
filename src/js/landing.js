import { expandCalculatorSheet } from './calculator';

export const initLanding = () => {
  const landing = document.querySelector('[data-landing]');
  const app = document.querySelector('[data-app]');
  const menu = document.querySelector('[data-landing-menu]');
  const menuToggle = document.querySelector('[data-landing-menu-toggle]');
  const closeButton = document.querySelector('[data-close-calculator]');
  const hero = landing?.querySelector('.landing-hero');
  const mobileMenuQuery = window.matchMedia('(max-width: 850px)');
  let returnFocus = null;

  if (!landing || !app || !menu || !hero) return;

  const setMenuOpen = (open, restoreFocus = false) => {
    const isOpen = Boolean(open && mobileMenuQuery.matches);
    hero.classList.toggle('landing-hero--menu-open', isOpen);
    document.body.classList.toggle('landing-menu-open', isOpen);
    menuToggle?.setAttribute('aria-expanded', String(isOpen));
    menuToggle?.setAttribute('aria-label', isOpen ? 'Закрыть меню' : 'Открыть меню');
    if (isOpen) menu.querySelector('[data-menu-expand]')?.focus();
    else if (restoreFocus) menuToggle?.focus();
  };

  const openCalculator = (trigger) => {
    returnFocus = trigger;
    landing.hidden = true;
    app.hidden = false;
    window.scrollTo({ top: 0 });
    closeButton?.focus();
    setMenuOpen(false);
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
    if (returnFocus?.isConnected) {
      returnFocus.focus({ preventScroll: true });
      returnFocus.scrollIntoView({ block: 'center' });
    }
  });

  menuToggle?.addEventListener('click', () => {
    setMenuOpen(!hero.classList.contains('landing-hero--menu-open'), true);
  });

  menu.querySelectorAll('[data-menu-expand]').forEach((link) => {
    link.addEventListener('click', (event) => {
      if (!mobileMenuQuery.matches) return;
      event.preventDefault();
      link.setAttribute('aria-expanded', String(link.getAttribute('aria-expanded') !== 'true'));
    });

    const item = link.closest('[data-menu-item]');
    item?.addEventListener('pointerenter', () => {
      if (!mobileMenuQuery.matches) link.setAttribute('aria-expanded', 'true');
    });
    item?.addEventListener('pointerleave', () => {
      if (!mobileMenuQuery.matches) link.setAttribute('aria-expanded', 'false');
    });
  });

  menu?.addEventListener('click', (event) => {
    if (event.target.closest('a:not([data-menu-expand])')) setMenuOpen(false);
  });

  mobileMenuQuery.addEventListener('change', () => setMenuOpen(false));

  document.addEventListener('keydown', (event) => {
    if (!hero.classList.contains('landing-hero--menu-open')) return;

    if (event.key === 'Escape') {
      setMenuOpen(false, true);
    }

    if (event.key === 'Tab') {
      const focusable = Array.from(hero.querySelectorAll('.landing-hero__header a, .landing-hero__header button'))
        .filter((element) => element.getClientRects().length > 0);
      const first = focusable[0];
      const last = focusable[focusable.length - 1];
      if (event.shiftKey && document.activeElement === first) {
        event.preventDefault();
        last?.focus();
      } else if (!event.shiftKey && document.activeElement === last) {
        event.preventDefault();
        first?.focus();
      }
    }
  });
};
