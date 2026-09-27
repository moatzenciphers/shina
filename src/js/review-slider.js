export const initReviewSlider = () => {
  const track = document.querySelector('[data-review-slider]');
  const prev = document.querySelector('[data-review-prev]');
  const next = document.querySelector('[data-review-next]');
  const current = document.querySelector('[data-review-current]');
  const slides = Array.from(track?.querySelectorAll('.slider-grid__item') || []);

  if (!track || !prev || !next || !current || !slides.length) return;

  const maxIndex = slides.length - 1;
  const slideWidth = () => slides[0].getBoundingClientRect().width;
  const getIndex = () => Math.min(maxIndex, Math.max(0, Math.round(track.scrollLeft / slideWidth())));

  const sync = () => {
    const index = getIndex();
    current.textContent = String(index + 1).padStart(2, '0');
    prev.disabled = index === 0;
    next.disabled = index === maxIndex;
  };

  const goTo = (index) => {
    const target = Math.min(maxIndex, Math.max(0, index));
    track.scrollTo({ left: target * slideWidth(), behavior: window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 'auto' : 'smooth' });
    current.textContent = String(target + 1).padStart(2, '0');
    prev.disabled = target === 0;
    next.disabled = target === maxIndex;
  };

  prev.addEventListener('click', () => goTo(getIndex() - 1));
  next.addEventListener('click', () => goTo(getIndex() + 1));
  track.addEventListener('scroll', sync, { passive: true });
  track.addEventListener('keydown', (event) => {
    if (event.key !== 'ArrowLeft' && event.key !== 'ArrowRight') return;
    event.preventDefault();
    goTo(getIndex() + (event.key === 'ArrowRight' ? 1 : -1));
  });
  window.addEventListener('resize', sync);
  sync();
};
