export const initDataHrefLinks = () => {
  const nativeControls = 'a[href], button, input, select, textarea, summary';

  document.querySelectorAll('[data-href]').forEach((item) => {
    if (item.matches(nativeControls)) return;
    if (!item.hasAttribute('role')) item.setAttribute('role', 'link');
    if (!item.hasAttribute('tabindex')) item.tabIndex = 0;
  });

  document.addEventListener('click', (event) => {
    const item = event.target.closest('[data-href]');
    if (!item || event.defaultPrevented) return;
    const control = event.target.closest(nativeControls);
    if (control && control !== item && item.contains(control)) return;
    const href = item.dataset.href?.trim();
    if (!href) return;
    event.preventDefault();
    if (event.ctrlKey || event.metaKey || event.shiftKey || item.getAttribute('target') === '_blank') {
      window.open(href, '_blank', 'noopener');
    } else {
      window.location.assign(href);
    }
  });

  document.addEventListener('keydown', (event) => {
    const item = event.target.closest('[data-href]');
    if (!item || event.target !== item || event.defaultPrevented) return;
    const expandWithSpace = event.key === ' ' && item.getAttribute('role') === 'button' && item.matches('a[href]');
    if (item.matches(nativeControls) && !expandWithSpace) return;
    if (event.key !== 'Enter' && !(event.key === ' ' && item.getAttribute('role') === 'button')) return;
    event.preventDefault();
    item.dispatchEvent(new MouseEvent('click', {
      bubbles: true,
      cancelable: true,
      ctrlKey: event.ctrlKey,
      metaKey: event.metaKey,
      shiftKey: event.shiftKey,
      altKey: event.altKey,
    }));
  });
};
