import GLightbox from 'glightbox';
import Masonry from 'masonry-layout';

import { photosApiEndpoint, photosPerPage } from './config';
import { photosState } from './state';

const serviceLabels = {
  seasonal: 'Сезонная замена',
  puncture: 'Ремонт прокола',
  storage: 'Хранение шин',
  conditioner: 'Заправка кондиционера',
};

const stripHtml = (value) => {
  const element = document.createElement('div');
  element.innerHTML = value || '';

  return element.textContent || '';
};

const getNestedUrl = (value) => {
  if (typeof value === 'string') {
    return value;
  }

  if (!value || typeof value !== 'object') {
    return '';
  }

  if (value.url || value.src) {
    return value.url || value.src;
  }

  const sizes = value.sizes || {};
  const preferredSizes = ['large', 'medium_large', 'medium', 'thumbnail'];

  for (const sizeName of preferredSizes) {
    const size = sizes[sizeName];

    if (typeof size === 'string') {
      return size;
    }

    if (size?.url || size?.src) {
      return size.url || size.src;
    }
  }

  return '';
};

const normalizePhotoType = (type) => {
  const value = Array.isArray(type) ? type[0] : type;
  const normalizedValue = String(value || '').toLowerCase();

  if (/seasonal|сезон|замен/.test(normalizedValue)) {
    return 'seasonal';
  }

  if (/puncture|прокол|ремонт/.test(normalizedValue)) {
    return 'puncture';
  }

  if (/storage|хран/.test(normalizedValue)) {
    return 'storage';
  }

  if (/conditioner|air|кондиц|заправ/.test(normalizedValue)) {
    return 'conditioner';
  }

  return normalizedValue || 'seasonal';
};

const normalizePhoto = (photo, index = 0) => {
  const title = typeof photo.title === 'object' ? photo.title.rendered : photo.title;
  const type = normalizePhotoType(photo.type || photo.repair_type || photo.service || photo.category || photo.category_slug);
  const image = photo.image || photo.photo || photo.media || photo;
  const fullUrl = getNestedUrl(photo.full || photo.full_url || photo.url || image);
  const thumbnailUrl = getNestedUrl(photo.thumbnail || photo.thumb || photo.preview || photo.image) || fullUrl;
  const label = stripHtml(photo.label || photo.work_label || photo.caption || photo.description || title || serviceLabels[type]);

  return {
    id: photo.id || photo.photo_id || `photo-${Date.now()}-${index}`,
    type,
    label,
    fullUrl,
    thumbnailUrl,
  };
};

const getPhotosApiUrl = (page) => {
  const url = new URL(photosApiEndpoint, window.location.origin);
  url.searchParams.set('page', String(page));
  url.searchParams.set('per_page', String(photosPerPage));

  if (photosState.activeFilter !== 'all') {
    url.searchParams.set('repair_type', photosState.activeFilter);
  }

  return url.toString();
};

const parsePhotosResponse = async (response, page) => {
  const data = await response.json();
  const totalPagesHeader = Number(response.headers.get('X-WP-TotalPages'));
  const rawPhotos = Array.isArray(data) ? data : data.photos || data.items || data.data || [];
  const totalPages = Number(data.total_pages || data.totalPages || data.max_num_pages || totalPagesHeader);
  const hasMore = typeof data.has_more === 'boolean' ? data.has_more : totalPages ? page < totalPages : rawPhotos.length === photosPerPage;

  return {
    hasMore,
    photos: rawPhotos
      .map(normalizePhoto)
      .filter((photo) => photo.fullUrl),
  };
};

const fetchPhotosPage = async (page) => {
  const response = await fetch(getPhotosApiUrl(page), {
    headers: {
      Accept: 'application/json',
    },
  });

  if (!response.ok) {
    throw new Error('Photos request failed');
  }

  return parsePhotosResponse(response, page);
};

const setPhotoStatus = (message) => {
  const status = document.querySelector('[data-photo-status]');

  if (status) {
    status.textContent = message;
  }
};

const getPhotoList = () => document.querySelector('[data-photo-list]');

const layoutPhotoMasonry = () => {
  const masonry = photosState.masonry;

  if (!masonry) {
    return;
  }

  window.requestAnimationFrame(() => {
    masonry.layout();
  });
};

const watchPhotoImages = (root) => {
  root.querySelectorAll('.photo-card__image').forEach((image) => {
    if (image.complete) {
      return;
    }

    image.addEventListener('load', layoutPhotoMasonry, {
      once: true,
    });
    image.addEventListener('error', layoutPhotoMasonry, {
      once: true,
    });
  });
};

const syncPhotoMasonry = () => {
  const list = getPhotoList();

  if (!list) {
    return;
  }

  if (!photosState.masonry) {
    photosState.masonry = new Masonry(list, {
      itemSelector: '.photo-card',
      columnWidth: '.photo-card',
      gutter: 10,
      percentPosition: true,
      transitionDuration: '0.2s',
    });
  } else {
    photosState.masonry.reloadItems();
  }

  watchPhotoImages(list);
  layoutPhotoMasonry();
  window.setTimeout(layoutPhotoMasonry, 120);
};

const destroyPhotoMasonry = () => {
  if (!photosState.masonry) {
    return;
  }

  photosState.masonry.destroy();
  photosState.masonry = null;
};

const refreshPhotosLightbox = () => {
  if (!photosState.lightbox) {
    photosState.lightbox = GLightbox({
      selector: '.photo-card__link',
      touchNavigation: true,
      loop: true,
      descPosition: 'bottom',
    });
    return;
  }

  photosState.lightbox.reload();
};

const createPhotoCard = (photo) => {
  const card = document.createElement('article');
  const link = document.createElement('a');
  const image = document.createElement('img');

  card.className = 'photo-card';
  card.dataset.photoType = photo.type;

  link.href = photo.fullUrl;
  link.className = 'photo-card__link glightbox';
  link.dataset.gallery = 'service-photos';
  link.dataset.type = 'image';
  link.dataset.description = photo.label;
  link.dataset.descPosition = 'bottom';
  link.setAttribute('aria-label', photo.label);

  image.src = photo.thumbnailUrl;
  image.alt = photo.label;
  image.loading = 'lazy';
  image.decoding = 'async';
  image.className = 'photo-card__image';

  link.append(image);
  card.append(link);

  return card;
};

const appendPhotos = (photos) => {
  const list = document.querySelector('[data-photo-list]');

  if (!list) {
    return;
  }

  const fragment = document.createDocumentFragment();
  photos.forEach((photo) => {
    fragment.append(createPhotoCard(photo));
  });

  list.append(fragment);
  refreshPhotosLightbox();
  syncPhotoMasonry();
};

const clearPhotos = () => {
  const list = document.querySelector('[data-photo-list]');

  if (list) {
    destroyPhotoMasonry();
    list.replaceChildren();
  }
};

const stopPhotosObserver = () => {
  photosState.hasMore = false;
  photosState.observer?.disconnect();
  photosState.observer = null;
};

const loadMorePhotos = async () => {
  const photoScreen = document.querySelector('[data-screen="photo"]');

  if (photosState.isLoading || !photosState.hasMore || !photoScreen?.classList.contains('screen--active')) {
    return;
  }

  photosState.isLoading = true;
  setPhotoStatus('Загрузка фото...');

  const nextPage = photosState.page + 1;

  try {
    const result = await fetchPhotosPage(nextPage);
    const photos = result.photos || [];

    if (!photos.length) {
      stopPhotosObserver();
      setPhotoStatus(photosState.page === 0 ? 'Фото пока не добавлены.' : '');
      return;
    }

    appendPhotos(photos);
    photosState.page = nextPage;
    photosState.hasMore = result.hasMore;
    setPhotoStatus('');

    if (!photosState.hasMore) {
      stopPhotosObserver();
    }
  } catch {
    stopPhotosObserver();
    setPhotoStatus(photosState.page === 0 ? 'Не удалось загрузить фото.' : '');
  } finally {
    photosState.isLoading = false;
  }
};

const startPhotosObserver = () => {
  const sentinel = document.querySelector('[data-photo-sentinel]');

  if (!sentinel || photosState.observer) {
    return;
  }

  photosState.observer = new IntersectionObserver((entries) => {
    if (entries.some((entry) => entry.isIntersecting)) {
      loadMorePhotos();
    }
  }, {
    rootMargin: '180px 0px',
  });

  photosState.observer.observe(sentinel);
};

const resetPhotosFeed = () => {
  photosState.page = 0;
  photosState.hasMore = true;
  photosState.isLoading = false;
  clearPhotos();
  setPhotoStatus('');
  startPhotosObserver();
};

const setPhotoFilter = (filter) => {
  const nextFilter = filter || 'all';

  if (photosState.activeFilter === nextFilter) {
    return;
  }

  photosState.activeFilter = nextFilter;

  document.querySelectorAll('[data-photo-filter]').forEach((button) => {
    const isActive = button.dataset.photoFilter === nextFilter;
    button.classList.toggle('review-filter-card--active', isActive);
    button.setAttribute('aria-pressed', String(isActive));
  });

  resetPhotosFeed();
  loadMorePhotos();
};

const initPhotoFilters = () => {
  const filters = document.querySelector('[data-photo-filters]');

  filters?.addEventListener('click', (event) => {
    const button = event.target.closest('[data-photo-filter]');

    if (!button) {
      return;
    }

    setPhotoFilter(button.dataset.photoFilter);
  });
};

export const initPhotosScreen = () => {
  const sentinel = document.querySelector('[data-photo-sentinel]');

  if (!sentinel || photosState.initialized) {
    return;
  }

  initPhotoFilters();
  refreshPhotosLightbox();
  syncPhotoMasonry();

  if (document.querySelectorAll('[data-photo-list] .photo-card').length) {
    photosState.page = 1;
  }

  photosState.initialized = true;
  startPhotosObserver();

  document.addEventListener('app:screenchange', (event) => {
    if (event.detail?.screen === 'photo') {
      syncPhotoMasonry();
    }

    if (event.detail?.screen === 'photo' && photosState.page === 0) {
      loadMorePhotos();
    }
  });

  if (document.querySelector('[data-screen="photo"]')?.classList.contains('screen--active')) {
    loadMorePhotos();
  }
};
