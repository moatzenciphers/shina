import GLightbox from 'glightbox';
// Shared by the standalone WordPress asset and the frontend bundle.
const initReviewDialog = () => {
  const track = document.querySelector('[data-review-slider]');
  const dialog = document.querySelector('[data-review-dialog]');
  if (!track || !dialog || dialog.dataset.reviewDialogReady) return;

  dialog.dataset.reviewDialogReady = 'true';
  const closeButton = dialog.querySelector('[data-review-close]');
  const stars = dialog.querySelector('[data-review-dialog-stars]');
  const text = dialog.querySelector('[data-review-dialog-text]');
  const service = dialog.querySelector('[data-review-dialog-service]');
  const author = dialog.querySelector('[data-review-dialog-author]');
  const date = dialog.querySelector('[data-review-dialog-date]');
  const photos = dialog.querySelector('[data-review-dialog-photos]');
  const heading = dialog.querySelector('.slider-grid__dialog-title');
  let opener = null;
  GLightbox({ selector: '[data-review-photo]', loop: false });

  track.classList.add('slider-grid__track--expandable');

  const syncButtons = () => {
    track.querySelectorAll('.slider-grid__item').forEach((card) => {
      const quote = card.querySelector('.slider-grid__quote');
      const button = card.querySelector('[data-review-open]');
      if (quote && button) button.hidden = quote.scrollHeight <= quote.clientHeight + 1;
    });
  };

  track.addEventListener('click', (event) => {
    const button = event.target.closest('[data-review-open]');
    if (!button || !track.contains(button)) return;
    const card = button.closest('.slider-grid__item');
    const cardStars = card.querySelector('.slider-grid__stars');
    const cardDate = card.querySelector('.slider-grid__date');
    opener = button;
    stars.textContent = cardStars?.textContent || '';
    stars.setAttribute('aria-label', cardStars?.getAttribute('aria-label') || '');
    text.textContent = card.querySelector('.slider-grid__quote')?.textContent || '';
    const serviceLabel = card.querySelector('.slider-grid__service');
    service.replaceChildren(...Array.from(serviceLabel?.childNodes || []).map((node) => node.cloneNode(true)));
    if (heading) heading.textContent = card.querySelector('.slider-grid__review-heading')?.textContent || 'Отзыв клиента';
    if (photos) photos.replaceChildren(...Array.from(card.querySelectorAll('[data-review-photos] img')).map((photo) => photo.cloneNode(true)));
    author.textContent = card.querySelector('.slider-grid__author-name')?.textContent || '';
    date.textContent = cardDate?.textContent || '';
    date.dateTime = cardDate?.dateTime || '';
    document.body.classList.add('review-dialog-open');
    dialog.showModal();
    closeButton?.focus();
  });

  closeButton?.addEventListener('click', () => dialog.close());
  dialog.addEventListener('click', (event) => {
    if (event.target === dialog) dialog.close();
  });
  dialog.addEventListener('close', () => {
    document.body.classList.remove('review-dialog-open');
    opener?.focus();
  });

  document.addEventListener('reviews:filtered', () => window.requestAnimationFrame(syncButtons));
  window.addEventListener('resize', syncButtons);
  document.fonts?.ready.then(syncButtons);
  syncButtons();
};

if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', initReviewDialog, { once: true });
} else {
  initReviewDialog();
}
