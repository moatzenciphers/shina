import Inputmask from 'inputmask';

import { diameterOptions, fixedArrivalTime, nightTariff, tariffs, tireMultipliers } from './config';
import { state } from './state';
import { clamp, formatPrice, getDiameterNumber, roundUpToStep } from './utils';

const getMoscowHour = () => {
  const hour = Number(
    new Intl.DateTimeFormat('ru-RU', {
      hour: '2-digit',
      hour12: false,
      timeZone: 'Europe/Moscow',
    })
      .format(new Date())
      .replace(/\D/g, ''),
  );

  return hour === 24 ? 0 : hour;
};

const isNightTariff = () => {
  const hour = getMoscowHour();
  const startHour = nightTariff.startHour;
  const endHour = nightTariff.endHour;

  if (startHour === endHour) {
    return false;
  }

  return startHour > endHour ? hour >= startHour || hour < endHour : hour >= startHour && hour < endHour;
};

const serviceLabels = {
  seasonal: 'Сезонная замена',
  puncture: 'Ремонт прокола',
  conditioner: 'Заправка кондиционера',
  storage: 'Хранение шин',
};

const carTypeLabels = {
  passenger: 'Легковой',
  crossover: 'Кроссовер',
  suv: 'Внедорожник',
  commercial: 'Спецтехника, грузовое авто',
};

const addonLabels = {
  copperGrease: 'Медная смазка',
  hubCleaning: 'Механическая чистка ступиц',
  lowProfile: 'Низкий профиль',
  reinforcedTire: 'Run Flat / C / AT',
  freon134: 'Дополнительно фреон 134А 100 г',
  antibacterial: 'Антибактериальная обработка системы',
  extraCircuit: 'Дополнительный контур',
  storageDelivery: 'Доставка',
};

const serviceAddonNames = {
  seasonal: ['copperGrease', 'hubCleaning', 'lowProfile', 'reinforcedTire'],
  puncture: ['lowProfile', 'reinforcedTire'],
  conditioner: ['freon134', 'antibacterial', 'extraCircuit'],
  storage: ['storageDelivery'],
};

const orderFieldNames = {
  order_service: 'service_label',
  order_service_key: 'service',
  order_address: 'address',
  order_car_type: 'car_type_label',
  order_car_type_key: 'car_type',
  order_diameter: 'diameter_label',
  order_diameter_key: 'diameter',
  order_addons: 'addons',
  order_addon_keys: 'addon_keys',
  order_price: 'price_text',
  order_price_value: 'price',
  order_callout_price_value: 'callout_price',
  order_service_price_value: 'service_price',
  order_arrival_time: 'arrival_time',
  order_tariff: 'tariff',
  order_is_night_tariff: 'is_night_tariff',
  order_location_status: 'location_status',
  order_inside_mkad: 'inside_mkad',
  order_distance_outside_mkad_km: 'distance_outside_mkad_km',
  order_coords: 'coords',
  order_phone: 'phone',
  order_details: 'details',
  order_payload: 'payload',
  phone: 'phone',
};

let submittedOrderRows = null;
let isCalculatorSheetExpanded = false;
let calculatorSheetAddress = '';
let suppressCalculatorToggleClick = false;

const calculatorSheetDragThreshold = 56;
const calculatorSheetMoveThreshold = 6;
const calculatorDesktopQuery = '(min-width: 1024px)';

const isDesktopCalculatorLayout = () => {
  return window.matchMedia?.(calculatorDesktopQuery).matches || false;
};

const serviceCarTypeOptions = {
  seasonal: ['passenger', 'crossover', 'suv'],
  puncture: ['passenger', 'crossover', 'suv'],
  conditioner: ['passenger', 'crossover', 'suv', 'commercial'],
};

const servicesWithVehicle = ['seasonal', 'puncture', 'conditioner'];
const servicesWithDiameter = ['seasonal', 'puncture', 'storage'];

export const setAddress = (value) => {
  state.address = value.trim();
  state.locationKnown = state.address.length > 0;
};

const getArrivalTimeByAddress = () => {
  return state.arrivalTime;
};

export const resetArrivalTime = () => {
  state.arrivalTime = null;
};

const getRouteDurationMinutes = (route) => {
  const durationSeconds = [route.getJamsTime?.(), route.getTime?.()].find(
    (value) => Number.isFinite(value) && value > 0,
  );

  return durationSeconds ? Math.ceil(durationSeconds / 60) : null;
};

const setArrivalTimeByMinutes = (minutes) => {
  if (!Number.isFinite(minutes) || minutes <= 0) {
    state.arrivalTime = fixedArrivalTime;
    updateSummary();
    return;
  }

  const minArrivalTime = Math.max(15, roundUpToStep(minutes, 5));

  state.arrivalTime = [minArrivalTime, minArrivalTime + 20];
  updateSummary();
};

export const setArrivalTimeByRoute = (route) => {
  setArrivalTimeByMinutes(getRouteDurationMinutes(route));
};

const getActiveCalloutTariff = () => {
  const tariffMode = isNightTariff() ? 'night' : 'day';
  const location = state.serviceLocation;

  if (location.status !== 'ready' || location.insideMkad) {
    return tariffs.callout.insideMkad[tariffMode];
  }

  return tariffs.callout.outsideMkad[tariffMode] + location.distanceOutsideKm * tariffs.callout.outsideMkad.perKm;
};

const getTireServiceMultiplier = () => {
  let multiplier = 1;

  if (state.carType === 'crossover') {
    multiplier += tireMultipliers.crossover;
  }

  if (state.carType === 'suv') {
    multiplier += tireMultipliers.suv;
  }

  if (state.addons.lowProfile) {
    multiplier += tireMultipliers.lowProfile;
  }

  if (state.addons.reinforcedTire) {
    multiplier += tireMultipliers.reinforcedTire;
  }

  return multiplier;
};

const getSeasonalPrice = () => {
  const workPrice = tariffs.seasonal.diameter[state.diameter] || 0;
  const addonsPrice = Object.entries(tariffs.seasonal.addons).reduce((sum, [addonName, price]) => {
    return state.addons[addonName] ? sum + price : sum;
  }, 0);

  return Math.round(workPrice * getTireServiceMultiplier()) + addonsPrice;
};

const getPuncturePrice = () => {
  const workPrice = tariffs.puncture.diameter[state.diameter] || 0;

  return Math.round(workPrice * getTireServiceMultiplier());
};

const getConditionerPrice = () => {
  const fixedAddonsPrice = Object.entries(tariffs.conditioner.addons).reduce((sum, [addonName, price]) => {
    return state.addons[addonName] ? sum + price : sum;
  }, 0);
  const addonsMultiplier = Object.entries(tariffs.conditioner.multipliers).reduce((sum, [addonName, value]) => {
    return state.addons[addonName] ? sum + value : sum;
  }, 1);
  const vehicleMultiplier = tariffs.conditioner.vehicleMultipliers[state.carType] || 0;
  const multiplier = addonsMultiplier + vehicleMultiplier;

  return Math.round((tariffs.conditioner.base + fixedAddonsPrice) * multiplier);
};

const getStoragePrice = () => {
  const diameter = getDiameterNumber(state.diameter);
  const storagePrice = diameter <= 16
    ? tariffs.storage.diameter.r12_16
    : diameter <= 19
      ? tariffs.storage.diameter.r17_19
      : tariffs.storage.diameter.r20_24;

  return storagePrice + (state.addons.storageDelivery ? tariffs.storage.delivery : 0);
};

const getSelectedServicePrice = () => {
  switch (state.service) {
    case 'puncture':
      return getPuncturePrice();
    case 'conditioner':
      return getConditionerPrice();
    case 'storage':
      return getStoragePrice();
    case 'seasonal':
    default:
      return getSeasonalPrice();
  }
};

const getCalculatorResult = () => {
  const time = getArrivalTimeByAddress(state.address);

  if (state.serviceLocation.status === 'denied') {
    return {
      price: null,
      time,
      unavailable: true,
    };
  }

  return {
    price: getActiveCalloutTariff() + getSelectedServicePrice(),
    time,
    unavailable: false,
  };
};

const hasEnteredAddress = () => state.address.length > 0;

const canContinueOrder = (result = getCalculatorResult()) => {
  return hasEnteredAddress() && !result.unavailable;
};

const getPhoneInput = () => {
  const orderForm = document.querySelector('[data-order-form]');

  return orderForm?.querySelector('input[type="tel"]') || document.querySelector('input[type="tel"]');
};

const formatTimeRange = (time) => {
  return Array.isArray(time) ? `${time[0]}-${time[1]} мин` : '—';
};

const getActiveAddonKeys = () => {
  return (serviceAddonNames[state.service] || [])
    .filter((addonName) => state.addons[addonName]);
};

const getActiveAddonLabels = () => {
  return getActiveAddonKeys()
    .map((addonName) => addonLabels[addonName])
    .filter(Boolean);
};

const getOrderDetailRows = ({ includePhone = false } = {}) => {
  const result = getCalculatorResult();
  const rows = [
    ['Услуга', serviceLabels[state.service] || state.service],
    ['Адрес', state.address || '—'],
  ];

  if (servicesWithVehicle.includes(state.service)) {
    rows.push(['Тип автомобиля', carTypeLabels[state.carType] || state.carType]);
  }

  if (servicesWithDiameter.includes(state.service)) {
    rows.push(['Диаметр колес', state.diameter.toUpperCase()]);
  }

  rows.push(['Дополнительно', getActiveAddonLabels().join(', ') || 'Не выбрано']);
  rows.push(['Стоимость', Number.isFinite(result.price) ? `от ${formatPrice(result.price)} ₽` : '—']);
  rows.push(['Время прибытия', formatTimeRange(result.time)]);

  if (includePhone) {
    rows.push(['Телефон', getPhoneInput()?.value || '—']);
  }

  return rows;
};

const getOrderPayload = ({ includePhone = true } = {}) => {
  const result = getCalculatorResult();
  const hasVehicle = servicesWithVehicle.includes(state.service);
  const hasDiameter = servicesWithDiameter.includes(state.service);
  const addonKeys = getActiveAddonKeys();
  const addonLabels = getActiveAddonLabels();
  const servicePrice = Number.isFinite(result.price) ? getSelectedServicePrice() : null;
  const calloutPrice = Number.isFinite(result.price) ? getActiveCalloutTariff() : null;
  const location = state.serviceLocation;
  const phone = includePhone ? getPhoneInput()?.value || '' : '';
  const rows = getOrderDetailRows({ includePhone });
  const payload = {
    service: state.service,
    service_label: serviceLabels[state.service] || state.service,
    address: state.address,
    car_type: hasVehicle ? state.carType : '',
    car_type_label: hasVehicle ? carTypeLabels[state.carType] || state.carType : '',
    diameter: hasDiameter ? state.diameter : '',
    diameter_label: hasDiameter ? state.diameter.toUpperCase() : '',
    addons: addonLabels.join(', '),
    addon_keys: addonKeys.join(', '),
    price: Number.isFinite(result.price) ? String(result.price) : '',
    price_text: Number.isFinite(result.price) ? `\u043e\u0442 ${formatPrice(result.price)} \u20bd` : '',
    callout_price: Number.isFinite(calloutPrice) ? String(calloutPrice) : '',
    service_price: Number.isFinite(servicePrice) ? String(servicePrice) : '',
    arrival_time: formatTimeRange(result.time),
    tariff: isNightTariff() ? 'night' : 'day',
    is_night_tariff: isNightTariff() ? 'yes' : 'no',
    location_status: location.status,
    inside_mkad: location.status === 'ready' ? location.insideMkad ? 'yes' : 'no' : '',
    distance_outside_mkad_km: location.status === 'ready' ? String(location.distanceOutsideKm || 0) : '',
    coords: Array.isArray(location.coords) ? location.coords.join(', ') : '',
    phone,
    details: rows.map(([label, value]) => `${label}: ${value}`).join('\n'),
  };

  return {
    ...payload,
    payload: JSON.stringify(payload),
  };
};

const renderDetailsList = (container, rows) => {
  if (!container) {
    return;
  }

  container.replaceChildren();

  rows.forEach(([label, value]) => {
    const row = document.createElement('div');
    const term = document.createElement('dt');
    const description = document.createElement('dd');

    row.className = 'order-details__row';
    term.className = 'order-details__label';
    description.className = 'order-details__value';
    term.textContent = label;
    description.textContent = value;

    row.append(term, description);
    container.append(row);
  });
};

const renderOrderDetails = () => {
  renderDetailsList(document.querySelector('[data-order-details]'), getOrderDetailRows());
  syncOrderHiddenFields();
};

const renderSuccessDetails = () => {
  renderDetailsList(document.querySelector('[data-success-summary]'), submittedOrderRows || getOrderDetailRows({ includePhone: true }));
  syncOrderHiddenFields();
};

const syncOrderHiddenFields = () => {
  const orderForm = document.querySelector('[data-order-form]');

  if (!orderForm) {
    return;
  }

  const payload = getOrderPayload({ includePhone: true });
  const visiblePhoneInput = orderForm.querySelector('input[type="tel"]');

  orderForm.querySelectorAll('[data-order-field]').forEach((field) => {
    field.value = payload[field.dataset.orderField] ?? '';
  });

  orderForm.querySelectorAll('[name]').forEach((field) => {
    if (field === visiblePhoneInput) {
      return;
    }

    const fieldKey = orderFieldNames[field.name];

    if (fieldKey) {
      field.value = payload[fieldKey] ?? '';
    }
  });
};

const showOrderStep = (step) => {
  const calculator = document.querySelector('[data-calculator]');
  const calculatorForm = document.querySelector('[data-calculator-form]');
  const orderForm = document.querySelector('[data-order-form]');
  const success = document.querySelector('[data-order-success]');

  if (calculator) {
    calculator.classList.toggle('calculator--step-calculator', step === 'calculator');
    calculator.classList.toggle('calculator--step-confirm', step === 'confirm');
    calculator.classList.toggle('calculator--step-success', step === 'success');
  }

  if (calculatorForm) {
    calculatorForm.hidden = step !== 'calculator';
  }

  if (orderForm) {
    orderForm.hidden = step !== 'confirm';
  }

  if (success) {
    success.hidden = step !== 'success';
  }
};

const isContactForm7Form = (form) => {
  return Boolean(form?.classList?.contains('wpcf7-form') || form?.closest?.('.wpcf7'));
};

const isOrderFormEvent = (event) => {
  return Boolean(event.target?.closest?.('[data-order-form]'));
};

const getPhoneDigits = (value) => {
  let digits = String(value || '').replace(/\D/g, '');

  if (!digits) {
    return '';
  }

  if (digits[0] === '8') {
    digits = `7${digits.slice(1)}`;
  }

  if (digits[0] !== '7') {
    digits = `7${digits}`;
  }

  return digits.slice(0, 11);
};

const initPhoneMask = (phoneInput) => {
  if (!phoneInput || phoneInput.inputmask) {
    return;
  }

  Inputmask({
    mask: '+7 (999) 999-99-99',
    showMaskOnHover: false,
  }).mask(phoneInput);
};

const isPhoneComplete = (value) => {
  const phoneInput = getPhoneInput();

  if (phoneInput?.inputmask) {
    return phoneInput.inputmask.isComplete();
  }

  return getPhoneDigits(value).length === 11;
};

const syncOrderSendButton = () => {
  const phoneInput = getPhoneInput();
  const sendButton = document.querySelector('[data-order-send]');

  if (sendButton) {
    sendButton.disabled = !isPhoneComplete(phoneInput?.value);
  }
};

const getCssNumber = (value, fallback = 0) => {
  const number = Number.parseFloat(value);

  return Number.isFinite(number) ? number : fallback;
};

const getCalculatorCollapsedHeight = (calculator) => {
  const handle = calculator?.querySelector('[data-calculator-toggle]');
  const orderForm = calculator?.querySelector('[data-order-form]');
  const isConfirmStep = orderForm && !orderForm.hidden;

  if (!calculator || !handle) {
    return 128;
  }

  const calculatorStyles = window.getComputedStyle(calculator);
  const paddingTop = getCssNumber(calculatorStyles.paddingTop);
  const paddingBottom = getCssNumber(calculatorStyles.paddingBottom);

  if (isConfirmStep) {
    const phoneField = orderForm.querySelector('.phone-field');
    const footer = orderForm.querySelector('.order-confirm__footer');

    if (!phoneField || !footer) {
      return 240;
    }

    const formStyles = window.getComputedStyle(orderForm);
    const formGap = getCssNumber(formStyles.rowGap || formStyles.gap, 0);
    const height = paddingTop + handle.offsetHeight + phoneField.offsetHeight + formGap + footer.offsetHeight + paddingBottom;

    return Math.ceil(height);
  }

  if (!hasEnteredAddress()) {
    return 128;
  }

  const form = calculator.querySelector('[data-calculator-form]');
  const addressField = calculator.querySelector('.address-field');
  const footer = calculator.querySelector('.calculator__footer');

  if (!form || !addressField || !footer) {
    return 292;
  }

  const formStyles = window.getComputedStyle(form);
  const formGap = getCssNumber(formStyles.rowGap || formStyles.gap, 0);
  const height = paddingTop + handle.offsetHeight + addressField.offsetHeight + formGap + footer.offsetHeight + paddingBottom;

  return Math.ceil(height);
};

const syncCalculatorSheet = () => {
  const calculator = document.querySelector('[data-calculator]');
  const toggleButton = document.querySelector('[data-calculator-toggle]');
  const orderForm = document.querySelector('[data-order-form]');
  const currentAddress = state.address;
  const hasAddress = hasEnteredAddress();
  const isConfirmStep = orderForm && !orderForm.hidden;

  if (!calculator) {
    return;
  }

  if (hasAddress && currentAddress !== calculatorSheetAddress) {
    isCalculatorSheetExpanded = true;
  }

  if (isDesktopCalculatorLayout()) {
    isCalculatorSheetExpanded = true;
  }

  calculatorSheetAddress = currentAddress;

  const isExpanded = isCalculatorSheetExpanded;
  const hasVisibleDetails = isExpanded;

  calculator.classList.toggle('calculator--expanded', isExpanded);
  calculator.classList.toggle('calculator--collapsed', !isExpanded);
  calculator.classList.toggle('calculator--has-address', hasAddress);
  calculator.classList.toggle('calculator--has-compact-confirm', Boolean(isConfirmStep));
  calculator.classList.toggle('calculator--details-visible', hasVisibleDetails);

  if (hasAddress || isConfirmStep) {
    calculator.style.setProperty('--calculator-collapsed-height', `${getCalculatorCollapsedHeight(calculator)}px`);
  } else {
    calculator.style.removeProperty('--calculator-collapsed-height');
  }

  if (toggleButton) {
    toggleButton.setAttribute('aria-expanded', String(isExpanded));
    toggleButton.setAttribute('aria-label', isExpanded ? 'Свернуть калькулятор' : 'Развернуть калькулятор');
  }
};

const setCalculatorSheetExpanded = (isExpanded) => {
  isCalculatorSheetExpanded = isDesktopCalculatorLayout() || Boolean(isExpanded);
  calculatorSheetAddress = state.address;
  syncCalculatorSheet();
};

export const expandCalculatorSheet = () => {
  setCalculatorSheetExpanded(true);
};

export const collapseCalculatorSheet = () => {
  setCalculatorSheetExpanded(false);
};

const getCalculatorSheetHeights = () => {
  const viewportHeight = window.innerHeight || document.documentElement.clientHeight || 0;
  const calculator = document.querySelector('[data-calculator]');
  const collapsedHeight = getCalculatorCollapsedHeight(calculator);

  return {
    collapsed: collapsedHeight,
    expanded: Math.max(220, Math.min(viewportHeight * 0.64, viewportHeight - 24)),
  };
};

const setCalculatorSheetDragHeight = (calculator, height) => {
  calculator.style.setProperty('--calculator-current-height', `${Math.round(height)}px`);
};

const resetCalculatorSheetDrag = (calculator) => {
  calculator.style.removeProperty('--calculator-current-height');
  calculator.classList.remove('calculator--dragging', 'calculator--drag-preview-expanded');
};

const getCalculatorSheetDragHeight = (dragState, deltaY) => {
  return clamp(dragState.startHeight - deltaY, dragState.heights.collapsed, dragState.heights.expanded);
};

const initCalculatorSheetDrag = ({ calculator, toggleButton }) => {
  if (!calculator || !toggleButton) {
    return;
  }

  let dragState = null;

  const finishDrag = (event, { isCancelled = false } = {}) => {
    if (!dragState || dragState.pointerId !== event.pointerId) {
      return;
    }

    const deltaY = dragState.lastY - dragState.startY;
    const nextHeight = getCalculatorSheetDragHeight(dragState, deltaY);
    const hasMoved = dragState.hasMoved;

    try {
      toggleButton.releasePointerCapture?.(event.pointerId);
    } catch {
      // Pointer capture may already be released by the browser.
    }

    resetCalculatorSheetDrag(calculator);

    if (hasMoved) {
      suppressCalculatorToggleClick = true;
      toggleButton.blur();
      window.setTimeout(() => {
        suppressCalculatorToggleClick = false;
      }, 0);
    }

    if (isCancelled) {
      setCalculatorSheetExpanded(dragState.startsExpanded);
      dragState = null;
      return;
    }

    if (!hasEnteredAddress()) {
      if (nextHeight > dragState.heights.collapsed + calculatorSheetDragThreshold) {
        setCalculatorSheetExpanded(true);
      } else {
        setCalculatorSheetExpanded(false);
      }

      dragState = null;
      return;
    }

    if (dragState.startsExpanded) {
      setCalculatorSheetExpanded(deltaY < calculatorSheetDragThreshold);
    } else {
      setCalculatorSheetExpanded(deltaY <= -calculatorSheetDragThreshold);
    }

    dragState = null;
  };

  toggleButton.addEventListener('pointerdown', (event) => {
    if (event.button !== undefined && event.button !== 0) {
      return;
    }

    dragState = {
      pointerId: event.pointerId,
      startY: event.clientY,
      lastY: event.clientY,
      startsExpanded: isCalculatorSheetExpanded,
      hasMoved: false,
      heights: getCalculatorSheetHeights(),
    };
    dragState.startHeight = dragState.startsExpanded ? dragState.heights.expanded : dragState.heights.collapsed;

    calculator.classList.add('calculator--dragging');

    try {
      toggleButton.setPointerCapture?.(event.pointerId);
    } catch {
      // Some browsers can refuse capture for synthetic pointer events.
    }
  });

  toggleButton.addEventListener('pointermove', (event) => {
    if (!dragState || dragState.pointerId !== event.pointerId) {
      return;
    }

    const deltaY = event.clientY - dragState.startY;

    dragState.lastY = event.clientY;
    dragState.hasMoved = dragState.hasMoved || Math.abs(deltaY) > calculatorSheetMoveThreshold;

    if (!dragState.hasMoved) {
      return;
    }

    const nextHeight = getCalculatorSheetDragHeight(dragState, deltaY);

    if (!dragState.startsExpanded && nextHeight > dragState.heights.collapsed + 24) {
      calculator.classList.add('calculator--drag-preview-expanded');
    } else if (!dragState.startsExpanded) {
      calculator.classList.remove('calculator--drag-preview-expanded');
    }

    setCalculatorSheetDragHeight(calculator, nextHeight);
    event.preventDefault();
  });

  toggleButton.addEventListener('pointerup', (event) => {
    finishDrag(event);
  });

  toggleButton.addEventListener('pointercancel', (event) => {
    finishDrag(event, {
      isCancelled: true,
    });
  });
};

export const updateSummary = () => {
  const priceOutput = document.querySelector('[data-price-output]');
  const pricePrefix = document.querySelector('[data-price-prefix]');
  const priceUnit = document.querySelector('[data-price-unit]');
  const timeOutput = document.querySelector('[data-time-output]');
  const heroStatusText = document.querySelector('[data-hero-status-text]');
  const timeUnits = document.querySelectorAll('[data-time-unit]');
  const submitButton = document.querySelector('[data-order-submit]');
  const result = getCalculatorResult();
  const hasPrice = Number.isFinite(result.price);
  const hasTime = Array.isArray(result.time);
  const timeText = hasTime ? `${result.time[0]}-${result.time[1]}` : '—';

  syncCalculatorSheet();

  if (priceOutput) {
    priceOutput.textContent = hasPrice ? formatPrice(result.price) : '—';
  }

  if (pricePrefix) {
    pricePrefix.hidden = !hasPrice;
  }

  if (priceUnit) {
    priceUnit.hidden = !hasPrice;
  }

  if (timeOutput) {
    timeOutput.textContent = timeText;
  }

  if (heroStatusText) {
    heroStatusText.innerHTML = hasTime ? `Ближайший мастер <br>в <span>${timeText}</span> мин от вас` : 'Укажите адрес для расчёта';
  }

  timeUnits.forEach((unit) => {
    unit.hidden = !hasTime;
  });

  if (submitButton) {
    submitButton.disabled = !canContinueOrder(result);
  }

  if (!document.querySelector('[data-order-form]')?.hidden) {
    renderOrderDetails();
  }
};

const visibleServiceSections = {
  seasonal: ['vehicle', 'diameter', 'seasonal-addons', 'vehicle-addons'],
  puncture: ['vehicle', 'diameter', 'vehicle-addons'],
  conditioner: ['vehicle', 'conditioner-addons'],
  storage: ['diameter', 'storage-addons'],
};

const syncCarTypeOptions = () => {
  const availableOptions = serviceCarTypeOptions[state.service] || [];
  const carTypeGroup = document.querySelector('[data-option-group="carType"]');

  if (!carTypeGroup) {
    return;
  }

  if (availableOptions.length && !availableOptions.includes(state.carType)) {
    state.carType = availableOptions[0];
  }

  carTypeGroup.querySelectorAll('[data-option-name="carType"]').forEach((button) => {
    const isAvailable = availableOptions.includes(button.dataset.optionValue);
    const isActive = button.dataset.optionValue === state.carType;

    button.hidden = !isAvailable;
    button.classList.toggle('option-card--active', isActive && isAvailable);
    button.setAttribute('aria-pressed', String(isActive && isAvailable));
  });
};

const updateCalculatorVisibility = () => {
  const activeSections = visibleServiceSections[state.service] || visibleServiceSections.seasonal;

  document.querySelectorAll('[data-service-section]').forEach((section) => {
    section.hidden = !activeSections.includes(section.dataset.serviceSection);
  });

  syncCarTypeOptions();
};

const syncAddonCards = () => {
  document.querySelectorAll('[data-addon-name]').forEach((input) => {
    const isChecked = Boolean(state.addons[input.dataset.addonName]);

    input.checked = isChecked;
    input.closest('.checkbox-card')?.classList.toggle('checkbox-card--checked', isChecked);
  });
};

const setOption = (button) => {
  const optionName = button.dataset.optionName;
  const optionValue = button.dataset.optionValue;
  const group = button.closest('[data-option-group]');

  if (!optionName || !optionValue || !group) {
    return;
  }

  state[optionName] = optionValue;

  group.querySelectorAll('[data-option-name]').forEach((item) => {
    const isActive = item === button;

    item.classList.toggle('option-card--active', isActive);
    item.setAttribute('aria-pressed', String(isActive));
  });

  if (optionName === 'service') {
    updateCalculatorVisibility();
  }

  updateSummary();
};

const syncDiameterSlider = ({ shouldUpdateSummary = false } = {}) => {
  const diameterRange = document.querySelector('[data-diameter-range]');
  const diameterOutput = document.querySelector('[data-diameter-output]');
  const diameterValue = document.querySelector('[data-diameter-value]');
  const diameterSlider = document.querySelector('[data-diameter-slider]');

  if (!diameterRange) {
    return;
  }

  const maxIndex = diameterOptions.length - 1;
  const index = clamp(Number(diameterRange.value), 0, maxIndex);
  const option = diameterOptions[index];

  diameterRange.max = String(maxIndex);
  diameterRange.value = String(index);
  state.diameter = option.value;

  if (diameterOutput) {
    diameterOutput.textContent = option.label;
  }

  if (diameterValue) {
    diameterValue.value = option.value;
  }

  if (diameterSlider) {
    diameterSlider.style.setProperty('--diameter-progress', `${(index / maxIndex) * 100}%`);
  }

  if (shouldUpdateSummary) {
    updateSummary();
  }
};

export const initCalculator = () => {
  document.addEventListener('click', (event) => {
    const optionButton = event.target.closest('[data-option-name]');

    if (optionButton) {
      setOption(optionButton);
    }
  });

  const diameterRange = document.querySelector('[data-diameter-range]');
  const form = document.querySelector('[data-calculator-form]');
  const orderForm = document.querySelector('[data-order-form]');
  const phoneInput = getPhoneInput();
  const calculatorToggle = document.querySelector('[data-calculator-toggle]');
  const addressInput = document.querySelector('[data-address-input]');
  const orderBackButton = document.querySelector('[data-order-back]');
  const successBackButton = document.querySelector('[data-order-success-back]');

  initPhoneMask(phoneInput);

  addressInput?.addEventListener('focus', () => {
    setCalculatorSheetExpanded(true);
  });

  calculatorToggle?.addEventListener('click', (event) => {
    if (suppressCalculatorToggleClick) {
      event.preventDefault();
      suppressCalculatorToggleClick = false;
      return;
    }

    const shouldExpand = !isCalculatorSheetExpanded;

    setCalculatorSheetExpanded(shouldExpand);
  });

  initCalculatorSheetDrag({
    calculator: document.querySelector('[data-calculator]'),
    toggleButton: calculatorToggle,
  });

  const desktopLayoutMedia = window.matchMedia?.(calculatorDesktopQuery);

  desktopLayoutMedia?.addEventListener?.('change', () => {
    if (isDesktopCalculatorLayout()) {
      isCalculatorSheetExpanded = true;
    }

    syncCalculatorSheet();
  });

  diameterRange?.addEventListener('input', () => {
    syncDiameterSlider({
      shouldUpdateSummary: true,
    });
  });

  form?.addEventListener('change', (event) => {
    const addonInput = event.target.closest('[data-addon-name]');

    if (!addonInput) {
      return;
    }

    state.addons[addonInput.dataset.addonName] = addonInput.checked;
    syncAddonCards();
    updateSummary();
  });

  form?.addEventListener('submit', (event) => {
    event.preventDefault();

    if (!canContinueOrder()) {
      return;
    }

    if (!isCalculatorSheetExpanded) {
      setCalculatorSheetExpanded(true);
      return;
    }

    renderOrderDetails();
    showOrderStep('confirm');
    setCalculatorSheetExpanded(true);
    syncOrderSendButton();
    syncOrderHiddenFields();
    phoneInput?.focus();
  });

  orderForm?.addEventListener('submit', (event) => {
    if (!isPhoneComplete(phoneInput?.value)) {
      event.preventDefault();
      event.stopPropagation();
      phoneInput?.focus();
      return;
    }

    syncOrderHiddenFields();
    submittedOrderRows = getOrderDetailRows({ includePhone: true });

    if (!isContactForm7Form(event.target)) {
      event.preventDefault();
    }
  }, true);

  document.addEventListener('wpcf7mailsent', (event) => {
    if (!isOrderFormEvent(event)) {
      return;
    }

    renderSuccessDetails();
    showOrderStep('success');
  });

  phoneInput?.addEventListener('input', () => {
    syncOrderSendButton();
    syncOrderHiddenFields();
  });

  phoneInput?.addEventListener('blur', () => {
    syncOrderSendButton();
    syncOrderHiddenFields();
  });

  orderBackButton?.addEventListener('click', () => {
    showOrderStep('calculator');
    updateSummary();
  });

  successBackButton?.addEventListener('click', () => {
    showOrderStep('calculator');
    updateSummary();
  });

  showOrderStep('calculator');
  syncDiameterSlider();
  syncAddonCards();
  updateCalculatorVisibility();
  syncOrderSendButton();
  updateSummary();
};
