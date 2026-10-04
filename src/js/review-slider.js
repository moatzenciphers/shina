export const initReviewSlider = () => {
  const catalog = document.querySelector('[data-review-catalog]');
  const track = catalog?.querySelector('[data-review-slider]');
  if (!track) return;
  const buttons = Array.from(catalog.querySelectorAll('[data-review-filter]'));
  const cards = Array.from(track.querySelectorAll('.slider-grid__item'));
  const visibleSlides = () => cards.filter((card) => !card.hidden);
  const applyFilter = (key, announce = true) => {
    cards.forEach((card) => {
      card.hidden = key !== 'all' && card.dataset.reviewService !== key;
      card.classList.remove('slider-grid__item--featured');
    });
    visibleSlides()[0]?.classList.add('slider-grid__item--featured');
    buttons.forEach((button) => button.setAttribute('aria-pressed', String(button.dataset.reviewFilter === key)));
    const status = catalog.querySelector('[data-review-filter-status]');
    if (status && announce) status.textContent = `Отзывов найдено: ${visibleSlides().length}`;
    document.dispatchEvent(new CustomEvent('reviews:filtered'));
  };
  buttons.forEach((button) => button.addEventListener('click', () => applyFilter(button.dataset.reviewFilter)));
  applyFilter('all', false);
};
