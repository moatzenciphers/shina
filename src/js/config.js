export const mkadKm = [
  [1,37.842762,55.774558],
  [2,37.842789,55.76522],
  [3,37.842627,55.755723],
  [4,37.841828,55.747399],
  [5,37.841217,55.739103],
  [6,37.840175,55.730482],
  [7,37.83916,55.721939],
  [8,37.837121,55.712203],
  [9,37.83262,55.703048],
  [10,37.829512,55.694287],
  [11,37.831353,55.68529],
  [12,37.834605,55.675945],
  [13,37.837597,55.667752],
  [14,37.839348,55.658667],
  [15,37.833842,55.650053],
  [16,37.824787,55.643713],
  [17,37.814564,55.637347],
  [18,37.802473,55.62913],
  [19,37.794235,55.623758],
  [20,37.781928,55.617713],
  [21,37.771139,55.611755],
  [22,37.758725,55.604956],
  [23,37.747945,55.599677],
  [24,37.734785,55.594143],
  [25,37.723062,55.589234],
  [26,37.709425,55.583983],
  [27,37.696256,55.578834],
  [28,37.683167,55.574019],
  [29,37.668911,55.571999],
  [30,37.647765,55.573093],
  [31,37.633419,55.573928],
  [32,37.616719,55.574732],
  [33,37.60107,55.575816],
  [34,37.586536,55.5778],
  [35,37.571938,55.581271],
  [36,37.555732,55.585143],
  [37,37.545132,55.587509],
  [38,37.526366,55.5922],
  [39,37.516108,55.594728],
  [40,37.502274,55.60249],
  [41,37.49391,55.609685],
  [42,37.484846,55.617424],
  [43,37.474668,55.625801],
  [44,37.469925,55.630207],
  [45,37.456864,55.641041],
  [46,37.448195,55.648794],
  [47,37.441125,55.654675],
  [48,37.434424,55.660424],
  [49,37.42598,55.670701],
  [50,37.418712,55.67994],
  [51,37.414868,55.686873],
  [52,37.407528,55.695697],
  [53,37.397952,55.702805],
  [54,37.388969,55.709657],
  [55,37.383283,55.718273],
  [56,37.378369,55.728581],
  [57,37.374991,55.735201],
  [58,37.370248,55.744789],
  [59,37.369188,55.75435],
  [60,37.369053,55.762936],
  [61,37.369619,55.771444],
  [62,37.369853,55.779722],
  [63,37.372943,55.789542],
  [64,37.379824,55.79723],
  [65,37.386876,55.805796],
  [66,37.390397,55.814629],
  [67,37.393236,55.823606],
  [68,37.395275,55.83251],
  [69,37.394709,55.840376],
  [70,37.393056,55.850141],
  [71,37.397314,55.858801],
  [72,37.405588,55.867051],
  [73,37.416601,55.872703],
  [74,37.429429,55.877041],
  [75,37.443596,55.881091],
  [76,37.459065,55.882828],
  [77,37.473096,55.884625],
  [78,37.48861,55.888897],
  [79,37.5016,55.894232],
  [80,37.513206,55.899578],
  [81,37.527597,55.90526],
  [82,37.543443,55.907687],
  [83,37.559577,55.909388],
  [84,37.575531,55.910907],
  [85,37.590344,55.909257],
  [86,37.604637,55.905472],
  [87,37.619603,55.901637],
  [88,37.635961,55.898533],
  [89,37.647648,55.896973],
  [90,37.667878,55.895449],
  [91,37.681721,55.894868],
  [92,37.698807,55.893884],
  [93,37.712363,55.889094],
  [94,37.723636,55.883555],
  [95,37.735791,55.877501],
  [96,37.741261,55.874698],
  [97,37.764519,55.862464],
  [98,37.765992,55.861979],
  [99,37.788216,55.850257],
  [100,37.788522,55.850383],
  [101,37.800586,55.844167],
  [102,37.822819,55.832707],
  [103,37.829754,55.828789],
  [104,37.837148,55.821072],
  [105,37.838926,55.811599],
  [106,37.840004,55.802781],
  [107,37.840965,55.793991],
  [108,37.841576,55.785017],
];

export const defaultCalculatorConfig = {
  fixedArrivalTime: [40, 60],
  nightTariff: {
    startHour: 22,
    endHour: 7,
  },
  tireMultipliers: {
    crossover: 0.2,
    suv: 0.4,
    lowProfile: 0.2,
    reinforcedTire: 0.4,
  },
  tariffs: {
    callout: {
      insideMkad: {
        day: 1500,
        night: 2000,
      },
      outsideMkad: {
        day: 2000,
        night: 2500,
        perKm: 40,
      },
    },
    seasonal: {
      diameter: {
        r12: 3100,
        r13: 3100,
        r14: 3100,
        r15: 3100,
        r16: 3500,
        r17: 4000,
        r18: 4500,
        r19: 5000,
        r20: 5500,
        r21: 6500,
        r22: 7500,
        r23: 8500,
        r24: 10000,
      },
      addons: {
        copperGrease: 700,
        hubCleaning: 800,
      },
    },
    puncture: {
      diameter: {
        r12: 2000,
        r13: 2000,
        r14: 2000,
        r15: 2000,
        r16: 2000,
        r17: 2000,
        r18: 2500,
        r19: 2500,
        r20: 3000,
        r21: 3500,
        r22: 4500,
        r23: 6000,
        r24: 6000,
      },
    },
    conditioner: {
      base: 4800,
      addons: {
        freon134: 500,
        antibacterial: 500,
      },
      multipliers: {
        extraCircuit: 0.7,
      },
      vehicleMultipliers: {
        commercial: 1,
      },
    },
    storage: {
      diameter: {
        r12_16: 5000,
        r17_19: 5500,
        r20_24: 6000,
      },
      delivery: 3500,
    },
  },
};

const isPlainObject = (value) => {
  return Object.prototype.toString.call(value) === '[object Object]';
};

const deepMerge = (target, source) => {
  if (Array.isArray(source)) {
    return source.slice();
  }

  if (!isPlainObject(source)) {
    return target;
  }

  return Object.entries(source).reduce(
    (merged, [key, value]) => {
      if (Array.isArray(value)) {
        merged[key] = value.slice();
        return merged;
      }

      if (isPlainObject(value)) {
        merged[key] = deepMerge(isPlainObject(merged[key]) ? merged[key] : {}, value);
        return merged;
      }

      if (value !== undefined && value !== null && value !== '') {
        merged[key] = value;
      }

      return merged;
    },
    {
      ...target,
    },
  );
};

const parseNumber = (value, fallback) => {
  if (value === undefined || value === null || value === '') {
    return fallback;
  }

  const normalizedValue = String(value).replace(/\s/g, '').replace(',', '.').replace(/[^\d.-]/g, '');
  const number = Number(normalizedValue);

  return Number.isFinite(number) ? number : fallback;
};

const parsePercent = (value, fallback) => parseNumber(value, fallback * 100) / 100;

const normalizePriceMap = (source = {}, fallback = {}) => {
  return Object.fromEntries(
    Object.entries(fallback).map(([key, value]) => [key, parseNumber(source[key], value)]),
  );
};

const normalizeFixedArrivalTime = (source, fallback) => {
  const min = parseNumber(source?.min ?? source?.[0], fallback[0]);
  const max = parseNumber(source?.max ?? source?.[1], fallback[1]);

  return [min, Math.max(min, max)];
};

const normalizeAcfCalculatorConfig = (sourceConfig, fallbackConfig) => {
  if (!isPlainObject(sourceConfig)) {
    return {};
  }

  const callout = sourceConfig.callout || {};
  const seasonal = sourceConfig.seasonal || {};
  const puncture = sourceConfig.puncture || {};
  const conditioner = sourceConfig.conditioner || {};
  const storage = sourceConfig.storage || {};
  const tireMultipliers = sourceConfig.tire_multipliers || sourceConfig.tireMultipliers || {};

  return {
    fixedArrivalTime: normalizeFixedArrivalTime(sourceConfig.arrival_time || sourceConfig.fixedArrivalTime, fallbackConfig.fixedArrivalTime),
    nightTariff: {
      startHour: parseNumber(sourceConfig.night_tariff?.start_hour ?? sourceConfig.nightTariff?.startHour, fallbackConfig.nightTariff.startHour),
      endHour: parseNumber(sourceConfig.night_tariff?.end_hour ?? sourceConfig.nightTariff?.endHour, fallbackConfig.nightTariff.endHour),
    },
    tireMultipliers: {
      crossover: parsePercent(tireMultipliers.crossover_percent, fallbackConfig.tireMultipliers.crossover),
      suv: parsePercent(tireMultipliers.suv_percent, fallbackConfig.tireMultipliers.suv),
      lowProfile: parsePercent(tireMultipliers.low_profile_percent, fallbackConfig.tireMultipliers.lowProfile),
      reinforcedTire: parsePercent(tireMultipliers.reinforced_tire_percent, fallbackConfig.tireMultipliers.reinforcedTire),
    },
    tariffs: {
      callout: {
        insideMkad: {
          day: parseNumber(callout.inside_mkad?.day ?? callout.insideMkad?.day, fallbackConfig.tariffs.callout.insideMkad.day),
          night: parseNumber(callout.inside_mkad?.night ?? callout.insideMkad?.night, fallbackConfig.tariffs.callout.insideMkad.night),
        },
        outsideMkad: {
          day: parseNumber(callout.outside_mkad?.day ?? callout.outsideMkad?.day, fallbackConfig.tariffs.callout.outsideMkad.day),
          night: parseNumber(callout.outside_mkad?.night ?? callout.outsideMkad?.night, fallbackConfig.tariffs.callout.outsideMkad.night),
          perKm: parseNumber(callout.outside_mkad?.per_km ?? callout.outsideMkad?.perKm, fallbackConfig.tariffs.callout.outsideMkad.perKm),
        },
      },
      seasonal: {
        diameter: normalizePriceMap(seasonal.diameter, fallbackConfig.tariffs.seasonal.diameter),
        addons: {
          copperGrease: parseNumber(seasonal.addons?.copper_grease ?? seasonal.addons?.copperGrease, fallbackConfig.tariffs.seasonal.addons.copperGrease),
          hubCleaning: parseNumber(seasonal.addons?.hub_cleaning ?? seasonal.addons?.hubCleaning, fallbackConfig.tariffs.seasonal.addons.hubCleaning),
        },
      },
      puncture: {
        diameter: normalizePriceMap(puncture.diameter, fallbackConfig.tariffs.puncture.diameter),
      },
      conditioner: {
        base: parseNumber(conditioner.base, fallbackConfig.tariffs.conditioner.base),
        addons: {
          freon134: parseNumber(conditioner.addons?.freon134, fallbackConfig.tariffs.conditioner.addons.freon134),
          antibacterial: parseNumber(conditioner.addons?.antibacterial, fallbackConfig.tariffs.conditioner.addons.antibacterial),
        },
        multipliers: {
          extraCircuit: parsePercent(conditioner.multipliers?.extra_circuit_percent, fallbackConfig.tariffs.conditioner.multipliers.extraCircuit),
        },
        vehicleMultipliers: {
          commercial: parsePercent(conditioner.multipliers?.commercial_percent, fallbackConfig.tariffs.conditioner.vehicleMultipliers.commercial),
        },
      },
      storage: {
        diameter: normalizePriceMap(storage.diameter, fallbackConfig.tariffs.storage.diameter),
        delivery: parseNumber(storage.delivery, fallbackConfig.tariffs.storage.delivery),
      },
    },
  };
};

const getRuntimeCalculatorConfig = () => {
  if (typeof window === 'undefined') {
    return {};
  }

  return window.shinaCalculatorConfig || window.SHINA_CALCULATOR_CONFIG || {};
};

const createCalculatorConfig = () => {
  const runtimeConfig = getRuntimeCalculatorConfig();
  const directConfig = deepMerge(defaultCalculatorConfig, runtimeConfig);
  const acfConfig = runtimeConfig.shina_calculator_config || runtimeConfig.calculator || runtimeConfig;

  return deepMerge(directConfig, normalizeAcfCalculatorConfig(acfConfig, directConfig));
};

export const calculatorConfig = createCalculatorConfig();
export const tariffs = calculatorConfig.tariffs;
export const tireMultipliers = calculatorConfig.tireMultipliers;
export const fixedArrivalTime = calculatorConfig.fixedArrivalTime;
export const nightTariff = calculatorConfig.nightTariff;
export const moscowCenter = [55.755864, 37.617698];
export const serviceGeocodeBounds = [
  [54.25, 35.15],
  [56.95, 40.25],
];
export const mkadPolygon = mkadKm.map(([, lng, lat]) => [lat, lng]);
export const diameterOptions = Array.from({ length: 13 }, (_, index) => {
  const value = index + 12;

  return {
    value: `r${value}`,
    label: `R${value}`,
  };
});
export const reviewsPerPage = 10;
export const reviewsApiEndpoint = '/wp-json/shina/v1/reviews';
export const photosPerPage = 10;
export const photosApiEndpoint = '/wp-json/shina/v1/photos';
export const faqPerPage = 20;
export const faqApiEndpoint = '/wp-json/shina/v1/faq';

function seededRandom(seed) {
  let value = seed;

  return () => {
    value = (value * 9301 + 49297) % 233280;
    return value / 233280;
  };
}

function createMoscowMasterPoints(count) {
  const random = seededRandom(247);
  const points = [];

  for (let index = 0; index < count; index += 1) {
    const angle = random() * Math.PI * 2;
    const radius = Math.sqrt(random());
    const lat = moscowCenter[0] + Math.sin(angle) * radius * 0.18;
    const lng = moscowCenter[1] + Math.cos(angle) * radius * 0.32;

    points.push({
      id: `master-${index + 1}`,
      coords: [Number(lat.toFixed(6)), Number(lng.toFixed(6))],
    });
  }

  return points;
}

export const moscowMasterPoints = createMoscowMasterPoints(37);
