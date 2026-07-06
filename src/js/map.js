import { fixedArrivalTime, moscowCenter, moscowMasterPoints, serviceGeocodeBounds } from './config';
import {
  collapseCalculatorSheet,
  resetArrivalTime,
  setAddress,
  setArrivalTimeByMinutes,
  updateSummary,
} from './calculator';
import {
  getServiceLocationByCoords,
  resetServiceLocation,
  setLocationStatus,
  setServiceLocationByCoords,
  showServiceLocationStatus,
} from './service-location';
import { state } from './state';
import {
  clamp,
  getBoundsCenter,
  getBoundsFromPoints,
  getDistanceBetweenCoords,
  getMercatorPoint,
} from './utils';

const serviceMapState = {
  debounceId: null,
  instance: null,
  lastRenderedAddress: '',
  markerLayouts: {},
  masterCollection: null,
  customerPlacemark: null,
  lastBounds: null,
  resizeFrameId: null,
  resizeObserver: null,
  resizeTimeoutId: null,
  route: null,
  routeAbortController: null,
  requestId: 0,
};

const mapPickerState = {
  instance: null,
  placemark: null,
  coords: null,
  address: '',
  geoObject: null,
};

const yandexMapsState = {
  promise: null,
  suggestView: null,
};

const desktopMapQuery = '(min-width: 1024px)';
const addressLocationResolvingText = 'Определяем местоположение';
const openRouteServiceDirectionsUrl = 'https://api.openrouteservice.org/v2/directions/driving-car/geojson';

const getYandexMapsUrl = () => String(window.__YANDEX_MAPS_URL__ || '');
const getOpenRouteServiceApiKey = () => String(window.__OPENROUTESERVICE_API_KEY__ || '');

const getAddressInputValue = (input) => {
  if (!input) {
    return '';
  }

  if (input.value === addressLocationResolvingText) {
    return input.dataset.locationPreviousValue || '';
  }

  return input.value;
};

const setAddressInputResolving = (isResolving) => {
  const input = document.querySelector('[data-address-input]');

  if (!input) {
    return;
  }

  if (isResolving) {
    if (!('locationPreviousValue' in input.dataset)) {
      input.dataset.locationPreviousValue = input.value;
      input.dataset.locationPreviousPlaceholder = input.getAttribute('placeholder') || '';
    }

    input.value = addressLocationResolvingText;
    input.placeholder = addressLocationResolvingText;
    input.disabled = true;
    input.setAttribute('aria-busy', 'true');
    return;
  }

  if (input.value === addressLocationResolvingText) {
    input.value = input.dataset.locationPreviousValue || '';
  }

  input.placeholder = input.dataset.locationPreviousPlaceholder || 'Введите адрес';
  input.disabled = false;
  input.removeAttribute('aria-busy');
  delete input.dataset.locationPreviousValue;
  delete input.dataset.locationPreviousPlaceholder;
};

const setYandexMapsMissingStatus = () => {
  setLocationStatus('Укажите YANDEX_MAPS_API_KEY и YANDEX_SUGGEST_API_KEY в .env для карт и подсказок.');
};

const isDesktopMapLayout = () => {
  return window.matchMedia?.(desktopMapQuery).matches || false;
};

const syncServiceMapBehaviors = () => {
  const map = serviceMapState.instance;

  if (!map?.behaviors) {
    return;
  }

  if (isDesktopMapLayout()) {
    map.behaviors.enable('scrollZoom');
  } else {
    map.behaviors.disable('scrollZoom');
  }
};

const isCalculatorSheetOpen = () => {
  return document.querySelector('[data-calculator]')?.classList.contains('calculator--expanded') || false;
};

const canUseServiceMapAsAddressPicker = () => {
  const calculator = document.querySelector('[data-calculator]');

  return !calculator?.classList.contains('calculator--step-confirm') &&
    !calculator?.classList.contains('calculator--step-success');
};

const closeYandexSuggest = (input = document.querySelector('[data-address-input]')) => {
  if (input && document.activeElement === input) {
    input.blur();
  }

  try {
    yandexMapsState.suggestView?.state?.set?.('open', false);
    yandexMapsState.suggestView?.state?.set?.('items', []);
  } catch {
    // SuggestView versions expose state slightly differently.
  }
};

const loadYandexMaps = () => {
  if (window.ymaps) {
    return new Promise((resolve) => {
      window.ymaps.ready(() => resolve(window.ymaps));
    });
  }

  if (yandexMapsState.promise) {
    return yandexMapsState.promise;
  }

  const scriptUrl = getYandexMapsUrl();

  if (window.__YANDEX_MAPS_DISABLED__ || !scriptUrl) {
    yandexMapsState.promise = Promise.reject(new Error('Yandex Maps API URL is not configured'));
    return yandexMapsState.promise;
  }

  yandexMapsState.promise = new Promise((resolve, reject) => {
    const existingScript = document.querySelector('script[data-yandex-maps-loader]');
    const script = existingScript || document.createElement('script');

    const handleLoad = () => {
      if (!window.ymaps) {
        yandexMapsState.promise = null;
        reject(new Error('Yandex Maps API did not expose window.ymaps'));
        return;
      }

      window.ymaps.ready(
        () => resolve(window.ymaps),
        () => {
          yandexMapsState.promise = null;
          reject(new Error('Yandex Maps API failed to initialize'));
        },
      );
    };

    script.addEventListener('load', handleLoad, { once: true });
    script.addEventListener('error', () => {
      yandexMapsState.promise = null;
      reject(new Error('Yandex Maps API failed to load'));
    }, { once: true });

    if (!existingScript) {
      script.src = scriptUrl;
      script.async = true;
      script.defer = true;
      script.dataset.yandexMapsLoader = '';
      document.head.append(script);
    }
  });

  return yandexMapsState.promise;
};

const scheduleAfterFirstPaint = (callback) => {
  const run = () => {
    if ('requestIdleCallback' in window) {
      window.requestIdleCallback(callback, { timeout: 1800 });
      return;
    }

    window.setTimeout(callback, 900);
  };

  window.requestAnimationFrame(() => {
    window.requestAnimationFrame(run);
  });
};

const getNearestMaster = (coords) => {
  return moscowMasterPoints.reduce((nearest, master) => {
    const distance = getDistanceBetweenCoords(master.coords, coords);

    if (!nearest || distance < nearest.distance) {
      return {
        ...master,
        distance,
      };
    }

    return nearest;
  }, null);
};

const normalizeMoscowAddress = (address) => address.trim();

const getServiceMarkerLayout = (type) => {
  if (!window.ymaps?.templateLayoutFactory) {
    return null;
  }

  if (!serviceMapState.markerLayouts[type]) {
    serviceMapState.markerLayouts[type] = window.ymaps.templateLayoutFactory.createClass(
      `<div class="service-map-marker service-map-marker--${type}"><span></span></div>`,
    );
  }

  return serviceMapState.markerLayouts[type];
};

const getServiceMarkerOptions = (type, radius) => {
  const layout = getServiceMarkerLayout(type);

  if (!layout) {
    return {
      iconColor: type === 'customer' ? '#4ea1ff' : '#ff9f1a',
      preset: 'islands#circleDotIcon',
    };
  }

  return {
    iconLayout: layout,
    iconShape: {
      type: 'Circle',
      coordinates: [0, 0],
      radius,
    },
  };
};

const createMasterPlacemark = (master) => {
  return new window.ymaps.Placemark(
    master.coords,
    {
      masterId: master.id,
      markerType: 'master',
    },
    {
      cursor: 'default',
      hasBalloon: false,
      hasHint: false,
      interactiveZIndex: false,
      openBalloonOnClick: false,
      openHintOnHover: false,
      opacity: 0,
      visible: false,
      zIndex: 1000,
      zIndexActive: 1000,
      ...getServiceMarkerOptions('master', 16),
    },
  );
};

const createCustomerPlacemark = (coords) => {
  return new window.ymaps.Placemark(
    coords,
    {
      markerType: 'customer',
    },
    {
      cursor: 'default',
      hasBalloon: false,
      hasHint: false,
      interactiveZIndex: false,
      openBalloonOnClick: false,
      openHintOnHover: false,
      zIndex: 1001,
      zIndexActive: 1001,
      ...getServiceMarkerOptions('customer', 18),
    },
  );
};

const createMapPickerPlacemark = (coords) => {
  return new window.ymaps.Placemark(
    coords,
    {
      markerType: 'customer',
    },
    {
      cursor: 'grab',
      draggable: true,
      hasBalloon: false,
      hasHint: false,
      openBalloonOnClick: false,
      openHintOnHover: false,
      ...getServiceMarkerOptions('customer', 18),
    },
  );
};

const initServiceMap = () => {
  const container = document.querySelector('[data-service-map]');

  if (!container || !window.ymaps?.Map) {
    return null;
  }

  if (serviceMapState.instance) {
    return serviceMapState.instance;
  }

  container.classList.add('hero__map-placeholder--loading');

  serviceMapState.instance = new window.ymaps.Map(
    container,
    {
      center: moscowCenter,
      controls: [],
      zoom: 10,
    },
    {
      suppressMapOpenBlock: true,
      yandexMapDisablePoiInteractivity: true,
    },
  );

  serviceMapState.instance.behaviors.disable([
    'rightMouseButtonMagnifier',
  ]);
  syncServiceMapBehaviors();

  container.addEventListener('pointerdown', (event) => {
    if (!canUseServiceMapAsAddressPicker()) {
      event.preventDefault();
      event.stopImmediatePropagation();
      return;
    }

    closeYandexSuggest();

    if (isDesktopMapLayout()) {
      return;
    }

    if (!isCalculatorSheetOpen()) {
      return;
    }

    collapseCalculatorSheet();
    scheduleServiceMapRefit();
  }, {
    capture: true,
  });

  serviceMapState.instance.events.add('click', (event) => {
    if (!canUseServiceMapAsAddressPicker()) {
      return;
    }

    const coords = event.get('coords');

    if (Array.isArray(coords)) {
      applyAddressFromCoords(coords);
    }
  });

  serviceMapState.masterCollection = new window.ymaps.GeoObjectCollection();
  moscowMasterPoints.forEach((master) => {
    serviceMapState.masterCollection.add(createMasterPlacemark(master));
  });

  serviceMapState.instance.geoObjects.add(serviceMapState.masterCollection);
  if ('ResizeObserver' in window && !serviceMapState.resizeObserver) {
    serviceMapState.resizeObserver = new ResizeObserver(scheduleServiceMapRefit);
    serviceMapState.resizeObserver.observe(container);
  } else {
    window.addEventListener('resize', scheduleServiceMapRefit);
  }

  container.classList.add('hero__map-placeholder--ready');
  container.classList.remove('hero__map-placeholder--loading');

  return serviceMapState.instance;
};

const clearServiceMapRoute = () => {
  const map = serviceMapState.instance;

  serviceMapState.routeAbortController?.abort();
  serviceMapState.routeAbortController = null;

  if (!map) {
    return;
  }

  if (serviceMapState.customerPlacemark) {
    map.geoObjects.remove(serviceMapState.customerPlacemark);
    serviceMapState.customerPlacemark = null;
  }

  if (serviceMapState.route) {
    map.geoObjects.remove(serviceMapState.route);
    serviceMapState.route = null;
  }
};

const showOnlyNearestMaster = (masterId) => {
  serviceMapState.masterCollection?.each((placemark) => {
    const isNearest = placemark.properties.get('masterId') === masterId;

    placemark.options.set({
      opacity: isNearest ? 1 : 0,
      visible: isNearest,
    });
  });
};

const showAllMasters = () => {
  serviceMapState.masterCollection?.each((placemark) => {
    placemark.options.set({
      opacity: 1,
      visible: true,
    });
  });
};

const resetServiceMapToMasters = () => {
  serviceMapState.requestId += 1;
  clearServiceMapRoute();
  showAllMasters();
  fitAllMasters();
};

const normalizeServiceMapBounds = (bounds) => {
  if (!bounds) {
    return null;
  }

  const normalizedBounds = getBoundsFromPoints(bounds);
  const [[minLat, minLng], [maxLat, maxLng]] = normalizedBounds;

  if (minLat !== maxLat || minLng !== maxLng) {
    return normalizedBounds;
  }

  const offset = 0.004;

  return [
    [minLat - offset, minLng - offset],
    [maxLat + offset, maxLng + offset],
  ];
};

const getServiceMapZoomMargin = (map) => {
  const [width = 0, height = 0] = map.container.getSize?.() || [];
  const horizontalMargin = Math.min(Math.round(width * 0.12), 36);
  const verticalMargin = Math.min(Math.round(height * 0.14), 36);
  const bottomSheetOverlap = isDesktopMapLayout() ? 0 : 42;

  return [verticalMargin, horizontalMargin, verticalMargin + bottomSheetOverlap, horizontalMargin];
};

const getServiceMapFitZoom = (map, bounds) => {
  const tileSize = 256;
  const maxZoom = 17;
  const minZoom = 1;
  const [[minLat, minLng], [maxLat, maxLng]] = bounds;
  const [width = 0, height = 0] = map.container.getSize?.() || [];
  const [topMargin, rightMargin, bottomMargin, leftMargin] = getServiceMapZoomMargin(map);
  const viewportWidth = Math.max(width - leftMargin - rightMargin, 1);
  const viewportHeight = Math.max(height - topMargin - bottomMargin, 1);
  const topLeft = getMercatorPoint([maxLat, minLng]);
  const bottomRight = getMercatorPoint([minLat, maxLng]);
  const lngDelta = Math.abs(bottomRight.x - topLeft.x);
  const latDelta = Math.abs(bottomRight.y - topLeft.y);
  const zoomByWidth = lngDelta > 0 ? Math.floor(Math.log2(viewportWidth / (tileSize * lngDelta))) : maxZoom;
  const zoomByHeight = latDelta > 0 ? Math.floor(Math.log2(viewportHeight / (tileSize * latDelta))) : maxZoom;

  return clamp(Math.min(zoomByWidth, zoomByHeight), minZoom, maxZoom);
};

const getCoordsFromMercatorPoint = ({ x, y }) => {
  const lng = x * 360 - 180;
  const lat = (180 / Math.PI) * (2 * Math.atan(Math.exp((0.5 - y) * 2 * Math.PI)) - Math.PI / 2);

  return [lat, lng];
};

const getServiceMapFitCenter = (map, bounds, zoom, zoomMargin) => {
  const tileSize = 256;
  const [width = 0, height = 0] = map.container.getSize?.() || [];
  const [topMargin, rightMargin, bottomMargin, leftMargin] = zoomMargin;
  const viewportWidth = Math.max(width - leftMargin - rightMargin, 1);
  const viewportHeight = Math.max(height - topMargin - bottomMargin, 1);
  const contentCenterX = leftMargin + viewportWidth / 2;
  const contentCenterY = topMargin + viewportHeight / 2;
  const fullCenterX = width / 2;
  const fullCenterY = height / 2;
  const pixelsPerWorld = tileSize * (2 ** zoom);
  const center = getMercatorPoint(getBoundsCenter(bounds));

  return getCoordsFromMercatorPoint({
    x: center.x - (contentCenterX - fullCenterX) / pixelsPerWorld,
    y: center.y - (contentCenterY - fullCenterY) / pixelsPerWorld,
  });
};

const fitServiceMap = (bounds) => {
  const map = serviceMapState.instance;
  const normalizedBounds = normalizeServiceMapBounds(bounds);

  if (!map || !normalizedBounds) {
    return;
  }

  serviceMapState.lastBounds = normalizedBounds;

  window.requestAnimationFrame(() => {
    map.container.fitToViewport();

    window.requestAnimationFrame(() => {
      map.container.fitToViewport();

      const zoom = getServiceMapFitZoom(map, normalizedBounds);
      const zoomMargin = getServiceMapZoomMargin(map);
      const center = getServiceMapFitCenter(map, normalizedBounds, zoom, zoomMargin);
      const setRouteCamera = () => {
        map.setCenter(center, zoom, {
          checkZoomRange: true,
          duration: 200,
        });
        map.setZoom(zoom, {
          duration: 200,
        });
      };

      if (typeof map.setBounds === 'function') {
        const boundsResult = map.setBounds(normalizedBounds, {
          checkZoomRange: true,
          duration: 200,
          zoomMargin,
        });

        if (boundsResult?.then) {
          boundsResult.then(() => {
            setRouteCamera();
            window.setTimeout(setRouteCamera, 120);
          });
          return;
        }
      }

      setRouteCamera();
      window.setTimeout(setRouteCamera, 120);
    });
  });
};

const refitServiceMap = () => {
  if (!serviceMapState.lastBounds) {
    serviceMapState.instance?.container?.fitToViewport();
    return;
  }

  fitServiceMap(serviceMapState.lastBounds);
};

const scheduleServiceMapRefit = () => {
  window.cancelAnimationFrame(serviceMapState.resizeFrameId);
  window.clearTimeout(serviceMapState.resizeTimeoutId);

  serviceMapState.resizeFrameId = window.requestAnimationFrame(() => {
    serviceMapState.resizeFrameId = null;
    refitServiceMap();
  });

  serviceMapState.resizeTimeoutId = window.setTimeout(refitServiceMap, 520);
};

const fitAllMasters = () => {
  fitServiceMap(getBoundsFromPoints(moscowMasterPoints.map((master) => master.coords)));
};

const renderInitialServiceMap = () => {
  loadYandexMaps().then(() => {
    const map = initServiceMap();

    if (!map) {
      return;
    }

    clearServiceMapRoute();
    showAllMasters();
    fitAllMasters();
  }).catch(() => {});
};

const requestOpenRouteServiceRoute = async (startCoords, endCoords, signal) => {
  const apiKey = getOpenRouteServiceApiKey();

  if (!apiKey) {
    throw new Error('OPENROUTESERVICE_API_KEY is not configured');
  }

  const response = await fetch(openRouteServiceDirectionsUrl, {
    method: 'POST',
    headers: {
      Accept: 'application/geo+json',
      Authorization: apiKey,
      'Content-Type': 'application/json',
    },
    body: JSON.stringify({
      coordinates: [startCoords, endCoords].map(([lat, lng]) => [lng, lat]),
    }),
    signal,
  });

  if (!response.ok) {
    throw new Error(`OpenRouteService request failed with status ${response.status}`);
  }

  const data = await response.json();
  const feature = data.features?.[0];
  const durationSeconds = Number(feature?.properties?.summary?.duration);
  const routeCoords = feature?.geometry?.coordinates?.map(([lng, lat]) => [Number(lat), Number(lng)]);
  const hasValidRoute = Array.isArray(routeCoords) && routeCoords.length >= 2 && routeCoords.every(
    ([lat, lng]) => Number.isFinite(lat) && Number.isFinite(lng),
  );

  if (!hasValidRoute || !Number.isFinite(durationSeconds) || durationSeconds <= 0) {
    throw new Error('OpenRouteService returned an invalid route');
  }

  return { durationSeconds, routeCoords };
};

const renderServiceMapForCoords = (customerCoords) => {
  loadYandexMaps().then(() => {
    const requestId = serviceMapState.requestId + 1;
    serviceMapState.requestId = requestId;

    const map = initServiceMap();
    const nearestMaster = getNearestMaster(customerCoords);

    if (!map || !nearestMaster) {
      return;
    }

    clearServiceMapRoute();
    showOnlyNearestMaster(nearestMaster.id);

    serviceMapState.customerPlacemark = createCustomerPlacemark(customerCoords);
    map.geoObjects.add(serviceMapState.customerPlacemark);

    const applyRouteFallback = () => {
      if (requestId !== serviceMapState.requestId) {
        return;
      }

      serviceMapState.routeAbortController = null;
      state.arrivalTime = fixedArrivalTime;
      updateSummary();
      fitServiceMap(getBoundsFromPoints([nearestMaster.coords, customerCoords]));
    };

    const routeAbortController = new AbortController();
    serviceMapState.routeAbortController = routeAbortController;

    requestOpenRouteServiceRoute(nearestMaster.coords, customerCoords, routeAbortController.signal).then(
      ({ durationSeconds, routeCoords }) => {
        if (requestId !== serviceMapState.requestId) {
          return;
        }

        serviceMapState.routeAbortController = null;
        setArrivalTimeByMinutes(durationSeconds / 60);
        serviceMapState.route = new window.ymaps.Polyline(routeCoords, {}, {
          opacity: 1,
          strokeColor: '#ff9f1a',
          strokeOpacity: 0.96,
          strokeStyle: 'solid',
          strokeWidth: 6,
          zIndex: 10,
        });
        map.geoObjects.add(serviceMapState.route);
        fitServiceMap(getBoundsFromPoints(routeCoords));
      },
      (error) => {
        if (error?.name !== 'AbortError') {
          applyRouteFallback();
        }
      },
    );
  }).catch(() => {
    setYandexMapsMissingStatus();
  });
};

const applyAddressFromCoords = (coords, { fallbackAddress = `${coords[0].toFixed(6)}, ${coords[1].toFixed(6)}` } = {}) => {
  const input = document.querySelector('[data-address-input]');

  closeYandexSuggest(input);
  resetArrivalTime();
  setLocationStatus('Определяем адрес...');

  if (!window.ymaps?.geocode) {
    if (input) {
      input.value = fallbackAddress;
    }

    serviceMapState.lastRenderedAddress = fallbackAddress;
    setAddress(fallbackAddress);
    setServiceLocationByCoords(coords);
    showServiceLocationStatus();
    updateSummary();
    renderServiceMapForCoords(coords);
    return;
  }

  window.ymaps.geocode(coords, { results: 1 }).then(
    (result) => {
      const firstGeoObject = result.geoObjects.get(0);
      const address = firstGeoObject?.getAddressLine?.() || firstGeoObject?.properties.get('text') || fallbackAddress;

      if (input) {
        input.value = address;
      }

      setAddress(address);
      serviceMapState.lastRenderedAddress = address;

      if (!setServiceLocationByCoords(coords, firstGeoObject)) {
        resetServiceMapToMasters();
        updateSummary();
        showServiceLocationStatus();
        return;
      }

      showServiceLocationStatus();
      updateSummary();
      renderServiceMapForCoords(coords);
    },
    () => {
      if (input) {
        input.value = fallbackAddress;
      }

      setAddress(fallbackAddress);
      serviceMapState.lastRenderedAddress = fallbackAddress;
      setServiceLocationByCoords(coords);
      showServiceLocationStatus();
      updateSummary();
      renderServiceMapForCoords(coords);
    },
  );
};

const renderServiceMapByAddress = (address) => {
  const normalizedAddress = normalizeMoscowAddress(address.trim());

  if (!normalizedAddress) {
    return;
  }

  loadYandexMaps().then(() => {
    if (!window.ymaps?.geocode) {
      setLocationStatus('Модуль геокодера Яндекс Карт не загрузился.', 'error');
      return;
    }

    const requestId = serviceMapState.requestId + 1;
    serviceMapState.requestId = requestId;

    window.ymaps.geocode(normalizedAddress, {
      boundedBy: serviceGeocodeBounds,
      results: 1,
    }).then(
      (result) => {
        if (requestId !== serviceMapState.requestId) {
          return;
        }

        const firstGeoObject = result.geoObjects.get(0);
        const coords = firstGeoObject?.geometry?.getCoordinates?.();

        if (!coords) {
          resetServiceLocation();
          updateSummary();
          setLocationStatus('Не удалось определить адрес. Уточните улицу и дом.', 'error');
          return;
        }

        if (!setServiceLocationByCoords(coords, firstGeoObject)) {
          resetServiceMapToMasters();
          resetArrivalTime();
          updateSummary();
          showServiceLocationStatus();
          return;
        }

        showServiceLocationStatus();
        updateSummary();
        renderServiceMapForCoords(coords);
      },
      () => {
        if (requestId !== serviceMapState.requestId) {
          return;
        }

        resetServiceLocation();
        resetArrivalTime();
        updateSummary();
        setLocationStatus('Не удалось проверить адрес. Попробуйте еще раз.', 'error');
      },
    );
  }).catch(() => {
    setYandexMapsMissingStatus();
  });
};

const renderServiceMapByCommittedAddress = (address) => {
  window.clearTimeout(serviceMapState.debounceId);

  const trimmedAddress = address.trim();

  if (trimmedAddress.length < 6 || trimmedAddress === serviceMapState.lastRenderedAddress) {
    return;
  }

  serviceMapState.lastRenderedAddress = trimmedAddress;
  renderServiceMapByAddress(trimmedAddress);
};

const scheduleServiceMapRenderByAddress = (address) => {
  window.clearTimeout(serviceMapState.debounceId);

  serviceMapState.debounceId = window.setTimeout(() => {
    renderServiceMapByCommittedAddress(address);
  }, 120);
};

const fillAddressByCoordinates = (coords) => {
  const position = [coords.latitude, coords.longitude];
  const fallbackAddress = `${coords.latitude.toFixed(6)}, ${coords.longitude.toFixed(6)}`;

  applyAddressFromCoords(position, { fallbackAddress });
};

const requestBrowserLocation = () => {
  if (!navigator.geolocation) {
    setAddressInputResolving(false);
    setLocationStatus('Браузер не поддерживает геолокацию.');
    return;
  }

  setAddressInputResolving(true);
  setLocationStatus('Определяем местоположение...');
  resetArrivalTime();
  resetServiceLocation();
  updateSummary();

  navigator.geolocation.getCurrentPosition(
    ({ coords }) => {
      fillAddressByCoordinates(coords);
      setAddressInputResolving(false);
    },
    () => {
      setAddressInputResolving(false);
      setLocationStatus('Не удалось получить текущее местоположение. Введите адрес вручную.');
    },
    {
      enableHighAccuracy: true,
      timeout: 15000,
      maximumAge: 60000,
    },
  );
};

const requestYandexLocation = () => {
  if (!window.ymaps?.geolocation) {
    requestBrowserLocation();
    return;
  }

  setAddressInputResolving(true);
  setLocationStatus('Определяем местоположение...');
  resetArrivalTime();
  resetServiceLocation();
  updateSummary();

  window.ymaps.geolocation.get({
    provider: 'browser',
    autoReverseGeocode: true,
    timeout: 15000,
  }).then(
    (result) => {
      const input = document.querySelector('[data-address-input]');
      const geoObject = result.geoObjects.get(0);
      const address = geoObject?.getAddressLine?.() || geoObject?.properties.get('text');
      const coords = geoObject?.geometry?.getCoordinates?.();
      const fallbackAddress = Array.isArray(coords) ? coords.join(', ') : getAddressInputValue(input);
      const resolvedAddress = address || fallbackAddress;

      if (input) {
        input.value = resolvedAddress || input.value;
      }

      serviceMapState.lastRenderedAddress = resolvedAddress.trim();
      setAddress(resolvedAddress);

      if (!coords) {
        setAddressInputResolving(false);
        updateSummary();
        setLocationStatus('Не удалось определить координаты. Введите адрес вручную.', 'error');
        return;
      }

      if (!setServiceLocationByCoords(coords, geoObject)) {
        setAddressInputResolving(false);
        resetServiceMapToMasters();
        updateSummary();
        showServiceLocationStatus();
        return;
      }

      showServiceLocationStatus();
      updateSummary();
      renderServiceMapForCoords(coords);
      setAddressInputResolving(false);
    },
    requestBrowserLocation,
  );
};

const setMapPickerStatus = (message, statusType = '') => {
  const status = document.querySelector('[data-map-picker-status]');

  if (status) {
    status.textContent = message;
    status.dataset.status = statusType;
  }
};

const setMapPickerApplyEnabled = (isEnabled) => {
  const applyButton = document.querySelector('[data-map-picker-apply]');

  if (applyButton) {
    applyButton.disabled = !isEnabled;
  }
};

const resetMapPickerSelection = () => {
  mapPickerState.coords = null;
  mapPickerState.address = '';
  mapPickerState.geoObject = null;
  setMapPickerApplyEnabled(false);
};

const updateMapPickerPlacemark = (coords) => {
  const map = mapPickerState.instance;

  if (!map) {
    return;
  }

  if (!mapPickerState.placemark) {
    mapPickerState.placemark = createMapPickerPlacemark(coords);
    mapPickerState.placemark.events.add('dragend', () => {
      selectMapPickerPoint(mapPickerState.placemark.geometry.getCoordinates());
    });
    map.geoObjects.add(mapPickerState.placemark);
    return;
  }

  mapPickerState.placemark.geometry.setCoordinates(coords);
};

const selectMapPickerPoint = (coords) => {
  mapPickerState.coords = coords;
  mapPickerState.address = `${coords[0].toFixed(6)}, ${coords[1].toFixed(6)}`;
  mapPickerState.geoObject = null;
  updateMapPickerPlacemark(coords);
  setMapPickerStatus('Определяем адрес...');
  setMapPickerApplyEnabled(false);

  if (!window.ymaps?.geocode) {
    setMapPickerStatus('Адрес будет выбран по координатам.', 'success');
    setMapPickerApplyEnabled(true);
    return;
  }

  window.ymaps.geocode(coords, { results: 1 }).then(
    (result) => {
      const firstGeoObject = result.geoObjects.get(0);
      const address = firstGeoObject?.getAddressLine?.() || firstGeoObject?.properties.get('text');
      const serviceLocation = getServiceLocationByCoords(coords, firstGeoObject);

      mapPickerState.geoObject = firstGeoObject;
      mapPickerState.address = address || mapPickerState.address;

      if (serviceLocation.status === 'denied') {
        setMapPickerStatus('Вызов невозможен по этому адресу. Выберите точку в Москве или Московской области.', 'error');
        setMapPickerApplyEnabled(false);
        return;
      }

      setMapPickerStatus(mapPickerState.address, 'success');
      setMapPickerApplyEnabled(true);
    },
    () => {
      setMapPickerStatus('Не удалось определить адрес. Можно выбрать точку по координатам.', 'success');
      setMapPickerApplyEnabled(true);
    },
  );
};

const initMapPicker = () => {
  const container = document.querySelector('[data-map-picker-canvas]');

  if (!container || !window.ymaps?.Map) {
    return null;
  }

  if (mapPickerState.instance) {
    return mapPickerState.instance;
  }

  mapPickerState.instance = new window.ymaps.Map(
    container,
    {
      center: state.serviceLocation.coords || moscowCenter,
      controls: [],
      zoom: state.serviceLocation.coords ? 14 : 10,
    },
    {
      suppressMapOpenBlock: true,
      yandexMapDisablePoiInteractivity: true,
    },
  );

  mapPickerState.instance.events.add('click', (event) => {
    selectMapPickerPoint(event.get('coords'));
  });

  return mapPickerState.instance;
};

const changeMapPickerZoom = (step) => {
  const map = mapPickerState.instance;

  if (!map) {
    return;
  }

  const currentZoom = Number(map.getZoom?.()) || 10;
  const nextZoom = clamp(currentZoom + step, 3, 19);

  map.setZoom(nextZoom, {
    checkZoomRange: true,
    duration: 180,
  });
};

const changeServiceMapZoom = (step) => {
  loadYandexMaps().then(() => {
    const map = initServiceMap();

    if (!map) {
      return;
    }

    map.container.fitToViewport();

    const currentZoom = Number(map.getZoom?.()) || 10;
    const nextZoom = clamp(currentZoom + step, 3, 19);

    map.setZoom(nextZoom, {
      checkZoomRange: true,
      duration: 180,
    });
  }).catch(() => {
    setYandexMapsMissingStatus();
  });
};

const openMapPicker = () => {
  const picker = document.querySelector('[data-map-picker]');

  if (!picker) {
    return;
  }

  picker.hidden = false;
  resetMapPickerSelection();
  setMapPickerStatus('Загружаем карту...');

  loadYandexMaps().then(() => {
    const map = initMapPicker();

    if (!map) {
      setMapPickerStatus('Карта не загрузилась. Введите адрес вручную.', 'error');
      return;
    }

    window.requestAnimationFrame(() => {
      map.container.fitToViewport();
      map.setCenter(state.serviceLocation.coords || moscowCenter, state.serviceLocation.coords ? 14 : 10);

      if (state.serviceLocation.coords) {
        selectMapPickerPoint(state.serviceLocation.coords);
      }
    });
  }).catch(() => {
    setMapPickerStatus('Карта не загрузилась. Проверьте ключ Яндекс Карт.', 'error');
  });
};

const closeMapPicker = () => {
  const picker = document.querySelector('[data-map-picker]');

  if (picker) {
    picker.hidden = true;
  }
};

const applyMapPickerSelection = () => {
  if (!mapPickerState.coords) {
    return;
  }

  const input = document.querySelector('[data-address-input]');

  if (input) {
    input.value = mapPickerState.address;
  }

  resetArrivalTime();
  setAddress(mapPickerState.address);

  if (!setServiceLocationByCoords(mapPickerState.coords, mapPickerState.geoObject)) {
    resetServiceMapToMasters();
    updateSummary();
    showServiceLocationStatus();
    closeMapPicker();
    return;
  }

  serviceMapState.lastRenderedAddress = mapPickerState.address;
  showServiceLocationStatus();
  updateSummary();
  renderServiceMapForCoords(mapPickerState.coords);
  closeMapPicker();
};

const initYandexSuggest = (input) => {
  if (!input || yandexMapsState.suggestView) {
    return Promise.resolve();
  }

  if (window.__YANDEX_SUGGEST_DISABLED__) {
    return Promise.resolve();
  }

  return loadYandexMaps().then(() => {
    if (yandexMapsState.suggestView) {
      return;
    }

    if (!window.ymaps?.SuggestView) {
      setLocationStatus('Модуль подсказок Яндекс Карт не загрузился.');
      return;
    }

    yandexMapsState.suggestView = new window.ymaps.SuggestView(input, {
      results: 5,
    });

    yandexMapsState.suggestView.events.add('select', (event) => {
      input.value = event.get('item').value;
      setAddress(input.value);
      resetArrivalTime();
      resetServiceLocation();
      updateSummary();
      renderServiceMapByCommittedAddress(input.value);
    });

    setLocationStatus('');
  }).catch(() => {});
};

const warmUpYandexMaps = (input) => {
  scheduleAfterFirstPaint(() => {
    renderInitialServiceMap();
    initYandexSuggest(input);
  });
};

export const initYandexAddress = () => {
  const input = document.querySelector('[data-address-input]');
  const locationButton = document.querySelector('[data-location-button]');
  const mapPickerClose = document.querySelector('[data-map-picker-close]');
  const mapPickerCancel = document.querySelector('[data-map-picker-cancel]');
  const mapPickerApply = document.querySelector('[data-map-picker-apply]');
  const serviceMapZoomButtons = document.querySelectorAll('[data-service-map-zoom]');
  const mapPickerZoomButtons = document.querySelectorAll('[data-map-picker-zoom]');
  const desktopLayoutMedia = window.matchMedia?.(desktopMapQuery);

  if (!input) {
    return;
  }

  input.addEventListener('input', () => {
    setAddress(input.value);
    resetArrivalTime();
    resetServiceLocation();
    updateSummary();
    setLocationStatus('');
    window.clearTimeout(serviceMapState.debounceId);
    serviceMapState.lastRenderedAddress = '';
    resetServiceMapToMasters();
  });

  input.addEventListener('blur', () => {
    scheduleServiceMapRenderByAddress(input.value);
  });

  input.addEventListener('focus', () => {
    initYandexSuggest(input);
  }, { once: true });

  locationButton?.addEventListener('click', () => {
    loadYandexMaps()
      .then(requestYandexLocation)
      .catch(requestBrowserLocation);
  });

  mapPickerClose?.addEventListener('click', closeMapPicker);
  mapPickerCancel?.addEventListener('click', closeMapPicker);
  mapPickerApply?.addEventListener('click', applyMapPickerSelection);
  serviceMapZoomButtons.forEach((button) => {
    button.addEventListener('click', (event) => {
      event.preventDefault();
      event.stopPropagation();
      changeServiceMapZoom(button.dataset.serviceMapZoom === 'in' ? 1 : -1);
    });
  });
  mapPickerZoomButtons.forEach((button) => {
    button.addEventListener('click', (event) => {
      event.preventDefault();
      event.stopPropagation();
      changeMapPickerZoom(button.dataset.mapPickerZoom === 'in' ? 1 : -1);
    });
  });

  const handleServiceMapLayoutChange = () => {
    syncServiceMapBehaviors();
    scheduleServiceMapRefit();
  };

  if (desktopLayoutMedia?.addEventListener) {
    desktopLayoutMedia.addEventListener('change', handleServiceMapLayoutChange);
  } else {
    desktopLayoutMedia?.addListener?.(handleServiceMapLayoutChange);
  }

  if (window.__YANDEX_MAPS_DISABLED__ || !getYandexMapsUrl()) {
    setYandexMapsMissingStatus();
    return;
  }

  if (window.__YANDEX_SUGGEST_DISABLED__) {
    setLocationStatus('Укажите YANDEX_SUGGEST_API_KEY в .env для подсказок.');
  }

  warmUpYandexMaps(input);
};
