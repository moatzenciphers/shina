export const initServiceFilters = () => {
  document.querySelectorAll('[data-service-catalog]').forEach((catalog) => {
    const buttons = Array.from(catalog.querySelectorAll('[data-service-filter]'));
    const cards = Array.from(catalog.querySelectorAll('[data-service-card]'));
    const status = catalog.querySelector('[data-service-filter-status]');
    const empty = catalog.querySelector('[data-service-filter-empty]');
    const applyFilter = (filter, announce = true) => {
      let visible = 0;
      cards.forEach((card) => {
        const tokens = (card.dataset.serviceTerms || '').split(/\s+/);
        card.hidden = filter !== 'all' && !tokens.includes(filter);
        if (!card.hidden) visible += 1;
      });
      buttons.forEach((button) => button.setAttribute('aria-pressed', String(button.dataset.serviceFilter === filter)));
      if (empty) empty.hidden = visible > 0;
      if (status && announce) status.textContent = `Услуг найдено: ${visible}`;
    };
    buttons.forEach((button) => button.addEventListener('click', () => applyFilter(button.dataset.serviceFilter)));
    applyFilter('all', false);
  });
};
