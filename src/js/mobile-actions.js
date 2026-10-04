export const initMobileActions = () => {
  const panel = document.querySelector('[data-mobile-actions]');
  const calculatorButton = panel?.querySelector('[data-mobile-calculator]');
  if (!panel || !calculatorButton) return;

  const mobileQuery = window.matchMedia('(max-width: 850px)');
  let scheduled = false;

  const isVisibleInViewport = (element) => {
    if (element.closest('[hidden], [inert]')) return false;
    const style = window.getComputedStyle(element);
    if (style.display === 'none' || style.visibility !== 'visible') return false;
    const bounds = element.getBoundingClientRect();
    return bounds.width > 0 && bounds.height > 0
      && bounds.bottom > 0 && bounds.top < window.innerHeight
      && bounds.right > 0 && bounds.left < window.innerWidth;
  };

  const sync = () => {
    scheduled = false;
    if (!mobileQuery.matches || panel.closest('[hidden]')) {
      calculatorButton.hidden = true;
      panel.classList.add('mobile-actions--single');
      return;
    }
    const otherButtons = document.querySelectorAll('[data-open-calculator]:not([data-mobile-calculator])');
    calculatorButton.hidden = Array.from(otherButtons).some((button) => (
      !button.closest('[data-site-header-root]') && isVisibleInViewport(button)
    ));
    panel.classList.toggle('mobile-actions--single', calculatorButton.hidden);
  };

  const scheduleSync = () => {
    if (scheduled) return;
    scheduled = true;
    window.requestAnimationFrame(sync);
  };

  window.addEventListener('scroll', scheduleSync, { passive: true });
  window.addEventListener('resize', scheduleSync);
  window.visualViewport?.addEventListener('resize', scheduleSync);
  mobileQuery.addEventListener('change', scheduleSync);
  document.addEventListener('landing:shown', sync);
  sync();
};
