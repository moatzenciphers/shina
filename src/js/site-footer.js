export const initSiteFooter = () => {
  const toggles = document.querySelectorAll('[data-footer-toggle]');
  if (!toggles.length) return;

  const mobile = window.matchMedia('(max-width: 850px)');
  const sync = () => toggles.forEach((toggle) => toggle.setAttribute('aria-expanded', String(!mobile.matches)));

  toggles.forEach((toggle) => {
    toggle.addEventListener('click', () => {
      if (!mobile.matches) return;
      toggle.setAttribute('aria-expanded', String(toggle.getAttribute('aria-expanded') !== 'true'));
    });
  });
  mobile.addEventListener('change', sync);
  sync();
};
