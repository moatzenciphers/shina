import '../scss/main.scss';

import { initCalculator } from './calculator';
import { initYandexAddress } from './map';
import { initLanding } from './landing';
import { initQuickCall } from './quick-call';
import { initReviewSlider } from './review-slider';
import { initMapPanel } from './map-panel';

document.addEventListener('DOMContentLoaded', () => {
  initCalculator();
  initYandexAddress();
  initLanding();
  initQuickCall();
  initReviewSlider();
  initMapPanel();
});
