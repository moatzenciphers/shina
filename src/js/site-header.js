export const initSiteHeader = () => {
  const header = document.querySelector('[data-site-header]');
  if (!header || header.dataset.siteHeaderReady === 'true') return;
  header.dataset.siteHeaderReady = 'true';

  const root = header.closest('[data-landing], [data-site-header-root]') || header.parentElement;
  const menu = header.querySelector('[data-landing-menu]');
  const menuToggle = header.querySelector('[data-landing-menu-toggle]');
  const mobileQuery = window.matchMedia('(max-width: 850px)');
  if (!root || !menu || !menuToggle) return;

  const setMenuOpen = (open, restoreFocus = false) => {
    const isOpen = Boolean(open && mobileQuery.matches);
    root.classList.toggle('landing-hero--menu-open', isOpen);
    document.body.classList.toggle('landing-menu-open', isOpen);
    menuToggle.setAttribute('aria-expanded', String(isOpen));
    menuToggle.setAttribute('aria-label', isOpen ? 'Закрыть меню' : 'Открыть меню');
    if (isOpen) {
      menu.querySelector('[data-menu-expand], a, button')?.focus();
    } else {
      menu.querySelectorAll('[data-menu-expand]').forEach((item) => item.setAttribute('aria-expanded', 'false'));
      if (restoreFocus) menuToggle.focus();
    }
  };

  menuToggle.addEventListener('click', () => {
    setMenuOpen(!root.classList.contains('landing-hero--menu-open'), true);
  });

  menu.querySelectorAll('[data-menu-expand]').forEach((item) => {
    item.addEventListener('click', (event) => {
      if (!mobileQuery.matches) return;
      event.preventDefault();
      item.setAttribute('aria-expanded', String(item.getAttribute('aria-expanded') !== 'true'));
    });
    if (item.tagName === 'SPAN') {
      item.addEventListener('keydown', (event) => {
        if (mobileQuery.matches && (event.key === 'Enter' || event.key === ' ')) {
          event.preventDefault();
          item.click();
        }
      });
    }

    const menuItem = item.closest('[data-menu-item]');
    menuItem?.addEventListener('pointerenter', () => {
      if (!mobileQuery.matches) item.setAttribute('aria-expanded', 'true');
    });
    menuItem?.addEventListener('pointerleave', () => {
      if (!mobileQuery.matches) item.setAttribute('aria-expanded', 'false');
    });
    menuItem?.addEventListener('focusin', () => {
      if (!mobileQuery.matches) item.setAttribute('aria-expanded', 'true');
    });
    menuItem?.addEventListener('focusout', (event) => {
      if (!mobileQuery.matches && !menuItem.contains(event.relatedTarget)) {
        item.setAttribute('aria-expanded', 'false');
      }
    });
  });

  menu.addEventListener('click', (event) => {
    if (event.target.closest('a:not([data-menu-expand])')) setMenuOpen(false);
  });

  mobileQuery.addEventListener('change', () => setMenuOpen(false));
  document.addEventListener('site-header:close-menu', () => setMenuOpen(false));

  document.addEventListener('keydown', (event) => {
    if (!root.classList.contains('landing-hero--menu-open')) return;
    if (event.key === 'Escape') {
      setMenuOpen(false, true);
      return;
    }
    if (event.key !== 'Tab') return;

    const focusable = Array.from(header.querySelectorAll('a, button, [data-menu-expand][tabindex="0"]'))
      .filter((element) => element.getClientRects().length > 0 && getComputedStyle(element).visibility === 'visible');
    const first = focusable[0];
    const last = focusable[focusable.length - 1];
    if (event.shiftKey && document.activeElement === first) {
      event.preventDefault();
      last?.focus();
    } else if (!event.shiftKey && document.activeElement === last) {
      event.preventDefault();
      first?.focus();
    }
  });
};
