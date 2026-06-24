import GLightbox from 'glightbox';
import authorPhoto from '../img/author.jpg';

import { reviewsApiEndpoint, reviewsPerPage } from './config';
import { reviewsState } from './state';
import { clamp } from './utils';

const stripHtml = (value) => {
  const element = document.createElement('div');
  element.innerHTML = value || '';

  return element.textContent || '';
};

const getReviewInitials = (author) => {
  return author
    .split(/\s+/)
    .filter(Boolean)
    .slice(0, 2)
    .map((part) => part[0])
    .join('')
    .toUpperCase();
};

const normalizeReviewPhotos = (photos) => {
  if (!Array.isArray(photos)) {
    return [];
  }

  return Array.from(new Set(photos
    .map((photo) => {
      if (typeof photo === 'string') {
        return photo;
      }

      return photo?.url || photo?.src || photo?.sizes?.medium || photo?.sizes?.thumbnail || '';
    })
    .filter(Boolean)));
};

const normalizeReviewType = (type) => {
  const value = Array.isArray(type) ? type[0] : type;
  const normalizedValue = String(value || '').toLowerCase();

  if (/seasonal|сезон|замена/.test(normalizedValue)) {
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

  return normalizedValue;
};

const normalizeReview = (review, index = 0) => {
  const title = typeof review.title === 'object' ? review.title.rendered : review.title;
  const content = typeof review.content === 'object' ? review.content.rendered : review.content;

  return {
    id: review.id || review.review_id || `review-${Date.now()}-${index}`,
    author: review.author || review.author_name || review.name || stripHtml(title) || 'Клиент',
    avatar: review.avatar || review.avatar_url || review.author_avatar || authorPhoto,
    date: review.date_label || review.dateText || review.relative_date || review.date || '',
    rating: Number(review.rating || review.rate || 5),
    text: stripHtml(review.text || review.review || review.description || content || ''),
    photos: normalizeReviewPhotos(review.photos || review.images || review.gallery),
    type: normalizeReviewType(review.type || review.repair_type || review.service || review.category || review.category_slug),
  };
};

const getReviewsApiUrl = (page) => {
  const url = new URL(reviewsApiEndpoint, window.location.origin);
  url.searchParams.set('page', String(page));
  url.searchParams.set('per_page', String(reviewsPerPage));

  if (reviewsState.activeFilter !== 'all') {
    url.searchParams.set('repair_type', reviewsState.activeFilter);
  }

  return url.toString();
};

const parseReviewsResponse = async (response, page) => {
  const data = await response.json();
  const totalPagesHeader = Number(response.headers.get('X-WP-TotalPages'));
  const rawReviews = Array.isArray(data) ? data : data.reviews || data.items || data.data || [];
  const totalPages = Number(data.total_pages || data.totalPages || data.max_num_pages || totalPagesHeader);
  const hasMore = typeof data.has_more === 'boolean' ? data.has_more : totalPages ? page < totalPages : rawReviews.length === reviewsPerPage;

  return {
    hasMore,
    reviews: rawReviews.map(normalizeReview),
  };
};

const fetchReviewsPage = async (page) => {
  const response = await fetch(getReviewsApiUrl(page), {
    headers: {
      Accept: 'application/json',
    },
  });

  if (!response.ok) {
    throw new Error('Reviews request failed');
  }

  return parseReviewsResponse(response, page);
};

const createReviewRating = (rating) => {
  const ratingElement = document.createElement('div');
  const stars = document.createElement('span');
  const value = document.createElement('span');
  const filledStars = clamp(Math.round(Number(rating) || 0), 0, 5);

  ratingElement.className = 'review-rating';
  ratingElement.setAttribute('aria-label', `Оценка ${rating.toFixed(1)} из 5`);

  stars.className = 'review-rating__stars';
  stars.setAttribute('aria-hidden', 'true');

  Array.from({ length: 5 }, (_, index) => {
    const star = document.createElement('span');
    star.className = index < filledStars ? 'review-rating__star' : 'review-rating__star review-rating__no-fill';
    stars.append(star);

    return star;
  });

  value.className = 'review-rating__value';
  value.textContent = rating.toFixed(1);

  ratingElement.append(stars, value);

  return ratingElement;
};

const createReviewPhoto = (photo, reviewId) => {
  const link = document.createElement('a');
  const image = document.createElement('img');

  link.href = photo;
  link.className = 'review-card__photo-link glightbox';
  link.dataset.gallery = `review-${reviewId}`;
  link.dataset.type = 'image';

  image.src = photo;
  image.alt = '';
  image.loading = 'lazy';
  image.decoding = 'async';
  image.className = 'review-card__photo';

  link.append(image);

  return link;
};

const createReviewCard = (review) => {
  const card = document.createElement('article');
  const header = document.createElement('header');
  const avatar = review.avatar ? document.createElement('img') : document.createElement('span');
  const meta = document.createElement('div');
  const author = document.createElement('h3');
  const date = document.createElement('span');
  const text = document.createElement('p');

  card.className = 'review-card';
  card.dataset.reviewSource = 'remote';

  if (review.type) {
    card.dataset.reviewType = review.type;
  }

  header.className = 'review-card__header';
  avatar.className = 'review-card__avatar';

  if (review.avatar) {
    avatar.src = review.avatar;
    avatar.alt = '';
    avatar.loading = 'lazy';
    avatar.decoding = 'async';
  } else {
    avatar.textContent = getReviewInitials(review.author);
  }

  meta.className = 'review-card__meta';
  author.className = 'review-card__author';
  author.textContent = review.author;
  date.className = 'review-card__date';
  date.textContent = review.date;
  text.className = 'review-card__text';
  text.textContent = review.text;

  meta.append(author, createReviewRating(review.rating));
  header.append(avatar, meta, date);
  card.append(header, text);

  if (review.photos.length) {
    const gallery = document.createElement('div');
    gallery.className = 'review-card__gallery';
    review.photos.slice(0, 3).forEach((photo) => {
      gallery.append(createReviewPhoto(photo, review.id));
    });
    card.append(gallery);
  }

  return card;
};

const refreshReviewsLightbox = () => {
  if (!reviewsState.lightbox) {
    reviewsState.lightbox = GLightbox({
      selector: '.review-card__photo-link',
      touchNavigation: true,
      loop: true,
    });
    return;
  }

  reviewsState.lightbox.reload();
};

const appendReviews = (reviews) => {
  const list = document.querySelector('[data-reviews-list]');

  if (!list) {
    return;
  }

  const fragment = document.createDocumentFragment();
  reviews.map(normalizeReview).forEach((review) => {
    fragment.append(createReviewCard(review));
  });

  list.append(fragment);
  applyReviewFilter();
  refreshReviewsLightbox();
};

const stopReviewsObserver = () => {
  reviewsState.hasMore = false;
  reviewsState.observer?.disconnect();
  reviewsState.observer = null;
};

const startReviewsObserver = () => {
  const sentinel = document.querySelector('[data-reviews-sentinel]');

  if (!sentinel || reviewsState.observer) {
    return;
  }

  reviewsState.observer = new IntersectionObserver((entries) => {
    if (entries.some((entry) => entry.isIntersecting)) {
      loadMoreReviews();
    }
  }, {
    rootMargin: '160px 0px',
  });

  reviewsState.observer.observe(sentinel);
};

const removeRemoteReviews = () => {
  document.querySelectorAll('[data-review-source="remote"]').forEach((card) => {
    card.remove();
  });
};

const isReviewVisibleByFilter = (card) => {
  return reviewsState.activeFilter === 'all' || card.dataset.reviewType === reviewsState.activeFilter;
};

const applyReviewFilter = () => {
  document.querySelectorAll('[data-review-type]').forEach((card) => {
    card.hidden = !isReviewVisibleByFilter(card);
  });
};

const setReviewsFilter = (filter) => {
  const nextFilter = filter || 'all';

  if (reviewsState.activeFilter === nextFilter) {
    return;
  }

  reviewsState.activeFilter = nextFilter;
  reviewsState.page = nextFilter === 'all' ? 1 : 0;
  reviewsState.hasMore = true;
  reviewsState.isLoading = false;

  document.querySelectorAll('[data-review-filter]').forEach((button) => {
    const isActive = button.dataset.reviewFilter === nextFilter;
    button.classList.toggle('review-filter-card--active', isActive);
    button.setAttribute('aria-pressed', String(isActive));
  });

  removeRemoteReviews();
  applyReviewFilter();
  refreshReviewsLightbox();
  startReviewsObserver();
};

const initReviewFilters = () => {
  const filters = document.querySelector('[data-review-filters]');

  filters?.addEventListener('click', (event) => {
    const button = event.target.closest('[data-review-filter]');

    if (!button) {
      return;
    }

    setReviewsFilter(button.dataset.reviewFilter);
  });
};

const loadMoreReviews = async () => {
  const reviewsScreen = document.querySelector('[data-screen="reviews"]');

  if (reviewsState.isLoading || !reviewsState.hasMore || !reviewsScreen?.classList.contains('screen--active')) {
    return;
  }

  reviewsState.isLoading = true;

  const nextPage = reviewsState.page + 1;

  try {
    const result = await fetchReviewsPage(nextPage);
    const reviews = result.reviews || [];

    if (!reviews.length) {
      stopReviewsObserver();
      return;
    }

    appendReviews(reviews);
    reviewsState.page = nextPage;
    reviewsState.hasMore = result.hasMore;

    if (!reviewsState.hasMore) {
      stopReviewsObserver();
    }
  } catch {
    stopReviewsObserver();
  } finally {
    reviewsState.isLoading = false;
  }
};

export const initReviewsScreen = () => {
  const sentinel = document.querySelector('[data-reviews-sentinel]');

  if (!sentinel || reviewsState.initialized) {
    return;
  }

  refreshReviewsLightbox();
  initReviewFilters();
  applyReviewFilter();
  reviewsState.initialized = true;
  startReviewsObserver();
};
