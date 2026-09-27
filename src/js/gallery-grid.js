import GLightbox from 'glightbox';
import 'glightbox/dist/css/glightbox.css';

export const initGalleryGrid = () => {
  const track = document.querySelector('[data-gallery-slider]');
  const prev = document.querySelector('[data-gallery-prev]');
  const next = document.querySelector('[data-gallery-next]');
  const slides = Array.from(track?.querySelectorAll('.gallery-grid__card') || []);
  if (!track || !prev || !next || !slides.length) return;

  GLightbox({ selector: '.gallery-grid__link', loop: false, touchNavigation: true });

  const width = () => slides[0].getBoundingClientRect().width;
  const index = () => Math.min(slides.length - 1, Math.max(0, Math.round(track.scrollLeft / width())));
  const sync = () => {
    const current = index();
    prev.disabled = current === 0;
    next.disabled = current === slides.length - 1;
  };
  const goTo = (target) => {
    const current = Math.min(slides.length - 1, Math.max(0, target));
    track.scrollTo({ left: current * width(), behavior: window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 'auto' : 'smooth' });
    prev.disabled = current === 0;
    next.disabled = current === slides.length - 1;
  };

  prev.addEventListener('click', () => goTo(index() - 1));
  next.addEventListener('click', () => goTo(index() + 1));
  track.addEventListener('scroll', sync, { passive: true });
  track.addEventListener('keydown', (event) => {
    if (event.key !== 'ArrowLeft' && event.key !== 'ArrowRight') return;
    event.preventDefault();
    goTo(index() + (event.key === 'ArrowRight' ? 1 : -1));
  });
  window.addEventListener('resize', sync);
  sync();
};
