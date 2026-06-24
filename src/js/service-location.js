import { mkadPolygon, moscowCenter } from './config';
import { createDefaultServiceLocation, state } from './state';
import { clamp } from './utils';

export const resetServiceLocation = () => {
  state.serviceLocation = createDefaultServiceLocation();
};

const isPointInsidePolygon = ([lat, lng], polygon) => {
  let isInside = false;

  for (let index = 0, previousIndex = polygon.length - 1; index < polygon.length; previousIndex = index, index += 1) {
    const [currentLat, currentLng] = polygon[index];
    const [previousLat, previousLng] = polygon[previousIndex];
    const currentY = currentLat;
    const currentX = currentLng;
    const previousY = previousLat;
    const previousX = previousLng;
    const intersects =
      currentY > lat !== previousY > lat &&
      lng < ((previousX - currentX) * (lat - currentY)) / (previousY - currentY) + currentX;

    if (intersects) {
      isInside = !isInside;
    }
  }

  return isInside;
};

const projectMoscowPoint = ([lat, lng]) => {
  const metersPerDegreeLat = 111320;
  const metersPerDegreeLng = metersPerDegreeLat * Math.cos((moscowCenter[0] * Math.PI) / 180);

  return {
    x: (lng - moscowCenter[1]) * metersPerDegreeLng,
    y: (lat - moscowCenter[0]) * metersPerDegreeLat,
  };
};

const getPointToSegmentDistance = (point, segmentStart, segmentEnd) => {
  const dx = segmentEnd.x - segmentStart.x;
  const dy = segmentEnd.y - segmentStart.y;
  const lengthSquared = dx * dx + dy * dy;

  if (!lengthSquared) {
    return Math.hypot(point.x - segmentStart.x, point.y - segmentStart.y);
  }

  const progress = clamp(((point.x - segmentStart.x) * dx + (point.y - segmentStart.y) * dy) / lengthSquared, 0, 1);
  const closestPoint = {
    x: segmentStart.x + progress * dx,
    y: segmentStart.y + progress * dy,
  };

  return Math.hypot(point.x - closestPoint.x, point.y - closestPoint.y);
};

const getDistanceToMkadMeters = (coords) => {
  const point = projectMoscowPoint(coords);

  return mkadPolygon.reduce((minDistance, currentPoint, index) => {
    const nextPoint = mkadPolygon[(index + 1) % mkadPolygon.length];
    const distance = getPointToSegmentDistance(
      point,
      projectMoscowPoint(currentPoint),
      projectMoscowPoint(nextPoint),
    );

    return Math.min(minDistance, distance);
  }, Number.POSITIVE_INFINITY);
};

const getServiceRegionText = (geoObject) => {
  const metaData = geoObject?.properties?.get?.('metaDataProperty');
  const components = metaData?.GeocoderMetaData?.Address?.Components || [];
  const componentText = components.map((component) => component.name).join(' ');

  return [
    geoObject?.getAddressLine?.(),
    geoObject?.properties?.get?.('text'),
    geoObject?.properties?.get?.('description'),
    ...(geoObject?.getAdministrativeAreas?.() || []),
    ...(geoObject?.getLocalities?.() || []),
    componentText,
  ]
    .filter(Boolean)
    .join(' ');
};

const isAllowedServiceRegion = (geoObject) => {
  const regionText = getServiceRegionText(geoObject);

  return /(^|[\s,])москва([\s,]|$)|московская область|moscow/i.test(regionText);
};

export const getServiceLocationByCoords = (coords, geoObject = null) => {
  if (geoObject && !isAllowedServiceRegion(geoObject)) {
    return {
      status: 'denied',
      coords,
      insideMkad: false,
      distanceOutsideKm: 0,
    };
  }

  const insideMkad = isPointInsidePolygon(coords, mkadPolygon);
  const distanceOutsideKm = insideMkad ? 0 : Math.ceil(getDistanceToMkadMeters(coords) / 1000);

  return {
    status: 'ready',
    coords,
    insideMkad,
    distanceOutsideKm,
  };
};

export const setServiceLocationByCoords = (coords, geoObject = null) => {
  state.serviceLocation = getServiceLocationByCoords(coords, geoObject);

  return state.serviceLocation.status !== 'denied';
};

export const setLocationStatus = (message, statusType = '') => {
  const status = document.querySelector('[data-location-status]');

  if (status) {
    status.textContent = message;
    status.dataset.status = statusType;
  }
};

export const showServiceLocationStatus = () => {
  const location = state.serviceLocation;

  if (location.status === 'denied') {
    setLocationStatus('Вызов невозможен по этому адресу. Работаем только по Москве и Московской области.', 'error');
    return;
  }

  if (location.status !== 'ready') {
    return;
  }

  if (location.insideMkad) {
    setLocationStatus('Адрес в пределах МКАД. Стоимость выезда рассчитана.', 'success');
    return;
  }

  setLocationStatus(`Адрес за МКАД: ${location.distanceOutsideKm} км. Стоимость выезда рассчитана.`, 'success');
};
