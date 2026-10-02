export const initReviewSlider = () => {
  const catalog = document.querySelector('[data-review-catalog]');
  const track = catalog?.querySelector('[data-review-slider]');
  if (!track) return;
  const prev = catalog.querySelector('[data-review-prev]');
  const next = catalog.querySelector('[data-review-next]');
  const current = catalog.querySelector('[data-review-current]');
  const total = catalog.querySelector('[data-review-total]');
  const buttons = Array.from(catalog.querySelectorAll('[data-review-filter]'));
  const cards = Array.from(track.querySelectorAll('.slider-grid__item'));
  const visibleSlides = () => cards.filter((card) => !card.hidden);
  const slideWidth = () => visibleSlides()[0]?.getBoundingClientRect().width || 1;
  const getIndex = () => Math.min(Math.max(0, visibleSlides().length - 1), Math.max(0, Math.round(track.scrollLeft / slideWidth())));
  const sync = () => {
    const slides = visibleSlides();
    const index = getIndex();
    if (current) current.textContent = slides.length ? String(index + 1).padStart(2, '0') : '00';
    if (total) total.textContent = String(slides.length).padStart(2, '0');
    if (prev) prev.disabled = index === 0;
    if (next) next.disabled = index >= slides.length - 1;
  };
  const goTo = (index) => {
    const target = Math.min(Math.max(0, visibleSlides().length - 1), Math.max(0, index));
    track.scrollTo({ left: target * slideWidth(), behavior: window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 'auto' : 'smooth' });
  };
  const applyFilter = (key, announce = true) => {
    cards.forEach((card) => {
      card.hidden = key !== 'all' && card.dataset.reviewService !== key;
      card.classList.remove('slider-grid__item--featured');
    });
    visibleSlides()[0]?.classList.add('slider-grid__item--featured');
    buttons.forEach((button) => button.setAttribute('aria-pressed', String(button.dataset.reviewFilter === key)));
    track.scrollTo({ left: 0, behavior: 'auto' });
    const status = catalog.querySelector('[data-review-filter-status]');
    if (status && announce) status.textContent = `Отзывов найдено: ${visibleSlides().length}`;
    sync();
    document.dispatchEvent(new CustomEvent('reviews:filtered'));
  };
  buttons.forEach((button) => button.addEventListener('click', () => applyFilter(button.dataset.reviewFilter)));
  prev?.addEventListener('click', () => goTo(getIndex() - 1));
  next?.addEventListener('click', () => goTo(getIndex() + 1));
  track.addEventListener('scroll', sync, { passive: true });
  track.addEventListener('keydown', (event) => {
    if (event.key !== 'ArrowLeft' && event.key !== 'ArrowRight') return;
    event.preventDefault();
    goTo(getIndex() + (event.key === 'ArrowRight' ? 1 : -1));
  });
  window.addEventListener('resize', sync);
  applyFilter('all', false);
};
