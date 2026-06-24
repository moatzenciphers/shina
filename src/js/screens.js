export const initScreens = () => {
  const app = document.querySelector('[data-app]');
  const screens = document.querySelectorAll('[data-screen]');
  const navItems = document.querySelectorAll('[data-screen-target]');
  const menus = document.querySelectorAll('[data-app-menu]');
  const menuToggles = document.querySelectorAll('[data-app-menu-toggle]');

  const setMenuOpen = (isOpen) => {
    menus.forEach((menu) => {
      menu.hidden = !isOpen;
    });

    menuToggles.forEach((toggle) => {
      toggle.setAttribute('aria-expanded', String(isOpen));
    });
  };

  const isMenuOpen = () => {
    return Array.from(menus).some((menu) => menu.hidden === false);
  };

  const syncAppState = (target) => {
    app?.classList.toggle('app--order-active', target === 'order');

    navItems.forEach((item) => {
      const isActive = item.dataset.screenTarget === target;

      if (item.classList.contains('app-menu-popover__item')) {
        item.classList.toggle('app-menu-popover__item--active', isActive);
      }

      item.toggleAttribute('aria-current', isActive);
    });
  };

  const currentScreen = document.querySelector('.screen--active[data-screen]')?.dataset.screen || 'order';

  syncAppState(currentScreen);

  menuToggles.forEach((toggle) => {
    toggle.addEventListener('click', (event) => {
      event.stopPropagation();
      setMenuOpen(!isMenuOpen());
    });
  });

  document.addEventListener('click', (event) => {
    if (!isMenuOpen()) {
      return;
    }

    if (event.target.closest('[data-app-menu]') || event.target.closest('[data-app-menu-toggle]')) {
      return;
    }

    setMenuOpen(false);
  });

  document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape') {
      setMenuOpen(false);
    }
  });

  navItems.forEach((button) => {
    button.addEventListener('click', () => {
      const target = button.dataset.screenTarget;

      screens.forEach((screen) => {
        screen.classList.toggle('screen--active', screen.dataset.screen === target);
      });

      syncAppState(target);
      setMenuOpen(false);

      window.scrollTo({
        top: 0,
      });

      document.dispatchEvent(new CustomEvent('app:screenchange', {
        detail: {
          screen: target,
        },
      }));
    });
  });
};
