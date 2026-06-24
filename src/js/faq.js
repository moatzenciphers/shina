import { faqApiEndpoint, faqPerPage } from './config';
import { faqState } from './state';

const stripHtml = (value) => {
  const element = document.createElement('div');
  element.innerHTML = value || '';

  return element.textContent || '';
};

const normalizeFaqAnswer = (answer) => {
  if (Array.isArray(answer)) {
    return answer.map(stripHtml).filter(Boolean);
  }

  return String(stripHtml(answer || ''))
    .split(/\n{2,}/)
    .map((paragraph) => paragraph.trim())
    .filter(Boolean);
};

const normalizeFaqItem = (item, index = 0) => {
  const title = typeof item.title === 'object' ? item.title.rendered : item.title;
  const content = typeof item.content === 'object' ? item.content.rendered : item.content;

  return {
    id: item.id || item.faq_id || `faq-${Date.now()}-${index}`,
    question: stripHtml(item.question || item.name || title || ''),
    answer: normalizeFaqAnswer(item.answer || item.text || item.description || content || ''),
  };
};

const getFaqApiUrl = (page) => {
  const url = new URL(faqApiEndpoint, window.location.origin);
  url.searchParams.set('page', String(page));
  url.searchParams.set('per_page', String(faqPerPage));

  if (faqState.search) {
    url.searchParams.set('search', faqState.search);
  }

  return url.toString();
};

const parseFaqResponse = async (response, page) => {
  const data = await response.json();
  const totalPagesHeader = Number(response.headers.get('X-WP-TotalPages'));
  const rawItems = Array.isArray(data) ? data : data.faq || data.items || data.data || [];
  const totalPages = Number(data.total_pages || data.totalPages || data.max_num_pages || totalPagesHeader);
  const hasMore = typeof data.has_more === 'boolean' ? data.has_more : totalPages ? page < totalPages : rawItems.length === faqPerPage;

  return {
    hasMore,
    items: rawItems.map(normalizeFaqItem).filter((item) => item.question && item.answer.length),
  };
};

const fetchFaqPage = async (page) => {
  const response = await fetch(getFaqApiUrl(page), {
    headers: {
      Accept: 'application/json',
    },
  });

  if (!response.ok) {
    throw new Error('FAQ request failed');
  }

  return parseFaqResponse(response, page);
};

const createFaqItem = (item) => {
  const details = document.createElement('details');
  const summary = document.createElement('summary');
  const question = document.createElement('span');
  const marker = document.createElement('span');
  const answer = document.createElement('div');

  details.className = 'faq-item';
  details.dataset.faqItem = '';
  details.dataset.faqSource = 'remote';

  summary.className = 'faq-item__question';
  question.textContent = item.question;
  marker.className = 'faq-item__marker';
  marker.setAttribute('aria-hidden', 'true');

  answer.className = 'faq-item__answer';
  item.answer.forEach((paragraphText) => {
    const paragraph = document.createElement('p');
    paragraph.textContent = paragraphText;
    answer.append(paragraph);
  });

  summary.append(question, marker);
  details.append(summary, answer);

  return details;
};

const setFaqStatus = (message) => {
  const status = document.querySelector('[data-faq-status]');

  if (status) {
    status.textContent = message;
  }
};

const getFaqScreen = () => document.querySelector('[data-screen="faq"]');

const isFaqScreenActive = () => getFaqScreen()?.classList.contains('screen--active');

const setInitialFaqVisibility = (isVisible) => {
  document.querySelectorAll('[data-faq-item]:not([data-faq-source="remote"])').forEach((item) => {
    item.hidden = !isVisible;
  });
};

const removeRemoteFaqItems = () => {
  document.querySelectorAll('[data-faq-source="remote"]').forEach((item) => {
    item.remove();
  });
};

const appendFaqItems = (items) => {
  const list = document.querySelector('[data-faq-list]');

  if (!list) {
    return;
  }

  const fragment = document.createDocumentFragment();
  items.forEach((item) => {
    fragment.append(createFaqItem(item));
  });

  list.append(fragment);
};

const stopFaqObserver = () => {
  faqState.hasMore = false;
  faqState.observer?.disconnect();
  faqState.observer = null;
};

const startFaqObserver = () => {
  const sentinel = document.querySelector('[data-faq-sentinel]');

  if (!sentinel || faqState.observer) {
    return;
  }

  faqState.observer = new IntersectionObserver((entries) => {
    if (entries.some((entry) => entry.isIntersecting)) {
      loadMoreFaqItems();
    }
  }, {
    rootMargin: '160px 0px',
  });

  faqState.observer.observe(sentinel);
};

const resetFaqList = ({ search = '' } = {}) => {
  faqState.search = search.trim();
  faqState.page = faqState.search ? 0 : 1;
  faqState.hasMore = true;
  faqState.isLoading = false;

  removeRemoteFaqItems();
  setInitialFaqVisibility(!faqState.search);
  setFaqStatus('');
  stopFaqObserver();
  faqState.hasMore = true;
  startFaqObserver();
};

const loadMoreFaqItems = async () => {
  if (faqState.isLoading || !faqState.hasMore || !isFaqScreenActive()) {
    return;
  }

  faqState.isLoading = true;
  setFaqStatus('Загрузка вопросов...');

  const nextPage = faqState.page + 1;

  try {
    const result = await fetchFaqPage(nextPage);
    const items = result.items || [];

    if (!items.length) {
      setFaqStatus(faqState.search && nextPage === 1 ? 'Ничего не найдено' : '');
      stopFaqObserver();
      return;
    }

    appendFaqItems(items);
    faqState.page = nextPage;
    faqState.hasMore = result.hasMore;
    setFaqStatus('');

    if (!faqState.hasMore) {
      stopFaqObserver();
    }
  } catch {
    setFaqStatus('Не удалось загрузить вопросы');
    stopFaqObserver();
  } finally {
    faqState.isLoading = false;
  }
};

const initFaqSearch = () => {
  const input = document.querySelector('[data-faq-search]');

  input?.addEventListener('input', () => {
    window.clearTimeout(faqState.searchDebounceId);

    faqState.searchDebounceId = window.setTimeout(() => {
      resetFaqList({
        search: input.value,
      });

      if (faqState.search && isFaqScreenActive()) {
        loadMoreFaqItems();
      }
    }, 350);
  });
};

export const initFaqScreen = () => {
  const sentinel = document.querySelector('[data-faq-sentinel]');

  if (!sentinel || faqState.initialized) {
    return;
  }

  initFaqSearch();
  faqState.initialized = true;
  startFaqObserver();
};
