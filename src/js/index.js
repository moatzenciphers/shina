import '../scss/main.scss';

import { initCalculator } from './calculator';
import { initYandexAddress } from './map';
import { initLanding } from './landing';
import { initQuickCall } from './quick-call';
import { initReviewSlider } from './review-slider';
import { initMapPanel } from './map-panel';
import { initGalleryGrid } from './gallery-grid';
import { initSplitForm } from './split-form';
import { initSiteFooter } from './site-footer';

document.addEventListener('DOMContentLoaded', () => {
  initCalculator();
  initYandexAddress();
  initLanding();
  initQuickCall();
  initReviewSlider();
  initMapPanel();
  initGalleryGrid();
  initSplitForm();
  initSiteFooter();
});
