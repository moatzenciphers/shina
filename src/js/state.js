export const createDefaultServiceLocation = () => ({
  status: 'unknown',
  coords: null,
  insideMkad: true,
  distanceOutsideKm: 0,
});

export const state = {
  service: 'seasonal',
  carType: 'passenger',
  diameter: 'r12',
  address: '',
  arrivalTime: null,
  locationKnown: false,
  serviceLocation: createDefaultServiceLocation(),
  addons: {
    copperGrease: false,
    hubCleaning: false,
    lowProfile: false,
    reinforcedTire: false,
    freon134: false,
    antibacterial: false,
    extraCircuit: false,
    storageDelivery: false,
  },
};

export const reviewsState = {
  activeFilter: 'all',
  hasMore: true,
  initialized: false,
  isLoading: false,
  lightbox: null,
  observer: null,
  page: 1,
};

export const photosState = {
  activeFilter: 'all',
  hasMore: true,
  initialized: false,
  isLoading: false,
  lightbox: null,
  masonry: null,
  observer: null,
  page: 0,
};

export const faqState = {
  hasMore: true,
  initialized: false,
  isLoading: false,
  observer: null,
  page: 1,
  search: '',
  searchDebounceId: null,
};
