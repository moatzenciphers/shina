import { mkadPolygon, moscowCenter, moscowMasterPoints } from './config';
import { createMasterPlacemark, loadYandexMaps } from './map-shared';

export const initCoverageMap = () => {
  const root = document.querySelector('[data-coverage-map]');
  if (!root) return;
  const canvas = root.querySelector('[data-coverage-canvas]');
  const status = root.querySelector('[data-coverage-status]');
  const buttons = Array.from(root.querySelectorAll('button'));
  const locationButton = root.querySelector('[data-coverage-location]');
  let map;
  let marker;
  root.querySelectorAll('[data-coverage-zoom]').forEach((button) => button.addEventListener('click', () => {
    if (map) map.setZoom(Math.max(3, Math.min(19, map.getZoom() + (button.dataset.coverageZoom === 'in' ? 1 : -1))), { checkZoomRange: true, duration: 180 });
  }));
  locationButton?.addEventListener('click', async () => {
    if (!map) return;
    locationButton.disabled = true;
    locationButton.setAttribute('aria-busy', 'true');
    status.hidden = false;
    status.textContent = 'Определяем местоположение…';
    try {
      const position = await new Promise((resolve, reject) => {
        if (!navigator.geolocation) return reject(new Error('Geolocation unavailable'));
        navigator.geolocation.getCurrentPosition(resolve, reject, { timeout: 15000, maximumAge: 60000 });
      });
      const coords = [position.coords.latitude, position.coords.longitude];
      if (marker) map.geoObjects.remove(marker);
      marker = new window.ymaps.Placemark(coords, {}, { preset: 'islands#circleDotIcon', iconColor: '#ff9f1a' });
      map.geoObjects.add(marker);
      map.setCenter(coords, 12, { duration: 250 });
      status.hidden = true;
    } catch {
      status.textContent = 'Не удалось определить местоположение. Разрешите доступ к геолокации в браузере.';
    } finally {
      locationButton.disabled = false;
      locationButton.removeAttribute('aria-busy');
    }
  });
  const start = async () => {
    try {
      await loadYandexMaps();
      map = new window.ymaps.Map(canvas, { center: moscowCenter, zoom: 9, controls: [] }, { suppressMapOpenBlock: true, yandexMapDisablePoiInteractivity: true });
      map.behaviors.disable('scrollZoom');
      map.geoObjects.add(new window.ymaps.Polygon([mkadPolygon], {}, { fillOpacity: 0, strokeColor: '#ff9f1a', strokeWidth: 2, interactivityModel: 'default#silent' }));
      moscowMasterPoints.forEach((master) => map.geoObjects.add(createMasterPlacemark(master, true)));
      if ('ResizeObserver' in window) new ResizeObserver(() => map.container.fitToViewport()).observe(canvas);
      status.hidden = true;
      buttons.forEach((button) => { button.disabled = false; });
    } catch {
      status.textContent = 'Карта не загрузилась. Работаем по Москве и Московской области.';
    }
  };
  if ('IntersectionObserver' in window) {
    const observer = new IntersectionObserver((entries) => {
      if (!entries.some((entry) => entry.isIntersecting)) return;
      observer.disconnect();
      start();
    }, { rootMargin: '200px' });
    observer.observe(root);
  } else start();
};
