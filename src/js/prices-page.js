export const initPricesPage = () => {
  const root = document.querySelector('.prices-page');
  if (!root || root.dataset.pricesReady) return;
  root.dataset.pricesReady = 'true';
  const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)');
  const states = new Map();

  const expand = (details, expanded, animate = true) => {
    const state = states.get(details);
    const start = details.getBoundingClientRect().height;
    state.animation?.cancel();
    state.animation = null;
    state.expanded = expanded;
    details.dataset.expanded = String(expanded);
    details.style.height = '';
    details.style.overflow = '';

    const finish = () => {
      details.open = expanded;
      details.style.height = '';
      details.style.overflow = '';
      delete details.dataset.expanded;
    };
    if (!animate || reduceMotion.matches || !details.animate) {
      finish();
      return;
    }
    // Keep the content mounted until closing finishes; measure before fixing height.
    details.open = true;
    const style = getComputedStyle(details);
    const end = expanded ? details.getBoundingClientRect().height
      : details.querySelector('summary').getBoundingClientRect().height
        + parseFloat(style.borderTopWidth) + parseFloat(style.borderBottomWidth);
    details.style.height = `${start}px`;
    details.style.overflow = 'hidden';
    const animation = details.animate({ height: [`${start}px`, `${end}px`] }, {
      duration: 240, easing: 'cubic-bezier(0.23, 1, 0.32, 1)', fill: 'both',
    });
    state.animation = animation;
    animation.onfinish = () => {
      if (state.animation !== animation) return;
      state.animation = null;
      finish();
      animation.cancel();
    };
  };

  root.querySelectorAll('details.prices-page__service').forEach((details) => {
    const state = { expanded: details.open, animation: null };
    states.set(details, state);
    details.querySelector('summary').addEventListener('click', (event) => {
      event.preventDefault();
      expand(details, !state.expanded);
    });
    details.addEventListener('toggle', () => {
      if (!state.animation) state.expanded = details.open;
    });
  });
  reduceMotion.addEventListener('change', () => {
    if (reduceMotion.matches) states.forEach((state, details) => expand(details, state.expanded, false));
  });

  const reveal = (hash, focus = false) => {
    const target = Array.from(states.keys()).find((item) => `#${item.id}` === hash);
    if (!target) return false;
    // One movement at a time: reveal immediately, then scroll to the service.
    expand(target, true, false);
    window.requestAnimationFrame(() => {
      if (focus) target.querySelector('summary').focus({ preventScroll: true });
      target.scrollIntoView({ behavior: reduceMotion.matches ? 'auto' : 'smooth', block: 'start' });
    });
    return true;
  };
  root.querySelector('.prices-page__nav')?.addEventListener('click', (event) => {
    const link = event.target.closest('a[href^="#"]');
    if (!link || event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) return;
    const hash = link.getAttribute('href');
    if (!reveal(hash, true)) return;
    event.preventDefault();
    if (window.location.hash !== hash) window.history.pushState(null, '', hash);
  });
  window.addEventListener('hashchange', () => reveal(window.location.hash));
  if (window.location.hash) reveal(window.location.hash);
};
