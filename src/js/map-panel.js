import Inputmask from 'inputmask';

import { fixedArrivalTime, mkadPolygon, moscowCenter, moscowMasterPoints, nightTariff, serviceGeocodeBounds, tariffs } from './config';
import { createMasterPlacemark, loadYandexMaps, requestOpenRouteServiceRoute } from './map-shared';
import { getServiceLocationByCoords } from './service-location';
import { formatPrice, getDistanceBetweenCoords, roundUpToStep } from './utils';

const isNight = () => {
  const hour = Number(new Intl.DateTimeFormat('ru-RU', {
    hour: '2-digit', hour12: false, timeZone: 'Europe/Moscow',
  }).format(new Date()).replace(/\D/g, '')) % 24;
  const { startHour, endHour } = nightTariff;

  if (startHour === endHour) return false;
  return startHour > endHour ? hour >= startHour || hour < endHour : hour >= startHour && hour < endHour;
};

const getCalloutPrice = (location) => {
  const mode = isNight() ? 'night' : 'day';
  return location.insideMkad
    ? tariffs.callout.insideMkad[mode]
    : tariffs.callout.outsideMkad[mode] + location.distanceOutsideKm * tariffs.callout.outsideMkad.perKm;
};

export const initMapPanel = () => {
  const panel = document.querySelector('[data-map-panel]');
  const form = panel?.querySelector('.wpcf7 form, [data-map-panel-form]');
  const input = panel?.querySelector('[data-map-panel-address], #map-panel-address');
  const phone = panel?.querySelector('[data-map-panel-phone], #map-panel-phone');
  const consent = panel?.querySelector('[data-map-panel-consent], input[name="privacy_consent"]');
  const contact = panel?.querySelector('[data-map-panel-contact]');
  const status = panel?.querySelector('[data-map-panel-status]');
  const loading = panel?.querySelector('[data-map-panel-loading]');
  const canvas = panel?.querySelector('[data-map-panel-canvas]');
  const zoomButtons = panel ? Array.from(panel.querySelectorAll('[data-map-panel-zoom]')) : [];
  const locationButton = panel?.querySelector('[data-map-panel-location]');
  const submitText = panel?.querySelector('[data-map-panel-submit-text]');
  const coordsField = panel?.querySelector('[data-map-panel-coords], #map-panel-coords');
  const priceField = panel?.querySelector('[data-map-panel-price], #map-panel-price');
  const arrivalField = panel?.querySelector('[data-map-panel-arrival], #map-panel-arrival');

  if (!form || !input || !phone || !consent || !contact || !status || !canvas || !coordsField || !priceField || !arrivalField || !submitText) return;
  form.classList.add('map-panel__form');
  phone.disabled = true;
  consent.disabled = true;

  panel.addEventListener('wpcf7mailsent', () => showStatus('Заявка отправлена. Скоро свяжемся с вами.', 'success'));
  panel.addEventListener('wpcf7invalid', () => showStatus('Проверьте заполнение полей.', 'error'));
  panel.addEventListener('wpcf7unaccepted', () => showStatus('Подтвердите согласие с политикой конфиденциальности.', 'error'));
  panel.addEventListener('wpcf7mailfailed', () => showStatus('Не удалось отправить заявку. Попробуйте позже.', 'error'));
  panel.addEventListener('wpcf7spam', () => showStatus('Не удалось отправить заявку. Попробуйте позже.', 'error'));

  Inputmask({ mask: '+7 (999) 999-99-99', showMaskOnHover: false }).mask(phone);

  let map = null;
  let marker = null;
  let suggest = null;
  let checkedAddress = '';
  let requestId = 0;

  const showStatus = (message, type = '') => {
    status.textContent = message;
    status.dataset.status = type;
    status.hidden = !message;
  };

  const resetCheck = () => {
    requestId += 1;
    checkedAddress = '';
    form.classList.remove('map-panel__form--checked');
    contact.hidden = true;
    phone.disabled = true;
    consent.disabled = true;
    phone.setCustomValidity('');
    submitText.textContent = 'Проверить адрес';
    coordsField.value = '';
    priceField.value = '';
    arrivalField.value = '';
    showStatus('');
    if (map && marker) map.geoObjects.remove(marker);
    marker = null;
  };

  const showPoint = (coords, denied = false) => {
    if (!map) return;
    if (marker) map.geoObjects.remove(marker);
    marker = new window.ymaps.Placemark(coords, {}, {
      preset: 'islands#circleDotIcon',
      iconColor: denied ? '#d95050' : '#ff9f1a',
    });
    map.geoObjects.add(marker);
    map.setCenter(coords, 13, { checkZoomRange: true, duration: 250 });
  };

  const showEstimate = (location, arrival) => {
    const price = getCalloutPrice(location);
    const range = arrival.range;
    priceField.value = String(price);
    arrivalField.value = `${range[0]}–${range[1]} мин`;
    const distance = location.insideMkad ? 'В пределах МКАД' : `За МКАД: ${location.distanceOutsideKm} км`;
    const timeNote = arrival.calculated
      ? `прибытие ориентировочно ${arrivalField.value}`
      : `прибытие ориентировочно ${arrivalField.value} (маршрут не рассчитан, время уточним)`;
    showStatus(`${distance}. Выезд от ${formatPrice(price)} ₽, ${timeNote}.`, 'success');
  };

  const getArrivalByRoute = async (coords) => {
    const nearest = moscowMasterPoints.reduce((best, point) => {
      const distance = getDistanceBetweenCoords(point.coords, coords);
      return !best || distance < best.distance ? { ...point, distance } : best;
    }, null);

    if (!nearest) return { range: fixedArrivalTime, calculated: false };

    const controller = new AbortController();
    const timeout = window.setTimeout(() => controller.abort(), 10000);
    try {
      const { durationSeconds } = await requestOpenRouteServiceRoute(nearest.coords, coords, controller.signal);
      const start = Math.max(15, roundUpToStep(durationSeconds / 60, 5));
      return { range: [start, start + 20], calculated: true };
    } catch {
      return { range: fixedArrivalTime, calculated: false };
    } finally {
      window.clearTimeout(timeout);
    }
  };

  const acceptGeoObject = async (geoObject, currentRequest, fallbackAddress = '') => {
    if (currentRequest !== requestId) return;
    const coords = geoObject?.geometry?.getCoordinates?.();
    if (!coords) {
      showStatus('Не удалось определить адрес. Уточните улицу и дом.', 'error');
      return;
    }

    const address = geoObject.getAddressLine?.() || geoObject.properties?.get?.('text') || fallbackAddress;
    const location = getServiceLocationByCoords(coords, geoObject);
    showPoint(coords, location.status === 'denied');

    if (location.status === 'denied') {
      showStatus('Вызов невозможен по этому адресу. Работаем только по Москве и Московской области.', 'error');
      return;
    }

    const arrival = await getArrivalByRoute(coords);
    if (currentRequest !== requestId) return;
    input.value = address;
    checkedAddress = address;
    form.classList.add('map-panel__form--checked');
    coordsField.value = coords.join(', ');
    contact.hidden = false;
    phone.disabled = false;
    consent.disabled = false;
    submitText.textContent = 'Отправить заявку';
    showEstimate(location, arrival);
    window.requestAnimationFrame(() => map?.container.fitToViewport());
  };

  const checkAddress = async (address) => {
    const normalized = address.trim();
    resetCheck();
    if (normalized.length < 6) {
      showStatus('Введите улицу и дом для проверки адреса.', 'error');
      input.focus();
      return;
    }

    const currentRequest = requestId;
    showStatus('Проверяем адрес…');
    try {
      await loadYandexMaps();
      const result = await window.ymaps.geocode(normalized, { boundedBy: serviceGeocodeBounds, results: 1 });
      await acceptGeoObject(result.geoObjects.get(0), currentRequest, normalized);
    } catch {
      if (currentRequest === requestId) showStatus('Не удалось проверить адрес. Попробуйте ещё раз.', 'error');
    }
  };

  const initMap = async () => {
    try {
      await loadYandexMaps();
      map = new window.ymaps.Map(canvas, { center: moscowCenter, zoom: 9, controls: [] }, {
        suppressMapOpenBlock: true,
        yandexMapDisablePoiInteractivity: true,
      });
      map.behaviors.disable(['scrollZoom', 'dblClickZoom', 'rightMouseButtonMagnifier']);
      const masters = new window.ymaps.GeoObjectCollection();
      moscowMasterPoints.forEach((master) => masters.add(createMasterPlacemark(master, true)));
      map.geoObjects.add(masters);
      if ('ResizeObserver' in window) {
        new ResizeObserver(() => map.container.fitToViewport()).observe(canvas);
      }
      if (window.ymaps.Polygon) {
        map.geoObjects.add(new window.ymaps.Polygon([mkadPolygon], {}, {
          fillOpacity: 0,
          strokeColor: '#ff9f1a',
          strokeOpacity: .9,
          strokeWidth: 3,
          interactivityModel: 'default#silent',
        }));
      }
      loading.hidden = true;
      zoomButtons.forEach((button) => { button.disabled = false; });
      if (locationButton) locationButton.disabled = false;
      map.events.add('click', async (event) => {
        const coords = event.get('coords');
        if (!Array.isArray(coords)) return;
        resetCheck();
        const currentRequest = requestId;
        showStatus('Проверяем выбранную точку…');
        try {
          const result = await window.ymaps.geocode(coords, { results: 1 });
          await acceptGeoObject(result.geoObjects.get(0), currentRequest);
        } catch {
          if (currentRequest === requestId) showStatus('Не удалось определить выбранную точку.', 'error');
        }
      });
    } catch {
      loading.textContent = 'Карта не загрузилась. Проверьте ключ Яндекс Карт.';
    }
  };

  const initSuggest = async () => {
    if (suggest || window.__YANDEX_SUGGEST_DISABLED__) return;
    try {
      await loadYandexMaps();
      if (!window.ymaps?.SuggestView) return;
      suggest = new window.ymaps.SuggestView(input, { results: 5 });
      suggest.events.add('select', (event) => {
        const address = event.get('item')?.value;
        if (address) {
          input.value = address;
          checkAddress(address);
        }
      });
    } catch {
      // Ошибку загрузки карты покажет проверка адреса.
    }
  };

  zoomButtons.forEach((button) => {
    button.addEventListener('click', (event) => {
      event.preventDefault();
      event.stopPropagation();
      if (!map) return;
      const step = button.dataset.mapPanelZoom === 'in' ? 1 : -1;
      map.setZoom(Math.max(3, Math.min(19, map.getZoom() + step)), { checkZoomRange: true, duration: 180 });
    });
  });

  locationButton?.addEventListener('click', async (event) => {
    event.preventDefault();
    event.stopPropagation();
    if (!map || locationButton.disabled) return;
    resetCheck();
    const currentRequest = requestId;
    locationButton.disabled = true;
    locationButton.setAttribute('aria-busy', 'true');
    showStatus('Определяем местоположение…');
    try {
      let geoObject;
      try {
        const result = await window.ymaps.geolocation.get({ provider: 'browser', autoReverseGeocode: true, timeout: 15000 });
        geoObject = result.geoObjects.get(0);
        if (!geoObject) throw new Error('Location unavailable');
      } catch {
        const position = await new Promise((resolve, reject) => {
          if (!navigator.geolocation) return reject(new Error('Geolocation unavailable'));
          navigator.geolocation.getCurrentPosition(resolve, reject, { enableHighAccuracy: true, timeout: 15000, maximumAge: 60000 });
        });
        const result = await window.ymaps.geocode([position.coords.latitude, position.coords.longitude], { results: 1 });
        geoObject = result.geoObjects.get(0);
      }
      await acceptGeoObject(geoObject, currentRequest);
    } catch {
      if (currentRequest === requestId) showStatus('Не удалось определить местоположение. Разрешите доступ к геолокации или введите адрес вручную.', 'error');
    } finally {
      locationButton.disabled = false;
      locationButton.removeAttribute('aria-busy');
    }
  });

  input.addEventListener('focus', initSuggest);
  input.addEventListener('input', resetCheck);
  phone.addEventListener('input', () => phone.setCustomValidity(''));

  form.addEventListener('submit', (event) => {
    if (!checkedAddress || input.value.trim() !== checkedAddress) {
      event.preventDefault();
      event.stopImmediatePropagation();
      checkAddress(input.value);
      return;
    }
    if (!phone.inputmask?.isComplete()) {
      event.preventDefault();
      event.stopImmediatePropagation();
      phone.setCustomValidity('Введите номер телефона полностью.');
      phone.reportValidity();
      phone.focus();
      return;
    }
    if (!consent.checked) {
      event.preventDefault();
      event.stopImmediatePropagation();
      consent.reportValidity();
      return;
    }

    if (form.closest('.wpcf7')) return;
    event.preventDefault();

    const request = new CustomEvent('map-panel:submit', {
      bubbles: true,
      cancelable: true,
      detail: Object.fromEntries(new FormData(form)),
    });
    form.dispatchEvent(request);
    if (!request.defaultPrevented) showStatus('Отправка заявок пока не подключена.', 'error');
  }, true);

  if ('IntersectionObserver' in window) {
    const observer = new IntersectionObserver((entries) => {
      if (!entries.some((entry) => entry.isIntersecting)) return;
      observer.disconnect();
      initMap();
    }, { rootMargin: '300px' });
    observer.observe(panel);
  } else {
    initMap();
  }
};
