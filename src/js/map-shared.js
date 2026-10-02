const yandexMapsState = { promise: null };
const markerLayouts = {};
const getYandexMapsUrl = () => String(window.__YANDEX_MAPS_URL__ || '');
const getOpenRouteServiceApiKey = () => String(window.__OPENROUTESERVICE_API_KEY__ || '');
const openRouteServiceDirectionsUrl = 'https://api.openrouteservice.org/v2/directions/driving-car/geojson';

export const loadYandexMaps = () => {
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

const getServiceMarkerLayout = (type) => {
  if (!window.ymaps?.templateLayoutFactory) {
    return null;
  }

  if (!markerLayouts[type]) {
    markerLayouts[type] = window.ymaps.templateLayoutFactory.createClass(
      `<div class="service-map-marker service-map-marker--${type}"><span></span></div>`,
    );
  }

  return markerLayouts[type];
};

export const getServiceMarkerOptions = (type, radius) => {
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

export const createMasterPlacemark = (master, initiallyVisible = false) => {
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
      opacity: initiallyVisible ? 1 : 0,
      visible: initiallyVisible,
      zIndex: 1000,
      zIndexActive: 1000,
      ...getServiceMarkerOptions('master', 16),
    },
  );
};

export const requestOpenRouteServiceRoute = async (startCoords, endCoords, signal) => {
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

