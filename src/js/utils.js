export const formatPrice = (value) => new Intl.NumberFormat('ru-RU').format(value).replace(/\u00a0/g, ' ');
export const clamp = (value, min, max) => Math.min(Math.max(value, min), max);
export const roundUpToStep = (value, step) => Math.ceil(value / step) * step;
export const getDiameterNumber = (diameter) => Number(String(diameter).replace(/\D/g, '')) || 0;

export const getDistanceBetweenCoords = ([latA, lngA], [latB, lngB]) => {
  const earthRadius = 6371000;
  const toRadians = (value) => (value * Math.PI) / 180;
  const dLat = toRadians(latB - latA);
  const dLng = toRadians(lngB - lngA);
  const a =
    Math.sin(dLat / 2) ** 2 +
    Math.cos(toRadians(latA)) * Math.cos(toRadians(latB)) * Math.sin(dLng / 2) ** 2;

  return earthRadius * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
};

export const getBoundsFromPoints = (points) => {
  const latitudes = points.map(([lat]) => lat);
  const longitudes = points.map(([, lng]) => lng);

  return [
    [Math.min(...latitudes), Math.min(...longitudes)],
    [Math.max(...latitudes), Math.max(...longitudes)],
  ];
};

export const getBoundsCenter = (bounds) => {
  const [[minLat, minLng], [maxLat, maxLng]] = bounds;

  return [(minLat + maxLat) / 2, (minLng + maxLng) / 2];
};

export const getMercatorPoint = ([lat, lng]) => {
  const sinLatitude = clamp(Math.sin((lat * Math.PI) / 180), -0.9999, 0.9999);

  return {
    x: (lng + 180) / 360,
    y: 0.5 - Math.log((1 + sinLatitude) / (1 - sinLatitude)) / (4 * Math.PI),
  };
};
